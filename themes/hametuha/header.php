<?php
/**
 * 破滅派の共通ヘッダー
 */
get_header( 'meta' );
?>
<header id="header" class="navbar navbar-expand-lg navbar-light fixed-top" role="navigation">
	<div class="container d-flex justify-content-between align-items-center">
		<!-- Toggle buttons (left side) -->
		<div class="d-flex">
			<button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#header-navigation"
				aria-controls="header-navigation" aria-expanded="false" aria-label="Toggle navigation">
				<i class="icon-menu"></i>
			</button>

			<?php if ( ! is_hamenew() ) : ?>
				<button class="navbar-toggler navbar-write write-panel-btn ms-2" type="button">
					<i class="icon-quill"></i>
				</button>
			<?php endif; ?>
		</div>

		<!-- Logo (center) -->
		<a class="navbar-brand logo" rel="home" href="<?php echo home_url( '/' ); ?>">
			<i class="icon-hametuha"></i><span><?php bloginfo( 'name' ); ?></span>
		</a>

		<!-- User info (right side) -->
		<div id="user-info"></div>

	</div><!-- .container -->
</header>

<!-- Offcanvas Navigation Menu -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="header-navigation" aria-labelledby="header-navigationLabel">
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
					<a class="nav-link" href="<?php echo get_post_type_archive_link( 'news' ); ?>">
						<i class="icon-newspaper"></i> はめにゅー
					</a>
					<ul class="nav flex-column ps-3">
						<?php foreach ( get_terms( 'genre' ) as $term ) : ?>
							<li class="nav-item">
								<a class="nav-link" href="<?php echo get_term_link( $term ); ?>">
									<?php echo esc_html( $term->name ); ?> <small
										class="text-muted"><?php echo number_format_i18n( $term->count ); ?></small>
								</a>
							</li>
						<?php endforeach; ?>
						<li class="nav-item">
							<span class="nav-link"><i class="icon-tag5"></i> キーワード</span>
							<ul class="nav flex-column ps-3">
								<?php foreach ( get_terms( 'nouns' ) as $term ) : ?>
									<li class="nav-item">
										<a class="nav-link" href="<?php echo get_term_link( $term ); ?>">
											<?php echo esc_html( $term->name ); ?> <small
												class="text-muted"><?php echo number_format_i18n( $term->count ); ?></small>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
					</ul>
				</li>

				<li class="nav-item">
					<hr class="dropdown-divider">
				</li>
				<li class="nav-item"><span class="nav-link text-muted">作品</span></li>

				<li class="nav-item">
					<a class="nav-link"
						href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>">全ジャンル</a>
					<ul class="nav flex-column ps-3">
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
							<span class="nav-link">すべてのタグ</span>
							<ul class="nav flex-column ps-3">
								<?php foreach ( get_tags() as $tag ) : ?>
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
				<li class="nav-item"><span class="nav-link text-muted">コミュニティ</span></li>

				<li class="nav-item">
					<a class="nav-link" href="<?php echo home_url( '/authors/' ); ?>">
						<i class="icon-user"></i> 執筆者一覧
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?php echo get_post_type_archive_link( 'thread' ); ?>">
						<i class="icon-stack-list"></i> なんでも掲示板
					</a>
					<ul class="nav flex-column ps-3">
						<?php foreach ( get_terms( 'topic' ) as $term ) : ?>
							<li class="nav-item">
								<a class="nav-link" href="<?php echo get_term_link( $term ); ?>">
									<?php echo esc_html( $term->name ); ?>
									<small class="text-muted"><?php echo number_format_i18n( $term->count ); ?>
										件</small>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?php echo get_post_type_archive_link( 'lists' ); ?>">
						<i class="icon-library2"></i> みんなで作るリスト
					</a>
				</li>
				<li class="nav-item">
					<span class="nav-link">
						<i class="icon-bubble"></i> 読者によるレビュー
					</span>
					<ul class="nav flex-column ps-3">
						<?php foreach ( get_terms( 'review', [ 'hide_empty' => false ] ) as $term ) : ?>
							<li class="nav-item">
								<a class="nav-link"
									href="<?php echo home_url( "/reviewed/{$term->term_id}/" ); ?>"><?php echo esc_html( $term->name ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
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
				<li class="nav-item"><span class="nav-link text-muted">About</span></li>

				<li class="nav-item">
					<a class="nav-link" href="<?php echo home_url( '/about/' ); ?>">
						<i class="icon-ha"></i> 破滅派とは
					</a>
					<ul class="nav flex-column ps-3">
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
						<li class="nav-item">
							<a class="nav-link" href="<?php echo get_post_type_archive_link( 'newsletters' ); ?>">
								メルマガバックナンバー
							</a>
						</li>
					</ul>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?php echo home_url( 'help' ); ?>">
						<i class="icon-question2"></i> ヘルプセンター
					</a>
					<?php
					$faqs = get_terms( [ 'taxonomy' => 'faq_cat' ] );
					if ( $faqs && ! is_wp_error( $faqs ) ) :
						?>
						<ul class="nav flex-column ps-3">
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
</div>
<!-- // .offcanvas -->
