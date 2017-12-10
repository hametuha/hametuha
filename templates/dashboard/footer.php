<?php
$slug = isset( $slug ) ? $slug : '';
if ( ! $slug ) {
	return;
}
do_action( 'taro_ad_field', $slug, '<hr /><section class="hb-post-style">', '</section>' );
