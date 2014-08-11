<?php

$query_args = '';

?>

<form method="get">
	<select name="order">
		<option value="ASC"<?php if(isset($_REQUEST['order']) && $_REQUEST['order'] == 'ASC') echo ' selected="selected"';?>>昇順</option>
		<option value="DESC"<?php if(!isset($_REQUEST['order']) || $_REQUEST['order'] != 'ASC') echo ' selected="selected"';?>>降順</option>
	</select>
	<select name="orderby">
		<option value="date"<?php if(!isset($_REQUEST['orderby']) || $_REQUEST['orderby'] == 'date') echo ' selected="selected"';?>>公開日</option>
		<option value="modified"<?php if(isset($_REQUEST['orderby']) && $_REQUEST['orderby'] == 'modified') echo ' selected="selected"';?>>最終更新日</option>
		<option value="author"<?php if(isset($_REQUEST['orderby']) && $_REQUEST['orderby'] == 'author') echo ' selected="selected"';?>>作者</option>
		<option value="title"<?php if(isset($_REQUEST['orderby']) && $_REQUEST['orderby'] == 'title') echo ' selected="selected"';?>>タイトル</option>
		<option value="comment_count"<?php if(isset($_REQUEST['orderby']) && $_REQUEST['orderby'] == 'comment_count') echo ' selected="selected"';?>>コメント数</option>
	</select>
	<?php if(isset($_GET['s']) && !empty($_GET['s'])): ?>
		<input type="hidden" name="s" value="<?php the_search_query();?>" />
	<?php endif; ?>
	<?php if(isset($_GET['post_type']) && !empty($_GET['post_type'])): ?>
		<input type="hidden" name="post_type" value="<?php echo esc_attr($_GET['post_type']); ?>" />
	<?php endif; ?>
	<input type="submit" class="submit button" value="並び替え" />
</form>
