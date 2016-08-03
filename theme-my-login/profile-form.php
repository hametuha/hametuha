<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/

/** @var Theme_My_Login_Template $template */

$GLOBALS['current_user'] = $current_user = wp_get_current_user();
$GLOBALS['profileuser']  = $profileuser = get_user_to_edit( $current_user->ID );

$user_can_edit = false;
foreach ( array( 'posts', 'pages' ) as $post_cap ) {
	$user_can_edit |= current_user_can( "edit_$post_cap" );
}
?>

<div id="profile-indicator" class="text-center">
	<?php
	$percent = get_user_status_sufficient( $current_user->ID, true );
	if ( $percent <= 25 ) {
		$class_name = 'bg-danger';
	} elseif ( 50 >= $percent ) {
		$class_name = 'bg-warning';
	} elseif ( 75 >= $percent ) {
		$class_name = 'bg-info';
	} else {
		$class_name = 'bg-success';
	}
	?>
	<span class="<?= $class_name ?>" style="width: <?= $percent; ?>%;"></span>
	<strong>プロフィール充実度: <?= $percent; ?>%</strong>
</div>

<ul id="profile-navi" class="nav nav-pills">
	<?php if ( current_user_can( 'edit_posts' ) ) : ?>
	<li><a href="<?= home_url( '/doujin/detail/'.esc_attr( $profileuser->user_nicename ).'/') ?>" target="_blank">確認</a></li>
	<?php endif; ?>
</ul>

