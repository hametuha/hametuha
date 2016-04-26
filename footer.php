<?php if ( ! is_front_page() && 'kdp' !== get_query_var( 'meta_filter' ) ) {
	get_template_part( 'parts/list', 'kdp' );
} ?>


<div class="modal fade" id="searchBox" tabindex="-1" role="dialog" aria-labelledby="searchBox">
	<div class="modal-dialog">
		<form action="<?= home_url( '/' ) ?>" data-action="<?= home_url( '/' ) ?>">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="icon-search2"></i>検索フォーム</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="searchBoxS">キーワード</label>
						<input class="form-control" type="text" name="s" id="searchBoxS"
						       value="<?php the_search_query() ?>" placeholder="ex. 面白い小説"/>
					</div>
					<div class="form-group">
						<label class="radio-inline">
							<input type="radio" name="post_type"
							       value="any" <?php checked( false !== array_search( get_query_var( 'post_type' ), [
									'',
									'any'
								] ) ) ?>/> すべて
						</label>
						<?php
						foreach (
							[
								'post'   => '作品',
								'thread' => '掲示板',
								'anpi'   => '安否',
								'faq'    => 'よくある質問',
							] as $post_type => $label
						) :
							?>
							<label class="radio-inline">
								<input type="radio" name="post_type"
								       value="<?= $post_type ?>" <?php checked( get_query_var( 'post_type' ) === $post_type ) ?>/> <?= $label ?>
							</label>
						<?php endforeach; ?>
						<label class="radio-inline">
							<input type="radio" name="post_type" data-search-action="<?= home_url( 'authors/' ) ?>"
							       value="author" <?php checked( 'author' === get_query_var( 'post_type' ) ) ?> /> 著者
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
					<input type="submit" class="btn btn-primary" value="検索"/>
				</div>
			</div><!-- /.modal-content -->
		</form>
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<footer id="footer" class="footer-wrapper">
	<div id="footer-sidebar" class="container">

		<div class="row footer-subscription">

			<div class="col-xs-12 col-sm-4 text-center">
				<h3>メルマガ</h3>
				<p class="text-muted">
					月一（努力目標）で配信しています。
				</p>
				<a class="btn btn-primary" href="<?= home_url( '/merumaga/' ) ?>">
					<i class="icon-mail"></i> 購読する
				</a>
			</div>

			<div class="col-xs-12 col-sm-4 text-center">
				<h3>Facebook</h3>

				<p class="text-muted">
					破滅派からのお知らせが届きます。
				</p>

				<div class="fb-like" data-href="https://www.facebook.com/hametuha.inc" data-show-faces="false"
				     data-layout="button_count"></div>

			</div>

			<div class="col-xs-12 col-sm-4 text-center">
				<h3>Twitter</h3>

				<p class="text-muted">
					更新情報をつぶやいています。
				</p>
				<a href="https://twitter.com/hametuha" class="twitter-follow-button" data-show-count="false"
				   data-lang="ja" data-size="large">@hametuhaさんをフォロー</a>
			</div>

		</div><!-- //.row -->
	</div>

	<div class="footer-links-wrapper">
		<p class="footer-hametuha text-center">
			<i class="icon-hametuha"></i>
		</p>
		<div class="container">



			<div class="row footer-links">

				<div class="col-xs-12">
					<?php wp_nav_menu( array(
						'theme_location' => 'hametuha_global_about',
						'container'      => false,
						'menu_class'     => 'footer-links-nav',
						'depth'          => 1,
					) ); ?>

				</div>

				<div class="col-xs-12">
					<ul class="footer-links-nav">
						<?php
						foreach (
							[
								'ha'          => [ 'https://hametuha.co.jp', '株式会社破滅派', '' ],
								'cup'         => [ 'http://hametuha.tokyo', '無目的スペース', '' ],
								'image2'      => [ 'http://hametuha.pics', 'はめぴくっ！', '' ],
								'googleplus3' => [
									'https://plus.google.com/b/115001047459194790006/115001047459194790006/about/p/pub',
									'Google+',
									'rel="publisher"'
								],
								'youtube'     => [ 'https://www.youtube.com/user/hametuha', 'Youtube', '' ],
								'minicome'    => [ 'http://minico.me', 'ミニコme!', '' ],
								'github3'     => [ 'https://github.com/hametuha/', 'Github', '' ],
							] as $icon => list(
							$url, $label, $atts
						)
						) :
							?>
							<li>
								<a href="<?= $url ?>" <?= $atts ?>><?= esc_html( $label ) ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

			</div><!-- //.row -->
		</div><!-- //.container -->

	</div>

	<div class="footer-copynote">
		<p class="copy-right text-center">
			&copy; <span itemprop="copyrightYear">2007</span> 破滅派
		</p>
	</div><!-- copynote -->

</footer>

</div><!-- //#whole-body -->
<?php
if ( is_preview() ) {
	echo '<div id="watermark">プレビュー</div>';
}
get_template_part( 'parts/modal' );
wp_footer();
?>
</body>
</html>
