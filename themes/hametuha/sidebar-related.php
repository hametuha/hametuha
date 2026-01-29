<?php
/**
 * 作品リストを表示する
 *
 * このサイドバーは全ページで共通なので、10分キャッシュする
 */
$sidebar_cache_key = 'hametuha-sidebar-related';
$sidebar_cache     = get_transient( $sidebar_cache_key );
if ( false !== $sidebar_cache ) {
	// キャッシュがあればそれを返して終了
	echo $sidebar_cache;
	return;
}
ob_start();
?>
<div class="container recommend-wrapper">
	<div class="row row--recommend row--catNav">

		<div class="col-xs-12 col-sm-4">
			<?php
			$lists = new WP_Query( [
				'my-content'     => 'recommends',
				'post_type'      => 'lists',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => [ 'date' => 'DESC' ],
			] );
			if ( $lists->have_posts() ) :
				foreach ( $lists->posts as $list ) :
					$sub_query = new WP_Query( [
						'post_type'      => 'post',
						'post_status'    => 'publish',
						'in_list'        => $list->ID,
						'posts_per_page' => '3',
					] );
					?>
					<h3 class="list-title"><?php echo esc_html( get_the_title( $list ) ); ?></h3>
					<ul class="post-list">
						<?php
						while ( $sub_query->have_posts() ) {
							$sub_query->the_post();
							get_template_part( 'parts/loop', 'front' );
						}
						wp_reset_postdata();
						?>
					</ul>
				<?php endforeach; ?>
			<?php else : ?>
			<div class="alert alert-warning">
				編集部の怠慢により、おすすめリストが整備されていません。お手数ですが、編集部までお知らせください。
			</div>
			<?php endif; ?>
		</div>

		<div class="col-xs-12 col-sm-4">
			<h3 class="list-title">新着</h3>
			<?php
			$recent_posts = hametuha_recent_posts( 3 );
			if ( ! empty( $recent_posts ) ) :
				?>
				<ul class="post-list">
					<?php
					foreach ( $recent_posts as $post ) {
						setup_postdata( $post );
						get_template_part( 'parts/loop', 'front' );
					}
					wp_reset_postdata();
					?>
				</ul>
			<?php else : ?>
				<div class="alert alert-warning mt-3">
					最近、誰も作品を公開していません。破滅派存続の危機です。
				</div>
			<?php endif; ?>
		</div>

		<div class="col-xs-12 col-sm-4">
			<h3 class="list-title">タグ</h3>
			<p class="tag-cloud">
				<?php wp_tag_cloud(); ?>
			</p>
		</div>
	</div>
</div>
