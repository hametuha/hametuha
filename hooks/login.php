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
add_filter( 'login_headertitle', function() {
	return get_bloginfo( 'name' );
} );

/**
 * Change icon if site icon is set.
 */
add_action( 'login_head', function() {
    wp_enqueue_style( 'hametuha-login-screen', get_stylesheet_directory_uri() . '/assets/css/login.css', [], hametuha_version() );
    wp_enqueue_script( 'hametuha-login-helper', get_stylesheet_directory_uri() . '/assets/js/dist/components/login-helper.js', [ 'jquery' ], hametuha_version(), true );
    wp_localize_script( 'hametuha-login-helper', 'HametuhaLoginHelper', [
        'submitLabel'      => __( 'Agree with Contract and Register', 'hametuha' ),
        'loginPlaceHolder' => __( 'e.g. hametu_tarou', 'hametuha' ),
        'loginDescription' => __( 'Login name will be used as a part of URL. Alphanumeric value and some symbols(.-_@) are allowed.', 'hametuha' ),
        'emailPlaceholder' => __( 'e.g. hametuha@example.com', 'hametuha' ),
    ] );
	$site_icon = get_site_icon_url( 84 );
	if ( ! $site_icon ) {
		return;
	}
	$version = md5( $site_icon );
	$site_icon = add_query_arg( [
        'version' => $version,
    ], $site_icon );
	?>
	<style>
        .login h1 a{
            background-image: url("<?= esc_url( $site_icon ) ?>");
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
          <?php esc_html_e( 'メールマガジンに登録する', 'hametuha' ) ?>
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
 * Add divider to login screen
 *
 * @internal
 */
function hametuha_login_divider() {
	?>
    <hr class="login-form-divider" />
    <h3 class="login-form-title">または</h3>
	<?php
}
add_action( 'register_form', 'hametuha_login_divider', 2 );
add_action( 'login_form', 'hametuha_login_divider', 2 );

/**
 * Add login tag line
 */
add_filter( 'login_message', function( $messages ) {
    $messages = sprintf( '<p class="login-tagline">%s</p>', esc_html( get_bloginfo( 'description' ) ) ) . $messages;
    return $messages;
}, 9999 );

/**
 * Change login message for
 */
add_filter( 'login_message', function( $messages ) {
    // Change login message.
    $messages = preg_replace_callback( '#<p class="message register">([^<]+)</p>#u', function( $matches ) {
        return sprintf(
                '<p class="message register">%s</p>',
                sprintf(
                        '<a href="%s" target="_blank">利用規約</a>に同意の上、破滅派に登録してください。',
                        home_url( 'contract' )
                )
        );
    }, $messages );
    return $messages;
} );
