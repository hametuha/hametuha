<?php

namespace Hametuha\QueryHighJack;

use Hametuha\Model\Review;
use WPametu\API\QueryHighJack;

/**
 * リアクションを元に投稿を絞り込む
 *
 * @feature-group search
 * @property-read Review $review
 */
class ReactionQuery extends QueryHighJack {


	/**
	 * @var \class-string[]
	 */
	protected $models = [
		'review' => Review::class,
	];

	protected $query_var = [ 'reaction' ];

	protected $rewrites = [
		'reaction/([^/]+)/page/([0-9]+)/?' => 'index.php?reaction=$matches[1]&paged=$matches[2]',
		'reaction/([^/]+)/?'               => 'index.php?reaction=$matches[1]',
	];

	/**
	 * リアクションの指定があるページ
	 *
	 * @return bool
	 */
	public function is_reaction_page() {
		$reaction = get_query_var( 'reaction' );
		return $reaction && ! is_array( $reaction ) && $this->review->is_valid_tag( urldecode( $reaction ) );
	}

	/**
	 * リアクションの指定があるページのタイトル
	 *
	 * @return string
	 */
	public function reaction_page_title() {
		if ( ! $this->is_reaction_page() ) {
			return '';
		}
		$reaction = get_query_var( 'reaction' );
		return sprintf( '「%s」という評価を受けた作品', urldecode( $reaction ) );
	}

	public function wp_title( $title, $sep, $sep_location ) {
		if ( $this->is_reaction_page() ) {
			$title = implode( ' ' . $sep . ' ', [ $this->reaction_page_title(), get_bloginfo( 'name' ) ] );
		}
		return $title;
	}

	/**
	 * @param \WP_Query $wp_query
	 *
	 * @return bool
	 */
	protected function is_valid_query( \WP_Query $wp_query ) {
		$reviews = $wp_query->get( 'reaction' );
		return ! empty( $reviews );
	}

	/**
	 * レビュータグが指定されている場合は絞り込み
	 *
	 * クエリパラメータ:
	 * - reaction[]=知的&reaction[]=泣ける: 複数のレビュータグで絞り込み（AND条件）
	 *
	 * 各タグは _review_tag_{タグ名} というpost_metaに件数が保存されている
	 * 1件以上獲得しているものを絞り込む
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return void
	 */
	public function pre_get_posts( \WP_Query &$wp_query ) {
		if ( ! $this->is_valid_query( $wp_query ) ) {
			return;
		}
		// 指定されたリアクションを配列に変換
		$reactions = $wp_query->get( 'reaction' );
		if ( ! is_array( $reactions ) ) {
			$reactions = [ $reactions ];
		}

		// エンコードされているかもしれないので復元
		$reactions = array_map( function ( $tag ) {
			// URLエンコードされている場合はデコード
			return urldecode( $tag );
		}, $reactions );

		// 有効なレビュータグのリストを取得
		$valid_tags   = [];
		foreach ( $this->review->feedback_tags as $key => $terms ) {
			$valid_tags = array_merge( $valid_tags, $terms );
		}

		// 指定されたタグをフィルタリング
		$reviews = array_filter( $reactions, function ( $tag ) use ( $valid_tags ) {
			return in_array( $tag, $valid_tags, true );
		} );

		if ( empty( $reactions ) ) {
			return;
		}

		// 既存のmeta_queryを取得
		$meta_query = $wp_query->get( 'meta_query' ) ?: [];

		// 各レビュータグでAND条件を追加
		foreach ( $reviews as $tag ) {
			$meta_query[] = [
				'key'     => '_review_tag_' . $tag,
				'value'   => 1,
				'type'    => 'NUMERIC',
				'compare' => '>=',
			];
		}

		$wp_query->set( 'meta_query', $meta_query );
	}
}
