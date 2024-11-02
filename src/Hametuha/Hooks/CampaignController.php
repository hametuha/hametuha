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
	}

	/**
	 * Query object
	 *
	 * @param \WP_Query $wp_query
	 * @return void
	 */
	public function pre_get_posts( $wp_query ) {
		if ( is_admin() || ! $wp_query->is_main_query() || ! is_user_logged_in()) {
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
		// 散会してる場合は非公開・下書きも含めて表示
		if ( $this->is_user_participating( $terms, get_current_user_id() ) ) {
			$wp_query->set( 'post_status', [ 'publish', 'private', 'draft' ] );
		}
	}

	/**
	 * ユーザーがキャンペーンに参加しているかどうか
	 *
	 * @param \WP_Term|\WP_Term[] $term_or_terms
	 * @param int                 $user_id
	 * @return bool
	 */
	public function is_user_participating( $term_or_terms, $user_id ) {
		if ( is_a( $term_or_terms, 'WP_Term' ) ) {
			$term_or_terms = [ $term_or_terms ];
		}
		$query = new \WP_Query( [
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'tax_query'      => [
				[
					'taxonomy' => 'campaign',
					'field'    => 'term_id',
					'terms'    => wp_list_pluck( $term_or_terms, 'term_id' ),
				],
			],
		] );
		return $query->have_posts();
	}
}
