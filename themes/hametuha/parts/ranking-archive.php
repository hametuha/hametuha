<?php
/**
 * ランキングアーカイブ
 */

global $wp_query;
$query = $wp_query;
if ( $query->have_posts() ) :


	?>
	<ol class="archive-ranking">
		<?php
		$counter = 0;
		while ( $query->have_posts() ) {
			$query->the_post();
			++$counter;
			$even = ( 0 === $counter % 2 ) ? ' even' : ' odd';
			get_template_part( 'parts/loop', 'ranking' );
		}
		?>
	</ol>

	<?php wp_pagenavi( [ 'query' => $query ] ); ?>


<?php else : ?>

	<div class="nocontents-found alert alert-warning">
		<p><?php esc_html_e( 'この条件のランキングに該当する投稿はありませんでした。', 'hametuha' ); ?></p>
	</div>

	<?php
endif;
wp_reset_postdata();
