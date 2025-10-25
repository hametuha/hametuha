<?php
/**
 * 検索用サイドバー
 */
wp_enqueue_script( 'hametuha-components-post-search-helper' );

$current_cat = get_query_var( 'cat' );
?>
<div class="col-12 col-lg-3 order-1" id="sidebar" role="navigation">

	<!-- モバイル用トグルボタン -->
	<button class="btn btn-primary d-lg-none w-100 mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
		<i class="bi bi-funnel"></i> 絞り込み検索
	</button>

	<!-- 折りたたみコンテンツ（デスクトップでは常に表示） -->
	<div class="collapse d-lg-block" id="filterCollapse">
		<div class="post-search">
			<h2 class="h5 mb-3">作品検索</h2>

			<form method="get" action="<?php echo home_url( '/latest/' ); ?>" id="post-filter-form">

				<!-- 現在のフィルター表示 -->
				<div class="filter-status mb-3" id="filter-status" style="display: none;">
					<div class="d-flex flex-wrap gap-2 mb-2" id="active-filters"></div>
					<button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-filters">
						すべてクリア
					</button>
				</div>

				<!-- 検索実行ボタン（上部） -->
				<button type="submit" class="btn btn-primary w-100 mb-3">
					<i class="bi bi-search"></i> 検索する
				</button>

				<!-- おすすめタグ -->
				<?php
				$popular_tags = hametuha_get_popular_tags( 10 );
				if ( ! empty( $popular_tags ) ) :
					?>
					<div class="mb-3">
						<h3 class="h6 mb-2">おすすめタグ</h3>
						<div class="d-flex flex-wrap gap-2">
							<?php foreach ( $popular_tags as $tag ) : ?>
								<button type="button" class="btn btn-sm btn-outline-primary tag-quick-select" data-tag="<?php echo esc_attr( $tag->name ); ?>">
									<?php echo esc_html( $tag->name ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
					<?php
				endif;
				?>

				<!-- フィルターアコーディオン -->
				<div class="accordion accordion-flush" id="filterAccordion">

					<!-- ジャンル -->
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#genreFilter" aria-expanded="false" aria-controls="genreFilter">
								<?php esc_html_e( 'ジャンル', 'hametuha' ); ?>
							</button>
						</h3>
						<div id="genreFilter" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
							<div class="accordion-body">
								<?php
								$categories = get_categories();
								?>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="genre"
										value="" id="genre-all"
										<?php checked( '', $current_cat ); ?>
										data-action="<?php echo esc_url( home_url( 'latest' ) ); ?>">
									<label class="form-check-label" for="genre-all"><?php esc_html_e( 'すべてのジャンル', 'hametuha' ); ?></label>
								</div>
								<?php
								foreach ( $categories as $category ) {
									?>
									<div class="form-check">
										<input class="form-check-input" type="radio" name="genre"
											data-action="<?php echo esc_url( get_term_link( $category ) ) ?>"
											value=""
											<?php checked( $current_cat, $category->term_id ); ?>
											id="genre-<?php echo esc_attr( $category->slug ); ?>">
										<label class="form-check-label" for="genre-<?php echo esc_attr( $category->slug ); ?>">
											<?php echo esc_html( $category->name ); ?>
										</label>
									</div>
									<?php
								}
								?>
							</div>
						</div>
					</div>

					<!-- タグ -->
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tagFilter" aria-expanded="false" aria-controls="tagFilter">
								タグ
							</button>
						</h3>
						<div id="tagFilter" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
							<div class="accordion-body">
								<div class="mb-2">
									<input type="text" class="form-control form-control-sm" name="tag" placeholder="タグで検索">
								</div>
								<?php
								// 人気タグをもっと多く取得（20個）
								$filter_tags = hametuha_get_popular_tags( 20 );
								foreach ( $filter_tags as $tag ) :
									$tag_id = 'tag-' . $tag->term_id;
									?>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" name="tags[]" value="<?php echo esc_attr( $tag->name ); ?>" id="<?php echo esc_attr( $tag_id ); ?>">
										<label class="form-check-label" for="<?php echo esc_attr( $tag_id ); ?>">
											<?php echo esc_html( $tag->name ); ?>
										</label>
									</div>
									<?php
								endforeach;
								?>
							</div>
						</div>
					</div>

					<!-- 長さ -->
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#lengthFilter" aria-expanded="false" aria-controls="lengthFilter">
								文字数
							</button>
						</h3>
						<div id="lengthFilter" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
							<div class="accordion-body">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="length[]" value="short" id="length-short">
									<label class="form-check-label" for="length-short">短編（〜5,000字）</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="length[]" value="medium" id="length-medium">
									<label class="form-check-label" for="length-medium">中編（5,000〜20,000字）</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="length[]" value="long" id="length-long">
									<label class="form-check-label" for="length-long">長編（20,000字〜）</label>
								</div>
							</div>
						</div>
					</div>

					<!-- 評価 -->
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ratingFilter" aria-expanded="false" aria-controls="ratingFilter">
								評価
							</button>
						</h3>
						<div id="ratingFilter" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
							<div class="accordion-body">
								<div class="form-check">
									<input class="form-check-input" type="radio" name="rating" value="" id="rating-all" checked>
									<label class="form-check-label" for="rating-all">すべて</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="rating" value="5" id="rating-5">
									<label class="form-check-label" for="rating-5">★★★★★ 5つ星</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="rating" value="4" id="rating-4">
									<label class="form-check-label" for="rating-4">★★★★☆ 4つ星以上</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="rating" value="3" id="rating-3">
									<label class="form-check-label" for="rating-3">★★★☆☆ 3つ星以上</label>
								</div>
							</div>
						</div>
					</div>

					<!-- コメント数 -->
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#commentFilter" aria-expanded="false" aria-controls="commentFilter">
								コメント数
							</button>
						</h3>
						<div id="commentFilter" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
							<div class="accordion-body">
								<div class="form-check">
									<input class="form-check-input" type="radio" name="comments" value="" id="comments-all" checked>
									<label class="form-check-label" for="comments-all">すべて</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="comments" value="10" id="comments-10">
									<label class="form-check-label" for="comments-10">10件以上</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="comments" value="5" id="comments-5">
									<label class="form-check-label" for="comments-5">5件以上</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="comments" value="1" id="comments-1">
									<label class="form-check-label" for="comments-1">1件以上</label>
								</div>
							</div>
						</div>
					</div>

				</div><!-- //.accordion -->

				<!-- 検索実行ボタン（下部） -->
				<button type="submit" class="btn btn-primary w-100 mt-3">
					<i class="bi bi-search"></i> 検索する
				</button>

			</form>

		</div>
	</div>

</div><!-- //#sidebar -->
