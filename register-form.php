<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="login" id="theme-my-login<?php $template->the_instance(); ?>">
	<p class="message notice">
		<a target="_blank" href="/contract/">利用規約</a>にご同意頂いた上で登録していただくと、登録確認用のメールが送信されます。そのメールに記載されたURLへ移動すると登録完了です。
	</p>
	<?php $template->the_errors(); ?>
    <form class="registerform" name="registerform" id="registerform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'register' ); ?>" method="post">
		<p>
			<label for="user_login<?php $template->the_instance(); ?>">ユーザー名:</label>
			<input type="text" name="user_login" id="user_login<?php $template->the_instance(); ?>" value="<?php $template->the_posted_value( 'user_login' ); ?>" />
		</p>
		<p>
			<label for="user_email<?php $template->the_instance(); ?>">メールアドレス:</label>
			<input type="<?php attr_email(); ?>" name="user_email" id="user_email<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_email' ); ?>" size="20" />
		</p>
		<?php do_action_ref_array( 'tml_register_form', array( &$template ) ); //TML hook ?>
		<?php do_action( 'register_form' ); // Wordpress hook ?>
        <p class="center">
            <input class="button-primary" type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="利用規約に同意して登録" />
			<input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url( 'register' ); ?>" />
			<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
        </p>
    </form>
	<?php $template->the_action_links( array( 'register' => false ) ); ?>
</div>