<div class="login profile" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php $template->the_action_template_message( 'profile' ); ?>
	<?php if( $error = $template->get_errors() ): ?>
		<div class="alert alert-danger">
			<?= $error; ?>
		</div>
	<?php endif; ?>
	<form id="your-profile" action="<?php $template->the_action_url( 'profile' ); ?>" method="post" class="validator">
		<?php wp_nonce_field( 'update-user_' . $current_user->ID ) ?>
		<input type="hidden" name="from" value="profile"/>
		<input type="hidden" name="checkuser_id" value="<?php echo $current_user->ID; ?>"/>
		<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $current_user->ID ); ?>"/>


		<section>
			<h3><i class="icon-pen4"></i> 名前</h3>

			<div class="form-group">
				<label for="user_login">ユーザー名</label>
				<input type="text" name="user_login" id="user_login"
					   value="<?php echo esc_attr( $profileuser->user_login ); ?>" class="form-control required"
					   readonly="readonly"/>

				<p class="description text-muted"><i class="icon-info2"></i> ユーザー名はこの画面では編集できません。<a class="button"
																									id="change-user-login"
																									href="<?= home_url( '/login/change/', 'https' ) ?>">こちら</a>で変更できます。
				</p>
			</div>

			<?php if ( current_user_can( 'edit_posts' ) ) : ?>

				<div class="form-group">
					<label for="nickname">筆名 <span class="label label-danger">必須</span></label>
					<input type="text" name="nickname" id="nickname"
						   value="<?php echo esc_attr( $profileuser->nickname ) ?>" class="form-control required"/>
				</div>

				<div class="form-group">
					<input type="hidden" name="first_name" id="first_name"
						   value="<?php echo esc_attr( $profileuser->first_name ) ?>"/>
					<label for="last_name">筆名読みがな <span class="label label-danger">必須 / ひらがな</span></label>
					<input type="text" name="last_name" id="last_name"
						   value="<?php echo esc_attr( $profileuser->last_name ) ?>" class="form-control required"/>
					<?php if ( empty( $profileuser->last_name ) ) : ?>
						<p class="description text-danger"><i class="icon-warning2"></i>
							筆名の読みがなが入力されていません。ひらがなで入力してください。</p>
					<?php elseif ( ! preg_match( '/^[あ-ん 　]+$/u', $profileuser->last_name ) ) : ?>
						<p class="description text-danger"><i class="icon-warning2"></i> 筆名の読みはひらがなでなくてはなりません。</p>
					<?php endif; ?>
				</div>

			<?php else : ?>

				<div class="form-group">
					<label for="nickname">ニックネーム <span class="label label-danger">必須</span></label>
					<input type="text" name="nickname" id="nickname"
						   value="<?php echo esc_attr( $profileuser->nickname ) ?>" class="form-control required"/>
				</div>
			<?php endif; ?>

			<input type="hidden" name="display_name" id="display_name"
				   value="<?= esc_attr( $profileuser->nickname ) ?>"/>

			<p class="submit">
				<input type="submit" class="btn btn-primary btn-lg btn-block" value="更新"/>
			</p>

		</section>


		<section>
			<h3><i class="icon-lock"></i> ログイン情報</h3>

			<div class="form-group">
				<label for="email">メールアドレス <span class="label label-danger">必須</span></label>
				<input type="<?php attr_email(); ?>" name="email" id="email"
					   value="<?php echo esc_attr( $profileuser->user_email ) ?>" class="form-control required"/>

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
					<input type="password" name="pass1" id="pass1" value="" autocomplete="off" class="form-control"/>

					<p class="description text-muted"><?php _e( 'If you would like to change the password type a new one. Otherwise leave this blank.', 'theme-my-login' ); ?></p>
				</div>
				<div class="form-group">
					<label for="pass2">パスワード確認</label>
					<input type="password" name="pass2" id="pass2" value="" autocomplete="off" class="form-control"/>

					<p class="description text-muted"><?php _e( 'Type your new password again.', 'theme-my-login' ); ?></p>
				</div>
				<div id="pass-strength-result"><?php _e( 'Strength indicator', 'theme-my-login' ); ?></div>
				<p class="description indicator-hint text-muted">
					<i class="icon-info2"></i> パスワードは少なくとも7文字の長さが望ましいです。小文字、大文字の数字などの記号を使用すると、より強力なものになります。例えば ! " ? $ %
					^ & ) など。
				</p>
			<?php endif; ?>
			<p class="submit">
				<input type="submit" class="btn btn-primary btn-lg btn-block" value="更新"/>
			</p>
		</section>


		<section>

			<h3><i class="icon-profile"></i> プロフィール</h3>

			<div class="alert alert-info">
				<?php if ( current_user_can( 'edit_posts' ) ) : ?>
					この情報はあなたの
					<a class="alert-link" href="<?= home_url( sprintf( '/doujin/detail/%s/', $profileuser->user_nicename ) ) ?>">
						プロフィールページ
					</a>
					に表示されます。
					読者があなたのことを知るきっかけになりますので、なるべく詳細に入力しましょう。
					入力していない情報は表示されません。
				<?php else : ?>
					現在、破滅派では投稿者ではない人のプロフィールは表示されませんが、
					SNS的な機能がついた場合は表示されるようになります。
					さしつかえない範囲で入力してください。
				<?php endif; ?>
			</div>

			<div class="form-group">
				<label for="description">紹介文</label>
				<textarea class="form-control" placeholder="ex. 恥の多い生涯を送って来ました。" name="description" id="description"
						  rows="5"><?php echo esc_textarea( $profileuser->description ); ?></textarea>
				<?php if ( current_user_can( 'edit_posts' ) && empty( $profileuser->description ) ) : ?>
					<p class="description text-danger"><i class="icon-warning2"></i>
						プロフィールが入力されていません。読者のためにも入力しておいてください。</p>
				<?php endif; ?>
				<p class="description text-muted">
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						この情報は公開されます。あなたのことを簡潔に説明する文章を入力してください。読者があなたを知るための手助けとなるでしょう。
					<?php else : ?>
						<i class="icon-info2"></i> この情報は公開されませんが、今後プライバシー設定機能をつけたのち、公開されることがあります。
					<?php endif; ?>
				</p>
			</div>

			<div class="form-group">
				<label for="url"><i class="icon-link"></i> WebサイトのURL</label>
				<input type="text" placeholder="ex. http://example.jp" name="url" id="url"
					   value="<?php echo esc_attr( $profileuser->user_url ) ?>" class="form-control code"/>
				<p class="description text-muted">Webサイトやブログをお持ちの場合は記入してください。</p>
			</div>

			<?php if ( function_exists( '_wp_get_user_contactmethods' ) ) :
				foreach ( _wp_get_user_contactmethods() as $name => $desc ) {
					$type = 'text';
					$placeholder = '';
					$instruction = '';
					$class_names = [ 'form-control' ];
					$textarea = false;
					switch ( $name ) {
						case 'aim':
							$placeholder = ' placeholder="ex. 私のブログ"';
							break;
						case 'twitter':
							$placeholder = ' placeholder="ex. hametuha"';
							$instruction = '@は不要です。';
							break;
						case 'location':
							$placeholder = ' placeholder="ex. 東京都港区南青山"';
							$instruction = '現在住んでいる地域や主な活動場所を入れてください。';
							break;
						case 'birth_place':
							$placeholder = ' placeholder="ex. 東京都千代田区"';
							$instruction = '自分のアイデンティティが育まれた場所を入力してください。';
							break;
						case 'favorite_authors':
							$placeholder = ' placeholder="ex. 夏目漱石,チャールズ・ディケンズ"';
							$instruction = '好きな作家をカンマ区切りで入力してください。';
							break;
						case 'favorite_words':
							$textarea    = true;
							$placeholder = ' placeholder="ex. 人生は一行のボオドレウヱルに如かない——芥川龍之介"';
							$instruction = '好きな言葉を出店付きで入力してください。';
							break;
						default:
							// Do nothing
							break;
					}
					?>
					<div class="form-group">
						<label
							for="<?php echo $name; ?>"><?php echo apply_filters( 'user_' . $name . '_label', $desc ); ?></label>
                        <?php if ( $textarea ) : ?>

                        <textarea <?php echo $placeholder; ?>
                            name="<?php echo $name; ?>" rows="5"
                                                          id="<?php echo $name; ?>"
                                                          class="<?= implode( ' ', $class_names ) ?>"><?= esc_textarea( $profileuser->$name ) ?></textarea>
                        <?php else : ?>
						<input<?php echo $placeholder; ?> type="<?= $type ?>" name="<?php echo $name; ?>"
														  id="<?php echo $name; ?>"
														  value="<?php echo esc_attr( $profileuser->$name ) ?>"
														  class="<?= implode( ' ', $class_names ) ?>"/>
                        <?php endif; ?>
						<?php if ( $instruction ) : ?>
							<p class="description text-muted"><?= $instruction ?></p>
						<?php endif ?>
					</div>
					<?php
				}
			endif;
			?>
			<p class="submit">
				<input type="submit" class="btn btn-primary btn-lg btn-block" value="更新"/>
			</p>
		</section>


		<section class="clearfix">
			<h3><i class="icon-image"></i> 写真</h3>

			<div class="col-sm-4 text-center">
				<?php echo get_avatar( $current_user->ID, 120 ); ?>
				<a id="user-profile-picture-edit" class="btn btn-block btn-default"
				   href="<?= home_url( '/account/picture/', 'https' ) ?>">写真を変更</a>
			</div>

			<div class="col-sm-8">
				<?php if ( has_original_picture( $current_user->ID ) ) : ?>
					<p class="description text-success"><i class="icon-checkmark-circle2"></i> プロフィール写真をアップロード済みです。</p>
				<?php elseif ( has_gravatar( $current_user->ID ) ) : ?>
					<p class="description text-success"><i class="icon-checkmark-circle2"></i> Gravatarが有効です。</p>
				<?php else : ?>
					<p class="description text-danger"><i class="icon-warning2"></i> プロフィール写真がありません。</p>
				<?php endif; ?>
				<p class="description text-muted">
					<i class="icon-info2"></i> Gravatarを設定するか、ファイルをアップロードするとプロフィール写真が表示されます。
					あなたが同人として活動している場合は、なるべくプロフィール写真を設定しましょう。（<a
						href="<?php echo home_url( '/faq/gravatar/' ); ?>">詳しく</a>）
				</p>
			</div>

		</section>

		<?php if ( current_user_can( 'edit_posts' ) ) : ?>
		<section class="clearfix">
			<h3><i class="icon-gift"></i> 報酬</h3>

			<hr />
			<h4>ニュース報酬</h4>
			<p class="description text-muted">ニュース記事を書いて1記事あたり貰える金額です。詳細は<a href="<?= home_url( '/faq-cat/news/' ) ?>">よくある質問</a>を御覧ください。</p>
			<p>
				<strong>2,000PVを超えた記事に関して1,000円</strong>を受け取ることができます。
				<?php if ( $news_guranterr = \Hametuha\Model\Sales::get_instance()->get_guarantee( $current_user->ID, 'news' ) ) : ?>
					 また、<strong>最低保証額として1記事あたり<?= number_format( $news_guranterr ) ?>円が保証</strong>されています。
				<?php endif; ?>
			</p>



		</section>
		<?php endif; ?>


		<section class="misc-section">
			<h2><i class="icon-plus2"></i> その他</h2>
			<?php do_action( 'show_user_profile', $profileuser ); ?>

			<p class="submit">
				<input type="submit" class="btn btn-primary btn-lg btn-block" value="更新"/>
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
							if ( ! $wp_roles->is_role( $cap ) ) {
								if ( $output != '' ) {
									$output .= ', ';
								}
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
