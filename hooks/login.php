<?php
/**
 * ログイン関連の処理
 *
 * @package hametuha
 */


/**
 * Change login header URL.
 */
add_filter( 'login_headerurl', function() {
	return home_url();
} );

/**
 * Change login header string.
 */
add_filter( 'login_headertext', function() {
	return get_bloginfo( 'name' );
} );

/**
 * Enqueue login assets.
 */
add_action( 'login_enqueue_scripts', function() {
	wp_enqueue_style( 'hametuha-login-screen' );
	wp_enqueue_script( 'hametuha-login-helper' );
	wp_localize_script(
		'hametuha-login-helper',
		'HametuhaLoginHelper',
		[
			'submitLabel'      => __( 'Agree with Contract and Register', 'hametuha' ),
			'loginPlaceHolder' => __( 'e.g. hametu_tarou', 'hametuha' ),
			'loginDescription' => __( 'Login name will be used as a part of URL. Alphanumeric value and some symbols(.-_@) are allowed.', 'hametuha' ),
			'emailPlaceholder' => __( 'e.g. hametuha@example.com', 'hametuha' ),
		]
	);
} );

/**
 * Change icon if site icon is set.
 */
add_action( 'login_head', function() {
	$site_icon = get_template_directory_uri() . '/dist/img/brand/hametuha.svg';
	$version   = md5_file( get_template_directory() . '/dist/img/brand/hametuha.svg' );
	$site_icon = add_query_arg(
		[
			'version' => $version,
		],
		$site_icon
	);
	// Aspect ratio 300x146
	?>
	<style>
		.login h1 a{
			background-image: url("<?php echo esc_url( $site_icon ); ?>");
			width: 200px;
			height: 98px;
			background-size: 200px 98px;
			margin-bottom: 10px;
		}
		.hametuha-email-subscribe {
			margin-bottom: 20px !important;
		}
	</style>
	<?php
} );

/**
 * Add mail magazine.
 */
add_action( 'register_form', function() {
	?>
	<p class="hametuha-email-subscribe">
	  <label>
		  <input type="checkbox" name="optin" value="1" checked />
		  <?php esc_html_e( 'メールマガジンに登録する', 'hametuha' ); ?>
	  </label>
	</p>
	<?php
}, 1 );

/**
 * Register mail magazine.
 *
 * @param int $user_id
 */
add_action( 'register_new_user', function( $user_id ) {
	if ( filter_input( INPUT_POST, 'optin' ) ) {
		update_user_meta( $user_id, 'optin', 1 );
	}
} );

/**
 * Add divider to login screen if gianism is activated.
 *
 * @internal
 */
function hametuha_login_divider() {
	if ( ! function_exists( 'gianism_login' ) ) {
		return;
	}
	?>
	<hr class="login-form-divider" />
	<h3 class="login-form-title"><?php esc_html_e( 'または', 'hametuha' ); ?></h3>
	<?php
}
add_action( 'register_form', 'hametuha_login_divider', 2 );
add_action( 'login_form', 'hametuha_login_divider', 2 );

/**
 * Add login tag line
 */
add_filter( 'login_message', function( $messages ) {
	// First, tag line.
	$messages = sprintf( '<p class="login-tagline">%s</p>', esc_html__( 'オンライン文芸誌', 'hametuha' ) ) . $messages;
	return $messages;
}, 9999 );

/**
 * Change login message for registration page.
 */
add_filter( 'login_message', function( $messages ) {
	// Change login message.
	$messages = preg_replace_callback(
		'#<p class="message register">([^<]+)</p>#u',
		function( $matches ) {
			return wp_kses_post( sprintf(
				'<p class="message register">%s</p>',
				sprintf(
					// translators: %s is terms-of-service link.
					__( '<a href="%s" target="_blank" rel="noopener noreferrer">利用規約</a>に同意の上、破滅派に登録してください。すでに登録済みの方は<a href="%s">ログイン</a>してください。', 'hametuha' ),
					home_url( 'contract' ),
					wp_login_url( $_GET['redirect_to'] ?? '' )
				)
			) );
		},
		$messages
	);
	return $messages;
} );

/**
 * Change back button.
 */
add_filter( 'login_site_html_link', function( $link ) {
	return sprintf( '<a href="%s">%s</a>', home_url(), esc_html__( '破滅派ホームへ戻る', 'hametuha' ) );
} );

/**
 * Rendered in login footer.
 */
add_action( 'login_footer', function() {
	$interim_login = ! empty( $_REQUEST['interim-login'] );
	switch ( filter_input( INPUT_GET, 'action' ) ) {
		case null:
			if ( ! $interim_login ) {
				echo wp_kses_post( sprintf(
					'<p class="login-note"><span class="login-note-text">%s</span><a class="btn btn-outline-primary" href="%s">%s</a></p>',
					__( 'まだアカウントをお持ちでない方は新規登録してください。', 'hametuha' ),
					wp_registration_url(),
					__( '新規登録', 'hametuha' )
				) );
			}
			break;
		default:
			// Do nothing.
			break;
	}
} );
