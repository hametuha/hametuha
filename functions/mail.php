<?php
/**
 * メール関係の関数
 */

/**
 * メルマガ購読ページはno cache headers
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

add_action( 'wp_footer', function () {
	echo <<<HTML
<script type="text/javascript" src="//s3.amazonaws.com/downloads.mailchimp.com/js/signup-forms/popup/embed.js" data-dojo-config="usePlainJson: true, isDebug: false"></script>
<script type="text/javascript">
require(["mojo/signup-forms/Loader"], function(L) { L.start({"baseUrl":"mc.us14.list-manage.com","uuid":"9b5777bb4451fb83373411d34","lid":"0565845d29"}) })
</script>
HTML;
} );
