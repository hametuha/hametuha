<?php
/**
 * パンクズリストを改良する。
 */


/**
 * ランキングページパンクズリストの改良
 */
add_action( 'bcn_after_fill', function ( bcn_breadcrumb_trail $bcn ) {
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
		$bcn->add( new bcn_breadcrumb( sprintf( '%dページ目', $paged ), null, [ 'ranking-archive-pages' ], esc_url( home_url( $_SERVER['REQUEST_URI'] ) ), '', false ) );
		$link_last = true;
	}
	if ( is_ranking( 'best' ) ) {
		// カテゴリー指定されているか？
		$category = get_query_var( 'category_name' );
		if ( $category ) {
			$cat = get_term_by( 'slug', $category, 'category' );
			if ( $cat ) {
				$bcn->add( new bcn_breadcrumb( sprintf( '%s部門', $cat->name ), null, [ 'ranking-archive-category' ], home_url( "ranking/best/{$category}" ), '', $link_last ) );
				$link_last = true;
			}
		}
		// ベストのトップ
		$bcn->add( new bcn_breadcrumb( '歴代ベスト', null, [ 'ranking-best' ], home_url( 'ranking/best/' ), '', $link_last ) );
	} else {
		// 通常のアーカイブ
		$y = get_query_var( 'year' );
		$m = get_query_var( 'monthnum' );
		$d = get_query_var( 'day' );
		// 週間ランキンはすべての指定がある。
		if ( is_ranking( 'weekly' ) ) {
			$bcn->add( new bcn_breadcrumb( sprintf( '%d年%d月%d日週間ランキング', $y, $m, $d ), null, [ 'ranking-archive-weekly' ], home_url( sprintf( 'ranking/weekly/%04d/%02d/%02d/', $y, $m, $d ) ), '', $link_last ) );
			$link_last = true;
		}
		if ( $d ) {
			$bcn->add( new bcn_breadcrumb( sprintf( '%d日', $d ), null, [ 'ranking-archive-day' ], home_url( sprintf( 'ranking/%04d/%02d/%02d/', $y, $m, $d ) ), '', $link_last ) );
			$link_last = true;
		}
		if ( $m ) {
			$bcn->add( new bcn_breadcrumb( sprintf( '%d月', $m ), null, [ 'ranking-archive-month' ], home_url( sprintf( 'ranking/%04d/%02d/', $y, $m ) ), '', $link_last ) );
			$link_last = true;
		}
		if ( $y ) {
			$bcn->add( new bcn_breadcrumb( sprintf( '%d年', $y ), null, [ 'ranking-archive-year' ], home_url( "ranking/{$y}/" ), '', $link_last ) );
			$link_last = true;
		}
	}
	// ランキングトップ
	$bcn->add( new bcn_breadcrumb( 'ランキング', null, [ 'ranking-home' ], home_url( 'ranking' ), '', ! is_ranking( 'top' ) ) );
	// 最後にホーム
	$bcn->add( new bcn_breadcrumb( 'ホーム', null, [ 'main-home' ], home_url(), '', true ) );
} );

/**
 * KDPのページ
 */
add_action( 'bcn_after_fill', function ( bcn_breadcrumb_trail $bcn ) {
	if ( 'kdp' !== get_query_var( 'meta_filter' ) ) {
		return;
	}
	$trails = [];
	foreach ( $bcn->trail as $item ) {
		/** @var bcn_breadcrumb $item */
		if ( in_array( 'post-series-archive', $item->get_types(), true ) ) {
			// これは作品集のアーカイブ
			$trail = new bcn_breadcrumb( __( '電子書籍', 'hametuha' ), null, [ 'post-series-archive-kdp' ], home_url( 'kdp' ), 'kdp-archive', true );
			if ( 1 < (int) get_query_var( 'paged' ) ) {
				// ページネーションされてる。
				$trails[] = $trail;
				$trails[] = $item;
			} else {
				// ページネーションされてない。
				$trail->set_linked( false );
				$trails[] = $trail;
				$trails[] = new bcn_breadcrumb( $item->get_title(), null, array_filter( $item->get_types(), function ( $type ) {
					return 'current-item' !== $type;
				} ), $item->get_url(), $item->get_id(), true );
			}
		} else {
			if ( ! in_array( 'series-root', $item->get_types(), true ) ) {
				// 投稿一覧じゃなければ既存のアイテムを追加
				$trails[] = $item;
			}
		}
	}
	$bcn->trail = $trails;
} );

