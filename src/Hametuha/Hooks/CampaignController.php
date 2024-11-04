<?php

namespace Hametuha\Hooks;


use WPametu\Pattern\Singleton;

/**
 * Campaign controller
 */
class CampaignController extends Singleton {

	/**
	 * {@inheritDoc}
	 */
	public function __construct( array $setting = array() ) {
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
		add_action( 'hametuha_campaign_supporter_add', [ $this, 'notify_new_supporter' ], 10, 2 );
	}

	/**
	 * Query object
	 *
	 * @param \WP_Query $wp_query
	 * @return void
	 */
	public function pre_get_posts( $wp_query ) {
		if ( is_admin() || ! $wp_query->is_main_query() || ! is_user_logged_in() ) {
			return;
		}
		if ( ! current_user_can( 'edit_posts' ) || current_user_can( 'edit_other_posts' ) ) {
			// 著者でなければ何もしない
			return;
		}
		if ( is_tax( 'campaign' ) ) {
			// キャンペーン一覧なので、参加しているか調べる
			$terms = [ get_queried_object() ];
		} elseif ( $wp_query->get( 'p' ) ) {
			$post = get_post( $wp_query->get( 'p' ) );
			if ( ! $post || 'post' !== $post->post_type || ! in_array( $post->post_status, [ 'private', 'draft' ], true ) ) {
				// 投稿ページでないので何もしない
				return;
			}
			if ( (int) $post->post_author === get_current_user_id() ) {
				// 自分の投稿なので何もしない
				return;
			}
			// シングルページなので、キャンペーンに属しているかチェック
			$terms  = get_the_terms( $post, 'campaign' );
			if ( ! $terms || is_wp_error( $terms ) ) {
				return;
			}
		}
		// 現在のキャンペーンにユーザーが参加しているかを調べ
		// 参加してる場合は非公開・下書きも含めて表示
		foreach ( $terms as $term ) {
			if ( $this->is_user_participating( $term, get_current_user_id() ) || $this->is_user_supporting( $term, get_current_user_id() ) ) {
				$wp_query->set( 'post_status', [ 'publish', 'private', 'draft' ] );
				return;
			}
		}
	}

	/**
	 * ユーザーがキャンペーンに作品で参加しているかどうか
	 *
	 * @param \WP_Term $term_or_terms
	 * @param int      $user_id
	 * @return bool
	 */
	public function is_user_participating( $term, $user_id ) {
		$query = new \WP_Query( [
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'author'         => $user_id,
			'tax_query'      => [
				[
					'taxonomy' => 'campaign',
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				],
			],
		] );
		return $query->have_posts();
	}

	/**
	 * ユーザーがキャンペーンをサポートしているかどうか
	 *
	 * @param \WP_Term $term
	 * @param int      $user_id
	 * @return bool
	 */
	public function is_user_supporting( $term, $user_id ) {
		if ( ! get_term_meta( $term->term_id, '_is_collaboration', true ) ) {
			// 共同型でないので、サポートできない
			return false;
		}
		$terms = get_user_meta( $user_id, 'supporting_campaigns' );
		return in_array( (string) $term->term_id, $terms, true );
	}

	/**
	 * サポーターを取得する
	 *
	 * @param \WP_Term|int|\WP_Term[]|int[] $term_or_terms キャンペーン
	 * @return \WP_User[]
	 */
	public function get_supporters( $term_or_terms ) {
		$args = [
			'orderby'    => 'user_registered',
			'order'      => 'ASC',
			'meta_query' => []
		];
		if ( is_array( $term_or_terms ) ) {
			$args['meta_query'][] = [
				'key'     => 'supporting_campaigns',
				'compare' => 'IN',
				'value'   => array_map( function( $term ) {
					return is_a( $term, 'WP_Term' ) ? $term->term_id : $term;
				}, $term_or_terms ),
			];
		} else {
			$args['meta_query'][] = [
				'key'   => 'supporting_campaigns',
				'value' => is_a( $term_or_terms, 'WP_Term' ) ? $term_or_terms->term_id : intval( $term_or_terms ),
			];
		}
		$user = new \WP_User_Query( $args );
		return $user->get_results();
	}

	/**
	 * ユーザーが追加されたら通知する
	 *
	 * @param \WP_Term $term    キャンペーン
	 * @param int      $user_id ユーザーID
	 * @return void
	 */
	public function notify_new_supporter( $term, $user_id ) {
		$user = get_userdata( $user_id );
		$message = _x( '公募「%1$s」に %3$s さんがサポーターとして参加されました %2$s', 'slack', 'hametuha' );
		$message = sprintf( $message, $term->name, get_term_link( $term ), $user->display_name );
		hametuha_slack( $message, [], '#magazine' );
	}
}
