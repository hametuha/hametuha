<?php
/**
 * 画像、CSS、JSに関するものを記載
 *
 *
 *
 */

/**
 * デバッグ環境ならminをつける
 *
 * @param string $ext
 *
 * @return string
 */
function hametuha_min_ext( $ext = 'js' ) {
	$ext = '.' . $ext;
	if ( ! WP_DEBUG ) {
		$ext = '.min' . $ext;
	}

	return $ext;
}


/**
 * ループ内で投稿タイプのラベルを返す
 * @return string
 */
function get_current_post_type_label() {
	$post_type = get_post_type();
	switch ( $post_type ) {
		case 'info':
		case 'faq':
		case 'announcement':
			$post_type = get_post_type_object( $post_type );

			return $post_type->labels->singular_name;
			break;
		default:
			return '作品';
			break;
	}
}


/**
 * 投稿が少なくとも一つの画像を持っているか否か
 * @global object $post
 * @global wpdb $wpdb
 *
 * @param mixed $post
 *
 * @return boolean
 */
function has_image_attachment( $post = null ) {
	if ( is_null( $post ) ) {
		global $post;
	} else {
		$post = get_post( $post );
	}
	global $wpdb;
	$sql = "SELECT ID FORM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'attachment' AND post_mime_type LIKE 'image%'";

	return (bool) $wpdb->get_var( $wpdb->prepare( $sql, $post->ID ) );
}

/**
 * media_side_load_imageのパクリ
 *
 * GIF非対応のため
 *
 * @since 2.6.0
 *
 * @see media_sideload_image
 * @param string $file The URL of the image to download
 * @param int $post_id The post ID the media is to be associated with
 * @param string $desc Optional. Description of the image
 * @return int|WP_Error Attachment ID or WP_Error on failure
 */
function hametuha_sideload_image( $file, $post_id, $desc = null ) {
	// 写真アップロード用のライブラリを読み込み
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	if ( empty( $file ) ) {
		return new WP_Error( 500, 'ファイル名が指定されていません。', [ 'status' => 500 ] );
	}
	// Fix for external image
	$http = parse_url( $file );
	if ( isset( $http['query'] ) && ! empty( $http['query'] ) ) {
		parse_str( $http['query'], $str );
		if ( isset( $str['url'] ) ) {
			$file = rawurldecode( $str['url'] );
		}
	}
	// Set variables for storage, fix file filename for query strings.
	preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png|gif)\b/i', $file, $matches );
	$file_array         = array();
	$file_array['name'] = basename( $matches[0] );
	// Download file to temp location.
	$file_array['tmp_name'] = download_url( $file );
	// If error storing temporarily, return the error.
	if ( is_wp_error( $file_array['tmp_name'] ) ) {
		return $file_array['tmp_name'];
	}
	// Do the validation and storage stuff.
	$id = media_handle_sideload( $file_array, $post_id, $desc );
	// If error storing permanently, unlink.
	if ( is_wp_error( $id ) ) {
		unlink( $file_array['tmp_name'] );
	}
	return $id;
}
