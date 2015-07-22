<?php

list($uri) = explode('?', $_SERVER['REQUEST_URI']);

$base = preg_replace('#/page/[0-9]+/?#', '/', $uri);

$order = isset($_GET['order']) && strtolower($_GET['order']) == 'asc' ? 'asc' : 'desc';
$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'date';

// 全体のクエリ
$args = [
	'date' => [],
	'popular' => [
		'orderby' => 'popular',
	    'order'  => 'desc',
	],
	'social' => [
		'orderby' => 'social',
		'order'  => 'desc',
	],
];
$current = [
	'date' => [],
	'popular' => [],
	'social' => [],
];
switch( $orderby ){
	case 'popular':
		$args['popular']['order'] = 'desc' == $order ? 'asc' : 'desc';
		$current['popular'][] = 'active';
		break;
	case 'social':
		$args['social']['order'] =  'desc' == $order ? 'asc' : 'desc';
		$current['social'][] = 'active';
		break;
	default:
		$current['date'][] = 'active';
		if( 'desc' == $order ){
			$args['date']['order'] = 'asc';
		}else{
			$current['date'][] = 'asc';
		}
		break;
}

?>
<!-- Nav tabs -->
<ul class="nav nav-tabs nav-tabs--archive">
	<li role="presentation" class="<?= implode(' ', $current['date'] )  ?>">
		<a href="<?= add_query_arg($args['date'], $base) ?>" aria-controls="latest">
			新着順
			<i class="icon-arrow-down"></i>
			<i class="icon-arrow-up"></i>
		</a>
	</li>
	<?php if( is_tag() || is_category() || is_home() || ( is_search() && !get_query_var('post_type') )): ?>
	<li role="presentation" class="<?= implode(' ', $current['popular'] )  ?>">
		<a href="<?= add_query_arg($args['popular'], $base) ?>" aria-controls="profile">
			人気
			<i class="icon-arrow-down"></i>
			<i class="icon-arrow-up"></i>
		</a>
	</li>
	<li role="presentation" class="<?= implode(' ', $current['social'] )  ?>">
		<a href="<?= add_query_arg($args['social'], $base) ?>" aria-controls="socail">
			SNS人気
			<i class="icon-arrow-down"></i>
			<i class="icon-arrow-up"></i>
		</a>
	</li>
	<?php endif; ?>
</ul>
