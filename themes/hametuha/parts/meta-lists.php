<?php get_template_part( 'parts/bar', 'posttype' ); ?>

<?php get_template_part( 'parts/meta', 'thumbnail' ); ?>


<!-- title -->
<div class="page-header">
	<h1 class="post-title" itemprop="name">
		<?php the_title(); ?>
		<?php if ( is_recommended() ) : ?>
			<span class="label label-danger">オススメ</span>
		<?php else : ?>
			<small>リスト</small>
		<?php endif; ?>
	</h1>
</div>

<!-- Meta data -->
<div <?php post_class( 'post-meta' ); ?>>
	<?php get_template_part( 'parts/meta', 'single' ); ?>
</div><!-- //.post-meta -->

<?php if ( has_excerpt() ) : ?>
	<div class="excerpt">
		<?php the_excerpt(); ?>
	</div><!-- //.excerpt -->
<?php endif; ?>


<?php if ( is_user_logged_in() && get_current_user_id() == get_the_author_meta( 'ID' ) ) : ?>
<script id="my-list-deleter"
        type="application/json"
        data-list-id="<?php echo esc_attr( get_the_ID() ); ?>"
        data-url-base="<?php echo esc_url( home_url( Hametuha\Rest\ListCreator::$prefix . '/deregister/' . get_the_ID() . '/' ) ); ?>"
        data-nonce-action="list-deregister">
</script>
<?php endif; ?>
