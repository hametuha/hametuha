<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/

$GLOBALS['current_user'] = $current_user = wp_get_current_user();
$GLOBALS['profileuser'] = $profileuser = get_user_to_edit( $current_user->ID );

$user_can_edit = false;
foreach ( array( 'posts', 'pages' ) as $post_cap )
	$user_can_edit |= current_user_can( "edit_$post_cap" );
?>


<div class="login profile" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php $template->the_action_template_message( 'profile' ); ?>
	<?php $template->the_errors(); ?>
	<form id="your-profile" action="" method="post">
		<?php wp_nonce_field( 'update-user_' . $current_user->ID ) ?>
		<p>
			<input type="hidden" name="from" value="profile" />
			<input type="hidden" name="checkuser_id" value="<?php echo $current_user->ID; ?>" />
		</p>
<?php /*
		<h3><?php _e( 'Personal Options', 'theme-my-login' ); ?></h3>

		<table class="form-table">
		<?php if ($current_user->user_level > 0 && rich_edit_exists() && $user_can_edit ) : // don't bother showing the option if the editor has been removed ?>
		<tr>
			<th scope="row"><?php _e( 'Visual Editor', 'theme-my-login' )?></th>
			<td><label for="rich_editing"><input name="rich_editing" type="checkbox" id="rich_editing" value="false" <?php checked( 'false', $profileuser->rich_editing ); ?> /> <?php _e( 'Disable the visual editor when writing', 'theme-my-login' ); ?></label></td>
		</tr>
		<?php endif; ?>
		<?php if ($current_user->user_level > 0 && count( $GLOBALS['_wp_admin_css_colors'] ) > 1 && has_action( 'admin_color_scheme_picker' ) ) : ?>
		<tr>
			<th scope="row"><?php _e( 'Admin Color Scheme', 'theme-my-login' )?></th>
			<td><?php do_action( 'admin_color_scheme_picker' ); ?></td>
		</tr>
		<?php
		endif; // $_wp_admin_css_colors
		if ($current_user->user_level > 0 && $user_can_edit ) : ?>
		<tr>
			<th scope="row"><?php _e( 'Keyboard Shortcuts', 'theme-my-login' ); ?></th>
			<td><label for="comment_shortcuts"><input type="checkbox" name="comment_shortcuts" id="comment_shortcuts" value="true" <?php if ( !empty( $profileuser->comment_shortcuts ) ) checked( 'true', $profileuser->comment_shortcuts ); ?> /> <?php _e( 'Enable keyboard shortcuts for comment moderation.', 'theme-my-login' ); ?></label> <?php _e( '<a href="http://codex.wordpress.org/Keyboard_Shortcuts" target="_blank">More information</a>', 'theme-my-login' ); ?></td>
		</tr>
		<?php endif; ?>
		<?php do_action( 'personal_options', $profileuser ); ?>
		</table>
		<?php do_action( 'profile_personal_options', $profileuser );username_exists($username)?>
*/ ?>
		<h3><?php _e( 'Name', 'theme-my-login' ) ?></h3>

		<table class="form-table">
		<tr>
			<th><label for="user_login">ユーザー名<span class="required">（必須）</span></label></th>
			<td>
				<input type="text" name="user_login" id="user_login" value="<?php echo esc_attr( $profileuser->user_login ); ?>" class="regular-text disabled" readonly="readonly" />
				<a class="button" id="change-user-login" href="<?php echo admin_url('admin-ajax.php?action=username_change'); ?>">変更</a>
			</td>
		</tr>
<?php if(current_user_can('edit_posts')): ?>
		<tr>
			<th><label for="last_name">筆名読みがな<span class="required">（必須 / ひらがな）</span></label></th>
			<td>
				<input type="hidden" name="first_name" id="first_name" value="<?php echo esc_attr( $profileuser->first_name ) ?>" />
				<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $profileuser->last_name ) ?>" class="regular-text" />
				<?php if(empty($profileuser->last_name)): ?>
					<p class="small-message warning">筆名の読みがなが入力されていません。ひらがなで入力してください。</p>
				<?php elseif(!preg_match("/^[あ-ん 　]+$/u", $profileuser->last_name)): ?>
					<p class="small-message warning">筆名の読みはひらがなでなくてはなりません。</p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th><label for="nickname">筆名 <span class="required">（必須）</span></label></th>
			<td><input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $profileuser->nickname ) ?>" class="regular-text" /></td>
		</tr>
<?php else: ?>
		<tr>
			<th><label for="nickname">ニックネーム <span class="required">（必須）</span></label></th>
			<td>
				<input type="hidden" name="first_name" id="first_name" value="<?php echo esc_attr( $profileuser->first_name ) ?>" />
				<input type="hidden" name="last_name" id="last_name" value="<?php echo esc_attr( $profileuser->last_name ) ?>" />
				<input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $profileuser->nickname ) ?>" class="regular-text" />
			</td>
		</tr>
