<?php

namespace Hametuha\Widget;
use WPametu\UI\Widget;


/**
 * 最新投稿を登録するウィジェット
 *
 * @package Hametuha\Widget
 */
class Recent extends Widget
{
    public $id_base = 'recent-post-widget';

    protected $title = '投稿タイプ別最新投稿';

    protected $description = '投稿タイプ別に最新の投稿を表示します。';


    /**
     * ウィジェットの中身を表示する
     *
     * @param array $instance
     * @return false|string|void
     */
    protected function widget_content( array $instance = [] ){
        extract($instance);
        /** @var string $post_type */
        /** @var string $number */
        /** @var string $layout */
        /** @var string $thumbnail_size */
        $query = new \WP_Query([
            "post_status" => "publish",
            "post_type" => $post_type,
            "posts_per_page" => $number,
        ]);
        if( $query->have_posts() ){
            $out = '<ul class="widgets-content recent-widgets">';
            while( $query->have_posts() ){
                $query->the_post();
                $output = $this->set_avatar($this->replace($layout, $instance));
                $out .= '<li>'.$output.'</li>';
            }
            wp_reset_postdata();
            $out .= '</ul>';
            $link = $post_type == 'post' ? home_url('/latest/'): get_post_type_archive_link($post_type);
            $out .= '<p class="right"><a class="btn btn-sm btn-default btn-block" href="'.$link.'">一覧</a></p>';
            return $out;
        }else{
            return false;
        }
    }


    /**
     * プレースホルダーを返す
     *
     * @return array
     */
    protected function get_placeholders(){
        return array_merge(parent::get_placeholders(), [
            'thumb' => '投稿サムネイル',
            'avatar' => '作者アバター（00の部分はサイズ）'
        ]);
    }

    /**
     * Fill content
     *
     * @param string $placeholder
     * @param array $instance
     * @return bool|string
     */
    protected function fill($placeholder, $instance){
        switch($placeholder){
            case 'thumb':
                $replace = get_the_post_thumbnail(get_the_ID(), $instance['thumbnail_size']);
                return $replace ?: '';
                break;
            case 'avatar':
                return get_avatar(get_the_author_meta('ID'), 96);
                break;
            default:
                return parent::fill($placeholder, $instance);
                break;
        }
    }

    /**
     * Replace avatar
     *
     * @param $content
     * @return string
     */
    protected function set_avatar($content){
        $sizes = [];
        $match = array();
        if( preg_match_all("/avatar_([0-9]{1,3})/", $content, $match)){
            foreach( $match as $m ){
                $sizes[] = $m[1];
            }
            $sizes = array_unique($sizes);
            foreach($sizes as $size){
                $avatar = get_avatar(get_the_author_meta('ID'), $size);
                $content = str_replace("%avatar_{$size}%", $avatar, $content);
            }
        }
        return $content;
    }


    /**
     * フォームを表示する
     *
     * @param array $instance
     * @return string|void
     */
    public function form($instance){
        $atts = shortcode_atts(array(
            'title' => '最新の投稿',
            'post_type' => 'post',
            'number' => 10,
            'layout' => "",
            'thumbnail_size' => 'thumbnail'
        ), $instance);
        extract($atts);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                タイトル<br />
                <input name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('post_type'); ?>">
                投稿タイプ<br />
                <select name="<?php echo $this->get_field_name('post_type'); ?>" id="<?php echo $this->get_field_id('post_type'); ?>">
                    <?php foreach(get_post_types(array('public' => true), 'objects') as $type): ?>
                        <option value="<?php echo $type->name; ?>"<?php if($post_type == $type->name) echo ' selected="selected"';?>><?php echo $type->labels->name; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">
                件数<br />
                <input name="<?php echo $this->get_field_name('number'); ?>" id="<?php echo $this->get_field_id('number'); ?>" value="<?php echo (int) $number; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('layout'); ?>">レイアウト</label><br />
            <textarea rows="5" name="<?php echo $this->get_field_name('layout'); ?>" id="<?php echo $this->get_field_id('layout'); ?>"><?php echo $layout; ?></textarea><br />
				<span class="description">
				</span>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('thumbnail_size'); ?>">
                サムネイルサイズ<br />
                <select name="<?php echo $this->get_field_name('thumbnail_size'); ?>" id="<?php echo $this->get_field_id('thumbnail_size'); ?>">
                    <?php
                    $sizes = array('thumbnail', 'medium', 'large');
                    global $_wp_additional_image_sizes;
                    $sizes = array('thumbnail', 'medium', 'large', 'full');
                    foreach($_wp_additional_image_sizes as $key => $var){
                        $sizes[] = $key;
                    }
                    foreach($sizes as $size):
                        ?>
                        <option value="<?php echo $size; ?>" <?php if($thumbnail_size == $size) echo ' selected="selected"';?>><?php echo $size; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
    <?php
    }

}
