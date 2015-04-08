<?php
$model = \Hametuha\Model\Review::get_instance();
foreach([
	'auth/' => '<i class="icon-bubble-user"></i> 登録ユーザーからの評価 <small>重複がありません</small>',
	'' => '<i class="icon-bubble-star"></i> 匿名ユーザーを含む評価 <small>同じ人が何度も投稿している場合もあり</small>',
] as $pref => $label): ?>
	<h3><?= $label ?></h3>
	<ul class="nav nav-pills">
	    <li class="active">
	    <?php foreach( $model->feedback_tags as $key => $tags ){
		    foreach( $tags as $positive => $tag ){
			    $term = get_term_by('name', $tag, $model->taxonomy);
		        printf('<li class="%s"><a href="%s" class="">%s</a></li>',
			        $term->term_id == get_query_var('reviewed_as') ? 'active' : '',
			        home_url("reviewed/{$pref}{$term->term_id}/"),
			        esc_html($term->name));
		    }
	    } ?>
	</ul>
<?php endforeach; ?>
