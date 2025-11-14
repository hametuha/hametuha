<?php get_header( 'login' ); ?>

<div id="login-body">

	<div class="alert alert-info">
		<p>
			<?php echo esc_html( $login_name ); ?>さん、あなたのログイン情報を変更しようとしています。これは重要な情報なので、注意書きを理解したうえで行ってください。
		</p>
	</div>

	<form id="change-login-form" method="post" action="<?php echo $action; ?>">
		<?php echo $nonce; ?>

		<div class="form-group">
			<label>現在のログイン名</label>
			<input class="form-control" type="text" readonly value="<?php echo esc_attr( $login ); ?>"/>
		</div>

		<div class="form-group has-feedback">
			<label for="login_name">新しいログイン名</label>
			<input class="form-control" type="text" id="login_name" name="login_name" data-check="<?php echo $check_url; ?>"
					value="" autocomplete="off"/>
			<?php input_icon(); ?>
			<p class="help-block">
				ログイン名は半角英数字および半角スペースをはじめとした各種半角記号が利用できます。
				<span class="help-text help-success">このログイン名は利用できます。</span>
				<span class="help-text help-error">このログイン名は利用できません。</span>
			</p>
		</div>

		<div class="form-group">
			<label for="login_nicename">URL表示</label>

			<div class="input-group">
				<span
					class="input-group-addon"><?php echo str_replace( 'http://', '', home_url( '/author/' ) ); ?></span>
				<input type="text" class="form-control" id="login_nicename" readonly value="<?php echo $nicename; ?>">
			</div>
			<p class="help-block">
				作品を公開した場合、あなたのプロフィールページはこのURLになります。
			</p>
		</div>

		<div class="form-group">
			<ul class="notice-list">
				<li>ログイン名は重要な情報なので、変更後にはもう一度ログインしなおす必要があります。</li>
				<li>他のユーザーが使用しているログイン名は利用できません。</li>
				<li>すでに作品を公開されている方は、作品一覧URLが以下のように変更になります。</li>
			</ul>

			<p class="text-muted text-center">
				<?php echo home_url( '/author/' ); ?><code>baudelaire</code>/<br/>
				↓<br/>
				<?php echo home_url( '/author/' ); ?><code>rimbaud</code>/
			</p>

		</div>

		<p>
			<input type="submit" class="btn btn-primary btn-block btn-lg" disabled value="ログイン名を変更">
		</p>

	</form>

</div><!--  -->

<p class="text-center">
	<a href="<?php echo admin_url( 'profile.php' ); ?>">プロフィール編集へ戻る</a>
</p>

<?php get_footer( 'login' ); ?>
