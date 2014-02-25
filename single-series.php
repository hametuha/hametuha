<?php get_header('narrow'); ?>
	
<?php
	if(have_posts()): while(have_posts()): the_post();
	//作品集のクエリを作っておく
	$query = new WP_Query("post_type=post&post_status=publish&posts_per_page=-1&order=ASC&post_parent=".get_the_ID()."&paged=".get_query_var('paged'));
?>
<div class="wrap">
<div <?php post_class('post-meta post-meta-single clearfix')?>>
	<h1 class="mincho">
		<span class="series"><a href="<?php echo get_post_type_archive_link('series'); ?>">作品集</a></span>
		<?php the_title(); ?>
	</h1>
	<p class="author">
		編集者： <a href="#post-author"><?php the_author(); ?></a>
	</p>
	<p class="genre">
		<span class="category">全<?php echo number_format_i18n(count($query->posts)); ?>作品</span>
	</p>
	
	<?php if(has_post_thumbnail()): ?>
		<?php the_post_thumbnail(); ?>
	<?php elseif(has_pixiv()): ?>
		<?php pixiv_output(); ?>
	<?php else: ?>
		<img class="attachment-post-thumbnail" width="300" height="400" alt="<?php the_title(); ?>" src="<?php echo get_template_directory_uri(); ?>/img/covers/default-300x400.jpg" />
	<?php endif; ?>
	
	<?php if(has_excerpt()): ?>
		<div class="desc clearfix clrB">
			<?php the_excerpt(); ?>
		</div>
	<?php endif; ?>
</div><!-- //.post-meta-single -->

<div class="post-content single-post-content mincho clearfix">
	<?php 
		global $post;
		if(!empty($post->post_content)){
			the_content();
		}else{
			'なにも書いてない';
		}
		//TODO: 作品集をePub書き出しする場合にどうするか検討
		wp_link_pages(array('before' => '<p class="link-pages clrB">ページ: ', 'after' => '</p>', 'link_before' => '<span>', 'link_after' => '</span>'));
	?>
</div><!-- //.single-post-content -->

<table class="works-list serires-list">
	<caption>『<?php the_title(); ?>』目録</caption>
	<tbody>
		<?php $authors = array(get_the_author()); if($query->have_posts()): ?>
			<?php while($query->have_posts()): $query->the_post(); ?>
		<tr>
			<th class="thumbnail"><?php echo get_avatar(get_the_author_meta('ID'), 64); ?></th>
			<td class="detail">
				<span class="date mono"><?php the_post_time_diff(); ?></span>
				<h3 class="title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>
				<p class="meta">
					<span>作者</span>
					<?php
						the_author();
						if(false === array_search(get_the_author(), $authors)){
							$authors[] = get_the_author();
						}
					?>
					&nbsp;
					<span>ジャンル</span>
					<?php echo implode(', ', array_map(function($term){
						return $term->name;
					}, get_the_category())); ?>
					<span>文字数</span>
					<?php the_post_length('', '', '計測不能');?>
					<span>コメント</span>
					<?php comments_number('0', '1', '%'); ?>
				</p>
				<div class="excerpt">
					<?php the_excerpt(); ?>
				</div><!-- .excerpt -->
			</td>
			<td class="more">
				<a href="<?php the_permalink(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/img/icon-list-table.png" alt="<?php the_title(); ?>" width="64" height="32" /></a>
			</td>
		</tr>
			<?php endwhile; ?>
		<?php else: ?>
		<tr>
			<td colspan="3"><p class="message warning">この作品集には一つも作品がリストされていません。</p></td>
		</tr>
		<?php endif; wp_reset_postdata(); ?>
	</tbody>
</table><!-- //.works-list -->

<p id="single-post-footernote">
	&copy; <?php the_time('Y'); ?> <?php echo implode(', ', $authors); ?>
</p>

</div><!-- //.wrap -->


<div id="post-feedback">
	<ul class="clearfix">
		<li class="author">
			<a href="#post-author" title="編集者について"><i></i>編集者</a>
		</li><li class="detail">
			<a href="#post-detail" title="作品集の統計情報"><i></i>統計</a>
		</li><?php /*<li class="tag">
			<a href="#post-tags" title="各作品につけられたタグ"><i></i>タグ</a>
		</li>*/ ?><li class="share">
			<a href="#post-share" title="作品集をシェア"><i></i>シェア</a>
		</li>
	</ul>
