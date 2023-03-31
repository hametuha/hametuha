<?php

// 投稿フォーマットを登録する
add_theme_support( 'post-formats', array( 'image' ) );

// 固定ページに抜粋を追加する
add_post_type_support( 'page', 'excerpt' );

// 固定ページのコメントを停止
add_action( 'init', function () {
	remove_post_type_support( 'page', 'comments' );
} );

/**
 * 投稿タイプを登録する
 *
 *
 */
add_action( 'init', function () {
	global $wpdb;
	//シリーズ
	$series = 'series';
	$args   = array(
		'description'     => '著者によってまとめられた作品集です。特定のテーマに基づいた連作や長編小説などがあります。近々ePubなどの形式に書き出せるようになる予定（2012年9月現在）です。',
		'label'           => '作品集・連載',
		'labels'          => [
			'featured_image'        => '表紙画像',
			'set_featured_image'    => '表紙画像を設定する',
			'remove_featured_image' => '表紙画像を削除',
			'use_featured_image'    => '表紙画像として使用する',
		],
		'public'          => true,
		'menu_position'   => 5,
		'menu_icon'       => 'dashicons-book-alt',
		'supports'        => array( 'title', 'editor', 'author', 'slug', 'thumbnail', 'excerpt' ),
		'has_archive'     => true,
		'capability_type' => 'post',
		'rewrite'         => array( 'slug' => $series ),
	);
	register_post_type( $series, $args );

	// リスト
	register_post_type( 'lists', array(
		'label'               => 'リスト',
		'description'         => '破滅派同人が作る作品集です。あなただけの選集を作りましょう！',
		'public'              => true,
		'show_ui'             => false,
		'has_archive'         => true,
		'capability_type'     => 'post',
		'exclude_from_search' => true,
		'rewrite'             => array( 'slug' => 'lists' ),
	) );

	// 告知
	$annoucement_post_type = 'announcement';
	$args                  = array(
		'label'           => '告知',
		'description'     => '破滅派同人による告知です。イベントなどの公式告知情報もあります。',
		'public'          => true,
		'menu_position'   => 20,
		'menu_icon'       => 'dashicons-pressthis',
		'supports'        => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments' ),
		'has_archive'     => true,
		'show_in_rest'    => true,
		'capability_type' => 'page',
		'rewrite'         => array( 'slug' => $annoucement_post_type ),
	);
	register_post_type( $annoucement_post_type, $args );

	//安否情報
	$args = array(
		'label'                 => '安否情報',
		'description'           => '破滅派同人の安否を知るための最新情報です。書いていない人のことは心配してあげてください。',
		'public'                => true,
		'menu_position'         => 10,
		'menu_icon'             => 'dashicons-microphone',
		'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'comments' ),
		'has_archive'           => true,
		'capability_type'       => 'post',
		'show_in_rest'          => true,
		'rest_controller_class' => 'WP_REST_Posts_Controller',
		'rewrite'               => array( 'slug' => 'anpi/archives' ),
	);
	register_post_type( 'anpi', $args );

	//安否情報カテゴリー
	register_taxonomy( 'anpi_cat', array( 'anpi' ), array(
		'hierarchical' => true,
		'show_ui'      => true,
		'query_var'    => true,
		'rewrite'      => array( 'slug' => 'anpi-cat' ),
		'label'        => 'カテゴリー',
	) );

	// レビュー
	register_taxonomy( 'review', 'post', array(
		'label'        => 'レビューポイント',
		'hierarchical' => false,
		'show_ui'      => false,
		'query_var'    => true,
		'capabilities' => [
			'manage_terms' => 'manage_options',
			'edit_terms'   => 'manage_options',
			'delete_terms' => 'manage_options',
			'assign_terms' => 'manage_options',
		],
		'rewrite'      => array( 'slug' => 'review' ),
	) );

	// アイデア
	register_post_type( 'ideas', [
		'label'           => 'アイデア',
		'description'     => '作品執筆の手助けとなるアイデアです。「自分が書くのはちょっと……」というシャイなあなたにもオススメ。非公開設定もあります。',
		'public'          => true,
		'menu_icon'       => 'dashicons-lightbulb',
		'supports'        => [ 'title', 'editor', 'author', 'comments' ],
		'has_archive'     => true,
		'taxonomies'      => [ 'post_tag' ],
		'capability_type' => 'page',
	] );
} );

