<?php get_header('meta') ?>
	<div class="container">

        <p class="text-center">
            <a id="login-logo" href="<?= home_url('/', 'http') ?>" rel="home" title="破滅派に戻る"><i class="icon-hametuha"></i></a>
        </p>

		<div>
			<h1 class="text-center text-muted">404: Not Found</h1>
			<div class="error-container">
				<p class="message warning">
					お探しのページは見つかりませんでした。次のいずれかをお試しください。
				</p>
                <p>&nbsp;</p>
                <ol>
                    <li><strong>アドレス</strong>に間違いがないか確認する</li>
                    <li><a href="<?= home_url('/', 'http') ?>">トップページ</a>へ戻る</li>
                    <li><a href="/search/">検索してみる</a></li>
                </ol>
			</div>
		</div>

        <?php get_search_form() ?>

		<div id="footer-login" class="footer-note">
			<p class="copy-right text-center text-muted">&copy; 2007- HAMETUHA</p>
		</div>
	</div>


<?php wp_footer(); ?>
</body>
</html>