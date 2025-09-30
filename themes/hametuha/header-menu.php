<?php
/**
 * ヘッダーメニュー
 */

$cache_key = 'hametuha-header-menu-' . wp_get_theme()->get( 'Version' );
$cached = get_transient( $cache_key );
if ( 'local' !== wp_get_environment_type() && false !== $cached ) {
	// キャッシュが存在していたらそれを返して終了。
	echo $cached;
	return;
}
// キャッシュ開始
ob_start();
?>
<div class="offcanvas-header">
	<h5 class="offcanvas-title" id="header-navigationLabel">メニュー</h5>
	<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
</div>
<div class="offcanvas-body">
	<nav class="navbar-nav flex-column">
		<ul class="nav flex-column">
			<li class="nav-item">
				<a class="nav-link" rel="home" href="<?php echo home_url(); ?>">
					<i class="icon-home"></i> ホーム
				</a>
			</li>
			<li class="nav-item">
				<div class="nav-link-group d-flex justify-content-between align-items-center">
					<a class="nav-link flex-grow-1" href="<?php echo get_post_type_archive_link( 'news' ); ?>">
						<i class="icon-newspaper"></i> 文学ニュース
					</a>
					<button class="btn btn-sm btn-link nav-toggle" data-bs-toggle="collapse" data-bs-target="#newsSubmenu" aria-expanded="false">
						<i class="icon-arrow-up close"></i>
						<i class="icon-plus open"></i>
					</button>
				</div>
				<ul class="collapse nav flex-column ps-3" id="newsSubmenu">
					<?php foreach ( get_terms( 'genre' ) as $term ) : ?>
						<li class="nav-item">
							<a class="nav-link" href="<?php echo get_term_link( $term ); ?>">
								<?php echo esc_html( $term->name ); ?> <small
									class="text-muted"><?php echo number_format_i18n( $term->count ); ?></small>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</li>

			<li class="nav-item">
				<hr class="dropdown-divider">
			</li>

			<li class="nav-item"><span class="nav-link text-muted">作品</span></li>

			<li class="nav-item">
				<div class="nav-link-group d-flex justify-content-between align-items-center">
					<a class="nav-link flex-grow-1" href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>">
						すべての作品
					</a>
					<button class="btn btn-sm btn-link nav-toggle" data-bs-toggle="collapse" data-bs-target="#worksSubmenu" aria-expanded="false">
						<i class="icon-arrow-up close"></i>
						<i class="icon-plus open"></i>
					</button>
				</div>
				<ul class="collapse nav flex-column ps-3" id="worksSubmenu">
					<?php foreach ( get_categories( [ 'parent' => 0 ] ) as $cat ) : ?>
						<li class="nav-item">
							<a class="nav-link" href="<?php echo get_category_link( $cat ); ?>">
								<?php echo esc_html( $cat->name ); ?>
								<small class="text-muted"><?php echo number_format_i18n( $cat->count ); ?>
									作品</small>
							</a>
						</li>
					<?php endforeach; ?>
					<li class="nav-item">
						<a class="nav-link d-flex justify-content-between align-items-center" href="#genreSubmenu" data-bs-toggle="collapse" aria-expanded="false">
							<span>ジャンル</span>
							<i class="icon-arrow-up close"></i>
							<i class="icon-plus open"></i>
						</a>
						<ul class="collapse nav flex-column ps-3" id="genreSubmenu">
							<?php
							$genres = get_tags( [
								'meta_query' => [
									[
										'key'   => 'genre',
										'value' => 'サブジャンル'
									],
								],
							] );
							foreach ( $genres as $tag ) :
								?>
								<li class="nav-item">
									<a class="nav-link" href="<?php echo get_tag_link( $tag ); ?>">
										<?php echo esc_html( $tag->name ); ?>
										<small class="text-muted"><?php echo number_format_i18n( $tag->count ); ?>
											件</small>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				</ul>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo home_url( '/kdp/' ); ?>">
					<i class="icon-amazon"></i> Kindle電子書籍
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo get_post_type_archive_link( 'series' ); ?>">
					<i class="icon-stack"></i> 連載
				</a>
			</li>
			<li class="nav-item">
				<hr class="dropdown-divider">
			</li>
			<li class="nav-item"><span class="nav-link text-muted">読者の反応</span></li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo home_url( '/ranking/' ); ?>">
					<i class="icon-crown"></i> 厳粛なランキング
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo home_url( '/recommends/' ); ?>">
					<i class="icon-heart"></i> 編集部おすすめ
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo get_post_type_archive_link( 'lists' ); ?>">
					<i class="icon-library2"></i> みんなで作るリスト
				</a>
			</li>
			<li class="nav-item">
				<div class="nav-link-group d-flex justify-content-between align-items-center">
					<span class="nav-link flex-grow-1 text-muted">
						<i class="icon-bubble"></i> レビュー
					</span>
					<button class="btn btn-sm btn-link nav-toggle" data-bs-toggle="collapse" data-bs-target="#reviewSubmenu" aria-expanded="false">
						<i class="icon-arrow-up close"></i>
						<i class="icon-plus open"></i>
					</button>
				</div>
				<ul class="collapse nav flex-column ps-3" id="reviewSubmenu">
					<?php foreach ( get_terms( 'review', [ 'hide_empty' => false ] ) as $term ) : ?>
						<li class="nav-item">
							<a class="nav-link"
								href="<?php echo home_url( "/reviewed/{$term->term_id}/" ); ?>"><?php echo esc_html( $term->name ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</li>
			<li class="nav-item">
				<hr class="dropdown-divider">
			</li>
			<li class="nav-item"><span class="nav-link text-muted">コミュニティ</span></li>

			<li class="nav-item">
				<a class="nav-link" href="<?php echo home_url( '/authors/' ); ?>">
					<i class="icon-user"></i> 執筆者一覧
				</a>
			</li>
			<li class="nav-item">
				<div class="nav-link-group d-flex justify-content-between align-items-center">
					<a class="nav-link flex-grow-1" href="<?php echo get_post_type_archive_link( 'thread' ); ?>">
						<i class="icon-stack-list"></i> なんでも掲示板
					</a>
					<button class="btn btn-sm btn-link nav-toggle" data-bs-toggle="collapse" data-bs-target="#threadSubmenu" aria-expanded="false">
						<i class="icon-arrow-up close"></i>
						<i class="icon-plus open"></i>
					</button>
				</div>
				<?php
				$topics = get_terms( 'topic' );
				if ( $topics && ! is_wp_error( $topics ) ) : ?>
					<ul class="collapse nav flex-column ps-3" id="threadSubmenu">
						<?php
						foreach ( $topics as $term ) : ?>
							<li class="nav-item">
								<a class="nav-link" href="<?php echo get_term_link( $term ); ?>">
									<?php echo esc_html( $term->name ); ?>
									<small class="text-muted"><?php echo number_format_i18n( $term->count ); ?>
										件</small>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo get_post_type_archive_link( 'ideas' ); ?>">
					<i class="icon-lamp"></i> 作品のアイデア
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo get_post_type_archive_link( 'anpi' ); ?>">
					<i class="icon-thumbs-up"></i> 同人の安否情報
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo get_post_type_archive_link( 'announcement' ); ?>">
					<i class="icon-bullhorn"></i> 告知
				</a>
			</li>

			<li class="nav-item">
				<hr class="dropdown-divider">
			</li>

			<li class="nav-item">
				<div class="nav-link-group d-flex justify-content-between align-items-center">
					<a class="nav-link flex-grow-1" href="<?php echo home_url( '/about/' ); ?>">
						<i class="icon-ha"></i> 破滅派とは
					</a>
					<button class="btn btn-sm btn-link nav-toggle" data-bs-toggle="collapse" data-bs-target="#aboutSubmenu" aria-expanded="false">
						<i class="icon-arrow-up close"></i>
						<i class="icon-plus open"></i>
					</button>
				</div>
				<ul class="collapse nav flex-column ps-3" id="aboutSubmenu">
					<li class="nav-item">
						<a class="nav-link" href="<?php echo home_url( '/inquiry/' ); ?>">
							<i class="icon-envelop"></i> お問い合わせ
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo home_url( '/sanka/' ); ?>">
							<i class="icon-enter"></i> 参加する
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo home_url( '/merumaga/' ); ?>">
							<i class="icon-mail"></i> メルマガ購読
						</a>
					</li>
				</ul>
			</li>
			<li class="nav-item">
				<div class="nav-link-group d-flex justify-content-between align-items-center">
					<a class="nav-link flex-grow-1" href="<?php echo home_url( 'help' ); ?>">
						<i class="icon-question2"></i> ヘルプセンター
					</a>
					<button class="btn btn-sm btn-link nav-toggle" data-bs-toggle="collapse" data-bs-target="#helpSubmenu" aria-expanded="false">
						<i class="icon-arrow-up close"></i>
						<i class="icon-plus open"></i>
					</button>
				</div>
				<?php
				$faqs = get_terms( [ 'taxonomy' => 'faq_cat' ] );
				if ( $faqs && ! is_wp_error( $faqs ) ) :
					?>
					<ul class="collapse nav flex-column ps-3" id="helpSubmenu">
						<?php foreach ( $faqs as $term ) : ?>
							<li class="nav-item">
								<a class="nav-link" href="<?php echo get_term_link( $term ); ?>">
									<?php echo esc_html( $term->name ); ?>について
									<small class="text-muted"><?php echo number_format_i18n( $term->count ); ?>
										件</small>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</li>
		</ul>
	</nav>
</div>
<?php
// 出力をキャッシュ保存
$cached_menu = ob_get_contents();
ob_end_flush();
set_transient( $cache_key, $cached_menu, 1800 );
