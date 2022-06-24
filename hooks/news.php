<?php
/**
 * News related hooks.
 *
 * @package hametuha
 */

/**
 * 広告を挿入する
 */
add_action( 'wp_head', function () {
	// Googleの広告
	if ( is_hamenew() ) {
		echo <<<HTML
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
  (adsbygoogle = window.adsbygoogle || []).push({
    google_ad_client: "ca-pub-0087037684083564",
    enable_page_level_ads: true
  });
</script>
HTML;
	}
}, 1 );

/**
 * ニュースだったらテンプレートを切り替える
 */
add_filter( 'template_include', function ( $path ) {
	if ( is_singular( 'news' ) ) {
		$path = get_template_directory() . '/templates/news/single.php';
	} elseif ( is_tax( 'nouns' ) || is_tax( 'genre' ) || ( is_post_type_archive( 'news' ) && 1 < (int) get_query_var( 'paged' ) ) ) {
		$path = get_template_directory() . '/templates/news/archive.php';
	} elseif ( is_post_type_archive( 'news' ) ) {
		$path = get_template_directory() . '/templates/news/front.php';
	}

	return $path;
} );


/**
 * ニュースページの場合は一覧ページを20件にする
 */
add_action( 'pre_get_posts', function ( WP_Query &$wp_query ) {
	if ( ! $wp_query->is_main_query() || is_admin() ) {
		return;
	}
	if ( $wp_query->is_singular( 'news' ) ) {

	}
	if (
		$wp_query->is_tax( [ 'nouns', 'genre' ] )
		||
		$wp_query->is_post_type_archive( 'news' )
	) {
		$wp_query->set( 'posts_per_page', 20 );
	}
} );

/**
 * ニュースアーカイブのタイトルを修正する
 *
 * @param string $name
 *
 * @return string
 */
add_filter( 'single_term_title', function ( $name ) {
	if ( is_tax( 'nouns' ) ) {
		$name = sprintf( 'キーワード「%s」を含む記事', $name );
	} elseif ( is_tax( 'genre' ) ) {
		$name = sprintf( 'ジャンル「%s」の記事', $name );
	}

	return $name;
} );

/**
 * ヘルプメニューを追加
 */
add_action( 'admin_head', function () {
	if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ! ( $screen = get_current_screen() ) || 'news' != $screen->post_type ) {
		return;
	}
	foreach (
		[
			'publish'   => [
				'公開フロー',
				'<p>ニュースは「レビュー待ち」として送信されたのち、破滅派編集部によるチェックを経て公開されます。なるべく早く行いますが、24時間365日で対応することはできませんので、その点ご了承ください。</p>',
			],
			'published' => [
				'公開済みニュース',
				'<p>一度公開されたニュースは破滅派編集部以外編集できません。修正要望がある場合はSLACKにてお問い合わせください。</p>',
			],
			'banned'    => [
				'ボツニュース',
				'<p>ニュースのステータスが「非公開」となっている場合、そのニュースはボツになっています。ボツになったニュースはもう編集できません。理由についてはSLACKにてお伝えしますので、お問い合わせください。</p>',
			],
			'contact'   => [
				'連絡方法',
				'<p>ニュースの連絡におけるすべてのやりとりは基本的にSLACKで行います。参加方法はよくある質問をご覧ください。</p>'
			],
		] as $id => list( $title, $content )
	) {
		$screen->add_help_tab( [
			'id'      => 'news-' . $id,
			'title'   => $title,
			'content' => $content,
		] );
	}

	// サイドバーを追加
	$term = get_term_by( 'slug', 'news', 'faq_cat' );
	if ( $term ) {
		$url = get_term_link( $term );
	} else {
		$url = get_post_type_archive_link( 'faq' );
	}
	$sidebar = <<<HTML
<ul>
	<li><a href="{$url}" target="_blank">よくある質問</a></li>
	<li><a href="https://hametuha.slack.com" target="_blank">破滅派SLACK</a></li>
</ul>
HTML;
	$screen->set_help_sidebar( $sidebar );
} );

/**
 * XMLサイトマップを追加
 *
 * @todo いまのところ、Googleに無視されているので、あとでやる
 */
add_filter( 'bwp_gxs_external_sitemaps', function ( $data ) {
	/** @var wpdb $wpdb */
	global $wpdb;
	$query = <<<SQL
		SELECT COUNT(ID) FROM {$wpdb->posts}
		WHERE post_type = 'news'
          AND post_status = 'publish'
SQL;
	$total = (int) $wpdb->get_var( $query );
	$per_page = get_option( 'posts_per_rss', 20 );
	for ( $i = 0, $l = ceil( $total / $per_page ); $i < $l; $i++ ) {
		$url = home_url( '/amp_sitemap/' ).( $i ? sprintf( '?paged=%d', $i + 1 ) : '' );
		$data[] = [
			'location' => $url,
		];
	}
	$post = get_posts( [
		'post_type' => 'news',
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'orderby' => [ 'date' => 'DESC' ],
	] );
	return $data;
} );

/**
 * RSSのタイトルを変更
 *
 * @param string $title
 * @return string
 */
add_filter( 'get_wp_title_rss', function( $title ) {
	if ( is_post_type_archive( 'news' ) ) {
		$title = 'はめにゅー | 文芸関連ニュース';
	}
	return $title;
} );

/**
 * AMPを変更
 */
add_filter( 'bloginfo_rss', function($value, $show){
	if ( 'description' == $show && is_post_type_archive( 'news' ) ) {
		$value = get_post_type_object( 'news' )->description;
	}
	return $value;
}, 10, 2);