/**
 * 作品集がKDPだったら
 */
add_action( 'bcn_after_fill', function ( bcn_breadcrumb_trail $bcn ) {
	if ( ! is_singular( 'series' ) ) {
		return;
	}
	$trails = [];
	foreach ( $bcn->trail as $item ) {
		/** @var bcn_breadcrumb $item */
		if ( ! in_array( 'series-root', $item->get_types(), true ) ) {
			// 投稿一覧じゃなければ既存のアイテムを追加
			$trails[] = $item;
		}
		if ( in_array( 'current-item', $item->get_types(), true ) ) {
			// 著者ページを追加
			$trails [] = new bcn_breadcrumb( hametuha_author_name( get_queried_object() ), null, [ 'post-author' ], hametuha_author_url( get_queried_object()->post_author ), '', true );
			// KDPリンクを追加
			$trails [] = new bcn_breadcrumb( __( '電子書籍', 'hametuha' ), null, [ 'post-series-archive-kdp' ], home_url( 'kdp' ), 'kdp-archive', true );
		}
	}
	$bcn->trail = $trails;
} );

/**
 * 投稿ページのパンクズリストを変更する
 *
 */
add_action( 'bcn_after_fill', function ( bcn_breadcrumb_trail $bcn ) {
	if ( ! is_singular( 'post' ) ) {
		// 投稿ページ以外はなにもしない。
		return;
	}
	// ホーム以外空にする
	$bcn->trail = [];
	// ページネーションされているか？
	$link_last = false;
	$paged     = max( 1, get_query_var( 'page' ) );
	if ( 1 < $paged ) {
		$bcn->add( new bcn_breadcrumb( sprintf( '%dページ目', $paged ), null, [ 'post-pages' ], esc_url( trailingslashit( get_permalink( get_queried_object() ) ) . 'page/' . $paged ), '', false ) );
		$link_last = true;
	}
	// URLをつける
	$bcn->add( new bcn_breadcrumb( get_the_title(), null, [ 'post-single' ], get_permalink( get_queried_object() ), '', $link_last ) );
	// 作品集の一部なら、作品集へリンク、それ以外ならカテゴリーへリンク
	$parent = get_queried_object()->post_parent ? get_post( get_queried_object()->post_parent ) : null;
	if ( $parent && 'series' === $parent->post_type ) {
		$bcn->add( new bcn_breadcrumb( get_the_title( $parent ), null, [ 'post-series' ], get_permalink( $parent ), '', true ) );
	} else {
		foreach ( [ 'campaign', 'category' ] as $taxonomy ) {
			// キャンペーン、あるいはカテゴリーのどちらかを入れる
			$terms = get_the_terms( get_queried_object_id(), $taxonomy );
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$bcn->add( new bcn_breadcrumb( $term->name, null, [ 'category' ], get_term_link( $term ), '', true ) );
					break 2;
				}
			}
		}
	}
	// 作者名をつける
	$bcn->add( new bcn_breadcrumb( hametuha_author_name( get_queried_object() ), null, [ 'post-author' ], hametuha_author_url( get_queried_object()->post_author ), '', true ) );
	// 作者一覧ページへ
	$bcn->add( new bcn_breadcrumb( __( '執筆者一覧', 'hametuha' ), null, [ 'authors' ], home_url( '/authors/' ), '', true ) );
	// 最後にホーム
	$bcn->add( new bcn_breadcrumb( __( '破滅派', 'hametuha' ), null, [ 'main-home' ], home_url(), '', true ) );
} );

/**
 * 応募ページだったら、一覧を追加
 */
