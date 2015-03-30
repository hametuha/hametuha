<?php
$model = \Hametuha\Model\Review::get_instance();
?>
<ul class="nav nav-pills">
    <li class="active">
    <?php foreach( $model->feedback_tags as $key => $tags ){
	    foreach( $tags as $positive => $tag ){
		    $term = get_term_by('name', $tag, $model->taxonomy);
	        printf('<li class="%s"><a href="%s" class="">%s</a></li>',
		        $term->term_id == get_query_var('reviewed_as') ? 'active' : '',
		        home_url("reviewed/{$term->term_id}/"),
		        esc_html($term->name));
	    }
    } ?>
</ul>