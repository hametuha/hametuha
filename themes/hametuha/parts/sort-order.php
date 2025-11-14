<?php
/**
 * 投稿一覧で並び順を変えるナビゲーション
 *
 */
list( $uri ) = explode( '?', $_SERVER['REQUEST_URI'] );

$base = preg_replace( '#/page/[0-9]+/?#', '/', $uri );

$order   = get_query_var( 'order' );
$order   = ( 'asc' === strtolower( $order ) ) ? 'asc' : 'desc';
$orderby = filter_input( INPUT_GET, 'orderby' ) ?: 'date';

// 全体のクエリ
$args    = [
	'date'    => [],
	'popular' => [
		'orderby' => 'popular',
		'order'   => 'desc',
	],
];
$current = [
	'date'    => [ 'nav-link' ],
	'popular' => [ 'nav-link' ],
];
switch ( $orderby ) {
	case 'popular':
		$current['popular'][] = 'active';
		if ( 'desc' == $order ) {
			$args['popular']['order'] = 'asc';
		} else {
			$current['popular'][] = 'asc';
		}
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
	<li role="presentation" class="nav-item">
		<a class="<?php echo implode( ' ', $current['date'] ); ?>"
		href="<?php echo add_query_arg( $args['date'], $base ); ?>" aria-controls="latest">
			新着順
			<i class="icon-arrow-down"></i>
			<i class="icon-arrow-up"></i>
		</a>
	</li>
	<?php if ( is_tag() || is_category() || is_home() || ( is_search() && ! get_query_var( 'post_type' ) ) ) : ?>
		<li role="presentation" class="nav-item">
			<a class="<?php echo implode( ' ', $current['popular'] ); ?>"
			href="<?php echo add_query_arg( $args['popular'], $base ); ?>" aria-controls="profile">
				人気
				<i class="icon-arrow-down"></i>
				<i class="icon-arrow-up"></i>
			</a>
		</li>
	<?php endif; ?>
</ul>