</div>

<div id="post-advanced" class="post-advanced-info">

	<div id="post-author" class="info-div">
		<?php $author_id = get_the_author_meta('ID'); $author = get_userdata($author_id) ?>
		<div class="author">
			<?php echo get_avatar($author_id, 60); ?>
			<small class="author-role"><?php the_author_roles($author_id); ?></small><br />
			<strong class="author-name"><?php echo $author->display_name; ?></strong><br />
			<a class="small-button" href="<?php get_author_link(true, $author_id); ?>" title="<?php echo $author->display_name; ?>の作品一覧">作品一覧</a>
			<div class="clrB profile">
				<?php echo wpautop($author->description);?>
			</div>
		</div><!-- //.author -->
		
		<table class="author-additional"">
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
		<?php $posts = get_posts("post_type=post&author=".get_the_author_meta('ID')."&posts_per_page=5&post_status=publish");?>
		<?php if(!empty($posts)): ?>
			<h3>作者の最新投稿</h3>
			<ul>
				<?php foreach($posts as $p): ?>
				<li><a href="<?php echo get_permalink($p->ID); ?>"><?php echo apply_filters('the_title', $p->post_title, $p->ID); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif;?>
	</div><!-- #info-div -->
	
	<div id="post-detail">
		<table class="data-table">
			<thead>
				<tr>
					<th scope="col">&nbsp;</th>
					<th scope="col">この作品集</th>
					<th scope="col">1作品あたり平均</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th scope="row">文字数</th>
					<td><?php the_post_length('<strong class="old">', '</strong>', '計測不能');?></td>
					<td><strong class="old"><?php echo number_format_i18n(get_post_length_avg(get_the_ID()));?></strong></td>
				</tr>
				<tr class="even">
					<th scope="row">ページ<?php help_tip('平均的な文庫本のサイズ36文字×18行で計算しています'); ?></th>
					<td><?php the_post_length('<strong class="old">', 'P</strong>', '計測不能', 36 * 18);?></td>
					<td><strong class="old"><?php echo number_format_i18n( round(get_post_length_avg(get_the_ID()) / 36 / 18) );?>P</strong></td>
				</tr>
			</tbody>
		</table>
		<h3>読者の反応</h3>
		<p class="post-rank-counter">
			<span class="post-rank-indicator" style="width:<?php printf('%d%%', round(get_post_rank() / 5 * 100));?>;"></span>
		</p>
		<p class="post-rank-detail">
			<strong class="old"><?php echo (number_format_i18n(get_post_rank(), 1)); ?></strong>
			<small>(<span class="old"><?php echo number_format_i18n(get_post_rank_count());?></span>件)</small>
		</p>
		<p class="clrB center post-chart">
			<?php the_post_chart(); ?>
		</p>
		<p class="right">
			<small>破滅チャートとは<?php help_tip('破滅派読者が入力した感想を元に生成されるチャートです。赤いほど破滅度が高く、青いほど健全な作品です。');?></small>
		</p>
	</div><!-- #post-detail -->
	
	<div id="post-share">
		<?php hametuha_share(get_the_title(), get_permalink()) ; ?>
		<?php if(get_current_user_id() == get_the_author_ID()):?>
			<p class="author-notify">
				これはあなたの作品集です。積極的に宣伝し、たくさんの読者に読んでもらいましょう。<br />
				<a href="#post-advanced">いいねやTwitterでの宣伝</a>など、周囲に疎まれる限界まで宣伝してください。
				<label>この作品のURL: <input class="regular-text" type="text" value="<?php echo home_url('/?p='.get_permalink()); ?>" onclick="this.select();" /></label>
			</p>
		<?php endif; ?>
	</div><!-- #post-share -->
<?php /*
	<div id="post-tags">
		<div class="all-tags">
			<h3>作品につけられたタグ</h3>
			<?php if(!the_user_tags('<p id="all-user-tag-container" class="tag-container">')): ?>
				<p class="message notice">
					この作品集にはまだ誰もタグをつけていません。ぜひ最初のタグをつけてください！
				</p>
			<?php endif; ?>
		</div><!-- // all-tags -->
	</div><!-- //#post-tags -->
	
	<div id="post-comment">
		<?php comments_template(); ?>
	</div>
*/ ?>	
</div>
<!-- // .post-advanced-info -->

<?php endwhile; endif; ?>

<?php get_footer('narrow'); ?>