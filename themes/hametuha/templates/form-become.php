<?php
/**
 * 同人（投稿者）になるためのフォーム
 *
 * @var string $name
 * @var string $action
 * @var string $nonce
 */
get_header( 'login' );
?>

	<div id="login-body">

		<div class="alert alert-info">
			<p>
				こんにちは、<strong><?php echo esc_html( $name ); ?></strong>さん。あなたは破滅派同人になろうとしています。<br />
				以下の注意事項と<a class="alert-link" href="<?php echo home_url( '/contract/' ); ?>" target="_blank">利用規約</a>をご覧になり、
				同意の上で「同人になる」ボタンをクリックしてください。
			</p>
		</div>

		<form id="become-author-form" method="post" action="<?php echo $action; ?>">
			<?php echo $nonce; ?>

			<div class="mb-5">
				<p class="h2 mt-5 mb-3 text-center">注意事項</p>
				<ul class="notice-list">
					<li>同人になると、作品を公開することができるようになりますが、読者に戻ることはできません。</li>
					<li>同人はプロフィール、名前などが公開されます。筆名などがそのままでもよいかどうか、<a href="<?php echo esc_url( home_url( 'dashboard/profile/' ) ); ?>">プロフィール編集</a>で検討してから同人になってください。</li>
					<li>作品の著作権はあなたに所属します。と同時に、作品の著作権の担保や公開することの責任もあなたに委ねられます。注意して操作を行ってください。</li>
				</ul>
			</div>

			<div class="form-check d-flex justify-content-center gap-1 mb-5">
				<input class="form-check-input form-unlimiter" type="checkbox" name="review_contract" value="1" id="become-login-check" />
				<label class="form-check-label" for="become-login-check">
					利用規約に同意する
				</label>
			</div>

			<p class="text-center">
				<input id="become-login-button" type="submit" class="btn btn-primary btn-lg" disabled value="同人になる">
			</p>

		</form>

	</div><!-- // #login-body -->

	<p class="text-center">
		<a href="<?php echo esc_url( home_url( 'dashboard' ) ); ?>">
			<?php esc_html_e( 'ダッシュボードへ戻る', 'hametuha' ); ?>
		</a>
	</p>

<?php get_footer( 'login' ); ?>