add_action( 'bcn_after_fill', function ( bcn_breadcrumb_trail $bcn ) {
	if ( ! is_tax( 'campaign' ) ) {
		return;
	}
	$parent = hametuha_get_campaign_page();
	if ( ! $parent ) {
		return;
	}
	$trails = [];
	foreach ( $bcn->trail as $item ) {
		/** @var bcn_breadcrumb $item */
		if ( in_array( 'post-root', $item->get_types(), true ) ) {
			// 親キャンペーンを追加
			$trails [] = new bcn_breadcrumb( get_the_title( $parent ), null, [ 'post-series-archive-kdp' ], get_permalink( $parent ), 'campaign-archive', true );
		} else {
			$trails[] = $item;
		}
	}
	$bcn->trail = $trails;
} );

/**
 * 著者アーカイブに詳細を追加
 */
add_action( 'bcn_after_fill', function ( bcn_breadcrumb_trail $bcn ) {
	if ( ! is_author() ) {
		return;
	}
	$trails = [];
	foreach ( $bcn->trail as $item ) {
		/** @var bcn_breadcrumb $item */
		if ( in_array( 'home', $item->get_types(), true ) ) {
			// 同人詳細ページを追加
			$trails [] = new bcn_breadcrumb( get_queried_object()->display_name, null, [ 'post-author' ], hametuha_author_url( get_queried_object()->ID ), '', true );
			// 作者一覧ページへ
			$trails [] = new bcn_breadcrumb( __( '執筆者一覧', 'hametuha' ), null, [ 'authors' ], home_url( '/authors/' ), '', true );
		}
		$trails[] = $item;
	}
	$bcn->trail = $trails;
} );

/**
 * 著者検索に著者一覧を追加
 */
add_action( 'bcn_after_fill', function ( bcn_breadcrumb_trail $bcn ) {
	$profile_name = get_query_var( 'profile_name' );
	if ( '0' !== $profile_name && ! $profile_name ) {
		return;
	}
	$trails = [];
	foreach ( $bcn->trail as $item ) {
		/** @var bcn_breadcrumb $item */
		if ( in_array( 'home', $item->get_types(), true ) ) {
			// 作者一覧ページへ
			$trails [] = new bcn_breadcrumb( __( '執筆者一覧', 'hametuha' ), null, [ 'authors' ], home_url( '/authors/' ), '', true );
		}
		if ( in_array( 'current-item', $item->get_types(), true ) ) {
			$trails [] = new bcn_breadcrumb( __( '執筆者検索', 'hametuha' ), null, [ 'authors-search' ], home_url( '/authors/' ), '', false );
		} else {
			$trails[] = $item;
		}
	}
	$bcn->trail = $trails;
} );


/**
 * キーワード検索に投稿タイプのアーカイブを追加
 */
add_action( 'bcn_after_fill', function ( bcn_breadcrumb_trail $bcn ) {
	if ( ! is_search() || ! get_query_var( 's' ) ) {
		// 検索ページじゃなければ何もしない
		return;
	}
	$post_type = get_query_var( 'post_type' );
	if ( ! $post_type ) {
		// これは投稿タイプ別アーカイブの検索結果ではない
		return;
	}
	$trails = [];
	foreach ( $bcn->trail as $trail ) {
		if ( in_array( 'search', $trail->get_types(), true ) ) {
			// ラベルを変更
			$s    = get_query_var( 's' );
			$link = get_post_type_archive_link( $post_type );
			$trail->set_title( sprintf( __( '「%s」の検索結果', 'hametuha' ), esc_html( $s ) ) );
			$trail->set_url( add_query_arg( [
				's' => rawurlencode( $s ),
			], $link ) );
			$trails[]  = $trail;
			$trails [] = new bcn_breadcrumb(
				get_post_type_object( $post_type )->label,
				null,
				[ 'post-type-archive' ],
				$link,
				'',
				true
			);
		} else {
			$trails[] = $trail;
		}
	}
	$bcn->trail = $trails;
} );
