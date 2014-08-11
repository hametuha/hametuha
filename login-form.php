<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
global $theme_my_login;
?>
<div class="login" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php if( isset($theme_my_login->errors->error_data) && array_key_exists('reauth', $theme_my_login->errors->error_data) ): ?>
		<p class="alert alert-warning">
			はじめての方は<?php wp_register('', ''); ?>をお願いいたします。
		</p>
	<?php else: ?>
		<?php $template->the_errors(); ?>
	<?php endif; ?>
	<form name="loginform" id="loginform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'login' ); ?>" method="post">

        <input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url( 'login' ); ?>" />
        <input type="hidden" name="testcookie" value="1" />
        <input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />

        <div class="form-group">
            <label for="user_login<?php $template->the_instance(); ?>">メールアドレス・ユーザー名</label>
            <input type="text" name="log" id="user_login<?php $template->the_instance(); ?>" class="form-control" value="<?php $template->the_posted_value( 'log' ); ?>" />
        </div>

        <div class="form-group">
            <label for="user_pass<?php $template->the_instance(); ?>">パスワード</label>
            <input type="password" name="pwd" id="user_pass<?php $template->the_instance(); ?>" class="form-control" />
        </div>

        <div class="checkbox">
            <label>
                <input name="rememberme" type="checkbox" id="rememberme<?php $template->the_instance(); ?>" value="forever"  checked="checked" />
                次回から自動ログイン
            </label>
        </div>

		<p>
			<input class="btn btn-success btn-block" type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="ログインする" />
		</p>

        <hr>

        <?php
            do_action_ref_array( 'tml_login_form', array( &$template ) ); // TML hook
            do_action( 'login_form' ); // Wordpress hook
        ?>


	</form>
	<?php $template->the_action_links( array( 'login' => false ) ); ?>
</div>