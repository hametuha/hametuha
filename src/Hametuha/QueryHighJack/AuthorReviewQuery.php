<?php

namespace Hametuha\QueryHighJack;


use Hametuha\Model\Review;
use WPametu\API\QueryHighJack;

/**
 * Review results Query
 *
 * @package Hametuha\Rest
 * @property-read Review $review
 */
class AuthorReviewQuery extends QueryHighJack
{


	/**
	 * @var string
	 */
	protected $pseudo_post_type = 'profile';

    /**
     * Query vars
     *
     * @var array
     */
    protected $query_var = ['reviewed_as', 'authenticated'];

    protected $models = [
        'review' => Review::class,
    ];


	/**
     * リライトルール
     *
     * @var array
     */
    protected $rewrites = [
        'reviewed/([0-9]+)/page/([0-9]+)/?$' => 'index.php?reviewed_as=$matches[1]&paged=$matches[2]',
        'reviewed/([0-9]+)/?$' => 'index.php?reviewed_as=$matches[1]',
        'reviewed/auth/([0-9]+)/page/([0-9]+)/?$' => 'index.php?reviewed_as=$matches[1]&paged=$matches[2]&authenticated=1',
        'reviewed/auth/([0-9]+)/?$' => 'index.php?reviewed_as=$matches[1]&authenticated=1',
    ];

	/**
	 * タイトル変更
	 *
	 * @param string $title
	 * @param string $sep
	 * @param string $sep_location
	 *
	 * @return string
	 */
	public function wp_title($title, $sep, $sep_location){
		$term = get_term((int)get_query_var('reviewed_as'), $this->review->taxonomy);
		if( !$term || is_wp_error($term) ){
			return '';
		}
		$auth = get_query_var('authenticated') ? '登録ユーザーから' : '';
		return sprintf("「%s」という評価を%s受けた作者 %s %s", $term->name, $auth, $sep, get_bloginfo( 'name' ) );
	}

	/**
	 * Get recent request
	 *
	 * @param string $request
	 * @param \WP_Query $wp_query
	 *
	 * @return false|null|string
	 */
	public function posts_request($request, \WP_Query $wp_query){
		if( ($term = $this->get_term($wp_query) ) ){

			$per_page = $wp_query->get('posts_per_page') ?: get_option('posts_per_page');
			$offset = (max(1, $wp_query->get('paged')) - 1) * $per_page;
			$author = $wp_query->get('authenticated') ? 'AND r.user_id > 0' : '';

			$query = <<<SQL
				SELECT SQL_CALC_FOUND_ROWS
					p.*,
					'{$this->pseudo_post_type}' AS post_type,
					COUNT(DISTINCT r.updated) AS score,
					COUNT(DISTINCT p.ID) AS work_count
 				FROM {$this->review->table} AS r
				LEFT JOIN {$this->review->db->posts} AS p
				ON r.object_id = p.ID
				LEFT JOIN {$this->review->db->users} AS u
				ON p.post_author = u.ID
				WHERE r.term_taxonomy_id = %d
				  AND p.post_status = 'publish'
				  {$author}
				GROUP BY p.post_author
				ORDER BY COUNT(r.updated) DESC
				LIMIT %d, %d
SQL;
			$request = $this->review->db->prepare($query, $term->term_taxonomy_id, $offset, $per_page);
		}
		return $request;
	}

    /**
     * クエリがレビューかどうか
     *
     * @param \WP_Query $wp_query
     * @return bool
     */
    protected function is_valid_query( \WP_Query $wp_query ){
	    return (bool) $this->get_term($wp_query);
    }

	/**
	 * Get term
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return bool|mixed|null|\WP_Error
	 */
	protected function get_term(\WP_Query $wp_query){
		$term_id = (int)$wp_query->get('reviewed_as');
		if( !$term_id ){
			return false;
		}
		$term = get_term($term_id, $this->review->taxonomy);
		return $term && !is_wp_error($term) ? $term : false;
	}

}
