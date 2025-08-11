<?php
/**
 * メール関係の関数
 */

/**
 * メルマガ購読ページはno cache headers
 *
 * @deprecated
 */
add_action( 'template_redirect', function () {
	if ( is_page( 'merumaga' ) ) {
		nocache_headers();
	}
} );

/**
 * FromがWordPressにならないように
 */
add_filter( 'wp_mail_from_name', function ( $from_name ) {
	if ( 'WordPress' == $from_name ) {
		$from_name = get_bloginfo( 'name' );
	}

	return $from_name;
} );

/**
 * Fromのアドレスの初期値を設定
 */
add_filter( 'wp_mail_from', function ( $from_mail ) {
	if ( 0 === strpos( $from_mail, 'wordpress@' ) ) {
		$from_mail = 'no-reply@hametuha.com';
	}
	return $from_mail;
} );

/**
 * ALO Easy Mail を上書きするフォーム
 *
 * @deprecated
 */
add_shortcode( 'ALO-EASYMAIL-PAGE', function () {
	ob_start();
	?>
	<!-- Begin MailChimp Signup Form -->
	<div id="mc_embed_signup">
		<form action="//gianism.us14.list-manage.com/subscribe/post?u=9b5777bb4451fb83373411d34&amp;id=0565845d29"
			  method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate"
			  target="_blank" novalidate>
			<div id="mc_embed_signup_scroll" class="mc-form">
				<fieldset class="form-fieldset">

					<input type="hidden" name="language" value="ja">

					<legend class="form-legend">破滅派通信を購読する</legend>

					<p class="form-helper text-right">
						<span class="form-required">*</span>は必須項目
					</p>

					<div class="form-group">
						<label for="mce-EMAIL">
							メールアドレス <span class="form-required">*</span>
						</label>
						<input type="email" value="" name="EMAIL" class="form-control" id="mce-EMAIL"
							   placeholder="hametuah@example.com">
					</div>

					<div class="form-group">
						<label for="mce-FNAME">お名前 </label>
						<input type="text" value="" placeholder="ミニ子" name="FNAME" class="form-control" id="mce-FNAME">
					</div>



					<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
					<div style="display: none;" aria-hidden="true"><input type="text"
																							  name="b_9b5777bb4451fb83373411d34_0565845d29"
																							  tabindex="-1" value="">
					</div>
					<div class="clear">

						<input type="submit" value="購読する" name="subscribe" id="mc-embedded-subscribe"
											  class="btn btn-success btn-lg">

						<span class="form-helper">
							<a href="http://us14.campaign-archive1.com/home/?u=9b5777bb4451fb83373411d34&id=0565845d29"
							   target="_blank" class="form-helper-link">
								こんなメールが届きます
							</a>
							<span class="form-helper-sep">|</span>
							<a href="http://gianism.us14.list-manage.com/unsubscribe?u=9b5777bb4451fb83373411d34&id=0565845d29"
							   target="_blank" class="form-helper-link">
								購読を解除する
							</a>
						</span>
					</div>
				</fieldset>
			</div>
		</form>
	</div>

	<!--End mc_embed_signup-->
	<?php
	$content = ob_get_contents();
	ob_end_clean();

	return $content;
} );

/**
 * Add user fields.
 *
 * @apram array   $fields
 * @param WP_User $user
 * @return array
 */
add_filter( 'hamail_user_field', function( $fields, $user ) {
	$fields['optin']  = (int) get_user_meta( $user->ID, 'optin', true );
	$fields['pseudo'] = preg_match( '/@pseudo\./u', $user->user_email ) ? 'pseudo' : 'valid';
	return $fields;
}, 10, 2 );

/**
 * Register generic group.
 *
 * @param array $groups
 * @return array
 */
add_filter( 'hamail_generic_user_group', function( $groups ) {
	if ( class_exists( 'Hametuha\\Hamail\\Pattern\\RecipientSelector' ) ) {
		$groups[] = [
			'id'       => 'hamail_tag_authors',
			'label'    => __( 'タグのついた投稿の関係者', 'hametuha' ),
			'endpoint' => 'hametuha/v1/recipients/tag-authors',
		];
	}
	return $groups;
} );

/**
 * Enable hamail APIs.
 */
if ( class_exists( 'Hametuha\\Hamail\\Pattern\\RecipientSelector' ) ) {
	\Hametuha\Plugins\Hamail\TagAuthor::get_instance();
}
if ( class_exists( 'Hametuha\Hamail\Pattern\Filters\UserFilterInputPattern' ) ) {
	\Hametuha\Plugins\Hamail\UserFilterPayment::get_instance();
}

/**
 * メルマガ用CSSにパスを追加する
 *
 * @param string[] $path
 * @return string[]
 */
add_filter( 'hamail_css_path', function( $path ) {
	$path[] = get_template_directory() . '/assets/css/hamail.css';
	return $path;
} );
