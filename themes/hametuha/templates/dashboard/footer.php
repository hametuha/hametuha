<?php
/**
 * Home screen of hashboard/
 *
 * @parm array $args
 */
$slug = $args['slug'] ?? '';
if ( ! $slug ) {
	return;
}
do_action( 'taro_ad_field', $slug, '<hr /><section class="hb-post-style">', '</section>' );
