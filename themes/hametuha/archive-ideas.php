<?php
/**
 * アイデア帳のテンプレート
 *
 * @feature-group ideas
 * @since 7.9.0
 */

get_header();
get_header( 'sub' );
wp_enqueue_script( 'hametuha-components-idea-filter' );
get_template_part( 'templates/idea/form' );
global $wp_query;
?>
<header class="book-list-header">
	<div class="container">
		<small>Idea Notes</small>
		<h1><?php esc_html_e( '破滅派アイデア帳', 'hametuha' ); ?></h1>
		<p class="description">
			<?php echo esc_html( get_post_type_object( 'ideas' )->description ); ?>
		</p>
		<p>
			<button class="btn btn-lg btn-primary">
				<?php esc_html_e( 'アイデアを投稿する', 'hametuha' ); ?>
			</button>
		</p>
	</div>
</header>

<?php get_header( 'breadcrumb' ); ?>

<div class="container archive">

	<div class="idea-filter mb-5 mt-5">
		<?php
		// アイデアの絞り込み
		$ideas_by = [
			''      => 'みんなのアイデア',
			'mine'  => 'あなたのアイデア',
			'stock' => 'ストックしたアイデア',
		];
		$current_ide_by        = array_key_exists( get_query_var( 'idea_type' ), $ideas_by ) ? get_query_var( 'idea_type' ) : '';
		$current_idea_by_label = $ideas_by[ $current_ide_by ] ?? $ideas_by['みんなのアイデア'];
		// 検索クエリ
		$s = get_query_var( 's' );
		// 現在のタグ
		$terms = get_terms( [
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
			'parent'     => 0,
			'meta_query' => [
				[
					'key'   => 'genre',
					'value' => 'サブジャンル',
				],
			],
		] );
		$cur_tag = array_filter( explode( ',', get_query_var( 'tag' ) ) );
		?>
		<!-- 現在のステータス -->
		<div class="d-flex justify-content-between align-items-center mb-3">
			<div>
				<?php esc_html_e( 'アイデアの絞り込み: ', 'hametuha' ); ?>
				<span class="term-link">
					<?php echo esc_html( $current_idea_by_label ); ?>
				</span>
				<?php if ( $s ) : ?>
					<span class="term-link">
						<?php printf( esc_html__( '「%s」で検索', 'hametuha' ), esc_html( $s ) ); ?>
					</span>
				<?php endif; ?>
				<?php
				foreach ( $terms as $term ) :
					if ( ! in_array( $term->slug, $cur_tag, true ) ) {
						continue;
					}
					?>
					<span class="term-link">
						<?php echo esc_html( '#' . $term->name ); ?>
					</span>
				<?php endforeach; ?>
			</div>

			<!-- トグルボタン -->
			<button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
				data-bs-target="#ideaFilterForm" aria-expanded="false" aria-controls="ideaFilterForm">
				<i class="icon-filter"></i>
				<?php esc_html_e( '絞り込み', 'hametuha' ); ?>
			</button>
		</div>

		<!-- 絞り込みフォーム（折りたたみ） -->
		<div class="collapse" id="ideaFilterForm">
		<form method="get" action="<?php echo esc_url( home_url( '/ideas/' ) ); ?>" id="idea-filter-form" class="form-filter">

			<div class="row mb-5">
				<div class="col-6 col-xs-12">
					<h2 class="h6 form-label"><?php esc_html_e( '種別', 'hametuha' ); ?></h2>
					<?php
					foreach ( $ideas_by as $value => $label ) :
						$id = 'idea-type' . ( $value ? '-' . $value : '' );
						$url = trailingslashit( get_post_type_archive_link( 'ideas' ) );
						if ( $value ) {
							$url .=  $value . '/';
						}
						?>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="idea_type"
								id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>"
								<?php checked( $value, get_query_var( 'idea_type' ) ); ?>
								data-url="<?php echo esc_url( $url ); ?>"/>
							<label class="form-check-label" for="<?php echo esc_attr( $id ); ?>">
								<?php echo esc_html( $label ); ?>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="col-6 col-xs-12">
					<label for="idea-s" class="form-label"><?php esc_html_e( 'キーワード', 'hametuha' ); ?></label>
					<input type="seach" class="form-control" id="idea-s"
						placeholder="<?php esc_attr_e( '例・密室殺人', 'hametuha' ); ?>" name="s"
						value="<?php the_search_query(); ?>" />
				</div>

				<hr class="divider" />

				<div class="col-12">
					<h2 class="h6 form-label"><?php esc_html_e( 'タグ', 'hametuha' ); ?></h2>
					<input type="hidden" name="tag" value="<?php echo esc_attr( filter_input( INPUT_GET, 'tag' ) ); ?>" />
					<?php
					foreach ( $terms as $term ) :
						$id = 'idea-tag-' . $term->term_id;
						?>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="checkbox" name="tag"
								value="<?php echo esc_attr( $term->slug ); ?>"
								id="<?php echo esc_attr( $id ); ?>" />
							<label class="form-check-label" for="<?php echo esc_attr( $id ); ?>">
								<?php echo esc_html( $term->name ); ?>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- 検索・フィルターボタン -->
			<div class="d-flex gap-2">
				<button type="submit" class="btn btn-primary">
					<?php esc_html_e( '絞り込む', 'hametuha' ); ?>
				</button>
				<button type="button" class="btn btn-outline-secondary" onclick="window.location.href='<?php echo esc_url( get_post_type_archive_link( 'ideas' ) ); ?>'">
					<?php esc_html_e( 'リセット', 'hametuha' ); ?>
				</button>
			</div>
		</form>
		</div> <!-- /.collapse -->
	</div>

	<p class="text-muted mb-3 mt-3">
		<?php
		global $wp_query;
		printf( esc_html__( '%d件のアイデアが見つかりました', 'hametuha' ), $wp_query->found_posts );
		?>
	</p>

	<?php if ( have_posts() ) : ?>
		<div class="card-list row">

			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'templates/idea/loop' );
			endwhile;
			?>
		</div>

		<?php wp_pagenavi(); ?>
	<?php
	else :
		// 該当するコンテンツがない
		?>
		<div class="nocontents-found alert alert-warning mb-5">
			<p>
				<?php esc_html_e( '該当するアイデアは見つかりませんでした。アイデアの投稿・ストックなどをして、創作の種を集めておきましょう。', 'hametuha' ); ?>
			</p>
		</div>
		<?php
	endif;

	// タグクラウドを出力
	get_template_part( 'templates/idea/tag-cloud' );
	?>


</div><!-- //.container -->

<?php
get_footer( 'ebooks' );
get_footer( 'books' );
get_footer();
