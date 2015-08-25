<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="login" id="theme-my-login<?php $template->the_instance(); ?>">


    <p class="alert alert-info">
		登録したメールアドレスを入力してください。パスワード再設定用のURLが送信されます。
		メールアドレスも何もかも忘れてしまった方は<a class="alert-link" href="<?= home_url('/inquiry/', 'https') ?>">お問い合わせ</a>よりご連絡ください。
	</p>

	<?php $template->the_errors(); ?>

	<form name="lostpasswordform" id="lostpasswordform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'lostpassword' ); ?>" method="post">

        <input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url( 'lostpassword' ); ?>" />
        <input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
        <input type="hidden" name="action" value="lostpassword" />

        <div class="form-group">
            <label for="user_login<?php $template->the_instance(); ?>">メールアドレス</label>
            <input type="<?php attr_email(); ?>" name="user_login" id="user_login<?php $template->the_instance(); ?>" class="form-control" value="<?php $template->the_posted_value( 'user_login' ); ?>" />
        </div>

        <?php do_action( 'lostpassword_form' ); ?>


        <p>
			<input class="btn btn-success btn-block" type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="パスワード再設定" />
		</p>

	</form>
	<?php $template->the_action_links( array( 'lostpassword' => false ) ); ?>
</div>