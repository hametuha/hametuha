<?php
class Recent_Widget extends WP_Widget{
	
	var $replacer = array(
		'title' => '投稿タイトル',
		'url' => 'パーマリンク',
		'excerpt' => '抜粋',
		'date' => '公開日',
		'modified' => '最終更新日',
		'author' => '作者',
		'category' => 'カテゴリー',
		'tag' => '投稿タグ',
		'thumb' => '投稿サムネイル',
		'avatar' => '作者アバター（00の部分はサイズ）'
	);
	
	function Recent_Widget(){
		parent::WP_Widget(false, '投稿タイプ別最新投稿', array('description' => '投稿タイプ別に最新の投稿を表示します。'));
	}
	
	function widget($args, $instance) {
		extract($args);
		extract($instance);
		$query = new WP_Query("post_status=publish&post_type={$post_type}&posts_per_page={$number}");
		if($query->have_posts()){
			echo $before_widget;
			if(!empty($title)){
				echo $before_title;
				echo $title;
				echo $after_title;
			}
			echo '<ul class="widgets-content recent-widgets">';
			while($query->have_posts()){
				$query->the_post();
				$output = $layout;
				echo '<li>';
				foreach($this->replacer as $key => $val){
					if(false !== strpos($output, $key)){
						switch($key){
							case 'title':
								$replace = get_the_title();
								break;
							case 'url':
								$replace = get_permalink();
								break;
							case 'excerpt':
								$replace = get_the_excerpt();
								break;
							case 'date':
								$replace = get_the_date('Y/m/d');
								break;
							case 'modified':
								$replace = get_the_modified_date('Y/m/d');
								break;
							case 'author':
								$replace = get_the_author();
								break;
							case 'thumb':
								$replace = get_the_post_thumbnail(get_the_ID(), $thumbnail_size);
								if(!$replace){
									$replace = ' ';
								}
								break;
							case 'avatar':
								$match = array();
								if(preg_match("/avatar_([0-9]{1,3})/", $output, $match)){
									$size = $match[1];
								}else{
									$size = 96;
								}
								$replace = get_avatar(get_the_author_ID(), $size);
								$output = preg_replace('/%avatar(_[0-9]{1,3})?%/', $replace, $output);
								$replace = false;
								break;
						}
						if($replace){
							$output = str_replace("%{$key}%", $replace, $output);
						}
					}
				}
				echo $output.'</li>';
			}
			wp_reset_query();
			echo '</ul>';
			$link = $post_type == 'post' ? home_url('/latest/'): get_post_type_archive_link($post_type);
			echo '<p class="right"><a class="small-button" href="'.$link.'">一覧</a></p>';
			echo $after_widget;
		}
	}
	
	
	function update($newinstance, $oldinstance){
		return $newinstance;
	}
	
	function form($instance){
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
register_widget('Recent_Widget');

