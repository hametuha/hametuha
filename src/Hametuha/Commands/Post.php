<?php

namespace Hametuha\Commands;


use Hametuha\Model\Jobs;
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
		$term = get_term_by( 'id', $term_id, $taxonomy );
		if ( ! $term ) {
			self::e( sprintf( 'failed to get term %d of %s', $term_id, $taxonomy ) );
		}
		$posts = get_posts([
			'post_type' => 'post',
			'post_status' => 'any',
		    'tax_query' => [
		    	[
		    		'taxonomy' => $taxonomy,
			        'terms' => (int) $term_id,
			    ],
		    ],
		    'posts_per_page' => -1,
		]);
		if ( ! $posts ) {
			self::e( 'No post found.' );
		}
		$table = new Table();
		$table->setHeaders( [ 'ID', 'Length', '' ] );
		$total = 0;
		$length = 0;
		foreach ( $posts as $post ) {
			$length++;
			$content = strip_tags( apply_filters( 'the_content', $post->post_content ) );
			$char_length = mb_strlen( $content, 'utf-8' );
			$total += $char_length;
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
	 *   1 of xml, text, tag. Default xml.
	 *
	 * @synopsis <taxonomy> <term> [--format=<format>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function compile( $args, $assoc ) {
		list( $taxonomy, $term_id ) = $args;
		$format = isset( $assoc['format'] ) ? $assoc['format'] : 'xml';
		if ( ! in_array( $format, [ 'xml', 'text', 'tags' ] ) ) {
			self::e( sprintf( 'Format %s is wrong.', $format ) );
		}
		$term = get_term_by( 'id', $term_id, $taxonomy );
		if ( ! $term ) {
			self::e( sprintf( 'failed to get term %d of %s', $term_id, $taxonomy ) );
		}
		$upload_dir = wp_upload_dir();
		$dir = $upload_dir['basedir'] . '/indesign/' . $taxonomy . '/' . $term->slug;
		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0755, true );
		}
		if ( ! is_dir( $dir ) ) {
			self::e( sprintf( 'Directory %s missed.', $dir ) );
		}
		$posts = get_posts([
			'post_type' => 'post',
			'post_status' => 'any',
			'tax_query' => [
				[
					'taxonomy' => $taxonomy,
					'terms' => (int) $term_id,
				],
			],
			'posts_per_page' => -1,
		]);
		if ( ! $posts ) {
			self::e( 'No post found.' );
		}
		$tags = [];
		foreach ( $posts as $post ) {
			switch ( $format ) {
				case 'xml':
					$xml = $this->to_xml( $post );
					file_put_contents( "{$dir}/post-{$post->ID}.xml", $xml );
					echo '.';
					break;
				case 'text':
					$tagged_text = "<UNICODE-MAC>\n" . $this->to_text( $post );
					file_put_contents( "{$dir}/post-{$post->ID}.txt", mb_convert_encoding( str_replace( "\n", "\r", $tagged_text ), 'UTF-16BE', 'utf-8' ) );
					echo '.';
					break;
				case 'tags':
					foreach ( $this->get_tags( $post ) as $tag ) {
						$attributes = explode( ' ', $tag );
						$tag_name = array_shift( $attributes );
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
	 * @param null|int|\WP_Post $post
	 *
	 * @return string
	 */
	protected function to_text( $post = null ) {
		$post = get_post( $post );
		// Fix double space.
		$content = str_replace( "\r\n", "\n", $post->post_content );
		$content = str_replace( "\n\n", "\n", $content );
		// Remove empty line.
		$content = trim( implode( "\n", array_map( function( $line ) {
			return '&nbsp;' === $line ? '' : $line;
		}, explode( "\n", $content ) ) ) );
		// Convert Aside, blockquote.
		foreach ( [
			'#<strong>([^<]+)</strong>#u' => '<CharStyle:Emphasis>$1<CharStyle:>',
			'#<del>([^<]+)</del>#u' => '<CharStyle:Del>$1<CharStyle:>',
			'#<ruby>([^<]+)<rt>([^>]+)</rt></ruby>#' => '<cMojiRuby:0><cRuby:1><cRubyString:$2>$1<cMojiRuby:><cRuby:><cRubyString:>',
				  ] as $regexp => $converted ) {
			$content = preg_replace( $regexp, $converted, $content );
		}
		// Headings
		$content = preg_replace( '#<h(\d)>([^<]+)</h(\d)>#u', '<ParaStyle:Heading$1>$2', $content );
		// Block quote, Aside.
		foreach ( [ 'Aside', 'BlockQuote' ] as $tag ) {
			$tag_name = strtolower( $tag );
			$content = preg_replace_callback( "#<{$tag_name}>(.*?)</{$tag_name}>#us", function( $match ) use ( $tag ) {
				$lines = trim( $match[1] );
				return implode( "\n", array_map( function( $line ) use ( $tag ) {
					return sprintf( '<ParaStyle:%s>', ucfirst( $tag ) ) . $line;
				}, explode( "\n", $lines ) ) );
			}, $content );
		}
		// paragraph
		foreach ( [
			'#<p style="text-align: ([^>]+);">(.*?)</p>#u' => function( $match ) {
				return sprintf( '<ParaStyle:Align%s>%s', ucfirst( $match[1] ), $match[2] );
			},
			'#<p style="(text-indent|padding-left): ([^>]+);">(.*?)</p>#u' => function( $match ) {
				$indent = preg_replace( '/\D/', '', $match[2] );
				return $match[1] ? sprintf( '<ParaStyle:Indent%d>%s', $indent, $match[3] ) : $match[3];
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
		$jobs = Jobs::get_instance();
		$job = $jobs->get( $job_id );
		if ( ! $job || 'text_to_image' != $job->job_key ) {
			self::e( 'エラー' );
		}
		try{
			$api = gianism_fb_page_api();
			if ( is_wp_error( $api ) ) {
				throw new \Exception( $api->get_error_message(), $api->get_error_code() );
			}
			$response = $api->post( 'me/feed', [
				'message' => $message,
			] );
			self::s( $response->getGraphNode()->getField( 'id' ) );
		} catch (\Exception $e ){
			self::e( $e->getCode() . ': ' . $e->getMessage() );
		}
	}

}
