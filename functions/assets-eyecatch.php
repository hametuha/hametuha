<?php
/**
 * アイキャッチに関する処理
 */

//画像のサイズ（小さい）を追加
add_image_size( 'pinky', 160, 160, true );

// プロフィール写真を追加
add_image_size( 'profile', 300, 300, true );

// ePub表紙用画像
add_image_size( 'kindle-cover', 1200, 1920, true );

/**
 * 選択できる画像サイズを追加
 *
 * @param array $sizes
 *
 * @return array
 */
add_filter( 'image_size_names_choose', function ( $sizes ) {
	$sizes['pinky'] = '小型正方形';
	return $sizes;
} );

//投稿サムネイルを許可
global $content_width;
add_theme_support( 'post-thumbnails', [ 'post', 'page', 'series', 'announcement', 'news' ] );
set_post_thumbnail_size( $content_width, 696, false );


/**
 * Pixivの埋め込みタグを出力する
 * @global object $post
 *
 * @param mixed $post
 */
function pixiv_output( $post = null ) {
	$post = get_post( $post );
	if ( $post && ( $meta = get_post_meta( $post->ID, '_pixiv_embed', true ) ) ) {
		$match = array();
		if ( preg_match( '/data-id="([^"]+)"/', $meta, $match ) ) {
			echo '<script src="http://source.pixiv.net/source/embed.js" data-id="' . esc_attr( $match[1] ) . '" data-size="large" data-border="off" charset="utf-8"></script>';
		}
	}
}


/**
 * 投稿がPixivタグを持っているか否かを返す
 *
 * @param object $post
 *
 * @return boolean
 */
function has_pixiv( $post = null ) {
	$post = get_post( $post );

	return (boolean) get_post_meta( $post->ID, '_pixiv_embed', true );
}


/**
 * サムネイル用のメタボックスに表示するHTML
 *
 * @param string $content
 * @param int $post_id
 *
 * @return string
 */
add_filter( 'admin_post_thumbnail_html', function ( $content, $post_id ) {
	switch ( get_post_type( $post_id ) ) {
		case 'post':
			$help_url = home_url( '/faq/pixiv-embed/' );
			$nonce    = wp_create_nonce( 'pixiv_embed_nonce' );
			$embed    = esc_textarea( get_post_meta( $post_id, '_pixiv_embed', true ) );
			$content .= <<<HTML
				<p class="description">または</p>
				<input type="hidden" name="pixiv_embed_nonce" value="{$nonce}" />
				<label for="pixiv-embed-tag">Pixivの画像を貼付ける<a href="{$help_url}" target="_blank" data-tooltip-title="Pixivのembedタグについてはこちらをクリック"><i class="dashicons dashicons-editor-help"></i></a></label>
				<textarea placeholder="埋め込みタグをここにコピペしてください" name="pixiv_embed_tag" id="pixiv_embed_tag">{$embed}</textarea>
HTML;
			break;
		case 'series':
			$help_url = home_url( '/faq/ebook-cover-regulation' );
			// イメージサイズを取得
			global $_wp_additional_image_sizes;
			if ( isset( $_wp_additional_image_sizes['kindle-cover'] ) ) {
				$content .= <<<HTML
					<p class="description">
						表紙画像は電子書籍の表紙として利用されます。サイズは次の通りです（<a href="{$help_url}" target="_blank">もっと詳しく</a>）。
					</p>
					<table class="sidemeta__table sidemeta__table--regulation">
						<tr>
							<th>横幅</th>
							<td>{$_wp_additional_image_sizes['kindle-cover']['width']}px</td>
						</tr>
						<tr>
							<th>高さ</th>
							<td>{$_wp_additional_image_sizes['kindle-cover']['height']}px</td>
						</tr>
						<tr>
							<th>解像度</th>
							<td>72ppi</td>
						</tr>
					</table>
HTML;
			}
			break;
		default:
			// Do nothing.
			break;
	}

	return $content;
}, 10, 2 );


/**
 * PixivのIDを保存する
 *
 * @param int $post_id
 * @param WP_Post $post
 */
add_action( 'save_post', function ( $post_id, WP_Post $post ) {
	if ( wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) {
		return;
	}
	if ( isset( $_REQUEST['pixiv_embed_nonce'] ) && wp_verify_nonce( $_REQUEST['pixiv_embed_nonce'], 'pixiv_embed_nonce' ) ) {
		if ( isset( $_REQUEST['pixiv_embed_tag'] ) && ! empty( $_REQUEST['pixiv_embed_tag'] ) ) {
			update_post_meta( $post_id, '_pixiv_embed', (string) $_REQUEST['pixiv_embed_tag'] );
		} else {
			delete_post_meta( $post_id, '_pixiv_embed' );
		}
	}
}, 10, 2 );
