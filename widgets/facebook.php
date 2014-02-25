<?php
/**
 * ウィジェットにいいねを追加する
 * @package WordPress
 */
class Facebook_Like extends WP_Widget{
	function Facebook_Like() {
		// Instantiate the parent object
		parent::WP_Widget( false, 'Facebook いいねボックス' , array('description' => 'FacebookアップでAPIキーを取得してください'));
	}

	function widget( $args, $instance ) {
		// Widget output
		extract($args);
		extract($instance);
		if(file_exists(TEMPLATEPATH."/css/facebook.css")){
			$css = ' css="'.get_bloginfo('template_directory')."/css/facebook.css?".filemtime(TEMPLATEPATH."/css/facebook.css").'"';
		}else{
			$css = '';
		}
		if(isset($app_key, $page_id, $width, $height, $icons)){
			echo $before_widget;
			echo $before_title . 'Facebook' . $after_title;
			echo <<<EOS
<fb:fan profile_id="{$page_id}" connections="{$icons}" width="{$width}" stream="false" height="{$height}"{$css}></fb:fan>
EOS;
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
		return $new_instance;
	}

	/**
	 * フォームを出力する
	 * @param array $instance
	 * @reutrn void
	 */
	function form( $instance ) {
		extract(shortcode_atts(array(
			'title' => 'Facebook',
			'page_id' => null,
			'app_key' => null,
			'width' => 300,
			'height' => 200,
			'icons' => 20
		), $instance));
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">タイトル</label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">ページID</label> 
		<input class="widefat" id="<?php echo $this->get_field_id('page_id'); ?>" name="<?php echo $this->get_field_name('page_id'); ?>" type="text" value="<?php echo $page_id; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">APIキー</label> 
		<input class="widefat" id="<?php echo $this->get_field_id('app_key'); ?>" name="<?php echo $this->get_field_name('app_key'); ?>" type="text" value="<?php echo $app_key; ?>" />
		</p>
		<p>
			<label>
				幅<input type="text" name="<?php echo $this->get_field_name('width'); ?>" value="<?php echo $width; ?>" />
			</label><br />
			<label>
				高さ<input type="text" name="<?php echo $this->get_field_name('height'); ?>" value="<?php echo $height; ?>" />
			</label><br />
			<label>
				サムネイル数<input type="text" name="<?php echo $this->get_field_name('icons'); ?>" value="<?php echo $icons; ?>" />
			</label>
		</p>
		<?php 
	}
}

register_widget('Facebook_Like');