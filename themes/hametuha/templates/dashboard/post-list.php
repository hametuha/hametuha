<?php
/**
 * Post list
 *
 * @parm array $args
 */
$post_type = $args['post_type'] ?? '';
$as        = $args['as'];
?>
<div id="post-list-container" data-post-type="<?php echo esc_attr( $post_type ); ?>" data-as="<?php echo esc_attr( $as ); ?>">
</div>
<?php
