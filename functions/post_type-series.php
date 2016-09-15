<?php
/**
 * シリーズに関する処理／関数群
 */

use Hametuha\Model\Series;

/**
 * シリーズに属しているか否かを返す。属している場合は親ID
 *
 * @param WP_Post $post
 *
 * @return int
 */
function is_series( $post = null ) {
	$post = get_post( $post );

	return 'series' == get_post_type( $post->post_parent ) ? $post->post_parent : 0;
}

/**
 * シリーズが終了しているか
 *
 * @param null $post
 *
 * @return bool
 */
function is_series_finished( $post = null ) {
	$post = get_post( $post );
	if ( 'series' == $post->post_type ) {
		$series_id = $post->ID;
	} else {
		$series_id = $post->post_parent;
	}

	return Series::get_instance()->is_finished( $series_id );
}

/**
 * シリーズに属している場合にシリーズページへのリンクを返す
 *
 * @param string $pre
 * @param string $after
 * @param WP_Post $post
 */
function the_series( $pre = '', $after = '', $post = null ) {
	$series = is_series( $post );
	if ( $series ) {
		$series = get_post( $series );
		echo $pre . '<a href="' . get_permalink( $series->ID ) . '" itemprop="isPartOf">' . apply_filters( 'the_title', $series->post_title ) . '</a>' . $after;
	}
}

/**
 * Get KDP price
 *
 * @param null|int|WP_Post $post
 * @param bool|true $cache
 */
function the_series_price( $post = null, $cache = true ) {
	$price = get_series_price( $post, $cache );
	echo false !== $price ? number_format( $price ) : 'N/A';
}

/**
 * 料金を取得する
 *
 * @param null $post
 * @param bool|true $cache
 *
 * @return false|int
 */
function get_series_price( $post = null, $cache = true ) {
	$post   = get_post( $post );
	$series = Series::get_instance();
	if ( 2 != $series->get_status( $post->ID ) ) {
		return false;
	}
	$key   = 'kdp_price_' . $post->ID;
	$price = get_transient( $key );
	if ( false === $price || ! $cache ) {
		$url      = $series->get_kdp_url( $post->ID );
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) || ! preg_match( '#<(span|b)([^>]*?)class="a-color-price"([^>]*?)>([^<]+)</(span|b)>#', $response['body'], $match ) ) {
			return false;
		}
		$price = preg_replace( '#[^0-9]#', '', $match[4] );
		if ( is_numeric( $price ) ) {
			set_transient( $key, $price, 60 * 60 );
			$price = intval( $price );
		} else {
			return false;
		}
	}
	return $price;
}

/**
 * Get all user for series
 *
 * @param null|WP_Post|int $post
 *
 * @return array
 */
function get_series_authors( $post = null ) {
	$post = get_post( $post );

	return Series::get_instance()->get_authors( $post->ID );
}

/**
 * Show series range
 *
 * @param null|WP_Post|int $post
 * @param string $format
 */
function the_series_range( $post = null, $format = '' ) {
	$post   = get_post( $post );
	$format = $format ?: get_option( 'date_format' );
	$range  = Series::get_instance()->get_series_range( $post->ID );
	if ( $range && $range->start_date ) {
		echo mysql2date( $format, $range->start_date ) . '〜' . mysql2date( $format, $range->last_date );
	}
}

/**
 * リダイレクトされるのを防ぐ
 *
 * @param string $redirect_url
 *
 * @return string
 */
add_filter( 'redirect_canonical', function ( $redirect_url ) {
	if ( is_singular( 'series' ) && false !== strpos( $_SERVER['REQUEST_URI'], '/page/' ) ) {
		return false;
	} else {
		return $redirect_url;
	}
} );


/**
 * シリーズをみられないようにする
 *
 * @param string $content
 *
 * @return string
 */
function hametuha_series_hide( $content ) {
	// DOMの一部を切り出す
	$dom       = \WPametu\Utility\Formatter::get_dom( $content );
	$body      = $dom->getElementsByTagName( 'body' )->item( 0 );
	$dom_count = $body->childNodes->length;
	$limit     = floor( $dom_count / 4 );
	for ( $i = $dom_count - 1; $i >= 0; $i -- ) {
		if ( $i > $limit ) {
			$body->removeChild( $body->childNodes->item( $i ) );
		}
	}
	$content = \WPametu\Utility\Formatter::to_string( $dom );
	$content .= "\n<div class=\"content-hide-cover\"></div>";
	remove_filter( 'the_content', 'hametuha_series_hide' );

	return $content;
}


