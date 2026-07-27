<?php

namespace Hametuha\Commands;


use Hametuha\Hooks\StaleStatus;
use Hametuha\Model\Jobs;
use Hametuha\Model\Series;
use Hametuha\Sharee\Master\Address;
use WPametu\Utility\Command;
use cli\Table;

class Post extends Command {

	const COMMAND_NAME = 'hampost';

	/**
	 * 本文注番号のデフォルト書式（sprintf）。
	 *
	 * 縦書きでは半角アスタリスクが正しく反応しないため、全角アスタリスクを既定とする。
	 */
	const DEFAULT_NOTE_FORMAT = '＊%d';

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
			++$length;
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
	 *   taxonomy slug, or "series" to compile a series by post ID.
	 *
	 * : <term>
	 *   Term ID, or series post ID when taxonomy is "series".
	 *
	 * : <format>
	 *   1 of xml, text, plain, tags, csv. Default xml.
	 *
	 * : [--endmark]
	 *   If set, add end mark.
	 *
	 * : [--endmark-string=<endmark-string>]
	 *   Set to change default endmark "——了"
	 *
	 * : [--note-format=<note-format>]
	 *   text 書き出し時の注番号の sprintf 書式。%d が番号に置換される。
	 *   既定は "＊%d"（全角アスタリスク）。例: "［%d］", "†%d"
	 *
	 * @synopsis <taxonomy> <term> [--format=<format>] [--endmark] [--endmark-string=<endmark-string>] [--note-format=<note-format>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function compile( $args, $assoc ) {
		list( $taxonomy, $term_id ) = $args;
		$format                     = $assoc['format'] ?? 'xml';
		if ( ! in_array( $format, [ 'xml', 'text', 'plain', 'tags', 'csv' ] ) ) {
			self::e( sprintf( 'Format %s is wrong.', $format ) );
		}
		// 注番号の書式（text 書き出しで使用）。%d が番号に置換される。
		$note_format = $assoc['note-format'] ?? self::DEFAULT_NOTE_FORMAT;
		if ( ! preg_match( '/%[\'\-0-9.]*d/', $note_format ) ) {
			self::e( sprintf( 'Note format "%s" must contain a %%d placeholder (e.g. ＊%%d, ［%%d］).', $note_format ) );
		}
		// End mark.
		$endmark = ! empty( $assoc['endmark'] ) ? '<p style="text-align: right">——了</p>' : '';
		if ( $endmark && ! empty( $assoc['endmark-string'] ) ) {
			$endmark = $assoc['endmark-string'];
		}
		$upload_dir = wp_upload_dir();
		$series     = null;
		if ( 'series' === $taxonomy ) {
			$series = get_post( (int) $term_id );
			if ( ! $series || 'series' !== $series->post_type ) {
				self::e( sprintf( 'Series #%d not found.', $term_id ) );
			}
			$dir   = $upload_dir['basedir'] . '/indesign/series/' . $series->post_name;
			$posts = Series::get_series_posts( $series->ID );
		} else {
			$term = get_term_by( 'id', $term_id, $taxonomy );
			if ( ! $term ) {
				self::e( sprintf( 'failed to get term %d of %s', $term_id, $taxonomy ) );
			}
			$dir   = $upload_dir['basedir'] . '/indesign/' . $taxonomy . '/' . $term->slug;
			$posts = get_posts( [
				'post_type'      => 'post',
				'post_status'    => 'any',
				'tax_query'      => [
					[
						'taxonomy' => $taxonomy,
						'terms'    => (int) $term_id,
					],
				],
				'posts_per_page' => -1,
			] );
		}
		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0755, true );
		}
		if ( ! is_dir( $dir ) ) {
			self::e( sprintf( 'Directory %s missed.', $dir ) );
		}
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
					// Extract captions.
					// todo: wp-caption, wp-block-image.
					$images  = [];
					$content = preg_replace_callback( '#\[caption id="attachment_(\d+)".*](.*)\[/caption]#u', function ( $matches ) use ( &$images ) {
						list( $match, $id, $caption ) = $matches;
						$images[ $id ]                = trim( $caption );
						return '';
					}, $post->post_content );
					if ( ! empty( $images ) ) {
						$sub_dir = $dir . '/images';
						if ( ! is_dir( $sub_dir ) ) {
							mkdir( $sub_dir, 0755, true );
						}
						$json = [];
						foreach ( $images as $id => $caption ) {
							$attachment = wp_get_attachment_image_url( $id, 'full' );
							$path       = str_replace( home_url( '/' ), ABSPATH, $attachment );
							$json[]     = [
								'id'       => $id,
								'captions' => strip_tags( $caption ),
								'url'      => basename( $path ),
							];
							// Save file.
							copy( $path, $sub_dir . '/' . basename( $path ) );
						}
						file_put_contents( "{$dir}/post-{$post->ID}-captions.json", json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
					}

					// 本文中のリンクを脚注参照へ変換し、URL を脚注として書き出せるようにする。
					// キャプションは to_text() と同じ正規表現で先に除去しておき、
					// キャプション内リンクが本文に現れない脚注として混入するのを防ぐ（連番のズレ防止）。
					$content_source            = preg_replace( '#\[caption.*].*?\[/caption]#u', '', $post->post_content );
					$content_with_notes        = $this->inject_link_footernotes( $content_source );
					$has_link_notes            = ( $content_with_notes !== $content_source );
					$export_post               = clone $post;
					$export_post->post_content = $content_with_notes;

					// Post content.
					$tagged_text = "<UNICODE-MAC>\n" . $this->to_text( $export_post, $endmark, $note_format );
					file_put_contents( "{$dir}/post-{$post->ID}.txt", mb_convert_encoding( str_replace( "\n", "\r", $tagged_text ), 'UTF-16BE', 'utf-8' ) );
					self::l( sprintf( '#%1$d %3$s「%2$s」', $post->ID, get_the_title( $post ), get_the_author_meta( 'display_name', $post->post_author ) ) );
					// Footernotes.
					if ( $has_link_notes && get_post_meta( $post->ID, '_footernotes', true ) ) {
						// リンク脚注と手動メタ脚注は同一連番へ統合できないため、本文から脚注リストを再生成する。
						// ID を持たない合成 WP_Post を渡すことで get_post_meta() が空になり、
						// hametuha_get_footer_notes() が本文の脚注参照から自動生成する。
						self::l( sprintf( '  #%d: リンク脚注のため _footernotes メタを無視し、本文から脚注を再生成しました。', $post->ID ) );
						$note_post = new \WP_Post( (object) [ 'post_content' => $content_with_notes, 'filter' => 'raw' ] );
					} else {
						$note_post = $export_post;
					}
					$footernote = $this->to_footernote_text( $note_post, $note_format );
					if ( $footernote ) {
						file_put_contents( "{$dir}/post-{$post->ID}-footernote.txt", mb_convert_encoding( str_replace( "\n", "\r", $footernote ), 'UTF-16BE', 'utf-8' ) );
					}
					// Excerpt.
					if ( ! empty( $post->post_excerpt ) ) {
						file_put_contents( "{$dir}/post-{$post->ID}-excerpt.txt", $post->post_excerpt );
					}
					break;
				case 'plain':
					$header = implode( "\n", [
						'タイトル: ' . get_the_title( $post ),
						'著者: ' . get_the_author_meta( 'display_name', $post->post_author ),
						'URL: ' . get_permalink( $post ),
						str_repeat( '-', 40 ),
						'',
					] );
					file_put_contents( "{$dir}/post-{$post->ID}-plain.txt", $header . $post->post_content );
					self::l( sprintf( '#%1$d %3$s「%2$s」', $post->ID, get_the_title( $post ), get_the_author_meta( 'display_name', $post->post_author ) ) );
					break;
				case 'csv':
					if ( ! $lines ) {
						self::l( 'お届け先郵便番号,お届け先氏名,お届け先敬称,お届け先住所1行目,お届け先住所2行目,お届け先住所3行目,お届け先住所4行目,内容品' );
						++$lines;
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
		// Compile series preface and afterword.
		if ( $series ) {
			$extras = [
				'preface'   => get_post_meta( $series->ID, '_preface', true ),
				'afterword' => $series->post_content,
			];
			foreach ( $extras as $label => $content ) {
				if ( empty( $content ) ) {
					continue;
				}
				// filter=raw を指定しないと get_post() が sanitize_post() 経由で false を返し、
				// 序文・あとがきの内容が失われる（ID を持たない合成 WP_Post のため）。
				$content_post = new \WP_Post( (object) [ 'post_content' => $content, 'filter' => 'raw' ] );
				switch ( $format ) {
					case 'text':
						$tagged_text = "<UNICODE-MAC>\n" . $this->to_text( $content_post, '', $note_format );
						file_put_contents( "{$dir}/series-{$label}.txt", mb_convert_encoding( str_replace( "\n", "\r", $tagged_text ), 'UTF-16BE', 'utf-8' ) );
						self::l( sprintf( 'series %s saved.', $label ) );
						break;
					case 'plain':
						$header = implode( "\n", [
							'タイトル: ' . get_the_title( $series ) . "（{$label}）",
							str_repeat( '-', 40 ),
							'',
						] );
						file_put_contents( "{$dir}/series-{$label}-plain.txt", $header . $content );
						self::l( sprintf( 'series %s saved.', $label ) );
						break;
				}
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
	 * @param null|int|\WP_Post $post        Post object to compile.
	 * @param string            $endmark     Add endmark if set.
	 * @param string            $note_format 注番号の sprintf 書式（%d が番号に置換される）。
	 *
	 * @return string
	 */
	protected function to_text( $post = null, $endmark = '', $note_format = self::DEFAULT_NOTE_FORMAT ) {
		$post = get_post( $post );
		// Fix double space.
		$content = str_replace( "\r\n", "\n", $post->post_content );
		$content = str_replace( "\n\n", "\n", $content );
		// Remove residual images and links.
		// リンクは text 書き出し時に compile() 内で inject_link_footernotes() により
		// 脚注参照へ変換済み。ここは変換対象外（序文・あとがき等）に残ったリンクを
		// テキストのみ残す従来のフォールバック。
		$content = preg_replace( '#<a[^>]+>(.*?)</a>#u', '$1', $content );
		$content = preg_replace( '#<img[^>]+>#u', '', $content );
		$content = preg_replace( '#\[caption.*].*?\[/caption]#u', '', $content );

		// Remove empty line.
		$content = trim( implode( "\n", array_map( function ( $line ) {
			return '&nbsp;' === $line ? '' : $line;
		}, explode( "\n", $content ) ) ) );
		if ( $endmark ) {
			$content .= "\n" . $endmark;
		}
		// Convert Footernote.
		// InDesign では文字スタイルだけの注番号を組版上の注番号にできないため、
		// 半角スペースを親文字（FooterNoteRef で圧縮）にし、注番号をそのルビとして付ける。
		// こうすると前後の文脈に依存せず、リンク由来・通常脚注のどちらも同一形式で確実に出せる。
		// 注番号の見た目（＊/［］/ダガー等）は $note_format で切り替えられる。
		//
		// 文字スタイル（FooterNoteRef）はルビの外側に置く。内側に入れて <CharStyle:> で解除すると、
		// まだ閉じていないルビの文字属性まで一緒にリセットされ、InDesign 側でルビ終了タグが
		// 「対応する開始タグなし」エラーになるため。ルビを先に閉じてから文字スタイルを解除する。
		$note_id = 0;
		$content = preg_replace_callback( '#<small class="footernote-ref">(.*?)</small>#u', function ( $matches ) use ( &$note_id, $note_format ) {
			$note_id++;
			return sprintf(
				'<CharStyle:FooterNoteRef><cMojiRuby:0><cRuby:1><cRubyString:%s> <cMojiRuby:><cRuby:><cRubyString:><CharStyle:>',
				sprintf( $note_format, $note_id )
			);
		}, $content );

		// Inline elements and dashes.
		$content = $this->apply_inline_styles( $content );

		// UL, OL
		foreach ( [
			'#<ul>(.*?)</ul>#us' => 'UnorderedList',
			'#<ol>(.*?)</ol>#us' => 'OrderedList',
		] as $preg => $style ) {
			$content = preg_replace_callback( $preg, function ( $matches ) use ( $style ) {
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
			$content  = preg_replace_callback( "#<{$tag_name}>(.*?)</{$tag_name}>#us", function ( $match ) use ( $tag ) {
				$lines = trim( str_replace( "\n\n", "\n", $match[1] ) );
				return implode( "\n", array_map( function ( $line ) use ( $tag ) {
					return sprintf( '<ParaStyle:%s>', ucfirst( $tag ) ) . $line;
				}, explode( "\n", $lines ) ) );
			}, $content );
		}
		// paragraph
		foreach ( [
			'#<p style="text-align:([^"]+)">(.*?)</p>#us' => function ( $match ) {
				$align = ucfirst( trim( str_replace( ';', '', $match[1] ) ) );
				return sprintf( '<ParaStyle:Align%s>%s', $align, $match[2] );
			},
			'#<p style="(text-indent|padding-left):([^"]+)">(.*?)</p>#us' => function ( $match ) {
				$indent = preg_replace( '/\D/', '', $match[2] );
				return $match[1] ? sprintf( '<ParaStyle:Indent%d>%s', str_replace( ';', '', $indent ), $match[3] ) : $match[3];
			},
		] as $regexp => $callback ) {
			$content = preg_replace_callback( $regexp, $callback, $content );
		}
		// Remove span
		$content = preg_replace( '#<span([^>]*?>)(.*?)</span>#u', '$2', $content );
		// Add normal style.
		return implode( "\n", array_map( function ( $line ) {
			if ( 0 === strpos( $line, '<ParaStyle' ) ) {
				return $line;
			} else {
				return '<ParaStyle:Normal>' . $line;
			}
		}, explode( "\n", $content ) ) );
	}

	/**
	 * インライン装飾（strong/em/ruby など）を InDesign 文字スタイルへ変換する。
	 *
	 * 本文（to_text）と脚注（to_footernote_text）の双方から利用する。
	 *
	 * @param string $content 変換対象。
	 * @return string 文字スタイル変換後の文字列。
	 */
	protected function apply_inline_styles( $content ) {
		// Inline elements.
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
		return $content;
	}

	/**
	 * 脚注を InDesign タグ付きテキストとして生成する。
	 *
	 * hametuha_footer_notes() が生成する脚注 HTML（自動生成・_footernotes メタ双方に対応）を
	 * 解析し、各項目を `<ParaStyle:FooterNote><CharStyle:FooterNoteRef>{注番号}<CharStyle:>本文` の
	 * 1 行に変換する。注番号は本文側と同じ $note_format を使うため見た目が一致する。
	 * 脚注が無ければ空文字を返す。
	 *
	 * @param int|\WP_Post $note_post   脚注生成に使う投稿。
	 * @param string       $note_format 注番号の sprintf 書式（%d が番号に置換される）。
	 * @return string タグ付きテキスト（<UNICODE-MAC> ヘッダ付き）。脚注が無ければ空文字。
	 */
	protected function to_footernote_text( $note_post, $note_format = self::DEFAULT_NOTE_FORMAT ) {
		ob_start();
		hametuha_footer_notes( '', '', '', $note_post );
		$html = trim( ob_get_clean() );
		if ( '' === $html || ! preg_match_all( '#<li[^>]*>(.*?)</li>#us', $html, $matches ) ) {
			return '';
		}
		$lines = [];
		foreach ( $matches[1] as $index => $item ) {
			// 「N. 」の戻りリンクを除去。
			$item = preg_replace( '#<a class="footernote-link"[^>]*>.*?</a>#us', '', $item );
			// 残ったリンクはテキストのみ残す。
			$item = preg_replace( '#<a[^>]+>(.*?)</a>#us', '$1', $item );
			// インライン装飾を InDesign 文字スタイルへ。
			$item = $this->apply_inline_styles( $item );
			// 未変換の HTML タグ（<p> 等）を除去。InDesign タグ（CharStyle/ParaStyle 等）は残す。
			$item = preg_replace( '#</?(?!CharStyle|ParaStyle|cMojiRuby|cRubyString|cRuby)[a-zA-Z][^>]*>#u', '', $item );
			// エンティティを実体へ戻し、空白・改行を整理。
			$item = trim( preg_replace( '#\s+#u', ' ', html_entity_decode( $item, ENT_QUOTES, 'UTF-8' ) ) );
			if ( '' === $item ) {
				continue;
			}
			$lines[] = sprintf( '<ParaStyle:FooterNote><CharStyle:FooterNoteRef>%s<CharStyle:>%s', sprintf( $note_format, $index + 1 ), $item );
		}
		return empty( $lines ) ? '' : "<UNICODE-MAC>\n" . implode( "\n", $lines );
	}

	/**
	 * 本文中のリンクを脚注参照へ変換する。
	 *
	 * `<a href="URL">text</a>` を `text<small class="footernote-ref">URL</small>` に変換し、
	 * 既存の脚注機能と同じ経路（to_text() の本文側 *N と hametuha_get_footer_notes() のリスト側）を
	 * 通すことで、リンクと脚注が文書順で一貫した連番になるようにする。
	 *
	 * @param string $content 変換対象の本文。
	 * @return string リンクを脚注参照へ変換した本文。
	 */
	protected function inject_link_footernotes( $content ) {
		return preg_replace_callback( '#<a\s([^>]*?)>(.*?)</a>#us', function ( $matches ) {
			list( , $attributes, $text ) = $matches;
			if ( ! preg_match( '#href\s*=\s*(["\'])(.*?)\1#u', $attributes, $href_match ) ) {
				// href の無いアンカーはテキストのみ残す（従来挙動）。
				return $text;
			}
			$url = trim( $href_match[2] );
			// 内部アンカー（#foo）や空 URL は脚注化せずテキストのみ残す。
			if ( '' === $url || '#' === $url[0] ) {
				return $text;
			}
			// URL 内の & 等を XML 安全にエスケープしてから脚注参照へ埋め込む。
			$url = htmlspecialchars( $url, ENT_QUOTES | ENT_XML1, 'UTF-8' );
			return $text . sprintf( '<small class="footernote-ref">%s</small>', $url );
		}, $content );
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

	/**
	 * List command to stale.
	 *
	 * @synopsis <days> [--dry-run]
	 * @param array $args Option.
	 * @return void
	 */
	public function stales( $args, $assoc ) {
		$dry_run      = $assoc['dry-run'] ?? false;
		list( $days ) = $args;
		$result       = StaleStatus::get_instance()->bulk_stale( $days, $dry_run );
		if ( is_array( $result ) ) {
			// Display table.
			$table = new Table();
			$table->setHeaders( [ 'ID', 'Type', 'Title', 'Author', 'Date' ] );
			foreach ( $result as $post ) {
				$table->addRow( [ $post->ID, $post->post_type, $post->post_title, get_the_author_meta( 'display_name', $post->post_author ), $post->post_date ] );
			}
			$table->display();
		} else {
			// Display count.
			\WP_CLI::success( sprintf( __( '%d件の投稿が期限切れになりました。', 'hametuha' ), $result ) );
		}
	}

	/**
	 * Update work count of authors.
	 *
	 * @subcommand work-count
	 * @return void
	 */
	public function work_count() {
		global $wpdb;
		$query = <<<SQL
			SELECT post_author, COUNT(ID) AS work_count
			FROM {$wpdb->posts}
			WHERE post_type = 'post'
			  AND post_status = 'publish'
			GROUP BY post_author
SQL;
		$total = 0;
		foreach ( $wpdb->get_results( $query ) as $row ) {
			update_user_meta( $row->post_author, 'work_count', $row->work_count );
			echo '.';
			++$total;
		}
		\WP_CLI::success( sprintf( 'Done: %d', $total ) );
	}

	/**
	 * Update character count for all posts.
	 *
	 * ## OPTIONS
	 *
	 * [--post-type=<post-type>]
	 * : Post type to update. Default: post,series
	 *
	 * [--dry-run]
	 * : If set, only show what would be updated without actually updating.
	 *
	 * @subcommand update-length
	 * @synopsis [--post-type=<post-type>] [--dry-run]
	 * @param array $args
	 * @param array $assoc
	 * @return void
	 */
	public function update_length( $args, $assoc ) {
		$post_types = isset( $assoc['post-type'] ) ? explode( ',', $assoc['post-type'] ) : [ 'post', 'series' ];
		$dry_run    = isset( $assoc['dry-run'] ) && $assoc['dry-run'];

		if ( $dry_run ) {
			\WP_CLI::line( '=== DRY RUN MODE ===' );
		}

		foreach ( $post_types as $post_type ) {
			$post_type = trim( $post_type );
			\WP_CLI::line( sprintf( 'Processing post type: %s', $post_type ) );

			$query = new \WP_Query( [
				'post_type'      => $post_type,
				'post_status'    => [ 'publish', 'private' ],
				'posts_per_page' => -1,
				'fields'         => 'ids',
			] );

			if ( ! $query->have_posts() ) {
				\WP_CLI::warning( sprintf( 'No posts found for post type: %s', $post_type ) );
				continue;
			}

			$total = count( $query->posts );
			\WP_CLI::line( sprintf( 'Found %d posts', $total ) );

			$progress = \WP_CLI\Utils\make_progress_bar( sprintf( 'Updating %s', $post_type ), $total );

			$updated = 0;
			$skipped = 0;

			foreach ( $query->posts as $post_id ) {
				$post   = get_post( $post_id );
				$length = get_post_length( $post );

				if ( $dry_run ) {
					\WP_CLI::line( sprintf( 'Would update #%d with length: %d', $post_id, $length ) );
				} else {
					$old_length = get_post_meta( $post_id, '_post_length', true );
					update_post_meta( $post_id, '_post_length', $length );
					if ( $old_length !== $length ) {
						++$updated;
					} else {
						++$skipped;
					}
				}

				$progress->tick();
			}

			$progress->finish();

			if ( ! $dry_run ) {
				\WP_CLI::success( sprintf( 'Updated: %d, Skipped: %d (no change)', $updated, $skipped ) );
			}
		}

		\WP_CLI::success( 'Done!' );
	}

	/**
	 * Update rating average and count for all posts.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : If set, only show what would be updated without actually updating.
	 *
	 * @subcommand update-rating
	 * @synopsis [--dry-run]
	 * @param array $args
	 * @param array $assoc
	 * @return void
	 */
	public function update_rating( $args, $assoc ) {
		$dry_run = isset( $assoc['dry-run'] ) && $assoc['dry-run'];

		if ( $dry_run ) {
			\WP_CLI::line( '=== DRY RUN MODE ===' );
		}

		$rating = \Hametuha\Model\Rating::get_instance();

		\WP_CLI::line( 'Processing post type: post' );

		// まず総数を取得
		$count_query = new \WP_Query( [
			'post_type'      => 'post',
			'post_status'    => [ 'publish', 'private' ],
			'posts_per_page' => 1,
			'fields'         => 'ids',
		] );

		$total = $count_query->found_posts;

		if ( 0 === $total ) {
			\WP_CLI::warning( 'No posts found' );
			return;
		}

		\WP_CLI::line( sprintf( 'Found %d posts', $total ) );

		$progress = \WP_CLI\Utils\make_progress_bar( 'Updating ratings', $total );

		$updated = 0;
		$skipped = 0;

		// ページネーションで処理
		$per_page = 100;
		$pages    = ceil( $total / $per_page );

		for ( $page = 1; $page <= $pages; $page++ ) {
			$query = new \WP_Query( [
				'post_type'      => 'post',
				'post_status'    => [ 'publish', 'private' ],
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			] );

			if ( ! $query->have_posts() ) {
				continue;
			}

			foreach ( $query->posts as $post_id ) {
				if ( $dry_run ) {
					$post  = get_post( $post_id );
					$avg   = $rating->get_post_rating( $post );
					$count = $rating->get_post_rating_count( $post );
					\WP_CLI::line( sprintf( 'Would update #%d with average: %s, count: %d', $post_id, $avg ?: 'null', $count ) );
				} else {
					if ( $rating->update_post_average( $post_id ) ) {
						++$updated;
					} else {
						++$skipped;
					}
				}

				$progress->tick();
			}

			// メモリ解放
			wp_cache_flush();
		}

		$progress->finish();

		if ( ! $dry_run ) {
			\WP_CLI::success( sprintf( 'Updated: %d, Skipped: %d (no change)', $updated, $skipped ) );
		}

		\WP_CLI::success( 'Done!' );
	}

	/**
	 * Update review tag metadata for all posts
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Show what would be updated without actually updating
	 *
	 * ## EXAMPLES
	 *
	 *     # Update all posts
	 *     wp hampost update-review
	 *
	 *     # Dry run to see what would be updated
	 *     wp hampost update-review --dry-run
	 *
	 * @synopsis [--dry-run]
	 * @param array $args
	 * @param array $assoc
	 */
	public function update_review( $args, $assoc ) {
		$dry_run = isset( $assoc['dry-run'] );
		$review  = \Hametuha\Model\Review::get_instance();

		if ( $dry_run ) {
			\WP_CLI::line( '=== DRY RUN MODE ===' );
		}

		\WP_CLI::line( 'Processing post type: post' );

		// 総数を取得
		$total_query = new \WP_Query( [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => 1,
		] );
		$total       = $total_query->found_posts;

		\WP_CLI::line( sprintf( 'Found %d posts', $total ) );

		$progress = \WP_CLI\Utils\make_progress_bar( 'Processing posts', $total );
		$updated  = 0;
		$skipped  = 0;

		// ページネーションで処理
		$per_page = 100;
		$pages    = ceil( $total / $per_page );

		for ( $page = 1; $page <= $pages; $page++ ) {
			$query = new \WP_Query( [
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			] );

			if ( ! $query->have_posts() ) {
				continue;
			}

			foreach ( $query->posts as $post_id ) {
				if ( $dry_run ) {
					$post        = get_post( $post_id );
					$tag_counts  = $review->get_post_chart_points( $post->ID, false );
					$tag_summary = [];
					foreach ( $tag_counts as $tag_data ) {
						$tag_summary[] = sprintf( '%s: %d', $tag_data->name, $tag_data->score );
					}
					\WP_CLI::line( sprintf(
						'Would update #%d with tags: %s',
						$post_id,
						empty( $tag_summary ) ? 'none' : implode( ', ', $tag_summary )
					) );
				} else {
					if ( $review->update_post_review_tags( $post_id ) ) {
						++$updated;
					} else {
						++$skipped;
					}
				}

				$progress->tick();
			}

			// メモリ解放
			wp_cache_flush();
		}

		$progress->finish();

		if ( ! $dry_run ) {
			\WP_CLI::success( sprintf( 'Updated: %d, Skipped: %d (no change)', $updated, $skipped ) );
		}

		\WP_CLI::success( 'Done!' );
	}
}
