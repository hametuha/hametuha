<?php
/**
 * アーカイブページのトップに出る
 *
 *
 */
$post_object = get_post_type_object( get_post_type() );
?>
<p class="post-type-bar mb-3">
	<span>
		<?php echo $post_object->labels->name; ?>
	</span>
	<a class="btn btn-sm btn-primary" href="<?php echo get_post_type_archive_link( get_post_type() ); ?>">
		<?php echo $post_object->labels->name; ?>トップへ
	</a>
</p>
