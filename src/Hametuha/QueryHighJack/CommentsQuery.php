<?php

namespace Hametuha\QueryHighJack;


use WPametu\API\QueryHighJack;

/**
 * Class CommentsQuery
 *
 * @package Hametuha\QueryHighJack
 */
class CommentsQuery extends QueryHighJack
{

    /**
     * @var string
     */
    protected $pseudo_post_type = 'comments';

    /**
     * Filter query vars
     *
     * @var array
     */
    protected $query_var = ['comments_author'];

    /**
     * リライトルール
     *
     * @var array
     */
    protected $rewrites = [
        'your/comments/page/([0-9]+)/?$' => 'index.php?comments_author=0&paged=$matches[1]',
        'your/comments/?$' => 'index.php?comments_author=0',
    ];

	/**
	 * タイトルを変更
	 *
	 * @param string $title
	 * @param string $sep
	 * @param string $sep_location
	 *
	 * @return string
	 */
	public function wp_title($title, $sep, $sep_location){
		return "あなたのコメント {$sep} ";
	}

    /**
     * 現在のユーザーIDを設定する
     *
     * @param \WP_Query $wp_query
     */
    public function pre_get_posts( \WP_Query &$wp_query){
        if( $this->is_valid_query($wp_query)){
            if( is_user_logged_in() ){
                $wp_query->set('comments_author', get_current_user_id());
            }else{
                $wp_query->set_404();
            }
        }
    }

    /**
     * リクエストで投稿を取得する
     *
     * @param string $request
     * @param \WP_Query $wp_query
     * @return string
     */
    public function posts_request($request, \WP_Query $wp_query){
        if( $this->is_valid_query($wp_query) ){
            $paged = max(1, (int)$wp_query->get('paged'));
            $per_page = (int)get_option('posts_per_page');
            $user_id = $wp_query->get('comments_author');
            $sql = <<<SQL
              SELECT SQL_CALC_FOUND_ROWS
                c.comment_ID AS ID,
                p.post_title,
                p.post_excerpt,
                p.post_status,
                p.comment_status,
                c.comment_date AS post_date,
                c.user_id AS post_author,
                p.ping_status,
                p.post_password,
                p.post_name,
                c.comment_content AS post_content,
                p.to_ping,
                p.pinged,
                p.post_modified,
                p.post_modified_gmt,
                p.post_content_filtered,
                p.ID AS post_parent,
                p.guid,
                p.menu_order,
                'comment' AS post_type,
                p.post_mime_type,
                p.comment_count,
                p.post_category
              FROM {$this->db->comments} AS c
              LEFT JOIN {$this->db->posts} AS p
              ON c.comment_post_ID = p.ID
              WHERE c.user_id = %d
              ORDER BY c.comment_date DESC
              LIMIT %d, %d
SQL;
            $request = $this->db->prepare($sql, $user_id, ($paged - 1) * $per_page, $per_page);
        }
        return $request;
    }

    /**
     * コメントかどうか
     *
     * @param \WP_Query $wp_query
     * @return bool
     */
    protected function is_valid_query( \WP_Query $wp_query ){
        return is_numeric($wp_query->get('comments_author'));
    }
}
