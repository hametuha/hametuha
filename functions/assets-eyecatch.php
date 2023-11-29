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
add_theme_support( 'post-thumbnails', [ 'post', 'page', 'series', 'announcement', 'news', 'related-post-ad' ] );
set_post_thumbnail_size( $content_width, 696, false );

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
			$content .= sprintf(
				'<p class="description">%s</p>',
				esc_html__( 'アイキャッチ画像はSNSでシェアされる時に表示されます。表紙画像のようなものと考えてください。あってもなくてもよいです。', 'hametuha' )
			);
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
