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
		'description'     => '特定のテーマに基づいた連作や長編小説などがあります。電子書籍として販売されているものも含まれています。',
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
		'show_in_rest'    => true,
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
		'show_in_rest'        => true,
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
		'description'     => __( '作品執筆の手助けとなるアイデアです。「自分が書くのはちょっと……」というシャイなあなたにもオススメ。非公開設定もあります。', 'hametuha' ),
		'public'          => true,
		'menu_icon'       => 'dashicons-lightbulb',
		'supports'        => [ 'title', 'editor', 'author', 'comments' ],
		'has_archive'     => true,
		'taxonomies'      => [ 'post_tag' ],
		'capability_type' => 'page',
	] );
}, 10 );

/**
 * リライトルールを追加
 *
 */
add_filter( 'rewrite_rules_array', function ( array $rules ) {
	return array_merge( [
		'^lists/([0-9]+)/paged/([0-9]+)/?$' => 'index.php?p=$matches[1]&post_type=lists&paged=$matches[2]',
		'^lists/([0-9]+)/?$'                => 'index.php?p=$matches[1]&post_type=lists',
		'^idea/(\\d+)/?'                    => 'index.php?p=$matches[1]&post_type=ideas',
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
				<option value="" <?php selected( ! $genre ); ?>>指定なし</option>
				<?php
				foreach (
					[
						'サブジャンル',
						'固有名詞',
						'フラグ',
						'印象',
						'一般名詞',
					] as $val
				) :
					?>
					<option
						value="<?php echo esc_attr( $val ); ?>" <?php selected( $val == $genre ); ?>><?php echo esc_html( $val ); ?></option>
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
 * 検索クエリでpost_typeが指定されていない場合、postに限定する
 *
 * @param WP_Query $query
 */
add_action( 'pre_get_posts', function ( $query ) {
	// 管理画面、REST API、またはメインクエリでない場合はスキップ
	if ( is_admin() || ! $query->is_main_query() || wp_is_rest_endpoint() ) {
		return;
	}

	// 検索クエリでpost_typeが指定されていない場合
	if ( $query->is_search() && ! $query->get( 'post_type' ) ) {
		$query->set( 'post_type', 'post' );
	}
} );
