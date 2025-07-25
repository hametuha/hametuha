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
<script id="my-list-deleter" type="text/x-jsrender" data-href="<?php echo home_url( 'your/lists/' ); ?>">
	<li><a href="<?php echo Hametuha\Rest\ListCreator::deregister_link( get_the_ID(), '{{:postId}}' ); ?>" class="deregister-button btn btn-xs btn-danger">&times; リストから削除</a></li>
</script>
<?php endif; ?>
