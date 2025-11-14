<?php
// 人気作品
$authors_ranking = hametuha_get_author_popular_works( null, 6 );
// 前後の作品
$authors_recent = hametuha_get_author_work_siblings();

?>
<?php if ( $authors_recent ) : ?>
<div class="m20">
		<h3 class="list-title list-title-inverse">
			<?php esc_html_e( 'この作者の他の作品' ); ?>
		</h3>

		<ul class="post-list post-list-card">
			<?php
			foreach ( $authors_recent as $post ) :
				setup_postdata( $post );
				?>
				<?php get_template_part( 'parts/loop', 'front' ); ?>
				<?php
			endforeach;
			wp_reset_postdata();
			?>
		</ul>
</div>
<?php endif; ?>

<?php if ( $authors_ranking ) : ?>
<div class="m20">
	<h3 class="list-title list-title-inverse">
		<?php esc_html_e( 'この作者の人気作' ); ?>
	</h3>

	<ul class="post-list post-list-card">
		<?php
		foreach ( $authors_ranking as $post ) :
			setup_postdata( $post );
			?>
			<?php get_template_part( 'parts/loop', 'front' ); ?>
			<?php
		endforeach;
		wp_reset_postdata();
		?>
	</ul>

</div>
<?php endif; ?>

<section class="m20 mb-2 mt-2">

	<div class="text-center">
		<a href="<?php echo home_url( '/doujin/detail/' . get_the_author_meta( 'nicename' ) . '/' ); ?>" class="btn btn-primary btn-lg">
			<?php esc_html_e( '著者詳細プロフィール', 'hametuha' ); ?>
		</a>
	</div>

</section>
