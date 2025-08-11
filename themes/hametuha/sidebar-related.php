<?php
/**
 * 作品リストを表示する
 */

?>
<div class="container recommend-wrapper">
	<div class="row row--recommend row--catNav">

		<div class="col-xs-12 col-sm-4">
			<h3 class="list-title">オススメ</h3>
			<ul class="post-list">
				<?php
				$lists = get_posts( [
					'post_type'      => 'lists',
					'meta_query'     => [
						[
							'key'   => '_recommended_list',
							'value' => '1',
						],
					],
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'orderby'        => [ 'date' => 'DESC' ],
				] );
				foreach ( $lists as $list ) :
					$sub_query = new WP_Query( [
						'post_type'      => 'in_list',
						'post_status'    => 'publish',
						'post_parent'    => $list->ID,
						'posts_per_page' => '3',
					] );
					while ( $sub_query->have_posts() ) {
						$sub_query->the_post();
						get_template_part( 'parts/loop', 'front' );
					}
					wp_reset_postdata();
					?>
				<?php endforeach; ?>
			</ul>

		</div>

		<div class="col-xs-12 col-sm-4">
			<h3 class="list-title">新着</h3>
			<ul class="post-list">
				<?php
				foreach ( hametuha_recent_posts( 3 ) as $post ) {
					setup_postdata( $post );
					get_template_part( 'parts/loop', 'front' );
				}
				wp_reset_postdata();
				?>
			</ul>
		</div>

		<div class="col-xs-12 col-sm-4">
			<h3 class="list-title">タグ</h3>
			<p class="tag-cloud">
				<?php wp_tag_cloud(); ?>
			</p>
		</div>
	</div>
</div>
