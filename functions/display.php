<?php



/**
 * A template loader which accepts arguments.
 *
 * @param string       $slug
 * @param string|array $name
 * @param array        $args       Arguments to pass.
 * @param bool         $echo       If false, return string.
 * @param string       $deprecated
 *
 * @return null|string
 */
function hameplate( $slug, $name = '', $args = [], $echo = true, $deprecated = '-' ) {
	if ( $echo ) {
		get_template_part( $slug, $name, $args );
	} else {
		ob_start();
		get_template_part( $slug, $name, $args );
		return ob_get_clean();
	}
}

/**
 * ヘルプ用アイコンを出力する
 *
 * @param string $string
 * @param string $place left top right bottom
 */
function help_tip( $string, $place = null ) {
	printf( '<a href="#" class="btn btn-xs btn-default help-tip" data-toggle="tooltip" data-original-title="%s"%s><i class="icon-question5"></i></a>',
	esc_attr( $string ), ( $place ? 'data-placement="' . esc_attr( $place ) . '"' : '' ) );
}

/**
 * リンクを出力する
 *
 * @param string $url
 */
function linkify( $url ) {
	if ( preg_match( '#^https?://#', $url ) ) {
		list( $link ) = explode( '/', preg_replace( '#https?://#', '', $url ) );
		printf( '<a href="%s" rel="nofollow">%s</a>', esc_url( $url ), $link );
	}
}

/**
 * URLからドメインを取り出す
 *
 * @param string $url
 *
 * @return string
 */
function hametuha_grab_domain( $url ) {
	if ( ! preg_match( '#https?://([^/]+)#', $url, $match ) ) {
		return $url;
	}
	return $match[1];
}

/**
 * つぶやきを出力する
 *
 * @param null|WP_Post $post
 */
function the_tweet( $post = null ) {
	$post = get_post( $post );
	echo wpautop( preg_replace_callback( '@https?://[^ 　\\n\\r\\t\\z]+@', function( $match ) {
		return sprintf( '<a href="%s" rel="nofollow">%s</a>', esc_url( $match[0] ), $match[0] );
	}, esc_html( $post->post_excerpt ) ) );
}

/**
 * ヴァリデーション用アイコンを出力する
 */
function input_icon() {
	echo <<<HTML
<span class="icon-checkmark form-control-feedback"></span>
<span class="icon-spam form-control-feedback"></span>
<span class="icon-close form-control-feedback"></span>
<span class="icon-loop3 rotation form-control-feedback"></span>

HTML;
}

/**
 * PRGのメッセージを変更する
 *
 * @filter wpametu_prg_message_class
 *
 * @param string $markup
 * @param array $messages
 * @param string $class_name
 *
 * @return string
 */
add_filter( 'wpametu_prg_message_class', function ( $markup, $messages, $class_name ) {
	$class_name = 'alert ' . ( 'error' == $class_name ? 'alert-danger' : 'alert-success' );
	$html       = <<<HTML
        <div class="%s alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
                <span class="sr-only">Close</span>
            </button>
            %s
        </div>
HTML;

	return sprintf( $html, $class_name, implode( '', array_map( function ( $msg ) {
		return "<p>{$msg}</p>";
	}, $messages ) ) );
}, 10, 3 );

/**
 * Change WP-Pagenavi's output
 *
 * @package hametuha
 * @filter wp_pagenavi
 *
 * @param string $html
 *
 * @return string
 */
add_filter( 'wp_pagenavi', function ( $html ) {
	// Remove div.
	$html = trim( preg_replace( '/<\/?div([^>]*)?>/u', '', $html ) );
	// Wrap links with li.
	$html = preg_replace( '/(<a[^>]*?>[^<]*<\/a>)/u', '<li>$1</li>', $html );
	// Wrap links with span considering class name.
	$html = preg_replace_callback( '/<span([^>]*?)>[^<]*<\/span>/u', function ( $matches ) {
		if ( false !== strpos( $matches[1], 'current' ) ) {
			// This is current page.
			$class_name = 'active';
		} elseif ( false !== strpos( $matches[1], 'pages' ) ) {
			// This is page number.
			$class_name = 'disabled';
		} elseif ( false !== strpos( $matches[1], 'extend' ) ) {
			// This is ellipsis.
			$class_name = 'disabled';
		} else {
			// No class.
			$class_name = '';
		}

		return "<li class=\"{$class_name}\">{$matches[0]}</li>";
	}, $html );

	$html = str_replace( 'ページ', '', $html );

	// Wrap with ul as you like.
	return <<<HTML
<div class="row text-center">
    <ul class="pagination pagination-centered">{$html}</ul>
</div>
HTML;
}, 10, 2 );


