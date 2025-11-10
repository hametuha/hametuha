<!-- title -->
<div class="page-header mb-5">
	<h1 class="post-title" itemprop="name">
		<?php the_title(); ?>
		<?php if ( is_recommended() ) : ?>
			<span class="badge bg-danger">オススメ</span>
		<?php else : ?>
			<span class="badge bg-secondary">リスト</span>
		<?php endif; ?>
		<?php if ( get_current_user_id() === (int) get_the_author_meta( 'ID' ) )  : ?>
			<span class="badge bg-success">あなたのリスト</span>
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


<?php
// リストの編集権限がある場合は削除ボタンを出す
if ( current_user_can( 'edit_post', get_the_ID() ) ) :
	?>
	<script id="my-list-deleter"
		type="application/json"
		data-list-id="<?php echo esc_attr( get_the_ID() ); ?>">
	</script>
<?php endif; ?>
