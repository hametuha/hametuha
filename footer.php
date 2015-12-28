<?php if ( ! is_front_page() && 'kdp' !== get_query_var( 'meta_filter' ) ) {
	get_template_part( 'parts/list', 'kdp' );
} ?>




<div class="modal fade" id="searchBox" tabindex="-1" role="dialog" aria-labelledby="searchBox">
	<div class="modal-dialog">
		<form action="<?= home_url( '/', 'http' ) ?>" data-action="<?= home_url( '/', 'http' ) ?>">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="icon-search2"></i>検索フォーム</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="searchBoxS">キーワード</label>
						<input class="form-control" type="text" name="s" id="searchBoxS" value="<?php the_search_query() ?>" placeholder="ex. 面白い小説" />
					</div>
					<div class="form-group">
						<label class="radio-inline">
							<input type="radio" name="post_type" value="any" <?php checked( false !== array_search( get_query_var( 'post_type' ), [ '', 'any' ] ) ) ?>/> すべて
						</label>
						<?php
						foreach ( [
							'post' => '作品',
							'thread' => '掲示板',
							'anpi' => '安否',
							'faq' => 'よくある質問',
						] as $post_type => $label ) :
							?>
							<label class="radio-inline">
								<input type="radio" name="post_type" value="<?= $post_type ?>" <?php checked( get_query_var( 'post_type' ) === $post_type ) ?>/> <?= $label ?>
							</label>
						<?php endforeach; ?>
						<label class="radio-inline">
							<input type="radio" name="post_type" data-search-action="<?= home_url( 'authors/', 'http' ) ?>" value="author" <?php checked( 'author' === get_query_var( 'post_type' ) ) ?> /> 著者
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
					<input type="submit" class="btn btn-primary" value="検索" />
				</div>
			</div><!-- /.modal-content -->
		</form>
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<footer id="footer">
	<div id="footer-sidebar" class="container">
		<div class="col-sm-4 text-center">
			<h3>
				<small>FOLLOW US</small>
				<br/>
				フォローする
			</h3>
			<div class="row">

			</div>
			<hr/>
			<h4>メルマガ</h4>

			<p class="text-muted">
				月一（努力目標）で配信しています。
			</p>
			<a class="btn btn-primary" href="<?= home_url( '/merumaga/', 'http' ) ?>">
				<i class="icon-mail"></i> 購読する
			</a>

			<hr/>

			<h4>Facebook</h4>

			<p class="text-muted">
				破滅派からのお知らせが届きます。
			</p>

			<div class="fb-like" data-href="https://www.facebook.com/hametuha.inc" data-show-faces="false"
			     data-layout="button_count"></div>

			<hr/>

			<h4>Twitter</h4>

			<p class="text-muted">
				更新情報をつぶやいています。
			</p>
			<a href="https://twitter.com/hametuha" class="twitter-follow-button" data-show-count="false"
			   data-lang="ja" data-size="large">@hametuhaさんをフォロー</a>
		</div>
		<div class="col-sm-4">
			<h3 class="text-center">
				<small>ABOUT US</small>
				<br/>
				破滅派について
			</h3>
			<?php wp_nav_menu( array(
				'theme_location' => 'hametuha_global_about',
				'container'      => false,
				'menu_class'     => 'nav nav-pills nav-stacked',
			) ); ?>
		</div>
		<div class="col-sm-4">
			<h3 class="text-center">
				<small>LINKS</small>
				<br/>
				関連リンク
			</h3>
			<ul class="nav nav-pills nav-stacked external-links">
				<li>
					<a href="https://hametuha.co.jp">
						<i class="icon-ha"></i> <span>株式会社破滅派</span>
					</a>
				</li>
				<li>
					<a href="http://hametuha.tokyo">
						<i class="icon-cup"></i> <span>破滅サロン</span>
					</a>
				</li>
				<li>
					<a href="http://hametuha.pics">
						<i class="icon-images2"></i> <span>はめぴくっ！</span>
					</a>
				</li>
				<li>
					<a href="https://plus.google.com/b/115001047459194790006/115001047459194790006/about/p/pub"
					   rel="publisher">
						<i class="icon-googleplus3"></i> <span>Google+</span>
					</a>
				</li>
				<li>
					<a href="https://www.youtube.com/user/hametuha">
						<i class="icon-youtube"></i> <span>Youtube</span>
					</a>
				</li>
				<li>
					<a href="http://minico.me">
						<i class="icon-minicome"></i> <span>ミニコme!</span>
					</a>
				</li>
				<li>
					<a href="https://github.com/hametuha/">
						<i class="icon-github3"></i> <span>Github</span>
					</a>
				</li>
			</ul>
		</div>
	</div>

	<p class="copy-right text-center">
		&copy; <span itemprop="copyrightYear">2007</span> 破滅派
	</p>
</footer>
<?php
if ( is_preview() ) {
	echo '<div id="watermark">プレビュー</div>';
}
get_template_part( 'parts/modal' );
wp_footer();
?>
</body>
</html>
