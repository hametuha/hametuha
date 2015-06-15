<?php
$series = Hametuha\Model\Series::get_instance();

$series_id = 0;
if( 'series' == get_post_type() ){
	$series_id = get_the_ID();
}else{
	$series_id = $post->post_parent;
}
$limit = $series->get_visibiity($series_id);
if( $limit ){
	$asin = $series->get_asin($series_id);
	$permalink = get_permalink($series_id);
	$title = get_the_title($series_id);
	$msg = <<<HTML
		        	<a class="alert-link" href="{$permalink}">{$title}</a>は{$limit}話まで無料で読むことができます。
HTML;
	switch( $series->get_status($series_id) ){
		case 2:
			$msg .= '続きはAmazon Kindleで入手可能です。ぜひご利用ください。';
			$msg2 = do_shortcode("[tmkm-amazon asin='{$asin}'][/tmkm-amazon]");
			$class_name = 'success';
			break;
		case 1:
			$msg .= '続きは現在販売準備中です。乞うご期待。';
			$msg2 = '';
			$class_name = 'danger';
			break;
		default:
			$msg = false;
			$msg2 = '';
			break;
	}
	if( $msg ){
		echo  <<<HTML
				<div class="alert alert-{$class_name} text-center">
					<p>{$msg}</p>
					{$msg2}
				</div>
HTML;
	}
}