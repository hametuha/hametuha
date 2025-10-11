<?php
/**
 * News related hooks.
 *
 * @package hametuha
 */

/**
 * ニュース関連の投稿タイプを作成する。
 */
add_action( 'init', function() {

	// キーワード
	register_taxonomy( 'nouns', 'news', [
		'label'             => 'タグ（固有名詞）',
		'description'       => 'ニュースに出てくる作家名、雑誌名、出版社名などの固有名詞。',
		'hierarchical'      => false,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => [
			'slug' => 'news/nouns',
		],
		'capabilities'      => [
			'manage_terms' => 'edit_posts',
			'edit_terms'   => 'edit_posts',
			'delete_terms' => 'edit_others_posts',
			'assign_terms' => 'edit_posts',
		],
	] );

	// 形式
	register_taxonomy( 'genre', 'news', array(
		'label'             => 'ジャンル',
		'public'            => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'capabilities'      => [
			'manage_terms' => 'edit_others_posts',
			'edit_terms'   => 'edit_others_posts',
			'delete_terms' => 'edit_others_posts',
			'assign_terms' => 'edit_posts',
		],
		'show_admin_column' => true,
		'rewrite'           => [
			'slug'         => 'news/genre',
			'hierarchical' => true,
		],
	) );

	// ニュース

	register_post_type('news', [
		'label'           => 'ニュース',
		'description'     => 'はめにゅーはオンライン文芸誌サイト破滅派が提供する文学関連ニュースです。コンテキスト無き文学の世界で道標となることを目指しています。',
		'public'          => true,
		'menu_position'   => 6,
		'menu_icon'       => 'dashicons-admin-site',
		'supports'        => [ 'title', 'excerpt', 'editor', 'author', 'thumbnail', 'revisions', 'amp' ],
		'has_archive'     => true,
		'taxonomies'      => [ 'genre', 'nouns' ],
		'map_meta_cap'    => true,
		'capability_type' => [ 'news_post', 'news_posts' ],
		'show_in_rest'    => true,
		'template'        => [
			[
				'hametuha/excerpt',
				[],
			],
			[
				'core/paragraph',
				[
					'placeholder' => __( 'ここからニュースの本文を入力してください……', 'hametuha' ),
				],
			],
		],
	]);
} );

function wpb_change_title_text( $title ) {
	$screen = get_current_screen();

	if ( 'movie' == $screen->post_type ) {
		$title = 'Enter movie name with release year';
	}

	return $title;
}

/**
 * エディターのプレースホルダーでを変更。
 *
 * @param string $title プレースホルダー
 * @param WP_Post $post 投稿オブジェクト
 * @return string
 */
add_filter( 'enter_title_here', function( $title, $post ) {
	if ( 'news' === $post->post_type ) {
		$title = __( 'タイトル例・【快挙！】破滅派2000号がノーベル文学賞を受賞！', 'hametuha' );
	}
	return $title;
}, 10, 2 );

/**
 * ニュースのリライトルールを追加
 *
 */
add_filter( 'rewrite_rules_array', function ( array $rules ) {
	return array_merge( [
		'^news/article/([0-9]+)/([0-9]+)/?$' => 'index.php?p=$matches[1]&post_type=news&page=$matches[2]',
		'^news/article/([0-9]+)/amp/?$'      => 'index.php?p=$matches[1]&post_type=news&amp=true', // AMPはもう使っていないが後方互換で残す
		'^news/article/([0-9]+)/?$'          => 'index.php?p=$matches[1]&post_type=news',
	], $rules );
} );

/**
 *
 * パーマリンクをIDに
 *
 * @since 3.0.0
 *
 * @param string $post_link The post's permalink.
 * @param WP_Post $post The post in question.
 * @param bool $leavename Whether to keep the post name.
 * @param bool $sample Is it a sample permalink.
 */
add_filter( 'post_type_link', function ( $post_link, $post ) {
	if ( 'news' === $post->post_type ) {
		$post_link = home_url( "/news/article/{$post->ID}/" );
	}
	return $post_link;
}, 10, 2 );

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
	if ( is_hamenew() ) {
		if ( is_hamenew( 'single' ) ) {
			$path = get_template_directory() . '/templates/news/single.php';
		} elseif ( is_hamenew( 'front' ) ) {
			$path = get_template_directory() . '/templates/news/archive.php';
		} else {
			$path = get_template_directory() . '/templates/news/archive.php';
		}
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
		$name = sprintf( 'キーワード「%s」を含むニュース', $name );
	} elseif ( is_tax( 'genre' ) ) {
		$name = sprintf( 'ジャンル「%s」のニュース', $name );
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
				'<p>ニュースの連絡におけるすべてのやりとりは基本的にSLACKで行います。参加方法はよくある質問をご覧ください。</p>',
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
add_filter( 'bloginfo_rss', function( $value, $show ) {
	if ( 'description' == $show && is_post_type_archive( 'news' ) ) {
		$value = get_post_type_object( 'news' )->description;
	}
	return $value;
}, 10, 2);

/**
 * ニュースの関連記事を追加
 */
add_filter( 'related_posts_post_types', function( $post_types ) {
	$post_types[] = 'news';
	return $post_types;
}  );

/**
 * ニュースの関連記事スコア測定用静的解析を追加
 */
add_filter( 'related_posts_taxonomy_score', function ( $scores, $post_type ) {
	if ( 'news' === $post_type ) {
		$scores = [
			'nouns' => 3,
			'genre' => 1,
		];
	}
	return $scores;
}, 10, 2 );

/**
 * ニュース関連記事のタクソノミーを追加する
 */
add_filter( 'related_post_patch_main_taxonomy', function( $taxonomy, $post ) {
	if ( 'news' === $post->post_type ) {
		$taxonomy = 'genre';
	}
	return $taxonomy;
}, 10, 2 );
