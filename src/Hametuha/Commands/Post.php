<?php

namespace Hametuha\Commands;


use Hametuha\Model\Jobs;
use Hametuha\Sharee\Master\Address;
use WPametu\Utility\Command;
use cli\Table;

class Post extends Command {

	const COMMAND_NAME = 'hampost';

	/**
	 * Show statistic for specific condition
	 *
	 * ## OPTIONS
	 *
	 * : <taxonomy>
	 *   taxonomy
	 *
	 * : <term>
	 *   Term ID
	 *
	 * @synopsis <taxonomy> <term>
	 * @param array $args
	 * @param array $assoc
	 */
	public function statistic( $args, $assoc ) {
		list( $taxonomy, $term_id ) = $args;
		$term                       = get_term_by( 'id', $term_id, $taxonomy );
		if ( ! $term ) {
			self::e( sprintf( 'failed to get term %d of %s', $term_id, $taxonomy ) );
		}
		$posts = get_posts([
			'post_type'      => 'post',
			'post_status'    => 'any',
			'tax_query'      => [
				[
					'taxonomy' => $taxonomy,
					'terms'    => (int) $term_id,
				],
			],
			'posts_per_page' => -1,
		]);
		if ( ! $posts ) {
			self::e( 'No post found.' );
		}
		$table = new Table();
		$table->setHeaders( [ 'ID', 'Length', '' ] );
		$total  = 0;
		$length = 0;
		foreach ( $posts as $post ) {
			$length++;
			$content     = strip_tags( apply_filters( 'the_content', $post->post_content ) );
			$char_length = mb_strlen( $content, 'utf-8' );
			$total      += $char_length;
			$table->addRow( [ $post->ID, $char_length, '-' ] );
		}
		$table->setFooters( [ sprintf( '%d posts', $length ), sprintf( 'Total: %d', $total ), sprintf( 'Average: %d', round( $total / $length ) ) ] );
		$table->display();
	}

	/**
	 * Compile post to XML
	 *
	 * ## OPTIONS
	 *
	 * : <taxonomy>
	 *   taxonomy
	 *
	 * : <term>
	 *   Term ID
	 *
	 * : <format>
	 *   1 of xml, text, tag, csv. Default xml.
	 *
	 * : [--endmark]
	 *   If set, add end mark.
	 *
	 * : [--endmark-string=<endmark-string>]
	 *   Set to change default endmark "——了"
	 *
	 * @synopsis <taxonomy> <term> [--format=<format>] [--endmark] [--endmark-string=<endmark-string>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function compile( $args, $assoc ) {
		list( $taxonomy, $term_id ) = $args;
		$format                     = $assoc['format'] ?? 'xml';
		if ( ! in_array( $format, [ 'xml', 'text', 'tags', 'csv' ] ) ) {
			self::e( sprintf( 'Format %s is wrong.', $format ) );
		}
		$term = get_term_by( 'id', $term_id, $taxonomy );
		if ( ! $term ) {
			self::e( sprintf( 'failed to get term %d of %s', $term_id, $taxonomy ) );
		}
		// End mark.
		$endmark = ! empty( $assoc['endmark'] ) ? '<p style="text-align: right">——了</p>' : '';
		if ( $endmark && ! empty( $assoc['endmark-string'] ) ) {
			$endmark = $assoc['endmark-string'];
		}
		$upload_dir = wp_upload_dir();
		$dir        = $upload_dir['basedir'] . '/indesign/' . $taxonomy . '/' . $term->slug;
		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0755, true );
		}
		if ( ! is_dir( $dir ) ) {
			self::e( sprintf( 'Directory %s missed.', $dir ) );
		}
		$posts = get_posts([
			'post_type'      => 'post',
			'post_status'    => 'any',
			'tax_query'      => [
				[
					'taxonomy' => $taxonomy,
					'terms'    => (int) $term_id,
				],
			],
			'posts_per_page' => -1,
		]);
		if ( ! $posts ) {
			self::e( 'No post found.' );
		}
		$tags  = [];
		$lines = 0;
		foreach ( $posts as $post ) {
			switch ( $format ) {
				case 'xml':
					$xml = $this->to_xml( $post );
					file_put_contents( "{$dir}/post-{$post->ID}.xml", $xml );
					echo '.';
					break;
				case 'text':
					// Post content.
					$tagged_text = "<UNICODE-MAC>\n" . $this->to_text( $post, $endmark );
					file_put_contents( "{$dir}/post-{$post->ID}.txt", mb_convert_encoding( str_replace( "\n", "\r", $tagged_text ), 'UTF-16BE', 'utf-8' ) );
					self::l( sprintf( '#%1$d %3$s「%2$s」', $post->ID, get_the_title( $post ), get_the_author_meta( 'display_name', $post->post_author ) ) );
					// Footernotes.
					ob_start();
					hametuha_footer_notes( '<aside>', '</aside>', '', $post );
					$footernote = trim( ob_get_contents() );
					ob_end_clean();
					if ( $footernote ) {
						// Rmove footer note link.
						$footernote = preg_replace( '#<a class="footernote-link"[^>]+>(.*?)</a>#u', '', $footernote );
						file_put_contents( "{$dir}/post-{$post->ID}-footernote.xml", '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" . $footernote );
					}
					// Excerpt.
					if ( ! empty( $post->post_excerpt ) ) {
						file_put_contents( "{$dir}/post-{$post->ID}-excerpt.txt", $post->post_excerpt );
					}
					break;
				case 'csv':
					if ( ! $lines ) {
						self::l( 'お届け先郵便番号,お届け先氏名,お届け先敬称,お届け先住所1行目,お届け先住所2行目,お届け先住所3行目,お届け先住所4行目,内容品' );
						$lines++;
					}
					$line = [];
					foreach ( [ 'zip', 'name', 'address', 'address2' ] as $key ) {
						$value = get_user_meta( $post->post_author, '_billing_' . $key, true );
						switch ( $key ) {
							case 'name':
								$line[] = $value;
								$line[] = '様';
								break;
							case 'address':
								if ( preg_match( '/^(北海道|京都|東京都|大阪府|.*?県)(.*)$/u', $value, $matches ) ) {
									$line[] = $matches[1];
									$line[] = $matches[2];
								} else {
									$line[] = '';
									$line[] = '';
								}
								break;
							case 'address2':
								$line[] = '';
								$line[] = 'XXXXXX';
								break;
							case 'zip':
								$line[] = preg_replace( '/\D/u', '', $value );
								break;
							default:
								$line[] = $value;
								break;
						}
					}
					self::l( implode( ',', $line ) );
					break;
				case 'tags':
					foreach ( $this->get_tags( $post ) as $tag ) {
						$attributes        = explode( ' ', $tag );
						$tag_name          = array_shift( $attributes );
						$tags[ $tag_name ] = implode( ' ', $attributes );
					}
					break;
			}
		}
		if ( $tags ) {
			$table = new \cli\Table();
			$table->setHeaders( [ 'Tag Name', 'Attributes' ] );
			foreach ( $tags as $tag_name => $attr ) {
				$table->addRow( [ $tag_name, $attr ?: 'empty' ] );
			}
			$table->display();
		}
		self::l( '' );
		self::s( 'Done.' );
	}

	/**
	 * Get tags of texts.
	 *
	 * @param null|int|\WP_Post $post
	 * @return array
	 */
	protected function get_tags( $post = null ) {
		$post = get_post( $post );
		$tags = [];
		if ( preg_match_all( '#<([^/][^>]+)>#u', $post->post_content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$tags[] = $match[1];
			}
		}
		return array_unique( $tags );
	}

