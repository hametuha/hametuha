<?php
/**
 * パンクズリストを改良する。
 */


/**
 * ランキングページパンクズリストの改良
 */
add_action( 'bcn_after_fill', function( bcn_breadcrumb_trail $bcn ) {
	if ( ! is_ranking() ) {
		// なにもしない。
		return;
	}
	// ホーム以外空にする
	$bcn->trail = [];
	// ページネーションされているか？
	$link_last = false;
	$paged     = max( 1, get_query_var( 'paged' ) );
	if ( 1 < $paged ) {
		$bcn->add( new bcn_breadcrumb( sprintf( '%dページ目', $paged ), null, ['ranking-archive-pages'], esc_url( home_url( $_SERVER['REQUEST_URI'] ) ), '', false ) );
		$link_last = true;
	}
	if ( is_ranking( 'best' ) ) {
		// カテゴリー指定されているか？
		$category  = get_query_var( 'category_name' );
		if ( $category ) {
			$cat = get_term_by( 'slug', $category, 'category' );
			if ( $cat ) {
				$bcn->add( new bcn_breadcrumb( sprintf( '%s部門', $cat->name ), null, ['ranking-archive-category'], home_url( "ranking/best/{$category}" ), '', $link_last ) );
				$link_last = true;
			}
		}
		// ベストのトップ
		$bcn->add( new bcn_breadcrumb( '歴代ベスト', null, ['ranking-best'], home_url( 'ranking/best/' ), '', $link_last) );
	} else {
		// 通常のアーカイブ
		$y      = get_query_var( 'year' );
		$m      = get_query_var( 'monthnum' );
		$d      = get_query_var( 'day' );
		// 週間ランキンはすべての指定がある。
		if ( is_ranking( 'weekly' ) ) {
			$bcn->add( new bcn_breadcrumb( sprintf( '%d年%d月%d日週間ランキング', $y, $m, $d ), null, ['ranking-archive-weekly'], home_url( sprintf( 'ranking/weekly/%04d/%02d/%02d/', $y, $m, $d ) ), '', $link_last ) );
			$link_last = true;
		}
		if ( $d ) {
			$bcn->add( new bcn_breadcrumb( sprintf( '%d日', $d ), null, ['ranking-archive-day'], home_url( sprintf( 'ranking/%04d/%02d/%02d/', $y, $m, $d ) ), '', $link_last ) );
			$link_last = true;
		}
		if ( $m ) {
			$bcn->add( new bcn_breadcrumb( sprintf( '%d月', $m ), null, ['ranking-archive-month'], home_url( sprintf( 'ranking/%04d/%02d/', $y, $m ) ), '', $link_last ) );
			$link_last = true;
		}
		if ( $y ) {
			$bcn->add( new bcn_breadcrumb( sprintf( '%d年', $y ), null, ['ranking-archive-year'], home_url( "ranking/{$y}/" ), '', $link_last ) );
			$link_last = true;
		}
	}
	// ランキングトップ
	$bcn->add( new bcn_breadcrumb( 'ランキング', null, ['ranking-home'], home_url( 'ranking'), '', ! is_ranking( 'top' ) ) );
	// 最後にホーム
	$bcn->add( new bcn_breadcrumb( 'ホーム', null, ['main-home'], home_url(), '', true ) );
} );
