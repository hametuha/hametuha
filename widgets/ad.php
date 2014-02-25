<?php
/*
 * 広告ウィジェット
 */

class Advertize_Widget extends WP_Widget{
	
	var $adds = array(
		'MicroAd 画像タイプ(240)'
	);
	
	function Advertize_Widget(){
		parent::WP_Widget(false, '広告ユニット', array('description' => '広告ユニットを表示します'));
	}
	
	function widget($args, $instance){
		extract($args);
		extract($instance);
		echo $before_widget;
		if(!empty($title)){
			echo $before_title.esc_html($title).$after_title;
		}
		switch($type){
			case 0:
				echo <<<EOS
<script type="text/javascript"><!--
in_uid = '275631';
in_templateid = '15012';
in_charset = 'UTF-8';
in_group = 'sidebar';
in_matchurl = '';
in_HBgColor = 'FFFFFF';
in_HBorderColor = 'FFFFFF';
in_HTitleColor = '000000';
in_HTextColor = '888888';
in_HUrlColor = '0066CC';
frame_width = '240';
frame_height = '240';
--></script>
<script type='text/javascript' src='http://cache.microad.jp/send0100.js'></script>
EOS;
				break;
		}
		echo $after_widget;
	}
	
	function update($new_instance){
		return $new_instance;
	}
	
	function form($instance){
		extract(shortcode_atts(array(
			'title' => '',
			'type' => 0
		), $instance));
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">タイトル</label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('type'); ?>">広告タイプ</label> 
		<select id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
			<?php foreach($this->adds as $key => $val): ?>
				<option value="<?php echo $key; ?>"<?php if($key == $type) echo ' selected="selected"';?>><?php echo esc_html($val); ?></option>
			<?php endforeach;?>
		</select>
		</p>
		<?php
	}
}

register_widget('Advertize_Widget');