	/**
	 * Get tagged text for InDesign.
	 *
	 * @param null|int|\WP_Post $post    Post object to compile.
	 * @param string            $endmark Add endmark if set.
	 *
	 * @return string
	 */
	protected function to_text( $post = null, $endmark = '' ) {
		$post = get_post( $post );
		// Fix double space.
		$content = str_replace( "\r\n", "\n", $post->post_content );
		$content = str_replace( "\n\n", "\n", $content );
		// Remove Image and link
		// TODO: link should be saved as footernote.
		$content = preg_replace( '#<a[^>]+>(.*?)</a>#u', '$1', $content );
		$content = preg_replace( '#<img[^>]+>#u', '', $content );

		// Remove empty line.
		$content = trim( implode( "\n", array_map( function( $line ) {
			return '&nbsp;' === $line ? '' : $line;
		}, explode( "\n", $content ) ) ) );
		if ( $endmark ) {
			$content .= "\n" . $endmark;
		}
		// Convert Footernote.
		$note_id = 0;
		$content = preg_replace_callback( '#<small class="footernote-ref">(.*?)</small>#u', function( $matches ) use ( &$note_id ) {
			$note_id++;
			return sprintf( '<CharStyle:FooterNoteRef>*%d<CharStyle:>', $note_id );
		}, $content );

		// Inline elemnets.
		foreach ( [
			'#<strong>(.*?)</strong>#u'              => '<CharStyle:Strong>$1<CharStyle:>',
			'#<strong class="text-emphasis">([^<]+)</strong>#u' => '<CharStyle:StrongSesami>$1<CharStyle:>',
			'#<em>([^<]+)</em>#u'                    => '<CharStyle:Emphasis>$1<CharStyle:>',
			'#<s>(.*?)</s>#u'                        => '<CharStyle:Strike>$1<CharStyle:>',
			'#<cite>([^<]+)</cite>#u'                => '<CharStyle:Cite>$1<CharStyle:>',
			'#<span class="text-emphasis">([^<]+)</span>#u' => '<CharStyle:EmphasisSesami>$1<CharStyle:>',
			'#<del>([^<]+)</del>#u'                  => '<CharStyle:Del>$1<CharStyle:>',
			'#<ruby>([^<]+)<rt>([^>]+)</rt></ruby>#' => '<cMojiRuby:0><cRuby:1><cRubyString:$2>$1<cMojiRuby:><cRuby:><cRubyString:>',
			'#<small>([^<]+)</small>#u'              => '〔<CharStyle:Notes>$1<CharStyle:>〕',
		] as $regexp => $converted ) {
			$content = preg_replace( $regexp, $converted, $content );
		}

		// Convert Dashes.
		foreach ( [ '—', '―' ] as $char ) {
			$content = str_replace( $char . $char, sprintf( '<CharStyle:Dash>%s<CharStyle:>', '―' ), $content );
		}

		// UL, OL
		foreach ( [
			'#<ul>(.*?)</ul>#us' => 'UnorderedList',
			'#<ol>(.*?)</ol>#us' => 'OrderedList',
		] as $preg => $style ) {
			$content = preg_replace_callback( $preg, function( $matches ) use ( $style ) {
				list( $match, $lists ) = $matches;
				$lists                 = preg_replace( '#^\s*?<li>(.*?)</li>#um', '<ParaStyle:' . $style . '>$1', $lists );
				return $lists;
			}, $content );
		}

		// Headings
		$content = preg_replace( '#<h(\d)>([^<]+)</h(\d)>#u', '<ParaStyle:Heading$1>$2', $content );
		// Block quote, Aside, pre.
		foreach ( [ 'Aside', 'BlockQuote', 'Pre' ] as $tag ) {
			$tag_name = strtolower( $tag );
			$content  = preg_replace_callback( "#<{$tag_name}>(.*?)</{$tag_name}>#us", function( $match ) use ( $tag ) {
				$lines = trim( str_replace( "\n\n", "\n", $match[1] ) );
				return implode( "\n", array_map( function( $line ) use ( $tag ) {
					return sprintf( '<ParaStyle:%s>', ucfirst( $tag ) ) . $line;
				}, explode( "\n", $lines ) ) );
			}, $content );
		}
		// paragraph
		foreach ( [
			'#<p style="text-align:([^"]+)">(.*?)</p>#us' => function( $match ) {
				$align = ucfirst( trim( str_replace( ';', '', $match[1] ) ) );
				return sprintf( '<ParaStyle:Align%s>%s', $align, $match[2] );
			},
			'#<p style="(text-indent|padding-left):([^"]+)">(.*?)</p>#us' => function( $match ) {
				$indent = preg_replace( '/\D/', '', $match[2] );
				return $match[1] ? sprintf( '<ParaStyle:Indent%d>%s', str_replace( ';', '', $indent ), $match[3] ) : $match[3];
			},
		] as $regexp => $callback ) {
			$content = preg_replace_callback( $regexp, $callback, $content );
		}
		// Remove span
		$content = preg_replace( '#<span([^>]*?>)(.*?)</span>#u', '$2', $content );
		// Add normal style.
		return implode( "\n", array_map( function( $line ) {
			if ( 0 === strpos( $line, '<ParaStyle' ) ) {
				return $line;
			} else {
				return '<ParaStyle:Normal>' . $line;
			}
		}, explode( "\n", $content ) ) );
	}

