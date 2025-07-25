<p class="post-type-bar clearfix">
	<?php $post_object = get_post_type_object( get_post_type() ); ?>
	<a class="small-button alignright" href="<?php echo get_post_type_archive_link( get_post_type() ); ?>">
		<?php echo $post_object->labels->name; ?>トップへ
	</a>
	<?php echo $post_object->labels->name; ?>
</p>
