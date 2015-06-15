<?php

namespace Hametuha\Ajax;


use Hametuha\Model\Rating;
use Hametuha\Model\Review;
use WPametu\API\Ajax\AjaxForm;


/**
 * Feedback controller
 *
 * @package Hametuha\Ajax
 *
 * @property-read Review $review
 * @property-read Rating $rating
 */
class Feedback extends AjaxForm
{

    protected $action = 'feedback';

    protected $target = 'all';

    protected $screen = 'public';

    protected $method = 'post';

    protected $models = [
        'review' => Review::class,
        'rating' => Rating::class,
    ];

    protected $required = ['post_id', 'intelligence', 'completeness', 'readability', 'emotion', 'mood', 'to_author'];

    /**
     * Returns data as array.
     *
     * @return array
     */
    protected function get_data(){
        $user_id = get_current_user_id();
        $post = get_post($this->input->post('post_id'));
        if( !$post ){
            $this->error($this->__('該当する投稿が見つかりませんでした。'), 404);
        }
        // 登録ユーザーなら、既存のものがあれば削除
        if( $user_id ){
            $this->review->clear_user_review($user_id, $post->ID);
        }
        // データを挿入
        foreach( $this->review->feedback_tags as $key => $terms ){
            $index = $this->input->post($key) - 1;
            if( $index > -1 && $index < 2 ){
                $term = get_term_by('name', $terms[$index], $this->review->taxonomy);
                if( $term && !is_wp_error($term) ){
                    $this->review->add_review($user_id, $post->ID, $term->term_taxonomy_id);
                }
            }
        }
        // レビューがあれば保存
        if( $user_id ){
            $rank = $this->input->post('rating');
            if( is_numeric($rank) ){
                $rank = max(min(5, $rank), 1);
                $this->rating->update_rating($rank, $user_id, $post->ID);
            }
        }
        return [
            'success' => true,
            'message' => 'レビューを保存しました。ありがとうございました。',
            'guest' => !$user_id,
        ];
    }

    /**
     * みんなのレビューを表示する
     *
     * @param int $post_id
     */
    public static function all_review($post_id){
        $post = get_post($post_id);
        /** @var Feedback $instance */
        $instance = self::get_instance();
        self::view('parts/feedback', 'all', [
            'all_rating' => $instance->rating->get_post_rating($post),
            'chart' => $instance->review->get_chart($post),
            'total' => $instance->rating->get_post_rating_count($post)
        ]);
    }

    /**
     * Define form arguments
     *
     * @return array
     */
    public function form_arguments(){
        $terms = [];
        $all_terms = $this->review->feedback_tags;
        $user_terms = $this->review->user_voted_tags(get_current_user_id(), get_the_ID());
        $reviews = [];
        $labels = [];
        foreach( $all_terms as $label => list($positive, $negative) ){
            $labels[$label] = $this->review->review_tag_label($label);
            $positive_review = $this->walker->prop_exists($user_terms, 'name', $positive);
            $negative_review = $this->walker->prop_exists($user_terms, 'name', $negative);
            $reviews[$label] = [
                [$positive, 1, $positive_review],
                ['評価なし', 0, !($positive_review || $negative_review)],
                [$negative, 2, $negative_review]
            ];
        }
        return [
            'reviews' => $reviews,
            'review_label' => $labels,
            'reviewed' => !empty($reviews),
            'your_rating' => $this->rating->get_users_rating(get_the_ID(), get_current_user_id()),
        ];
    }
}