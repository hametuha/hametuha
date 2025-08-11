<?php

list( $uri ) = explode( '?', $_SERVER['REQUEST_URI'] );

$base = preg_replace( '#/page/[0-9]+/?#', '/', $uri );

$order   = isset( $_GET['order'] ) && strtolower( $_GET['order'] ) == 'asc' ? 'asc' : 'desc';
$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'date';

// 全体のクエリ
$args    = [
	'date'    => [],
	'popular' => [
		'orderby' => 'popular',
		'order'   => 'desc',
	],
];
$current = [
	'date'    => [],
	'popular' => [],
];
switch ( $orderby ) {
	case 'popular':
		$args['popular']['order'] = 'desc' == $order ? 'asc' : 'desc';
		$current['popular'][]     = 'active';
		break;
	default:
		$current['date'][] = 'active';
		if ( 'desc' == $order ) {
			$args['date']['order'] = 'asc';
		} else {
			$current['date'][] = 'asc';
		}
		break;
}

?>
<!-- Nav tabs -->
<ul class="nav nav-tabs nav-tabs--archive">
	<li role="presentation" class="<?php echo implode( ' ', $current['date'] ); ?>">
		<a href="<?php echo add_query_arg( $args['date'], $base ); ?>" aria-controls="latest">
			新着順
			<i class="icon-arrow-down"></i>
			<i class="icon-arrow-up"></i>
		</a>
	</li>
	<?php if ( is_tag() || is_category() || is_home() || ( is_search() && ! get_query_var( 'post_type' ) ) ) : ?>
		<li role="presentation" class="<?php echo implode( ' ', $current['popular'] ); ?>">
			<a href="<?php echo add_query_arg( $args['popular'], $base ); ?>" aria-controls="profile">
				人気
				<i class="icon-arrow-down"></i>
				<i class="icon-arrow-up"></i>
			</a>
		</li>
	<?php endif; ?>
</ul>
