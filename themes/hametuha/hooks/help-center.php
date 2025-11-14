<?php
/**
 * Help center related hooks.
 *
 * @package hametuha
 */


/**
 * Facebookチャットを表示する
 */
add_action( 'wp_footer', function () {
	static $did = false;
	if ( $did ) {
		return;
	}
	if ( is_singular( 'faq' ) || is_post_type_archive( 'faq' ) || is_tax( 'faq_cat' ) || is_page( 'help' ) ) {
		?>
		<!-- Your customer chat code -->
		<div class="fb-customerchat"
			attribution=setup_tool
			page_id="196103120449777"
			theme_color="#000000"
			logged_in_greeting="めつかれさまです。なにかお困りですか？"
			logged_out_greeting="めつかれさまです。なにかお困りですか？">
		</div>
		<?php
	}
} );

/**
 * FAQの閲覧を制限する
 */
add_filter( 'hamelp_access_type', function ( $types ) {
	if ( isset( $types['contributor'] ) ) {
		unset( $types['contributor'] );
	}
	unset( $types['author'] );
	$types['writer'] = [
		'label'    => '著者',
		'callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	];
	return $types;
} );

/**
 * よくある質問に関連記事を追加する
 */
add_filter( 'related_posts_post_types', function ( $post_types ) {
	$post_types[] = 'faq';
	return $post_types;
} );

/**
 * よくある質問のスコアを調整する
 */
add_filter( 'related_posts_taxonomy_score', function ( $scores, $post_type ) {
	if ( 'faq' === $post_type ) {
		$scores = [
			'faq_cat' => 10,
		];
	}
	return $scores;
}, 10, 2 );

/**
 * よくある質問のメインタクソノミーを変更する
 */
add_filter( 'related_post_patch_main_taxonomy', function ( $taxonomy, $post ) {
	if ( 'faq' === $post->post_type ) {
		$taxonomy = 'faq_cat';
	}
	return $taxonomy;
}, 10, 2 );
