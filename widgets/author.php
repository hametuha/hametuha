<?php
class Author_Widget extends WP_Widget{
	function Author_Widget(){
		parent::WP_Widget(false, '作者について', array('description' => '作者の情報を表示します'));
		
	}
	
	/**
	 *
	 * @global wpdb $wpdb
	 * @param array $args
	 * @param array $instance
	 * @return void 
	 */
	function widget($args, $instance) {
		global $wpdb, $user_ID;
		extract($args);
		if(!is_author() && !is_singular()){
			return;
		}
		$author_id = (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) ? $user_ID :get_the_author_ID();
		$author = get_userdata($author_id);
		echo <<<EOS
{$before_widget}
{$before_title}作者について{$after_title}
EOS;
?>
<div class="widget-author">
<?php echo get_avatar($author_id, 60); ?>
<small class="author-role"><?php the_author_roles($author_id); ?></small>
<strong class="author-name"><?php echo $author->display_name; ?></strong>
<a class="small-button" href="<?php get_author_link(true, $author_id); ?>" title="<?php echo $author->display_name; ?>の作品一覧">作品一覧</a>

<table class="author-additional">
	<tbody>
		<tr>
			<th>投稿数</th>
			<td><?php echo get_author_work_count($author_id); ?>作品</td>
		</tr>
		<tr>
			<th>登録日</th>
			<td><?php echo mysql2date(get_option('date_format'), $author->user_registered); ?></td>
		</tr>
		<tr>
			<th>最新投稿日</th>
			<td><?php echo mysql2date(get_option('date_format'), get_author_latest_published($author_id), false); ?></td>
		</tr>
		<tr>
			<th>Webサイト</th> 
			<td>
				<?php
					if($author->user_url != 'http://' && !empty($author->user_url)):
						$site_name = get_user_meta($author_id, 'aim', true);
						if(!$site_name){
							$site_name = $author->user_url;
						}
				?>
					<a target="_blank" href="<?php echo esc_attr($author->user_url); ?>"><?php echo $site_name; ?></a>
				<?php else: ?>
					なし
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>

<div class="author-desc">
	<?php echo wpautop(the_author_meta('description', $author_id)); ?>
</div>

</div>
<?php
	echo $after_widget;
	
/*
 * 最新の投稿一覧
 ---------------------------------*/
	$post_type = (is_author() || is_singular('post')) ? 'post' : get_post_type();
	$post_type_obj = get_post_type_object($post_type);
	$query = new WP_Query("author={$author_id}&post_type={$post_type}&post_status=publish&posts_per_page=5");
	if($query->have_posts()):
		echo $before_widget;
		echo $before_title.$author->display_name.'最新の'.$post_type_obj->label.$after_title;
		echo '<ul class="widgets-content recent-widgets">';
		while($query->have_posts()): $query->the_post();
			?>
				<li>
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><br />
					<small class="old"><?php the_time('Y-m-d'); ?></small>
				</li>
			<?php
		endwhile;
		echo '</ul>';
		if($post_type == 'post'){
			echo '<p class="right"><a class="small-button" href="'.  home_url('/author/'.$author->user_nicename.'/').'">一覧</a></p>';
		}
		echo $after_widget;
	endif;
	wp_reset_query();
	
	
/*
 * 最新のシリーズ一覧
 ---------------------------------*/
	if($post_type == 'post'){
		//シリーズ
		$query = new WP_Query("author={$author_id}&post_type=series&post_status=publish&posts_per_page=5");
		if($query->have_posts()): 
			echo $before_widget;
			echo $before_title.$author->display_name.'によるシリーズ'.$after_title;
			echo '<ul class="widgets-content recent-widgets">';
			while($query->have_posts()): $query->the_post();
				?>
					<li>
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><br />
						<small><span class="old">
						<?php
							$sql = "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' AND post_parent = %d";
							echo number_format_i18n($wpdb->get_var($wpdb->prepare($sql, get_the_ID())));
						?>		
						</span>作品</small>
					</li>
				<?php
			endwhile;
			echo '</ul>';
			echo '<p class="right"><a class="small-button" href="'.  get_post_type_archive_link('series').'">一覧</a></p>';
			echo $after_widget;
		endif;	wp_reset_query();
		}
	}
}
register_widget('Author_Widget');

