<?php get_header('narrow'); ?>
	
<?php if(have_posts()): while(have_posts()): the_post(); ?>

<div class="wrap">
<div <?php post_class('post-meta post-meta-single')?>>
	<h1 class="mincho">
		<?php the_series('<span class="series">', '</span>'); ?>
		<?php the_title(); ?>
	</h1>
	<p class="author">
		作： <a href="#post-author"><?php the_author(); ?></a>
	</p>
	<p class="genre">
		<span class="category"><?php the_category(', ');?></span>
	</p>
	
	<?php if(has_post_thumbnail()): ?>
		<?php the_post_thumbnail(); ?>
	<?php elseif(has_pixiv()): ?>
		<?php	pixiv_output(); ?>
	<?php endif; ?>
	
	<div class="desc clearfix clrB">
		<?php the_excerpt(); ?>
	</div>
</div><!-- //.post-meta-single -->

<?php
/* 縦書きの場合だけ変換用ソースを出力 */
if(!get_post_format() && false === strpos($post->post_content, '[flash')):
	echo '<p class="center"><a id="meta-opener" href="#reading" class="button flash-button open-reader">作品を読む</a></p>';
	echo '<p id="reader-open-indicator" class="center"><strong class="mono">5</strong>秒後に縦書きに変換して開きます...</p>';
endif; ?>

<div class="post-content single-post-content mincho clearfix">
	<?php the_content(); ?>
	<?php wp_link_pages(array('before' => '<p class="link-pages clrB">ページ: ', 'after' => '</p>', 'link_before' => '<span>', 'link_after' => '</span>')); ?>
</div><!-- //.single-post-content -->

<?php
/* 読み終えたか否かを判断するためのもの */
global $pages;
$current_page = get_query_var('page') ? get_query_var('page') : 1;
$max_page = count($pages);
if($max_page == 1 || $current_page == $max_page):?>
	<p id="work-end-ranker">&nbsp;</p>
<?php endif; ?>

<div class="next-previous center clrB">
	<?php if(is_series()): ?>
		<?php prev_series_link('&laquo; ', ''); ?>
		<span class="divider">｜</span>
		<?php next_series_link('', ' &raquo;'); ?>
	<?php else: ?>
		<?php previous_post_link('%link', '&laquo; %title'); ?>
		<span class="divider">｜</span>
		<?php next_post_link('%link', '%title &raquo;'); ?>
	<?php endif; ?>
</div>

<p id="single-post-footernote">
	&copy; <?php the_time('Y'); ?> <?php the_author(); ?>
</p>

</div><!-- // .wrap -->

<div id="post-feedback">
	<ul class="clearfix">
		<li class="author">
			<a href="#post-author" title="作者について"><i></i>作者</a>
		</li><li class="detail">
			<a href="#post-detail" title="作品の統計情報"><i></i>統計</a>
		</li><li class="tag">
			<a href="#post-tags" title="作品につけられたタグ"><i></i>タグ</a>
		</li><li class="share">
			<a href="#post-share" title="作品をシェア"><i></i>シェア</a>
		</li><li class="comment">
			<a href="#post-comment" title="作品へのコメント"><i></i>コメント<small class="mono"><?php comments_number('0', '1', '%'); ?></small></a>
		</li><li class="feedback">
			<a href="<?php echo get_feedback_page_url(); ?>&post_id=<?php the_ID(); ?>" title="作品のレビューを記入"><i></i>レビュー</a>
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
		<?php if(is_series()): ?>
		<h3>関連作品</h3>
			<p class="cloud">
				「<?php the_title(); ?>」は<?php the_author_posts_link(); ?>による<?php the_series(); ?>というシリーズの一部です。
			</p>
			<ul>
				<?php next_series_link('<li>次の作品: '); ?>
				<?php prev_series_link('<li>前の作品: '); ?>
			</ul>
		<?php endif; ?>
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
					<th scope="col">この作品</th>
					<th scope="col">平均</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th scope="row">文字数</th>
					<td><?php the_post_length('<strong class="old">', '</strong>', '計測不能');?></td>
					<td><strong class="old"><?php echo number_format_i18n(get_post_length_avg());?></strong></td>
				</tr>
				<tr class="even">
					<th scope="row">ページ<?php help_tip('平均的な文庫本のサイズ36文字×18行で計算しています'); ?></th>
					<td><?php the_post_length('<strong class="old">', 'P</strong>', '計測不能', 36 * 18);?></td>
					<td class="center">-</td>
				</tr>
				<tr>
					<th scope="row">フレーズ<?php help_tip('この作品でお気に入りフレーズとして保存された箇所の数です'); ?></th>
					<td><strong class="old"><?php echo number_format_i18n(get_user_favorite_count()) ?></strong></td>
					<td class="center">-</td>
				</tr>
			</tbody>
		</table>
		<h3>読者の反応</h3>
		<p class="post-rank-counter">
			<span class="post-rank-indicator" style="width:<?php echo round(get_post_rank() / 5 * 100);?>%;"></span>
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
				これはあなたの作品です。積極的に宣伝し、たくさんの読者に読んでもらいましょう。<br />
				<a href="#post-advanced">いいねやTwitterでの宣伝</a>など、周囲に疎まれる限界まで宣伝してください。
				<label>この作品のURL: <input class="regular-text" type="text" value="<?php echo esc_attr(wp_get_shortlink()); ?>" onclick="this.select();" /></label>
			</p>
		<?php endif; ?>
	</div><!-- #post-share -->

	<div id="post-tags">
		<div class="all-tags">
			<h3>みんながつけたタグ</h3>
			<?php if(!the_user_tags('<p id="all-user-tag-container" class="tag-container">')): ?>
				<p class="message notice">
					この投稿にはまだ誰もタグをつけていません。ぜひ最初のタグをつけてください！
				</p>
			<?php endif; ?>
		</div><!-- // all-tags -->
		
		<div class="your-tags">
			<h3>あなたがつけたタグ</h3>
			<?php if(is_user_logged_in()): ?>
				<?php get_template_part('templates/user-tags'); ?>
			<?php else: ?>
				<p class="need-authority center">
					この機能は会員限定です。<a href="<?php echo wp_login_url(get_permalink()); ?>">こちら</a>からログインまたは新規登録をしてください。
				</p>
			<?php endif; ?>
		</div><!-- //.your-tags -->
		
		<div class="author-tags">
			<h3>作者がつけたタグ<?php help_tip('作者が自らつけたタグです。古い作品は編集部がつけた場合があります。');?></h3>
			<p class="tag-container">
				<?php the_tags('', ''); ?>
			</p>
		</div>
	</div><!-- //#post-tags -->
	
	<div id="post-comment">
		<?php comments_template(); ?>
	</div>
	
</div>
<!-- // .post-advanced-info -->


<?php endwhile; endif; ?>


<?php get_footer('narrow'); ?>