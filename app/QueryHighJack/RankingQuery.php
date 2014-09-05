<?php

namespace Hametuha\QueryHighJack;


use WPametu\API\QueryHighJack;

/**
 * ランキング取得用のクエリ
 *
 * @package Hametuha\QueryHighJack
 */
class RankingQuery extends QueryHighJack
{

    /**
     * クエリバー
     *
     * @var array
     */
    protected $query_var = ['ranking'];

    /**
     * リライトルール
     *
     * @var array
     */
    protected $rewrites = [
        'ranking/([0-9]{4})/([0-9]{2})/([0-9]{2})/page/([0-9]+)/?' => 'index.php?ranking=daily&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]',
        'ranking/([0-9]{4})/([0-9]{2})/([0-9]{2})/?' => 'index.php?ranking=daily&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]',
        'ranking/([0-9]{4})/([0-9]{2})/page/([0-9]+)/?' => 'index.php?ranking=monthly&year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]',
        'ranking/([0-9]{4})/([0-9]{2})/?' => 'index.php?ranking=monthly&year=$matches[1]&monthnum=$matches[2]',
        'ranking/([0-9]{4})/page/([0-9]+)/?' => 'index.php?ranking=yearly&year=$matches[1]&paged=$matches[2]',
        'ranking/([0-9]{4})/?' => 'index.php?ranking=yearly&year=$matches[1]',
    ];

    /**
     * 現在のスコア
     *
     * @var array
     */
    public static $cur_score = [];

    /**
     * ランキング取得用にpost_requestを書き換え
     *
     * @param string $request
     * @param \WP_Query $wp_query
     * @return mixed|string
     */
    public function posts_request( $request, \WP_Query $wp_query ){
        if( $this->is_valid_query($wp_query) ){

            // 初期条件
            $wheres = $this->get_wheres($wp_query);
            // 初期条件を追加してWhere節を作る
            $where_clause = implode(' AND ', $wheres);
            // オフセット
            $per_page = intval($wp_query->get('posts_per_page') ?: get_option('posts_per_page'));
            $offset = ((max(1, $wp_query->get('paged')) - 1) * $per_page);
            $request = <<<SQL
            SELECT SQL_CALC_FOUND_ROWS
                p.*,
                ranking.pv
            FROM (
                SELECT
                    object_id,
                    SUM(object_value) AS pv
                FROM {$this->db->prefix}wpg_ga_ranking
                WHERE {$where_clause}
                GROUP BY object_id
            ) AS ranking
            INNER JOIN {$this->db->posts} AS p
            ON p.ID = ranking.object_id
            WHERE p.post_status = 'publish'
              AND p.post_type = 'post'
            ORDER BY ranking.pv DESC, p.post_date DESC
            LIMIT {$offset}, {$per_page}
SQL;
        }

        return $request;
    }

    /**
     * 投稿のランクを取得する
     *
     * @param array $posts
     * @param \WP_Query $wp_query
     * @return array
     */
    public function the_posts( array $posts, \WP_Query $wp_query ){
        if( $this->is_valid_query($wp_query) ){
            $where_clause = implode(' AND ', $this->get_wheres($wp_query));
            $query = <<<SQL
                SELECT COUNT(*)
                FROM (
                    SELECT
                        object_id,
                        SUM(object_value) AS pv
                    FROM {$this->db->prefix}wpg_ga_ranking
                    WHERE {$where_clause}
                    GROUP BY object_id
                ) AS ranking
                INNER JOIN {$this->db->posts} AS p
                ON p.ID = ranking.object_id
                WHERE p.post_status = 'publish'
                  AND p.post_type = 'post'
                  AND ranking.pv > %d
SQL;
            foreach( $posts as &$post ){
                if( isset($post->pv) ){
                    $post->rank = (int)$this->db->get_var($this->db->prepare($query, $post->pv)) + 1;
                }
            }
        }
        return $posts;
    }

    /**
     * Get where clause
     *
     * @param \WP_Query $wp_query
     * @return array
     */
    private function get_wheres( \WP_Query $wp_query ){
        $wheres  = [
            "category = 'general'"
        ];
        switch( $wp_query->get('ranking') ){
            case 'yearly':
                $wheres[] = $this->db->prepare("YEAR(calc_date) = %d", $wp_query->get('year'));
                break;
            case 'monthly':
                $wheres[] = $this->db->prepare("YEAR(calc_date) = %d", $wp_query->get('year'));
                $wheres[] = $this->db->prepare("MONTH(calc_date) = %d", $wp_query->get('monthnum'));
                break;
            case 'daily':
                $wheres[] = $this->db->prepare("calc_date = %s", sprintf("%s-%s-%s", $wp_query->get('year'), $wp_query->get('monthnum'), $wp_query->get('day')));
                break;
        }
        return $wheres;
    }

    /**
     * Detect if query var is valid
     *
     * @param \WP_Query $wp_query
     * @return bool
     */
    protected function is_valid_query(\WP_Query $wp_query)
    {
        return false !== array_search($wp_query->get('ranking'), ['yearly', 'monthly', 'daily']);
    }
}