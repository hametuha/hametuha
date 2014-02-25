<?php get_header(); ?>
<?php if(have_posts()): while(have_posts()): the_post(); ?>
	<?php if(has_post_thumbnail()){
		 the_post_thumbnail();
	} ?>
	<div class="front-page post-content">
		<?php the_content(); ?>
	</div>
<?php endwhile; endif; ?>

<div class="frontpage-widget clearfix">
	<?php dynamic_sidebar("frontpage-sidebar");?>
</div>

<h2 class="front-announcement center clearfix clrB">
	<a href="<?php echo get_post_type_archive_link('announcement');?>" class="small-button alignright">一覧</a> 
	破滅派編集部からの公式告知
</h2>
<ul class="front-announcement">
	<?php $announcements = get_recent_announcement(true, 3); foreach($announcements as $a): ?>
	<li class="clearfix">
		<a href="<?php echo get_permalink($a->ID); ?>"><?php echo $a->post_title; ?></a>
		<?php if(is_new_post(7, $a)): ?>
			<img src="<?php echo get_template_directory_uri();?>/img/icon-new-small.png" alt="New" width="16" height="16" />
		<?php endif; ?>
		<small class="old alignright"><?php echo mysql2date('Y/m/d', $a->post_date); ?></small>
	</li>
	<?php endforeach; ?>
</ul>

<div class="front-thread">
	<h2 class="front-thread center clearfix clrB">
		<a href="<?php echo get_post_type_archive_link('thread');?>" class="small-button alignright">一覧</a> 
		破滅派BBS〜同人が交流する掲示板〜
	</h2>
	<ul>
		<?php foreach(get_posts('post_type=thread&posts_per_page=10') as $post): setup_postdata($post);?>
		<li>
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?> 
				<span class="old">(<?php comments_number('0', '1', '%'); ?>)</span>
			</a>
			<small class="old"><?php echo human_time_diff(strtotime(get_the_time('Y-m-d H:i:s'))); ?>前</small>
			<?php if(is_new_post(7, $post)): ?>
				<img src="<?php echo get_template_directory_uri();?>/img/icon-new-small.png" alt="New" width="16" height="16" />
			<?php endif; ?>
		</li>
		<?php endforeach; wp_reset_postdata(); ?>
	</ul>
</div>


<!-- 統計情報 -->
<p class="frontpage-static">
	<span class="old"><?php echo date('Y/m/d'); ?></span>現在の統計
	<span>作品: </span><strong class="old"><?php echo number_format_i18n(get_current_post_count());?></strong>
	作品&nbsp;<a href="<?php echo home_url('/latest/');?>" class="small-button">一覧</a>
	<span>同人: </span><strong class="old"><?php echo number_format_i18n(get_author_count()); ?></strong>
	名&nbsp;<a href="<?php echo home_url('/authors/');?>" class="small-button">一覧</a>
</p>
<!-- 同人-->

<div class="alignright margin_2 margin_more clrB">
	<h2>新人さん</h2>
	<ul>
		<?php $counter = 0; foreach(get_recent_authors() as $user): $counter++;?>
		<li class="clearfix<?php if($counter % 2 == 0) echo ' even';?>">
			<?php echo get_avatar($user->ID, 40); ?>
			<h3><a href="<?php echo get_author_posts_url($user->ID, $user->user_nicename); ?>"><?php echo esc_html($user->display_name); ?></a></h3>
			<p class="author">
				@<?php echo mysql2date('Y/m/d', $user->user_registered); ?><br />
				最新投稿: <a href="<?php echo get_permalink($user->post_id); ?>"><?php echo $user->post_title; ?></a>
			</p>
		</li>
		<?php endforeach; ?>
	</ul>
</div>

<div class="alignright margin_2 margin_more">
	<h2>これまで一番投稿した人</h2>
	<ul>
		<?php $counter = 0; foreach(get_vigorous_author() as $user): $counter++;?>
		<li class="clearfix<?php if($counter % 2 == 0) echo ' even';?>">
			<strong class="posts_count old"><?php echo number_format_i18n($user->num);?></strong>
			<?php echo get_avatar($user->ID, 40); ?>
			<h3><a href="<?php echo get_author_posts_url($user->ID, $user->user_nicename); ?>"><?php echo esc_html($user->display_name); ?></a></h3>
			<p class="author">
				@<?php echo mysql2date('Y/m/d', $user->user_registered); ?>
			</p>
		</li>
		<?php endforeach; ?>
	</ul>
</div>


<div class="alignleft margin_2">
	<h2>最近がんばってる同人</h2>
	<?php $counter = 0; $activity_interval = 7; $vigorous = get_vigorous_author($activity_interval, 5); if(empty($vigorous)): ?>
		<p class="message warning">
			ここ<?php echo number_format($activity_interval); ?>日間というもの、誰も活動していません！　あなたの力が必要です。
			<?php if(!is_user_logged_in()): ?>
			
			<?php elseif(current_user_can('edit_posts')): ?>
			
			<?php endif; ?>
		</p>
	<?php else: ?>
		<ul>
		<?php foreach($vigorous as $user): $counter++;?>
			<li class="clearfix<?php if($counter % 2 == 0) echo ' even';?>">
				<strong class="posts_count old"><?php echo number_format_i18n($user->num);?></strong>
				<?php echo get_avatar($user->ID, 40); ?>
				<h3><a href="<?php echo get_author_posts_url($user->ID, $user->user_nicename); ?>"><?php echo esc_html($user->display_name); ?></a></h3>
				<p class="author">
					@<?php echo mysql2date('Y/m/d', $user->user_registered); ?><br />
				</p>
			</li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<!-- 同人終り -->



<div class="margin_1 clearfix clrB">
	<h2>編集部オススメ</h2>
	<ul>
		<?php
			$query = new WP_Query("post_type=post&posts_per_page=10&tag=recommended&orderby=rand");
			if($query->have_posts()): $counter = 0; while($query->have_posts()): $query->the_post(); $counter++;
		?>
		<li class="clearfix<?php if($counter % 2 == 1) echo ' clrL even';?>">
			<?php if(has_post_thumbnail()): ?>
				<?php the_post_thumbnail('pinky'); ?>
			<?php else: ?>
				<img alt="" class="wp-post-image" src="<?php echo get_bloginfo('template_directory').'/img/no-photo-pinky-'.get_post_type().".png"; ?>" width="80" height="80" />
			<?php endif; ?>
			<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<?php echo get_avatar(get_the_author_ID(), 32); ?>
			<p class="author">
				<?php	the_author_posts_link(); ?><br />
				@<?php the_time('Y/m/d'); ?>
			</p>
			<p class="description clrL">
				<?php echo trim_long_sentence(get_the_excerpt()); ?>
			</p>
		</li>
		<?php	endwhile;	endif;	wp_reset_query(); ?>
	</ul>
</div>

<div class="front-tagcloud clrL">
	<h2>人気のタグ</h2>
	<?php wp_tag_cloud();?>
</div>

<p class="center ads"><?php google_ads();?></p>

<?php get_footer(); ?>