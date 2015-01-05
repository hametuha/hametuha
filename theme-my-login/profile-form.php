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

<div id="profile-indicator" class="text-center">
    <?php $percent = get_user_status_sufficient($current_user->ID, true) ?>
    <span class="<?php if($percent <= 25) echo 'bg-danger'; elseif($percent <= 50) echo 'bg-warning'; elseif($percent <= 75) echo 'bg-info'; else echo 'bg-success';   ?>" style="width: <?= $percent; ?>%;"></span>
    <strong>プロフィール充実度: <?= $percent;?>%</strong>
</div>

<ul id="profile-navi" class="nav nav-pills">
</ul>

<div class="login profile" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php $template->the_action_template_message( 'profile' ); ?>
	<?php $template->the_errors(); ?>
	<form id="your-profile" action="<?php $template->the_action_url( 'profile' ); ?>" method="post" class="validator">
		<?php wp_nonce_field( 'update-user_' . $current_user->ID ) ?>
        <input type="hidden" name="from" value="profile" />
        <input type="hidden" name="checkuser_id" value="<?php echo $current_user->ID; ?>" />
        <input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $current_user->ID ); ?>" />



        <section>
            <h3><i class="icon-pen4"></i> 名前</h3>

            <div class="form-group">
                <label for="user_login">ユーザー名</label>
                <input type="text" name="user_login" id="user_login" value="<?php echo esc_attr( $profileuser->user_login ); ?>" class="form-control required" readonly="readonly" />
                <p class="description text-muted"><i class="icon-info2"></i> ユーザー名はこの画面では編集できません。<a class="button" id="change-user-login" href="<?= home_url('/login/change/', 'https') ?>">こちら</a>で変更できます。</p>
            </div>

        <?php if( current_user_can('edit_posts') ): ?>

            <div class="form-group">
                <label for="nickname">筆名 <span class="label label-danger">必須</span></label>
                <input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $profileuser->nickname ) ?>" class="form-control required" />
            </div>

            <div class="form-group">
                <input type="hidden" name="first_name" id="first_name" value="<?php echo esc_attr( $profileuser->first_name ) ?>" />
                <label for="last_name">筆名読みがな <span class="label label-danger">必須 / ひらがな</span></label>
                <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $profileuser->last_name ) ?>" class="form-control required" />
                <?php if(empty($profileuser->last_name)): ?>
                    <p class="description text-danger"><i class="icon-warning2"></i> 筆名の読みがなが入力されていません。ひらがなで入力してください。</p>
                <?php elseif(!preg_match("/^[あ-ん 　]+$/u", $profileuser->last_name)): ?>
                    <p class="description text-danger"><i class="icon-warning2"></i> 筆名の読みはひらがなでなくてはなりません。</p>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="form-group">
                <label for="nickname">ニックネーム <span class="label label-danger">必須</span></label>
                <input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $profileuser->nickname ) ?>" class="form-control required" />
            </div>
        <?php endif; ?>

            <input type="hidden" name="display_name" id="display_name" value="<?= esc_attr($profileuser->nickname) ?>" />

            <p class="submit">
                <input type="submit" class="btn btn-primary btn-lg btn-block" value="更新" />
            </p>

        </section>



        <section>
            <h3><i class="icon-lock"></i> ログイン情報</h3>

            <div class="form-group">
                <label for="email">メールアドレス <span class="label label-danger">必須</span></label>
                <input type="<?php attr_email(); ?>" name="email" id="email" value="<?php echo esc_attr( $profileuser->user_email ) ?>" class="form-control required" />
                <p class="description text-muted">
                    <i class="icon-info2"></i> 公開されることはありません
                </p>
            </div>

            <?php
            $show_password_fields = apply_filters( 'show_password_fields', true, $profileuser );
            if ( $show_password_fields ) :
                ?>
                <div class="form-group">
                    <label for="pass1">新しいパスワード</label>
                    <input type="password" name="pass1" id="pass1" value="" autocomplete="off" class="form-control" />
                    <p class="description text-muted"><?php _e( 'If you would like to change the password type a new one. Otherwise leave this blank.', 'theme-my-login' ); ?></p>
                </div>
                <div class="form-group">
                    <label for="pass2">パスワード確認</label>
                    <input type="password" name="pass2" id="pass2" value="" autocomplete="off" class="form-control" />
                    <p class="description text-muted"><?php _e( 'Type your new password again.', 'theme-my-login' ); ?></p>
                </div>
                <div id="pass-strength-result"><?php _e( 'Strength indicator', 'theme-my-login' ); ?></div>
                <p class="description indicator-hint text-muted">
                    <i class="icon-info2"></i> パスワードは少なくとも7文字の長さが望ましいです。小文字、大文字の数字などの記号を使用すると、より強力なものになります。例えば ! " ? $ % ^ & ) など。
                </p>
            <?php endif; ?>
            <p class="submit">
                <input type="submit" class="btn btn-primary btn-lg btn-block" value="更新" />
            </p>
        </section>



        <section>

            <h3><i class="icon-profile"></i> プロフィール</h3>

            <div class="form-group">
                <label for="url">WebサイトのURL</label>
                <input type="text" placeholder="ex. http://example.jp" name="url" id="url" value="<?php echo esc_attr( $profileuser->user_url ) ?>" class="form-control code" />
                <p class="description text-muted">Webサイトやブログをお持ちの場合は記入してください。</p>
            </div>

            <?php if ( function_exists( '_wp_get_user_contactmethods' ) ) :
                foreach ( _wp_get_user_contactmethods() as $name => $desc ) {
                    if($name == 'aim'){
                        $placeholder = ' placeholder="ex. 私のブログ"';
                    }else{
                        $placeholder = '';
                    }
                    ?>
                    <div class="form-group">
                        <label for="<?php echo $name; ?>"><?php echo apply_filters( 'user_'.$name.'_label', $desc ); ?></label>
                        <input<?php echo $placeholder;?> type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr( $profileuser->$name ) ?>" class="form-control" />
                    </div>
                <?php
                }
            endif;
            ?>

            <div class="form-group">
                <label for="description">紹介文</label>
                <textarea class="form-control" placeholder="ex. 恥の多い生涯を送って来ました。" name="description" id="description" rows="5"><?php echo esc_textarea( $profileuser->description ); ?></textarea>
                <?php if( current_user_can('edit_posts') && empty($profileuser->description) ): ?>
                    <p class="description text-danger"><i class="icon-warning2"></i> プロフィールが入力されていません。読者のためにも入力しておいてください。</p>
                <?php endif; ?>
                <p class="description text-muted">
                    <?php if( current_user_can('edit_posts') ): ?>
                        この情報は公開されます。あなたのことを簡潔に説明する文章を入力してください。読者があなたを知るための手助けとなるでしょう。
                    <?php else: ?>
                        <i class="icon-info2"></i> この情報は公開されませんが、今後プライバシー設定機能をつけたのち、公開されることがあります。
                    <?php endif; ?>
                </p>
            </div>
            <p class="submit">
                <input type="submit" class="btn btn-primary btn-lg btn-block" value="更新" />
            </p>
        </section>



        <section class="clearfix">
            <h3><i class="icon-image"></i> 写真</h3>

            <div class="col-sm-4 text-center">
                <?php echo get_avatar($current_user->ID, 120); ?>
                <a id="user-profile-picture-edit" class="btn btn-block btn-default" href="<?= home_url('/account/picture/', 'https') ?>">写真を変更</a>
            </div>

            <div class="col-sm-8">
                <?php if( has_original_picture($current_user->ID)): ?>
                    <p class="description text-success"><i class="icon-checkmark-circle2"></i> プロフィール写真をアップロード済みです。</p>
                <?php elseif(has_gravatar($current_user->ID)): ?>
                    <p class="description text-success"><i class="icon-checkmark-circle2"></i> Gravatarが有効です。</p>
                <?php else: ?>
                    <p class="description text-danger"><i class="icon-warning2"></i> プロフィール写真がありません。</p>
                <?php endif; ?>
                <p class="description text-muted">
                    <i class="icon-info2"></i> Gravatarを設定するか、ファイルをアップロードするとプロフィール写真が表示されます。
                    あなたが同人として活動している場合は、なるべくプロフィール写真を設定しましょう。（<a href="<?php echo home_url('/faq/gravatar/');?>">詳しく</a>）
                </p>
            </div>

        </section>



        <section class="misc-section">
            <h2><i class="icon-plus2"></i> その他</h2>
            <?php
                do_action( 'show_user_profile', $profileuser );
            ?>

            <p class="submit">
                <input type="submit" class="btn btn-primary btn-lg btn-block" value="更新" />
            </p>

        </section>

		<?php if ( count( $profileuser->caps ) > count( $profileuser->roles ) && apply_filters( 'additional_capabilities_display', true, $profileuser ) ) { ?>
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

	</form>
</div>