<?php endif; ?>

		<tr>
			<th><label for="display_name">サイトに表示される名前</label></th>
			<td>
				<select name="display_name" id="display_name">
				<?php
					$public_display = array();
					$public_display['display_nickname']  = $profileuser->nickname;
					$public_display['display_username']  = $profileuser->user_login;
					if ( !empty( $profileuser->first_name ) )
						$public_display['display_firstname'] = $profileuser->first_name;
					if ( !empty( $profileuser->last_name ) )
						$public_display['display_lastname'] = $profileuser->last_name;
					if ( !empty( $profileuser->first_name ) && !empty( $profileuser->last_name ) ) {
						$public_display['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
						$public_display['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
					}
					if ( !in_array( $profileuser->display_name, $public_display ) )// Only add this if it isn't duplicated elsewhere
						$public_display = array( 'display_displayname' => $profileuser->display_name ) + $public_display;
					$public_display = array_map( 'trim', $public_display );
					foreach ( $public_display as $id => $item ) {
						$selected = ( $profileuser->display_name == $item ) ? ' selected="selected"' : '';
				?>
						<option id="<?php echo $id; ?>" value="<?php echo esc_attr( $item ); ?>"<?php echo $selected; ?>><?php echo $item; ?></option>
				<?php } ?>
				</select>
			</td>
		</tr>
		</table>

		<h3><?php _e( 'Contact Info', 'theme-my-login' ) ?></h3>

		<table class="form-table">
		<tr>
			<th><label for="email">メールアドレス <span class="required"><?php _e( '(required)', 'theme-my-login' ); ?></span></label></th>
			<td>
				<input type="<?php attr_email(); ?>" name="email" id="email" value="<?php echo esc_attr( $profileuser->user_email ) ?>" class="regular-text" />
				<p class="description">
					※公開されることはありません
				</p>
			</td>
		</tr>

		<tr>
			<th><label for="url">WebサイトのURL</label></th>
			<td><input type="text" placeholder="http://example.jp" name="url" id="url" value="<?php echo esc_attr( $profileuser->user_url ) ?>" class="regular-text code" /></td>
		</tr>

		<?php if ( function_exists( '_wp_get_user_contactmethods' ) ) :
			foreach ( _wp_get_user_contactmethods() as $name => $desc ) {
				if($name == 'aim'){
					$placeholder = ' placeholder="ex. 私のブログ"';
				}else{
					$placeholder = '';
				}
		?>
		<tr>
			<th><label for="<?php echo $name; ?>"><?php echo apply_filters( 'user_'.$name.'_label', $desc ); ?></label></th>
			<td><input<?php echo $placeholder;?> type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr( $profileuser->$name ) ?>" class="regular-text" /></td>
		</tr>
		<?php
			}
			endif;
		?>
		</table>

		<h3><?php _e( 'About Yourself', 'theme-my-login' ); ?></h3>

		<table class="form-table">
		<tr>
			<th><label for="description">プロフィール</label></th>
			<td>
				<textarea placeholder="ex. 恥の多い生涯を送って来ました。" name="description" id="description" rows="5" cols="30"><?php echo esc_html( $profileuser->description ); ?></textarea><br />
				<span class="description">
					<?php if(current_user_can('edit_posts')): ?>
						この情報は公開されます。あなたのことを簡潔に説明する文章を入力してください。読者があなたを知るための手助けとなるでしょう。
					<?php else: ?>
						この情報は公開されませんが、今後プライバシー設定機能をつけたのち、公開されることがあります。
					<?php endif; ?>
				</span>
				<?php if(current_user_can('edit_posts') && empty($profileuser->description)): ?>
					<p class="small-message warning">プロフィールが入力されていません。読者のためにも入力しておいてください。</p>
				<?php endif; ?>
			</td>
		</tr>

		<?php
		$show_password_fields = apply_filters( 'show_password_fields', true, $profileuser );
		if ( $show_password_fields ) :
		?>
		<tr id="password">
			<th><label for="pass1"><?php _e( 'New Password', 'theme-my-login' ); ?></label></th>
			<td><input type="password" name="pass1" id="pass1" size="16" value="" autocomplete="off" /> <span class="description"><?php _e( 'If you would like to change the password type a new one. Otherwise leave this blank.', 'theme-my-login' ); ?></span><br />
				<input type="password" name="pass2" id="pass2" size="16" value="" autocomplete="off" /> <span class="description"><?php _e( 'Type your new password again.', 'theme-my-login' ); ?></span><br />
				<div id="pass-strength-result"><?php _e( 'Strength indicator', 'theme-my-login' ); ?></div>
				<p class="description indicator-hint"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).', 'theme-my-login' ); ?></p>
			</td>
		</tr>
		<?php endif; ?>
		</table>
		

		<?php
			do_action( 'show_user_profile', $profileuser );
		?>

		<?php if ( count( $profileuser->caps ) > count( $profileuser->roles ) && apply_filters( 'additional_capabilities_display', true, $profileuser ) ) { ?>
		<br class="clear" />
			<table width="99%" style="border: none;" cellspacing="2" cellpadding="3" class="editform">
				<tr>
					<th scope="row"><?php _e( 'Additional Capabilities', 'theme-my-login' ) ?></th>
					<td><?php
					$output = '';
					global $wp_roles;
					foreach ( $profileuser->caps as $cap => $value ) {
						if ( !$wp_roles->is_role( $cap ) ) {
							if ( $output != '' )
								$output .= ', ';
							$output .= $value ? $cap : "Denied: {$cap}";
						}
					}
					echo $output;
					?></td>
				</tr>
			</table>
		<?php } ?>

		<p class="center">
			<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $current_user->ID ); ?>" />
			<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Update Profile', 'theme-my-login' ); ?>" name="submit" />
		</p>
	</form>
</div>
