<?php
/**
 * 投稿一覧で並び順を変えるナビゲーション
 *
 * タブはリンクではなく、GET のフォームのボタンにしている。
 * リンクだとクローラーが「並び順 × ページ送り」の組み合わせをたどり続け、
 * /latest/page/152/?order=asc&orderby=popular のような URL まで掘られていたため。
 * フォームは今の絞り込み（キーワード・タグ・文字数など）を hidden で引き継ぐ。
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
		if ( 'desc' === $order ) {
			$args['popular']['order'] = 'asc';
		} else {
			$current['popular'][] = 'asc';
		}
		break;
	default:
		$current['date'][] = 'active';
		if ( 'desc' === $order ) {
			$args['date']['order'] = 'asc';
		} else {
			$current['date'][] = 'asc';
		}
		break;
}

// 引き継ぐ絞り込み。並び順とページ番号は引き継がない
$keep = wp_unslash( $_GET );
unset( $keep['order'], $keep['orderby'], $keep['paged'] );

$tabs = [
	'date' => '新着順',
];
// 検索は hooks/search.php が post_type=post を入れるので、post も作品の検索として扱う
if ( is_tag() || is_category() || is_home() || ( is_search() && in_array( get_query_var( 'post_type' ), [ '', 'post' ], true ) ) ) {
	$tabs['popular'] = '人気';
}
?>
<!-- Nav tabs -->
<ul class="nav nav-tabs nav-tabs--archive">
	<?php foreach ( $tabs as $key => $label ) : ?>
		<li role="presentation" class="nav-item">
			<form method="get" action="<?php echo esc_url( $base ); ?>">
				<?php hametuha_hidden_inputs( array_merge( $keep, $args[ $key ] ) ); ?>
				<button type="submit" class="<?php echo esc_attr( implode( ' ', $current[ $key ] ) ); ?>">
					<?php echo esc_html( $label ); ?>
					<i class="icon-arrow-down"></i>
					<i class="icon-arrow-up"></i>
				</button>
			</form>
		</li>
	<?php endforeach; ?>
</ul>
