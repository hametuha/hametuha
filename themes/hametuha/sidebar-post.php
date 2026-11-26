<?php
/**
 * 検索用サイドバー
 */
wp_enqueue_script( 'hametuha-components-post-search-helper' );

$queried_object = get_queried_object();
// カテゴリー
$current_cat = get_query_var( 'cat' );
// 現在していされているタグを取得（$query_tags は ?t= の分だけ。タグの一覧のタグはパスにある）
$query_tags   = hametuha_queried_tags();
$current_tags = $query_tags;
if ( is_a( $queried_object, 'WP_Term' ) && 'post_tag' === $queried_object->taxonomy ) {
	$current_tags[] = $queried_object->name;
}
// 投稿年
$cur_years = hametuha_queried_year_range();
// 文字の長さ
$cur_length = isset( $_GET['length'] ) ? (array) $_GET['length'] : [];
// 星による評価
$cur_rating = get_query_var( 'rating' );
// 感想
$cur_reviews = isset( $_GET['reaction'] ) ? (array) $_GET['reaction'] : [];
// コメント数
$cur_comments = get_query_var( 'comments' );
?>
<div class="col-12 col-lg-3 order-1 mb-3 post-search__wrapper pb-3 pt-3" id="sidebar" role="navigation">

	<!-- モバイル用トグルボタン -->
	<button class="btn btn-outline-primary d-lg-none w-100" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
		<i class="icon-filter"></i> 条件で絞り込み
	</button>

	<!-- 現在のフィルター表示 -->
	<?php
	$active_filters = [];
	$search_keyword = get_search_query();

	// 現在のURLパラメータを取得
	$current_params = $_GET;

	// 作品一覧（「投稿ページ」に指定した固定ページ。/latest/）
	$latest_url = get_post_type_archive_link( 'post' );

	// バッジを外したときの行き先。タグ・ジャンルなどの一覧では、その一覧にとどまる（#538）。
	// その一覧のタグ・ジャンル自体を外すときだけ、一覧を出て /latest/ に行く（ほかの条件は引き継ぐ）
	$filter_base = ( is_category() || is_tag() || is_tax() ) ? get_term_link( $queried_object ) : '';
	if ( ! $filter_base || is_wp_error( $filter_base ) ) {
		$filter_base = $latest_url;
	}
	$filter_url = function ( array $params, $leave_archive = false ) use ( $filter_base, $latest_url ) {
		return add_query_arg( $params, $leave_archive ? $latest_url : $filter_base );
	};

	// キーワード検索
	if ( ! empty( $search_keyword ) ) {
		$params_without_s = $current_params;
		unset( $params_without_s['s'] );
		$url              = $filter_url( $params_without_s );
		$active_filters[] = [
			'label' => sprintf( 'キーワード: %s', esc_html( $search_keyword ) ),
			'url'   => $url,
		];
	}

	// カテゴリー
	if ( $current_cat ) {
		$category           = get_category( $current_cat );
		$params_without_cat = $current_params;
		unset( $params_without_cat['cat'] );
		$url              = $filter_url( $params_without_cat, is_category() );
		$active_filters[] = [
			'label' => sprintf( 'ジャンル: %s', esc_html( $category->name ) ),
			'url'   => $url,
		];
	}

	// タグ
	foreach ( $current_tags as $tag_name ) {
		$params_without_tag      = $current_params;
		$remaining_tags          = array_diff( $query_tags, [ $tag_name ] );
		$params_without_tag['t'] = $remaining_tags;
		if ( empty( $remaining_tags ) ) {
			unset( $params_without_tag['t'] );
		}
		$url              = $filter_url( $params_without_tag, is_tag() && $tag_name === $queried_object->name );
		$active_filters[] = [
			'label' => sprintf( 'タグ: %s', esc_html( $tag_name ) ),
			'url'   => $url,
		];
	}

	// 投稿年
	if ( $cur_years['from'] || $cur_years['to'] ) {
		$params_without_years = $current_params;
		unset( $params_without_years['year_from'], $params_without_years['year_to'] );
		$active_filters[] = [
			'label' => sprintf(
				'投稿年: %s〜%s',
				$cur_years['from'] ? $cur_years['from'] . '年' : '',
				$cur_years['to'] ? $cur_years['to'] . '年' : ''
			),
			'url'   => $filter_url( $params_without_years ),
		];
	}

	// 文字数
	if ( ! empty( $cur_length ) ) {
		$length_categories = hametuha_story_length_category();
		foreach ( $cur_length as $length_key ) {
			if ( isset( $length_categories[ $length_key ] ) ) {
				$params_without_length           = $current_params;
				$remaining_length                = array_diff( $cur_length, [ $length_key ] );
				$params_without_length['length'] = $remaining_length;
				if ( empty( $remaining_length ) ) {
					unset( $params_without_length['length'] );
				}
				$url              = $filter_url( $params_without_length );
				$active_filters[] = [
					'label' => sprintf( '文字数: %s', esc_html( $length_categories[ $length_key ]['label'] ) ),
					'url'   => $url,
				];
			}
		}
	}

	// 評価
	if ( $cur_rating ) {
		$rating_labels         = [
			'4' => '★★★★ 4点台',
			'3' => '★★★ 3点台',
			'2' => '★★ 2点台',
			'1' => '★ 1点台',
		];
		$params_without_rating = $current_params;
		unset( $params_without_rating['rating'] );
		$url              = $filter_url( $params_without_rating );
		$active_filters[] = [
			'label' => sprintf( '評価: %s', isset( $rating_labels[ $cur_rating ] ) ? $rating_labels[ $cur_rating ] : $cur_rating ),
			'url'   => $url,
		];
	}

	// 感想（レビュータグ）
	if ( ! empty( $cur_reviews ) ) {
		foreach ( $cur_reviews as $review_tag ) {
			$params_without_review             = $current_params;
			$remaining_reviews                 = array_diff( $cur_reviews, [ $review_tag ] );
			$params_without_review['reaction'] = $remaining_reviews;
			if ( empty( $remaining_reviews ) ) {
				unset( $params_without_review['reaction'] );
			}
			$url              = $filter_url( $params_without_review );
			$active_filters[] = [
				'label' => sprintf( '感想: %s', esc_html( $review_tag ) ),
				'url'   => $url,
			];
		}
	}

	// コメント数
	if ( $cur_comments ) {
		$comment_groups = hametuha_comment_count_group();
		$comment_label  = '';
		foreach ( $comment_groups as $group ) {
			if ( (int) $cur_comments === $group['count'] ) {
				$comment_label = $group['label'];
				break;
			}
		}
		$params_without_comments = $current_params;
		unset( $params_without_comments['comments'] );
		$url              = $filter_url( $params_without_comments );
		$active_filters[] = [
			'label' => sprintf( 'コメント数: %s', esc_html( $comment_label ? $comment_label : $cur_comments . '件以上' ) ),
			'url'   => $url,
		];
	}

	$has_filters = ! empty( $active_filters );
	?>
	<div class="filter-status mb-3" id="filter-status"<?php echo $has_filters ? '' : ' style="display: none;"'; ?>>
		<div class="d-flex flex-wrap gap-2 mb-2 mt-2" id="active-filters">
			<?php foreach ( $active_filters as $filter ) : ?>
				<a href="<?php echo esc_url( $filter['url'] ); ?>" class="badge bg-primary text-decoration-none d-inline-flex align-items-center gap-1">
					<?php echo esc_html( $filter['label'] ); ?>
					<i class="icon-close"></i>
				</a>
			<?php endforeach; ?>
		</div>

		<a class="btn btn-sm btn-secondary" id="clear-all-filters" href="<?php echo esc_url( $latest_url ); ?>">
			<?php esc_html_e( 'すべてクリア', 'hametuha' ); ?>
		</a>
	</div>


	<!-- 折りたたみコンテンツ（デスクトップでは常に表示） -->
	<div class="collapse d-lg-block mt-3" id="filterCollapse">
		<div class="post-search">

			<h2 class="post-search__title h5 mb-3">作品検索</h2>

			<form method="get" action="<?php echo esc_url( $latest_url ); ?>" id="post-filter-form">
				<?php
				// 条件を変えても、今の並び順（タブで選んだもの）を引き継ぐ
				hametuha_hidden_inputs( array_intersect_key( wp_unslash( $_GET ), array_flip( [ 'order', 'orderby' ] ) ) );
				?>

				<!-- おすすめタグ -->
				<?php
				$popular_tags = hametuha_get_popular_tags( 10 );
				if ( ! empty( $popular_tags ) ) :
					?>
					<div class="mb-3">
						<h3 class="h6 mb-2">おすすめタグ</h3>
						<div class="d-flex flex-wrap gap-2">
							<?php
							foreach ( $popular_tags as $tag ) :
								$tag_link_classes   = [ 'btn', 'btn-sm', 'btn' ];
								$tag_link_classes[] = in_array( $tag->name, $current_tags, true ) ? 'btn-primary' : 'btn-outline-primary';
								?>
								<a class="<?php echo implode( ' ', $tag_link_classes ); ?>"
									href="<?php echo esc_url( get_term_link( $tag ) ); ?>">
									<?php echo esc_html( $tag->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
					<?php
				endif;
				?>

				<div class="search-input mb-3">
					<label class="form-label"><?php esc_html_e( 'キーワード検索', 'hametuha' ); ?></label>
					<input class="form-control" type="search" name="s" value="<?php the_search_query(); ?>" placeholder="<?php esc_attr_e( '例・檸檬', 'hametuha' ); ?>" id="post-search-keyword" />
				</div>

				<!-- フィルターアコーディオン -->
				<div class="accordion accordion-flush" id="filterAccordion">

					<!-- ジャンル -->
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button<?php echo $current_cat ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#genreFilter" aria-expanded="<?php echo $current_cat ? 'true' : 'false'; ?>" aria-controls="genreFilter">
								<?php esc_html_e( 'ジャンル', 'hametuha' ); ?>
							</button>
						</h3>
						<div id="genreFilter" class="accordion-collapse<?php echo $current_cat ? '' : ' collapse'; ?>">
							<div class="accordion-body">
								<?php
								$categories = get_categories();
								?>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="genre"
										value="" id="genre-all"
										<?php checked( '', $current_cat ); ?>
										data-action="<?php echo esc_url( $latest_url ); ?>">
									<label class="form-check-label" for="genre-all"><?php esc_html_e( 'すべてのジャンル', 'hametuha' ); ?></label>
								</div>
								<?php
								foreach ( $categories as $category ) {
									?>
									<div class="form-check">
										<input class="form-check-input" type="radio" name="genre"
											data-action="<?php echo esc_url( get_term_link( $category ) ); ?>"
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
							<button class="accordion-button<?php echo ! empty( $current_tags ) ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#tagFilter" aria-expanded="<?php echo ! empty( $current_tags ) ? 'true' : 'false'; ?>" aria-controls="tagFilter">
								タグ
							</button>
						</h3>
						<div id="tagFilter" class="accordion-collapse<?php echo ! empty( $current_tags ) ? '' : ' collapse'; ?>">
							<div class="accordion-body">
								<?php
								// 人気タグをもっと多く取得（20個）
								$filter_tags    = hametuha_get_popular_tags( 20 );
								$tag_to_display = array_filter( $current_tags, function ( $t ) use ( $filter_tags ) {
									return ! in_array( $t, array_map( function ( $f ) {
										return $f->name;
									}, $filter_tags ), true );
								} );
								?>
								<div class="mb-2">
									<input type="text" class="form-control form-control-sm" name="t[]" placeholder="タグで検索"
										id="tag-free-input"
										value="<?php echo esc_attr( implode( ',', $tag_to_display ) ); ?>" />
									<small class="form-text text-muted"><?php esc_html_e( '複数タグはカンマ（,）で区切って入力', 'hametuha' ); ?></small>
								</div>
								<?php
								foreach ( $filter_tags as $tag ) :
									$tag_id = 'tag-' . $tag->term_id;
									?>
									<div class="form-check">
										<input class="form-check-input" type="checkbox"
											name="t[]" value="<?php echo esc_attr( $tag->name ); ?>"
											<?php checked( in_array( $tag->name, $current_tags, true ) ); ?>
											id="<?php echo esc_attr( $tag_id ); ?>">
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

					<!-- 投稿年 -->
					<?php $has_years = $cur_years['from'] || $cur_years['to']; ?>
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button<?php echo $has_years ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#yearFilter" aria-expanded="<?php echo $has_years ? 'true' : 'false'; ?>" aria-controls="yearFilter">
								投稿年
							</button>
						</h3>
						<div id="yearFilter" class="accordion-collapse<?php echo $has_years ? '' : ' collapse'; ?>">
							<div class="accordion-body">
								<div class="d-flex align-items-center gap-2">
									<?php
									foreach ( [
										'from' => '開始年',
										'to'   => '終了年',
									] as $year_key => $year_label ) :
										if ( 'to' === $year_key ) {
											echo '<span>〜</span>';
										}
										?>
										<label class="visually-hidden" for="year-<?php echo esc_attr( $year_key ); ?>"><?php echo esc_html( $year_label ); ?></label>
										<select class="form-select form-select-sm" name="year_<?php echo esc_attr( $year_key ); ?>" id="year-<?php echo esc_attr( $year_key ); ?>">
											<option value=""<?php selected( 0, $cur_years[ $year_key ] ); ?>>指定なし</option>
											<?php foreach ( hametuha_post_years() as $year ) : ?>
												<option value="<?php echo esc_attr( $year ); ?>"<?php selected( $year, $cur_years[ $year_key ] ); ?>><?php echo esc_html( $year ); ?>年</option>
											<?php endforeach; ?>
										</select>
									<?php endforeach; ?>
								</div>
								<small class="form-text text-muted">
									<?php esc_html_e( '片方だけでも指定できます（例・2019年まで）', 'hametuha' ); ?>
								</small>
							</div>
						</div>
					</div>

					<!-- 長さ -->
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button<?php echo ! empty( $cur_length ) ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#lengthFilter" aria-expanded="<?php echo ! empty( $cur_length ) ? 'true' : 'false'; ?>" aria-controls="lengthFilter">
								文字数
							</button>
						</h3>
						<div id="lengthFilter" class="accordion-collapse<?php echo ! empty( $cur_length ) ? '' : ' collapse'; ?>">
							<div class="accordion-body">
								<?php
								$cur_length = isset( $_GET['length'] ) ? (array) $_GET['length'] : [];
								foreach ( hametuha_story_length_category() as $length_category => $values ) :
									$id = 'length-' . $length_category;
									?>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" name="length[]"
											value="<?php echo esc_attr( $length_category ); ?>" id="<?php echo esc_attr( $id ); ?>"
											<?php checked( in_array( $length_category, $cur_length, true ) ); ?>
											/>
										<label class="form-check-label" for="<?php echo esc_attr( $id ); ?>">
											<?php
											printf(
												'%s（〜%s字）',
												esc_html( $values['label'] ),
												esc_html( number_format( $values['max'] ) )
											);
											?>
										</label>
									</div>
								<?php endforeach; ?>
								<small class="form-text text-muted">
									<?php
									printf(
										wp_kses_post( __( '長編は<a href="%1$s">%2$s</a>からお探しください', 'hametuha' ) ),
										esc_url( get_post_type_archive_link( 'series' ) ),
										esc_html( get_post_type_object( 'series' )->label )
									);
									?>
								</small>
							</div>
						</div>
					</div>

					<!-- 評価 -->
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button<?php echo $cur_rating ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#ratingFilter" aria-expanded="<?php echo $cur_rating ? 'true' : 'false'; ?>" aria-controls="ratingFilter">
								評価
							</button>
						</h3>
						<div id="ratingFilter" class="accordion-collapse<?php echo $cur_rating ? '' : ' collapse'; ?>">
							<div class="accordion-body">
								<div class="form-check">
									<input class="form-check-input" type="radio" name="rating" value="" id="rating-all" <?php checked( '', $cur_rating ); ?>>
									<label class="form-check-label" for="rating-all">すべて</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="rating" value="4" id="rating-4" <?php checked( '4', $cur_rating ); ?>>
									<label class="form-check-label" for="rating-4">★★★★ 4点台（4.0〜5.0）</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="rating" value="3" id="rating-3" <?php checked( '3', $cur_rating ); ?>>
									<label class="form-check-label" for="rating-3">★★★ 3点台（3.0〜3.9）</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="rating" value="2" id="rating-2" <?php checked( '2', $cur_rating ); ?>>
									<label class="form-check-label" for="rating-2">★★ 2点台（2.0〜2.9）</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="rating" value="1" id="rating-1" <?php checked( '1', $cur_rating ); ?>>
									<label class="form-check-label" for="rating-1">★ 1点台（1.0〜1.9）</label>
								</div>
							</div>
						</div>
					</div>

					<!-- 感想 -->
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button<?php echo ! empty( $cur_reviews ) ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#reviewFilter" aria-expanded="<?php echo ! empty( $cur_reviews ) ? 'true' : 'false'; ?>" aria-controls="reviewFilter">
								感想
							</button>
						</h3>
						<div id="reviewFilter" class="accordion-collapse<?php echo ! empty( $cur_reviews ) ? '' : ' collapse'; ?>">
							<div class="accordion-body">
								<?php
								$review_model = \Hametuha\Model\Review::get_instance();
								foreach ( $review_model->feedback_tags as $key => $terms ) :
									$label = $review_model->review_tag_label( $key );
									?>
									<div class="mb-3">
										<strong class="d-block mb-2"><?php echo esc_html( $label ); ?></strong>
										<?php
										foreach ( $terms as $term ) :
											$term_id = 'reaction-' . sanitize_title( $term );
											?>
											<div class="form-check">
												<input class="form-check-input" type="checkbox" name="reaction[]"
													value="<?php echo esc_attr( $term ); ?>"
													<?php checked( in_array( $term, $cur_reviews, true ) ); ?>
													id="<?php echo esc_attr( $term_id ); ?>">
												<label class="form-check-label" for="<?php echo esc_attr( $term_id ); ?>">
													<?php echo esc_html( $term ); ?>
												</label>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endforeach; ?>
								<small class="form-text text-muted">
									<?php esc_html_e( '複数選択可能（AND条件）', 'hametuha' ); ?>
								</small>
							</div>
						</div>
					</div>

					<!-- コメント数 -->
					<div class="accordion-item">
						<h3 class="accordion-header">
							<button class="accordion-button<?php echo $cur_comments ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#commentFilter" aria-expanded="<?php echo $cur_comments ? 'true' : 'false'; ?>" aria-controls="commentFilter">
								コメント数
							</button>
						</h3>
						<div id="commentFilter" class="accordion-collapse<?php echo $cur_comments ? '' : ' collapse'; ?>">
							<div class="accordion-body">
									<div class="form-check">
									<input class="form-check-input" type="radio" name="comments" value="" id="comments-all" <?php checked( '', $cur_comments ); ?>>
									<label class="form-check-label" for="comments-all">すべて</label>
								</div>
								<?php
								foreach ( hametuha_comment_count_group() as $comment_group ) {
									$comment_radio = <<<'HTML'
										<div class="form-check">
											<input class="form-check-input" type="radio" name="comments" value="%1$d" id="comments-%1$d" %2$s/>
											<label class="form-check-label" for="comments-%1$d">%3$s（%1$d件以上）</label>
										</div>
HTML;
									printf(
										$comment_radio,
										$comment_group['count'],
										checked( $comment_group['count'], (int) $cur_comments, false ),
										esc_html( $comment_group['label'] )
									);
								}
								?>
							</div>
						</div>
					</div>

				</div><!-- //.accordion -->

				<!-- 検索実行ボタン（下部） -->
				<button type="submit" class="btn btn-primary w-100 mt-3 mb-3">
					<i class="icon-search"></i> <?php esc_html_e( 'この条件で検索する', 'hametuha' ); ?>
				</button>

				<div class="mt-3">
					<a class="btn btn-block btn-outline-secondary mb-1" href="<?php echo home_url( 'authors' ); ?>">
						<?php esc_html_e( '作者から探す', 'hametuha' ); ?>
					</a>
					<a class="btn btn-block btn-outline-secondary mb-1" href="<?php echo esc_url( get_post_type_archive_link( 'series' ) ); ?>">
						<?php esc_html_e( '連載から探す', 'hametuha' ); ?>
					</a>
					<a class="btn btn-block btn-outline-secondary mb-1" href="<?php echo esc_url( home_url( 'ranking' ) ); ?>">
						<?php esc_html_e( 'ランキングで探す', 'hametuha' ); ?>
					</a>
					<a class="btn btn-block btn-outline-secondary mb-1" href="<?php echo esc_url( get_post_type_archive_link( 'lists' ) ); ?>">
						<?php esc_html_e( 'みんなのリスト', 'hametuha' ); ?>
					</a>
					<a class="btn btn-block btn-outline-secondary" href="<?php echo esc_url( hametuha_get_campaign_page_url() ); ?>">
						<?php esc_html_e( '公募一覧', 'hametuha' ); ?>
					</a>
				</div>

			</form>

		</div>
	</div>

</div><!-- //#sidebar -->
