<?php
/**
 * 投稿に関する関数
 */


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
 * 人気の質問を取得する。
 *
 * @return array
 */
function hametuha_popular_faqs() {
	if ( ! class_exists( 'AFB\\Model\\FeedBacks' ) ) {
		return [];
	}
	return \AFB\Model\FeedBacks::get_instance()->search( [
		'post_type'   => 'faq',
		'post_status' => 'publish',
		'allow_empty' => false,
		'orderby'     => 'positive',
		'order'       => 'DESC',
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