/**
 * WordPressのリンク出力関数をBootstrap向けに直す
 *
 * @param string $pagination
 * @param string $size sm, lg, ''のいずれか
 *
 * @return string
 */
function hametuha_format_pagination( $pagination, $size = '' ) {
	$out = [];
	foreach ( explode( "\n", $pagination ) as $link ) {
		if ( false !== strpos( $link, 'current' ) ) {
			$out[] = sprintf( '<li class="active">%s</li>', $link );
		} else {
			$out[] = sprintf( '<li>%s</li>', $link );
		}
	}
	if ( $size ) {
		$size = ' pagination-' . $size;
	}
	return '<ul class="pagination pagination-centered' . $size . '">' . implode( "\n", $out ) . '</ul>';
}


/**
 * コメント表示関数
 *
 * @param object $comment
 * @param array $args
 * @param int $depth
 */
function hametuha_commment_display( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$class_name         = [ $comment->comment_type, 'media' ];
	$is_author          = get_the_author_meta( 'ID' ) == $comment->user_id;
	if ( hametuha_get_anonymous_user()->ID == $comment->user_id ) {
		$is_author = false;
	}
	$class_name[] = $is_author ? 'author' : 'commentor';
	$pull         = $is_author ? 'pull-right' : 'pull-left';
	if ( 2 < $depth ) {
		$class_name[] = 'deep-enough';
	}
	switch ( get_post_type( $comment->comment_post_ID ) ) {
		case 'thread':
			$author_label = 'スレ主';
			break;
		default:
			$author_label = '著者';
			break;
	}
	if ( is_singular( 'thread' ) ) {
		$prop = 'suggestedAnswer';
		$type = 'http://schema.org/Answer';
	} else {
		$prop = 'comment';
		$type = 'http://schema.org/Comment';
	}
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( implode( ' ', $class_name ) ); ?> data-depth="<?php echo $depth; ?>" itemprop="<?php echo $prop; ?>" itemscope itemtype="<?php echo $type; ?>">
	<?php
	switch ( $comment->comment_type ) :
		case 'pingback':
		case 'trackback':
			break;
		default:
			?>
			<div class="<?php echo $pull; ?>">
				<?php echo get_avatar( $comment, 120 ); ?>
			</div>
			<?php
			break;
endswitch;
	?>

	<div class="media-body">

		<div class="comment-author vcard">
			<h4>
				<span itemprop="author"><?php echo get_comment_author_link(); ?></span>
				<small>
				<?php
				switch ( $comment->comment_type ) {
					case 'pingback':
					case 'trackback':
						echo '外部サイト';
						break;
					default:
						echo hametuha_user_role( $comment->user_id );
						break;
				}
				?>
					 | <i class="icon-clock"></i> <span
						itemprop=""><?php echo get_comment_date( 'Y-m-d H:i' ); ?></span></small>
			</h4>
		</div><!-- .comment-author .vcard -->

		<div class="comment-content" itemprop="text">
			<?php
			if ( $comment->comment_approved == '0' ) {
				echo '<em class="comment-awaiting-moderation">このコメントは承認待ちです。</em>';
			} else {
				comment_text();
			}
			?>
		</div>

		<div class="hidden" itemprop="url"><?php echo get_comment_link( $comment ); ?></div>

		<div class="reply right">
			<?php if ( $comment->comment_type == 'comment' || $comment->comment_type === '' ) : ?>
				<?php
				comment_reply_link( array_merge( $args, array(
					'reply_text' => '<i class="icon-reply"></i> このコメントに返信',
					'login_text' => '<i class="icon-reply"></i> ログインして返信',
					'depth'      => $depth,
					'max_depth'  => $args['max_depth'],
				) ) );
				?>
			<?php endif; ?>
			<?php edit_comment_link( '<i class="icon-pencil2"></i> このコメントを編集', '', '' ); ?>
		</div><!-- .reply -->

		<?php if ( $is_author ) : ?>
			<span class="label label-danger"><?php echo $author_label; ?></span>
		<?php endif; ?>

	</div><!-- //.media-body -->

	<?php
}