/**
 * リライトルールを追加
 *
 */
add_filter( 'rewrite_rules_array', function ( array $rules ) {
	return array_merge( [
		'^lists/([0-9]+)/paged/([0-9]+)/?$' => 'index.php?p=$matches[1]&post_type=lists&paged=$matches[2]',
		'^lists/([0-9]+)/?$' => 'index.php?p=$matches[1]&post_type=lists',
		'^idea/(\\d+)/?' => 'index.php?p=$matches[1]&post_type=ideas',
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
	switch ( $post->post_type ) {
		case 'lists':
			$post_link = home_url( "/{$post->post_type}/{$post->ID}/" );
			break;
		case 'ideas':
			$post_link = home_url( "/idea/{$post->ID}/" );
			break;
		default:
			break;
	}

	return $post_link;
}, 10, 2 );

/**
 * 削除
 *
 * @param int $post_id
 */
add_action( 'delete_post', function ( $post_id ) {
	$post = get_post( $post_id );
	switch ( $post->post_type ) {
		case 'lists':
			// リストのリレーションを消す
			/** @var Hametuha\Model\Lists $lists */
			$lists = \Hametuha\Model\Lists::get_instance();
			$lists->clear_relation( $post_id );
			break;
		default:
			// Do nothing.
			break;
	}
} );

/**
 * アーカイブ系シングルの表示を変更する
 */
add_filter( 'single_template', function ( $template ) {
	if ( is_singular( 'lists' ) ) {
		$template = get_template_directory() . '/index.php';
	}

	return $template;
} );


/**
 * サブページじゃなければfalse、 サブページの場合は親の投稿IDを返す
 *
 * @global object $post
 * @param mixed $post
 *
 * @return int
 */
function is_subpage( $post = null ) {
	if ( is_null( $post ) ) {
		global $post;
	} else {
		$post = get_post( $post );
	}

	return (int) $post->post_parent;
}

/**
 * 指定した投稿がリストに含まれているか
 *
 * @param int|WP_Post $post
 * @param int|WP_Post $list
 *
 * @return bool
 */
function in_lists( $post, $list ) {
	$post = get_post( $post );
	$list = get_post( $list );
	if ( ! $post || ! $list || 'lists' !== $list->post_type ) {
		return false;
	} else {
		/** @var \Hametuha\Model\Lists $lists */
		$lists = \Hametuha\Model\Lists::get_instance();

		return $lists->exists_in( $list->ID, $post->ID );
	}
}

/**
 * 自分のコンテンツかいなか
 *
 * @param string $key
 *
 * @return bool
 */
function is_my_content( $key = '' ) {
	if ( empty( $key ) ) {
		$var = get_query_var( 'my-content' );

		return ! empty( $var );
	} else {
		return $key == get_query_var( 'my-content' );
	}
}

/**
 * 投稿がお勧めかどうか
 *
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function is_recommended( $post = null ) {
	$post = get_post( $post );
	/** @var Hametuha\Model\Lists $lists */
	$lists = Hametuha\Model\Lists::get_instance();

	return $lists->is_recommended( $post->ID );
}

/**
 * 現在のページがプロフィールページか否か
 *
 * @return bool
 */
function hametuha_is_profile_page() {
	return '0' === get_query_var( 'profile_name' );
}

/**
 * 現在のページの種別を返す
 *
 * @return string
 */
function hametuha_page_type() {
	if ( is_singular( 'post' ) || is_tag() || is_category() ) {
		return 'post';
	} elseif ( is_singular( 'news' ) || is_post_type_archive( 'news' ) || is_tax( 'genre' ) || is_tax( 'nouns' ) ) {
		return 'news';
	} elseif ( is_singular( 'anpi' ) || is_post_type_archive( 'anpi' ) || is_tax( 'anpi_cat' ) ) {
		return 'anpi';
	} elseif ( is_singular( 'thread' ) || is_post_type_archive( 'thread' ) || is_tax( 'topic' ) ) {
		return 'thread';
	} elseif ( is_singular( 'info' ) || is_post_type_archive( 'info' ) ) {
		return 'info';
	} elseif ( is_singular( 'announcemnt' ) || is_post_type_archive( 'announcement' ) ) {
		return 'announcement';
	} elseif ( is_singular( 'faq' ) || is_post_type_archive( 'faq' ) || is_tax( 'faq_cat' ) ) {
		return 'faq';
	} elseif ( is_front_page() ) {
		return 'front';
	} elseif ( is_page() ) {
		return 'page';
	} elseif ( is_search() ) {
		return 'search';
	} elseif ( is_singular( 'lists' ) || is_post_type_archive( 'lists' ) ) {
		return 'lists';
	} elseif ( is_singular( 'series' ) || is_post_type_archive( 'series' ) ) {
		return 'series';
	} else {
		return '';
	}
}

/**
 * Show field on admin screen
 *
 * @param stdClass $term
 * @param string $taxonomy
 */
add_action( 'post_tag_edit_form_fields', function ( $term ) {
	?>
	<tr>
		<th><label for="tag-genre">タグの種別</label></th>
		<td>
			<select name="tag_genre" id="tag-genre">
				<?php $genre = get_term_meta( $term->term_id, 'genre', true ); ?>
				<option value="" <?php selected( ! $genre ) ?>>指定なし</option>
				<?php foreach (
					[
						'サブジャンル',
						'固有名詞',
						'印象',
						'一般名詞',
					] as $val
				) : ?>
					<option
						value="<?= esc_attr( $val ) ?>" <?php selected( $val == $genre ) ?>><?= esc_html( $val ) ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><label for="tag-type">オプション</label></th>
		<td>
			<script>
				jQuery(document).ready(function ($) {
					$('#my-color').wpColorPicker();
				});
			</script>
			<?php wp_nonce_field( 'edit_tag_meta', '_tagmetanonce', false ); ?>
			<select name="tag_type" id="tag-type">
				<option value="" <?php selected( ! get_term_meta( $term->term_id, 'tag_type', true ) ) ?>>指定しない</option>
				<?php foreach ( [ 'idea' => 'アイデア募集中' ] as $key => $val ) : ?>
					<option value="<?= esc_attr( $key ) ?>" <?php selected( get_term_meta( $term->term_id, 'tag_type', true ) === $key ) ?>>
						<?= esc_html( $val ) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<?php
}, 10, 2 );

/**
 * Save term meta
 *
 * @param int $term_id
 * @param string $taxonomy
 */
add_action( 'edited_terms', function ( $term_id, $taxonomy ) {
	// Check and verify nonce.
	if ( 'post_tag' == $taxonomy && isset( $_POST['_tagmetanonce'] ) && wp_verify_nonce( $_POST['_tagmetanonce'], 'edit_tag_meta' ) ) {
		// Save term meta
		update_term_meta( $term_id, 'tag_type', $_POST['tag_type'] );
		update_term_meta( $term_id, 'genre', $_POST['tag_genre'] );
		wp_cache_delete( 'tag_genre', 'tags' );
	}
}, 10, 2 );


/**
 * 投稿本文をREST APIから削除
 *
 * @param WP_REST_Response $response
 * @param WP_Post $post
 * @param WP_REST_Request $request Request object.
 *
 * @return WP_REST_Response
 */
add_filter( 'rest_prepare_post', function ( WP_REST_Response $response, $post, $request ) {
	$response->data['content'] = $response->data['excerpt'];

	return $response;
}, 10, 3 );

/**
 * 人気の質問を取得する。
 *
 * @return array
 */
function hametuha_popular_faqs() {
    if ( ! class_exists( 'AFB\\Model\\FeedBacks' ) ) {
        return [];
    }
	return \AFB\Model\FeedBacks::get_instance()->search( [
		'post_type' => 'faq',
		'post_status' => 'publish',
		'allow_empty' => false,
		'orderby' => 'positive',
		'order' => 'DESC',
    ], 1, 5 );
}

/**
 * 投稿が十分な投稿かどうか、チェックする
 *
 * @param null|int|WP_Post $post
 * @return bool
 */
function hametuha_is_valid_post( $post = null ) {
    $post = get_post( $post );
    if ( ! $post || ( 'publish' !== $post->post_status ) ) {
        return false;
    }
    if ( post_password_required( $post ) ) {
        return false;
    }
    if ( empty( $post->post_title ) ) {
        return false;
    }
    return true;
}