/**
 * 投稿リストにカラムを追加
 */
add_filter( 'manage_posts_columns', function ( $columns, $post_type ) {
	$new_columns = [];
	foreach ( $columns as $key => $val ) {
		switch ( $post_type ) {
			case 'news':
				$new_columns[ $key ] = $val;
				if ( 'title' == $key ) {
					$new_columns['thumbnail'] = 'アイキャッチ';
				}
				break;
			case 'series':
				if ( 'author' == $key ) {
					$val = '編集者';
					if ( current_user_can( 'edit_others_posts' ) ) {
						$new_columns['menu_order'] = '順';
					}
				}
				$new_columns[ $key ] = $val;
				if ( 'title' == $key ) {
					$new_columns['thumbnail'] = '表紙画像';
					$new_columns['count']        = '作品数';
					$new_columns['sales_status'] = '販売状況';
				}
				break;
			case 'post':
				$new_columns[ $key ] = $val;
				if ( 'title' == $key ) {
					$new_columns['series'] = '作品集';
				}
				break;
			default:
				// Do nothing
				break;
		}
	}
	if ( isset( $new_columns['menu_order'] ) ) {
		// menu_orderがカラムに存在したら、ソート順を追加
		add_filter( 'manage_edit-series_sortable_columns', function( $sortable_columns ) {
			$sortable_columns['menu_order'] = [ 'menu_order', false ];
			return $sortable_columns;
		}, 10, 3 );
	}
	if ( $new_columns ) {
		$columns = $new_columns;
	}

	return $columns;
}, 10, 2 );


/**
 * 投稿リストのカラムを出力
 */
add_action( 'manage_posts_custom_column', function ( $column, $post_id ) {
	switch ( $column ) {
		case 'menu_order':
			$order = get_post( $post_id )->menu_order;
			if ( 99 < $order ) {
				$color = 'red';
			} elseif ( 9 < $order ) {
				$color = 'green';
			} elseif ( 0 < $order ) {
				$color = 'black';
			} else {
				$color = 'lightgrey';
			}
			printf( '<span style="color: %s">%s</span>', $color, number_format( $order ) );
			break;
		case 'thumbnail':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, 'thumbnail', [ 'class' => 'post-list-thumbnail' ] );
			} else {
				printf( '<img src="%s/assets/img/dammy/300.png" class="post-list-thumbnail" />', get_template_directory_uri() );
			}
			break;
		case 'count':
			$total = Series::get_instance()->get_total( $post_id );
			if ( $total ) {
				if ( Series::get_instance()->is_finished( $post_id )  ) {
					printf( '<span style="color: #a1de9e;"> 完結（%s作品）</span>', number_format( $total ) );
				} else {
					printf( '連載中（%s作品）', number_format( $total ) );
				}
			} else {
				echo '<span style="color: lightgrey;">登録なし</span>';
			}
			break;
		case 'sales_status':
			$status = Series::get_instance()->get_status( $post_id );
			$extra = '';
			switch ( $status ) {
				case 2:
					$color = 'green';
					break;
				case 1:
					$color = 'orange';
					if ( Series::get_instance()->validate( $post_id ) ) {
						$extra = '<strong style="color: red">（不備あり）</strong>';
					}
					break;
				default:
					$color = 'lightgrey';
					break;
			}
			$secret = '';
			if ( hametuha_is_secret_book( $post_id ) ) {
				$secret = '- <strong>シークレット</strong>';
			}
			printf( "<span style='color: %s'>%s%s</span>%s", $color, Series::get_instance()->status_label[ $status ], $extra, $secret );
			if ( $asin = Series::get_instance()->get_asin( $post_id ) ) {
				echo "<code>{$asin}</code>";
			}
			break;
		case 'series':
			$post = get_post( $post_id );
			if ( $post->post_parent && ( $parent = get_post( $post->post_parent ) ) ) {
				// 親がある
				if ( current_user_can( 'edit_post', $parent->ID ) ) {
					$url = admin_url( "post.php?post={$parent->ID}&action=edit" );
				} else {
					$url = get_permalink( $parent );
				}
				printf( '<a href="%s">%s</a>', esc_url( $url ), get_the_title( $parent ) );
			} else {
				// なし
				echo '<span style="color: #d3d3d3">--</span>';
			}
			break;
		default:
			// Do nothing.
			break;
	}
}, 10, 2 );

