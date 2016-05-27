<?php

/**
 * ページテンプレートを差し替え
 */
add_filter( 'template_include', function( $path ) {
	if ( is_singular( 'page' ) && ! is_home() && 'index.php' == basename( $path ) ) {
		$path = get_template_directory().'/single.php';
	}
	return $path;
} );


/**
 * A template loader which accepts arguments.
 *
 * @param string       $slug
 * @param string|array $name
 * @param array        $args Arguments to pass.
 * @param bool         $echo If false, return string.
 * @param string       $glue Default is '-'. You can specify '/' which means directory separator.
 *
 * @return null|string
 */
function hameplate( $slug, $name = '', $args = [], $echo = true, $glue = '-' ) {
	$file = [];
	if ( ! $name ) {
		$file[] = $slug;
	} elseif ( is_array( $name ) ) {
		for ( $i = count( $name ); $i > 0; $i -- ) {
			$file[] = $slug . $glue . implode( $glue, array_slice( $name, 0, $i ) );
		}
		$file[] = $slug;
	} else {
		$file[] = $slug . $glue . $name;
		$file[] = $slug;
	}
	$dirs = [ get_stylesheet_directory() ];
	if ( is_child_theme() ) {
		$dirs[] = get_template_directory();
	}
	$path = '';
	foreach ( $file as $f ) {
		foreach ( $dirs as $dir ) {
			$p = $dir . DIRECTORY_SEPARATOR . $f . '.php';
			if ( file_exists( $p ) ) {
				$path = $p;
				break 2;
			}
		}
	}
	if ( ! $path ) {
		return $echo ? null : '';
	}
	// Enable vars.
	global $posts, $post, $wp_query, $wp_rewrite, $wpdb;
	if ( $args ) {
		extract( $args );
	}
	if ( $echo ) {
		include $path;
	} else {
		ob_start();
		include $path;
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
}

/**
 * アドミンバーを常に非表示
 *
 * @filter show_admin_bar
 * @return boolean
 */
add_filter( 'show_admin_bar', function () {
	return false;
}, 1000 );


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
function linkify($url) {
	if ( preg_match( '#^https?://#', $url ) ) {
		list( $link ) = explode( '/', preg_replace( '#https?://#', '', $url ) );
		printf( '<a href="%s" rel="nofollow">%s</a>', esc_url( $url ), $link );
	}
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
	if ( get_anonymous_user()->ID == $comment->user_id ) {
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
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( implode( ' ', $class_name ) ) ?> data-depth="<?= $depth ?>" itemprop="<?= $prop ?>" itemscope itemtype="<?= $type ?>">
	<?php switch ( $comment->comment_type ):
		case "pingback":
		case "trackback":
			break;
		default: ?>
			<div class="<?= $pull ?>">
				<?= get_avatar( $comment, 120 ) ?>
			</div>
			<?php break; endswitch; ?>

	<div class="media-body">

		<div class="comment-author vcard">
			<h4>
				<span itemprop="author"><?= get_comment_author_link(); ?></span>
				<small><?php
					switch ( $comment->comment_type ) {
						case "pingback":
						case "trackback":
							echo '外部サイト';
							break;
						default:
							echo hametuha_user_role( $comment->user_id );
							break;
					}
					?> | <i class="icon-clock"></i> <span
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

		<div class="hidden" itemprop="url"><?= get_comment_link( $comment ) ?></div>

		<div class="reply right">
			<?php if ( $comment->comment_type == 'comment' || $comment->comment_type === '' ): ?>
				<?php comment_reply_link( array_merge( $args, array(
					'reply_text' => '<i class="icon-reply"></i> このコメントに返信',
					'login_text' => '<i class="icon-reply"></i> ログインして返信',
					'depth'      => $depth,
					'max_depth'  => $args['max_depth']
				) ) ); ?>
			<?php endif; ?>
			<?php edit_comment_link( '<i class="icon-pencil2"></i> このコメントを編集', '', '' ) ?>
		</div><!-- .reply -->

		<?php if ( $is_author ): ?>
			<span class="label label-danger"><?= $author_label ?></span>
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
			$string = preg_replace_callback( "#{$four_word}#u", function( $match ){
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
add_shortcode( 'fyeo', function( $atts = [], $content = '' ){
	$atts = shortcode_atts( [
		'tag_line' => '',
	    'capability' => '',
	], $atts, 'fyeo' );
	switch ( $atts['capabiltiy'] ) {
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
		$content = <<<HTML
<div class="alert alert-warning">
{$tag_line}
</div>
HTML;
	}
	return $content;
} );


//
// Theme My Loginを使っているときに
// REST APIプラグインがこけないようにする
//
if ( ! function_exists( 'login_header' ) ) {
	function login_header() {
		get_header( 'login' );
		?>
		<p class="catch-copy text-center">
			<?php bloginfo( 'description' ) ?>
		</p>

		<div id="login-body">
		<?php
	}

	function login_footer() {
		echo '</div>';
		get_footer( 'login' );
	}
}