/**
 * 長過ぎる文字列を短くして返す
 *
 * @param string $sentence
 * @param int $length
 * @param string $elipsis
 *
 * @return string
 */
function trim_long_sentence( $sentence, $length = 100, $elipsis = '…' ) {
	if ( mb_strlen( $sentence, 'utf-8' ) <= $length ) {
		return $sentence;
	} else {
		return mb_substr( $sentence, 0, $length - 1, 'utf-8' ) . $elipsis;
	}
}

/**
 * 文字列を検閲する
 *
 * @param string $string
 *
 * @return string
 */
function hametuha_censor( $string ) {
	foreach ( explode( "\r\n", get_option( 'four_words', '' ) ) as $four_word ) {
		if ( $four_word ) {
			$string = preg_replace_callback( "#{$four_word}#u", function( $match ) {
				$replace = '';
				for ( $i = 0, $l = mb_strlen( $match[0], 'utf-8' ); $i < $l; $i++ ) {
					$replace .= '●';
				}
				return $replace;
			}, $string );
		}
	}
	return $string;
}

/**
 * 検閲文字列を返す
 */
add_shortcode( 'censored_words', function () {
	if ( is_user_logged_in() ) {
		$words = trim( get_option( 'four_words' ) );
		if ( $words ) {
			return sprintf( '<pre>%s</pre>', esc_html( $words ) );
		} else {
			return <<<HTML
<div class="alert alert-success">
破滅派で現在検閲している単語はありません。
</div>
HTML;

		}
	} else {
		$url = wp_login_url( $_SERVER['REQUEST_URI'] );
		return <<<HTML
<div class="alert alert-warning">
破滅派で検閲対象となる文字列を知るには、<a href="{$url}" class="alert-link" rel="nofollow">ログイン</a>している必要があります。
</div>
HTML;

	}
} );

/**
 * ログインしている人にだけ見える文字列
 */
add_shortcode( 'fyeo', function( $atts = [], $content = '' ) {
	$atts = shortcode_atts( [
		'tag_line'   => '',
		'capability' => '',
	], $atts, 'fyeo' );
	switch ( $atts['capability'] ) {
		case 'writer':
			$visibility = current_user_can( 'edit_posts' );
			break;
		case 'editor':
			$visibility = current_user_can( 'edit_others_posts' );
			break;
		case 'reader':
		case '':
			$visibility = current_user_can( 'read' );
			break;
		default:
			$visibility = current_user_can( $atts['capability'] );
			break;
	}
	if ( ! $visibility ) {
		$tag_line = [ sprintf( '<a href="%s" class="alert-link" rel="nofollow">ログイン</a>済みで権限のある人にしか表示できません。', get_permalink() ) ];
		if ( $atts['tag_line'] ) {
			array_unshift( $tag_line, $atts['tag_line'] );
		}
		$tag_line = implode( ' ', $tag_line );
		$content  = <<<HTML
<div class="alert alert-warning">
{$tag_line}
</div>
HTML;
	}
	return $content;
} );


/**
 * 改行などを削除
 *
 * @param string $markup
 *
 * @return string
 */
function hametuha_format_html_indent_for_embed( $markup ) {
	// Delete all spacing chars
	$markup = preg_replace( '#(\r|\n|\t)#', '', $markup );
	// Add line break to div
	$broken_text = preg_replace( '#<div([^>]*?)>#u', "\n<div$1>\n", $markup );
	$broken_text = str_replace( '</div>', "\n</div>\n", $markup );
	return trim( $broken_text );
}

