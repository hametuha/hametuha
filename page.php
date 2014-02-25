<?php get_header(); ?>


<?php if(have_posts()): while(have_posts()): the_post(); ?>
<div <?php post_class('post-meta')?>>
	<?php if(has_post_thumbnail()): ?>
		<h1 class="thumbnail"><?php the_post_thumbnail(); ?></h1>
	<?php else: ?>
		<h1 class="mincho"><?php the_title(); ?></h1>
	<?php endif; ?>
	<div class="metadata clearfix">
		<?php if(defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE && current_user_can('edit_posts')): $sufficience =  get_user_status_sufficient(get_current_user_id());?>
			<div id="profile-indicator" class="clearfix alingleft">
				<strong style="width: <?php echo $sufficience; ?>%;"></strong>
				<span>プロフィール充実度: <?php echo $sufficience;?>%</span>
			</div>
		<?php endif; ?>
		<p class="date right">
			<?php
				if(is_member_page()){
					$date = mysql2date('Y/m/d', $wpdb->get_var($wpdb->prepare("SELECT user_registered FROM {$wpdb->users} WHERE ID = %d", get_current_user_id())),false);
				}else{
					$date = get_the_date('Y/m/d');
				}
			?>
			<span class="old">@<?php echo $date; ?></span>
			<?php if(!is_member_page()): ?>
				<small>（<span class="old"><?php the_modified_date(); ?></span>更新）</small>
			<?php endif; ?>
		</p>
	</div>
</div>
<?php switch($post->post_name){
	case 'your-favorites':
	case 'your-comments':
	case 'your-reviews':
		get_template_part('templates/'.$post->post_name);
		break;
	case 'authors':
		echo '<div class="post-content clearfix">';
		get_template_part('templates/author-list');
		the_content();
		wp_link_pages(array('before' => '<p class="link-pages clrB">ページ: ', 'after' => '</p>', 'link_before' => '<span>', 'link_after' => '</span>'));
		echo '</div>';
		break;
	case 'hamazon':
		get_template_part('templates/hamazon');
		break;
	default:
		echo '<div class="post-content clearfix">';
		the_content();
		wp_link_pages(array('before' => '<p class="link-pages clrB">ページ: ', 'after' => '</p>', 'link_before' => '<span>', 'link_after' => '</span>'));
		echo '</div>';
		break;
}?>

<?php 
	if(!is_member_page()) hametuha_share(get_the_title(), get_permalink()) ;
?>

<?php endwhile; endif; ?>

<?php get_footer(); ?>