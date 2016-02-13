<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="login" id="theme-my-login<?php $template->the_instance(); ?>">
	<p class="message notice">
		新しいパスワードを入力してください。
	</p>
	<?php $template->the_errors(); ?>
	<form name="resetpasswordform" id="resetpasswordform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'resetpass', 'login_post' ); ?>" method="post">

		<input type="hidden" id="user_login" value="<?php echo esc_attr( $GLOBALS['rp_login'] ); ?>" autocomplete="off" />
		<input type="hidden" name="rp_key" value="<?php echo esc_attr( $GLOBALS['rp_key'] ); ?>" />
		<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
        <input type="hidden" name="action" value="resetpass" />

        <div class="form-group wp-pwd">
            <label for="pass1">新しいパスワード</label>
            <input autocomplete="off" name="pass1" id="pass1" data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" class="form-control" value="" type="text" autocomplete="off" />
        </div>
        <div class="form-group">
            <label for="pass2">パスワード確認</label>
            <input autocomplete="off" name="pass2" id="pass2" class="form-control" value="" type="password" autocomplete="off" />
        </div>

		<div id="pass-strength-result" class="hide-if-no-js"><?php _e( 'Strength indicator', 'theme-my-login' ); ?></div>

		<p class="help-block"><?= wp_get_password_hint() ?></p>

		<?php do_action('resetpassword_form'); ?>

		<p>
			<input class="btn btn-primary btn-block" type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="パスワード登録" />
		</p>

	</form>
	<?php $template->the_action_links( [
		'login' => false,
	    'register' => false,
	    'lostpassword' => false,
	] ); ?>
</div>
