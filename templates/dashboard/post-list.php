<?php
/**
 * Post list
 *
 * @parm array $args
 */
$post_type = $args['post_type'] ?? '';
?>
<div id="post-list-container" data-post-type="<?php echo esc_attr( $post_type); ?>">
</div>
<?php
