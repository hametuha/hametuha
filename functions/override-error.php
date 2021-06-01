<?php
/**
 * エラーページに関係する関数群
 */



/**
 * wp_dieの描画関数をフィルター
 * @param string $function
 * @return string
 */
add_filter(
	'wp_die_handler',
	function ( $function ) {
		return '_hametuha_wp_die';
	},
	1000
);

/**
 * Render wp_die
 *
 * @param string $message
 * @param string $title
 * @param array $args
 */
function _hametuha_wp_die( $message, $title = '', $args = array() ) {
	$defaults = array( 'response' => 500 );
	$r        = wp_parse_args( $args, $defaults );

	$have_gettext = function_exists( '__' );

	if ( function_exists( 'is_wp_error' ) && is_wp_error( $message ) ) {
		if ( empty( $title ) ) {
			$error_data = $message->get_error_data();
			if ( is_array( $error_data ) && isset( $error_data['title'] ) ) {
				$title = $error_data['title'];
			}
		}
		$errors = $message->get_error_messages();
		switch ( count( $errors ) ) :
			case 0:
				$message = '';
				break;
			case 1:
				$message = "<p class=\"message warning\">{$errors[0]}</p>";
				break;
			default:
				$message = "<ul class=\"message warning\">\n\t\t<li>" . join( "</li>\n\t\t<li>", $errors ) . "</li>\n\t</ul>";
				break;
		endswitch;
	} elseif ( is_string( $message ) ) {
		$message = "<p class=\"message warning\">$message</p>";
	}

	if ( isset( $r['back_link'] ) && $r['back_link'] ) {
		$message .= "\n<p><a class=\"btn btn-block btn-danger\" href='javascript:history.back()'>戻る</a></p>";
	}

	if ( defined( 'WP_SITEURL' ) && '' != WP_SITEURL ) {
		$admin_dir = WP_SITEURL . '/wp-admin/';
	} elseif ( function_exists( 'get_bloginfo' ) && '' != get_bloginfo( 'wpurl' ) ) {
		$admin_dir = get_bloginfo( 'wpurl' ) . '/wp-admin/';
	} elseif ( strpos( $_SERVER['PHP_SELF'], 'wp-admin' ) !== false ) {
		$admin_dir = '';
	} else {
		$admin_dir = 'wp-admin/';
	}

	if ( ! function_exists( 'did_action' ) || ! did_action( 'admin_head' ) ) :
		if ( ! headers_sent() ) {
			status_header( $r['response'] );
			nocache_headers();
			header( 'Content-Type: text/html; charset=utf-8' );
		}

		if ( empty( $title ) ) {
			$title = $r['response'] . ' ' . get_status_header_desc( $r['response'] ) . '｜破滅派｜オンライン文芸誌';
		}

		$text_direction = 'ltr';
	endif;
	include TEMPLATEPATH . '/503.php';
	die();
}