/**
 * Get external URL.
 *
 * @param null|int|WP_Post $post
 * @return string
 */
function hametuha_external_url( $post = null ) {
	$post     = get_post( $post );
	$external = get_post_meta( $post->ID, '_external_url', true );
	return preg_match( '#^https?://#u', $external ) ? $external : '';
}

/**
 * Get external limit if set.
 *
 * @param string           $format Date format.
 * @param int|null|WP_Post $post   Post object
 * @return string
 */
function hametuha_external_url_limit( $format = '', $post = null ) {
	$post           = get_post( $post );
	$external_limit = trim( get_post_meta( $post->ID, '_external_url_limit', true ) );
	$external_limit = preg_match( '#^\d{4}-\d{2}-\d{2}$#u', $external_limit ) ? $external_limit : '';
	if ( ! $external_limit ) {
		return '';
	} elseif ( $format ) {
		return mysql2date( $format, $external_limit . ' 00:00:00' );
	} else {
		return $external_limit;
	}
}

/**
 * Can external link post be read?
 *
 * @param int|null|WP_Post $post Post object.
 * @return bool
 */
function hametuha_external_url_is_active( $post = null ) {
	$limit = hametuha_external_url_limit( '', $post );
	if ( ! $limit ) {
		return false;
	}
	return str_replace( '-', '', $limit ) >= date_i18n( 'Ymd' );
}

/**
 * URLを取得する
 *
 * @param string $url
 * @param int    $time
 * @return array|WP_Error
 */
function hametuha_remote_ogp( $url, $time = 3600 ) {
	$cache = wp_cache_get( $url, 'ext_ogp' );
	if ( ( false === $cache ) || ( 0 === $time ) ) {
		$html = wp_remote_get( $url, [
			'timeout'   => 5,
			'sslverify' => false,
		] );
		if ( is_wp_error( $html ) ) {
			return $html;
		}
		$body = $html['body'];
		$ogp  = [
			'title' => $url,
			'desc'  => '',
			'img'   => get_template_directory_uri() . '/assets/img/dammy/300.png',
		];
		foreach ( [
			'title' => '#<title[^>]*?>(.+)</title>#u',
			'desc'  => '#<meta[^>]*property=[\'"]og:description[\'"][^>]*content=[\'"](.*)[\'"]#u',
			'img'   => '#<meta[^>]*[\'"]og:image[\'"][^>]*content=[\'"](https://.*)[\'"]#u',
		] as $key => $regexp ) {
			if ( preg_match( $regexp, $body, $match ) ) {
				$ogp[ $key ] = $match[1];
			}
		}
		if ( $ogp ) {
			if ( $time ) {
				wp_cache_set( $url, $ogp, 'ext_ogp', $time );
			}
			$cache = $ogp;
		} else {
			return new WP_Error( 'no_ogp', '外部サイトのデータを取得できませんでした。' );
		}
	}
	return $cache;
}

/**
 * 初出を表示する
 *
 * @param bool             $format
 * @param null|int|WP_Post $post
 * @return string
 */
function hametuha_first_corrected( $format = false, $post = null ) {
	$post      = get_post( $post );
	$corrected = get_post_meta( $post->ID, '_first_collected', true );
	$url       = get_post_meta( $post->ID, 'oldurl', true );
	if ( ! $corrected && ! $url ) {
		return '';
	}
	if ( ! $corrected ) {
		$corrected = $url;
	}
	if ( ! $format || ! $url ) {
		return esc_html( $corrected );
	} else {
		return sprintf( '<a href="%s" target="_blank" rel="nofollow">%s</a>', esc_url( $url ), esc_html( $corrected ) );
	}
}

/**
 * Format markdown.
 *
 * @param string $markdown Markdown text.
 * @return string
 */
function hametuha_parse_markdown( $markdown ) {
	$converter = new \League\CommonMark\GithubFlavoredMarkdownConverter( [
		'allow_unsafe_links' => false,
	] );
	if ( method_exists( $converter, 'convert' ) ) {
		return $converter->convert( $markdown );
	} else {
		return $converter->convertToHtml( $markdown );
	}
}
