<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * Class Rank
 *
 * @package Hametuha\Model
 * @property-read string $posts
 */
class Rating extends Model
{

    /**
     * ユーザーとコンテンツを紐づけるテーブル名
     *
     * @var string
     */
    protected $name = 'user_content_relationships';

    /**
     *
     *
     * @var array
     */
    protected $related = ['posts'];

    /**
     * キー名
     *
     * @var string
     */
    protected $type = 'rank';

    /**
     * Primary key of this table
     *
     * @var string
     */
    protected $primary_key = 'ID';

    /**
     * @var string
     */
    protected $updated_column = 'updated';

    /**
     * @var array
     */
    protected $default_placeholder = [
        'ID' => '%d',
        'rel_type' => '%s',
        'object_id' => '%d',
        'user_id' => '%d',
        'location' => '%f',
        'content' => '%s',
    ];

    /**
     * Update rating
     *
     * @param int $rank
     * @param int $user_id
     * @param int $post_id
     * @return false|int
     */
    public function update_rating($rank, $user_id, $post_id){
        if( is_null($this->get_users_rating($post_id, $user_id)) ){
            return $this->insert([
                'rel_type' => $this->type,
                'object_id' => $post_id,
                'user_id' => $user_id,
                'location' => $rank / 10
            ]);
        }else{
            return $this->update([
                'location' => $rank / 10
            ], [
                'rel_type' => $this->type,
                'object_id' => $post_id,
                'user_id' => $user_id
            ]);
        }
    }

    /**
     * Get user's rating
     *
     * @param int $post_id
     * @param int $user_id
     * @return null
     */
    public function get_users_rating($post_id, $user_id){
        if( !$user_id ){
            return null;
        }
        $rank = $this->select("{$this->table}.location")
            ->wheres([
                "{$this->table}.rel_type = %s" => $this->type,
                "{$this->table}.user_id = %d" => $user_id,
                "{$this->table}.object_id = %d" => $post_id,
            ])->get_var();
        if( is_null($rank) ){
            return null;
        }else{
            return floor($rank * 10);
        }
    }

    /**
     * 投稿が取得した☆の平均を返す
     *
     * @param \WP_Post $post
     * @return null|float
     */
    function get_post_rating( \WP_Post $post = null){
        $post = get_post($post);
        $this->select("AVG({$this->table}.location)")
             ->where("{$this->table}.rel_type = %s", $this->type);
        if( $this->is_series($post) ){
            $this->where("{$this->posts}.post_parent = %d", $post->ID);
        }else{
            $this->where("{$this->table}.object_id = %d", $post->ID);
        }
        $avg = $this->get_var();
        if( is_null($avg) ){
            return null;
        }else{
            return round($avg * 10, 1);
        }
    }

    /**
     * 投稿に付与された評価の件数を返す
     *
     * @param \WP_Post $post
     * @return int
     */
    public function get_post_rating_count( \WP_Post $post ){
        $this->select("COUNT({$this->table}.ID)")
             ->where("{$this->table}.rel_type = %s", $this->type);
        if( $this->is_series($post) ){
            $this->where("{$this->posts}.post_parent = %d", $post->ID);
        }else{
            $this->where("{$this->table}.object_id = %d", $post->ID);
        }
        return (int)$this->get_var();
    }

    /**
     * If this is series?
     *
     * @param \WP_Post $post
     * @return bool
     */
    private function is_series( \WP_Post $post ){
        return 'series' === $post->post_type;
    }


    /**
     * Default join
     *
     * @return array
     */
    protected function default_join(){
        return [
            [$this->posts, "{$this->posts}.ID = {$this->table}.object_id", 'inner'],
        ];
    }
} 