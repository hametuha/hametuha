<div class="author-list">
	<?php the_excerpt();?>

	<p>現在、<?php echo number_format($total = get_author_count()); ?>人の同人が登録しています。</p>
	
	<div class="form-wrap">
	
		<form method="get">
			<label>キーワード: <input type="text" name="s" value="<?php the_search_query(); ?>" /></label>
			<input type="submit" class="button" value="検索" />
		</form>

		<form method="get">
			<select name="orderby">
				<option value="name"<?php if(!isset($_REQUEST['orderby']) || $_REQUEST['orderby'] == 'name') echo ' selected="selected"'; ?>>名前</option>
				<?php foreach(array('registered' => '登録日', 'posted' => '最新投稿日', 'count' => '作品数') as $key => $val):?>
				<option value="<?php echo $key; ?>"<?php if(isset($_REQUEST['orderby']) && $_REQUEST['orderby'] == $key) echo ' selected="selected"';?>><?php echo $val; ?></option>
				<?php endforeach; ?>
			</select>
			<select name="order">
				<option value="asc"<?php if(!isset($_REQUEST['order']) || $_REQUEST['order'] == 'asc') echo ' selected="selected"'; ?>>昇順</option>
				<option value="desc"<?php if(isset($_REQUEST['order']) && $_REQUEST['order'] == 'desc') echo ' selected="selected"'; ?>>降順</option>
			</select>
			<?php global $wp_query; $paged = max(1, absint($wp_query->query_vars['paged'])); if($paged > 1): ?>
				<input type="hidden" name="paged" value="<?php echo $paged; ?>" />
			<?php endif; ?>
			<?php if(isset($_REQUEST['s']) && !empty($_REQUEST['s'])): ?>
				<input type="hidden" name="s" value="<?php the_search_query(); ?>" />
			<?php endif; ?>
			<input type="submit" class="button" value="並べ替え" />
		</form>
	</div>		
		
	<?php $users = get_author_list(); global $wpdb; $found = $wpdb->get_var("SELECT FOUND_ROWS()"); if(!empty($users)): ?>
	<?php author_pagination($found); ?>
	<ol class="clearfix">
		<?php $counter = 0; foreach($users as $user): $counter++; ?>
		<li class="widget-author<?php if($counter % 3 == 1) echo ' third';?>">
			<?php echo get_avatar($user->ID, 80); ?>
			<small class="author-role"><?php the_author_roles($user->ID); ?></small>
			<h2 class="author-name"><ruby><rb><?php echo $user->display_name; ?></rb><rt><?php echo $user->ruby; ?></rt></ruby></h2>
			<a class="small-button" href="<?php get_author_link(true, $user->ID); ?>" title="<?php echo $user->display_name; ?>の作品一覧">作品一覧</a>
			<table class="author-additional">
				<tbody>
					<tr>
						<th>投稿数</th>
						<td><?php echo get_author_work_count($user->ID); ?>作品</td>
					</tr>
					<tr>
						<th>登録日</th>
						<td><?php echo mysql2date(get_option('date_format'), $user->user_registered); ?></td>
					</tr>
					<tr>
						<th>最新投稿日</th>
						<td><?php echo mysql2date(get_option('date_format'), get_author_latest_published($user->ID), false); ?></td>
					</tr>
					<tr>
						<th>Webサイト</th> 
						<td>
							<?php if($user->user_url != 'http://' && !empty($user->user_url)):
								$site_name = get_user_meta($user->ID, 'aim', true);
								if(!$site_name){
									$site_name = $user->user_url;
								}
							?>
							<a target="_blank" href="<?php echo esc_attr($user->user_url); ?>"><?php echo $site_name; ?></a>
							<?php else: ?>
								なし
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="author-desc">
				<?php echo wpautop($user->description); ?>
			</div>

		</li>
		<?php endforeach; ?>
	</ol>
	<?php author_pagination($found); ?>
	<?php else: ?>
	該当する同人は存在しませんでした
	<?php endif; ?>
</div>
