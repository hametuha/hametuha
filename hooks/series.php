<?php
/**
 * 作品集に関するフック
 */

use Hametuha\Model\Series;


/**
 * 投稿リストにカラムを追加
 */
add_filter( 'manage_posts_columns', function ( $columns, $post_type ) {
	$new_columns = [];
	foreach ( $columns as $key => $val ) {
		switch ( $post_type ) {
			case 'news':
				$new_columns[ $key ] = $val;
				if ( 'title' === $key ) {
					$new_columns['thumbnail'] = 'アイキャッチ';
				}
				break;
			case 'series':
				if ( 'author' === $key ) {
					$val = '編集者';
					if ( current_user_can( 'edit_others_posts' ) ) {
						$new_columns['menu_order'] = '順';
					}
				}
				$new_columns[ $key ] = $val;
				if ( 'title' === $key ) {
					$new_columns['char_count']   = __( '原稿用紙', 'hametuha' );
					$new_columns['thumbnail']    = '表紙画像';
					$new_columns['count']        = '作品数';
					$new_columns['sales_status'] = '販売状況';
				}
				break;
			case 'post':
				$new_columns[ $key ] = $val;
				if ( 'taxonomy-campaign' === $key ) {
					$new_columns['char_count'] = __( '原稿用紙', 'hametuha' );
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
				if ( Series::get_instance()->is_finished( $post_id ) ) {
					printf( '<span style="color: #a1de9e;"> 完結（%s作品）</span>', number_format( $total ) );
				} else {
					printf( '連載中（%s作品）', number_format( $total ) );
				}
			} else {
				echo '<span style="color: lightgrey;">登録なし</span>';
			}
			break;
		case 'char_count':
			echo number_format_i18n( ceil( get_post_length( $post_id ) / 400 ) ) . '枚';
			break;
		case 'sales_status':
			$status = Series::get_instance()->get_status( $post_id );
			$extra  = '';
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
		default:
			// Do nothing.
			break;
	}
}, 10, 2 );


