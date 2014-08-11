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
	<form name="resetpasswordform" id="resetpasswordform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'resetpass' ); ?>" method="post">

        <input type="hidden" name="key" value="<?php $template->the_posted_value( 'key' ); ?>" />
        <input type="hidden" name="login" id="user_login" value="<?php $template->the_posted_value( 'login' ); ?>" />
        <input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
        <input type="hidden" name="action" value="resetpass" />

        <div class="form-group">
            <label for="pass1<?php $template->the_instance(); ?>">新しいパスワード</label>
            <input autocomplete="off" name="pass1" id="pass1<?php $template->the_instance(); ?>" class="form-control" value="" type="password" autocomplete="off" />
        </div>
        <div class="form-group">
            <label for="pass2<?php $template->the_instance(); ?>">パスワード確認</label>
            <input autocomplete="off" name="pass2" id="pass2<?php $template->the_instance(); ?>" class="form-control" value="" type="password" autocomplete="off" />
        </div>

		<div id="pass-strength-result" class="hide-if-no-js"><?php _e( 'Strength indicator', 'theme-my-login' ); ?></div>

		<p class="help-block"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).', 'theme-my-login'); ?></p>

		<p>
			<input class="btn btn-primary btn-block" type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="パスワード登録" />
		</p>

	</form>
	<?php $template->the_action_links( array( 'lostpassword' => false ) ); ?>
</div>
