<?php

namespace Hametuha\Model;


/**
 * Review class
 *
 * @package Hametuha\Model
 */
class Review extends TermUserRelationships
{


    /**
     * レビュー用タクソノミー
     *
     * @var string
     */
    public $taxonomy = 'review';



    /**
     * フィードバック用のタグ名
     *
     * @var array
     */
    public $feedback_tags = array(
        'intelligence' => array('知的', 'バカ'),
        'completeness' => array('よくできてる', '破滅してる'),
        'readability' => array('わかりやすい', '前衛的'),
        'emotion' => array('泣ける', '笑える'),
        'mood' => array('生きたくなる', '死にたくなる'),
        'to_author' => array('作者を褒めたい', '作者を殴りたい')
    );


    /**
     * レビュー用タグのキー名からラベルを返す
     *
     * @param string $key
     * @return string
     */
    public function review_tag_label($key){
        switch ($key) {
            case 'intelligence':
                $label = '作品の知性';
                break;
            case 'completeness':
                $label = '作品の完成度';
                break;
            case 'readability':
                $label = '作品の構成';
                break;
            case 'emotion':
                $label = '作品から得た感情';
                break;
            case 'mood':
                $label = '作品を読んで';
                break;
            case 'to_author':
                $label = '作者の印象';
                break;
            default:
                $label = '';
                break;
        }
        return $label;
    }


    /**
     * ユーザーが指定したレビュータグを指定した投稿につけているか
     *
     * @param int $user_id
     * @param int $post_id
     * @param string $tag_name
     * @return boolean
     */
    public function is_user_vote_for($user_id, $post_id, $tag_name){
        return (bool)$this->select("{$this->table}.object_id")
            ->wheres([
                "{$this->table}.user_id = %d" => $user_id,
                "{$this->table}.object_id = %d" => $post_id,
                "{$this->taxonomy}.taxonomy = %s" => $this->taxonomy,
                "{$this->terms}.name = %s" => $tag_name,
            ])->get_var();
    }

    /**
     * ユーザーがつけたレビュータグのリストを返す
     *
     * @param int $user_id
     * @param int $post_id
     * @return array タームリスト
     */
    public function user_voted_tags($user_id, $post_id){
        if( !$user_id ){
            return [];
        }
        return (array)$this->select("{$this->terms}.*, {$this->term_taxonomy}.*")
            ->wheres([
                "{$this->table}.user_id = %d" => $user_id,
                "{$this->table}.object_id = %d" => $post_id,
                "{$this->term_taxonomy}.taxonomy = %s" => $this->taxonomy,
            ])->result();
    }

    /**
     * ユーザーがつけたレビューをすべて消す
     *
     * @param int $user_id
     * @param int $post_id
     * @return false|int
     */
    public function clear_user_review($user_id, $post_id){
        $result = $this->delete_where([
            ['user_id', '=', $user_id, '%d'],
            ['object_id', '=', $post_id, '%d'],
        ]);
        return $result;
    }

    /**
     * レビューを保存する
     *
     * @param int $user_id
     * @param int $post_id
     * @param int $term_taxonomy_id
     * @return false|int
     */
    public function add_review($user_id, $post_id, $term_taxonomy_id){
        return $this->insert([
            'user_id' => $user_id,
            'object_id' => $post_id,
            'term_taxonomy_id' => $term_taxonomy_id
        ]);
    }

    /**
     * JOINを繋げる
     *
     * @param string $join
     * @return string
     */
    public function reviewed_join($join){
        $join .= <<<SQL
          INNER JOIN {$this->table}
          ON {$this->table}.object_id = {$this->posts}.ID
          INNER JOIN {$this->term_taxonomy} AS review
          ON review.term_taxonomy_id = {$this->table}.term_taxonomy_id
SQL;
        return $join;
    }

    /**
     * レビューのWHEREを返す
     *
     * @param string $where
     * @param int $user_id
     * @return string
     */
    public function reviewed_where($where, $user_id){
        $new_where = <<<SQL
          AND (
            {$this->table}.user_id = %d
            AND
            review.taxonomy = %s
          )
SQL;
        return $where.$this->db->prepare($new_where, $user_id, $this->taxonomy);
    }

    /**
     * Get chart's JSON
     *
     * @param \WP_Post $post
     * @return string
     */
    public function get_chart( \WP_Post $post){
        $data = [
            'labels' => ['知性', '完成度', '構成', '読後感', '好感度', '作者'],
            'datasets' => [
                [
                    'label' => '健全指数',
                    'fillColor' => "rgba(172, 255, 165, 0.4)",
                    'strokeColor' => "rgba(172, 255, 165, 0.8)",
                    'pointColor' => "rgba(172, 255, 165, 1)",
                    'pointStrokeColor' => "#fff",
                    'pointHighlightFill' => "#fff",
                    'pointHighlightStroke' => "rgba(172, 255, 165, 1)",
                    'data' => [],
                    'label_set' => []
                ],
                [
                    'label' => '破滅指数',
                    'fillColor' => "rgba(232, 76, 63, 0.4)",
                    'strokeColor' => "rgba(232, 76, 63, 0.8)",
                    'pointColor' => "rgba(232, 76, 63, 1)",
                    'pointStrokeColor' => "#fff",
                    'pointHighlightFill' => "#fff",
                    'pointHighlightStroke' => "rgba(232, 76, 63, 1)",
                    'data' => [],
                    'label_set' => []
                ]
            ]
        ];
        // ポイントを取得
        $points = $this->get_post_chart_points($post->ID, ('series' == $post->post_type));

        // データ整形
        foreach( $this->feedback_tags as $key => $val ){
            for($i = 0, $l = count($val); $i < $l; $i++){
                $score = 0;
                foreach( $points as $point ){
                    if( $point->name == $val[$i] ){
                        $score = $point->score;
                        break;
                    }
                }
                $score = min($score * 20, 100);
                /* TODO: 投稿数が少な過ぎてたぶん意味ないので、平均は取らない
                $avg = get_review_average($val[$i]);
                $points[$i][] = ($point > $avg * 2) ? 100 : round($point / $avg * 50) ;
                 */
                $data['datasets'][$i]['data'][] = $score;
                $data['datasets'][$i]['label_set'][] = $val[$i];
            }
        }
        $data = json_encode($data);
        $html = <<<HTML
<div>
<canvas id="single-radar" width="300" height="300"></canvas>
<script type="text/javascript">
window.postScore = {$data};
</script>
</div>
HTML;
        return $html;
    }

    /**
     * チャートの点数を取得する
     *
     * @param int $post_id
     * @param bool $parent
     * @return array
     */
    public function get_post_chart_points($post_id, $parent = false){
        $this->select("COUNT({$this->table}.user_id) AS score, {$this->terms}.name")
             ->where("{$this->term_taxonomy}.taxonomy = %s", $this->taxonomy)
             ->group_by("{$this->terms}.term_id");
        if( $parent ){
            $sub_query = <<<SQL
                SELECT ID FROM {$this->db->posts}
                WHERE post_type = 'post' AND post_status = 'publish' AND post_parent = %d
SQL;
            $this->where_in_subquery("{$this->table}.object_id", $this->db->prepare($sub_query, $post_id));
        }else{
            $this->where("{$this->table}.object_id = %d", $post_id);
        }
        return $this->result();
    }

}
