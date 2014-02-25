<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="login" id="theme-my-login<?php $template->the_instance(); ?>">
	<p class="message notice">
		登録したメールアドレスを入力してください。パスワード再設定用のURLが送信されます。
		メールアドレスも何もかも忘れてしまった方は<a href="/inquiry/">お問い合わせ</a>よりご連絡ください。
	</p>
	<?php $template->the_errors(); ?>
	<form name="lostpasswordform" id="lostpasswordform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'lostpassword' ); ?>" method="post">
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="user_login<?php $template->the_instance(); ?>">メールアドレス</label></th>
					<td><input type="<?php attr_email(); ?>" name="user_login" id="user_login<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_login' ); ?>" size="20" /></td>
				</tr>
			</tbody>
		</table>
<?php
do_action( 'lostpassword_form' ); // Wordpress hook
do_action_ref_array( 'tml_lostpassword_form', array( &$template ) ); // TML hook
?>
		<p class="center">
			<input class="button-primary" type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="パスワード再登録" />
			<input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url( 'lostpassword' ); ?>" />
			<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
		</p>
	</form>
	<?php $template->the_action_links( array( 'lostpassword' => false ) ); ?>
</div>