	/**
	 * Get XML for InDesign
	 *
	 * @param null|int|\WP_Post $post
	 *
	 * @return string
	 */
	protected function to_xml( $post = null ) {
		$post = get_post( $post );
		setup_postdata( $post );
		$category = '創作';
		if ( $categories = get_the_category( $post ) ) {
			foreach ( $categories as $cat ) {
				$category = $cat->name;
			}
		}
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Root>
<title>%1$s</title>
<author>%2$s</author>
<category>%5$s</category>
<excerpt>%3$s</excerpt>
<article>%4$s</article>
</Root>';
		$xml = sprintf(
			$xml,
			get_the_title( $post ),
			get_the_author_meta( 'display_name', $post->post_author ),
			get_the_excerpt( $post ),
			apply_filters( 'the_content', $post->post_content ),
			$category
		);
		// 空白を変更
		$xml = str_replace( '&nbsp;', ' ', $xml );
		// センター寄せを変更
		$xml = str_replace( ' style="text-align: center"', ' class="text-center"', $xml );
		// 終了
		return $xml;
	}

	/**
	 * 試しにFacebookページとして投稿を行う
	 *
	 * @param array $args
	 * @synopsis <job_id> <message>
	 */
	public function share_pic( $args ) {
		list( $job_id, $message ) = $args;
		$jobs                     = Jobs::get_instance();
		$job                      = $jobs->get( $job_id );
		if ( ! $job || 'text_to_image' != $job->job_key ) {
			self::e( 'エラー' );
		}
		try {
			$api = gianism_fb_page_api();
			if ( is_wp_error( $api ) ) {
				throw new \Exception( $api->get_error_message(), $api->get_error_code() );
			}
			$response = $api->post( 'me/feed', [
				'message' => $message,
			] );
			self::s( $response->getGraphNode()->getField( 'id' ) );
		} catch ( \Exception $e ) {
			self::e( $e->getCode() . ': ' . $e->getMessage() );
		}
	}
}
