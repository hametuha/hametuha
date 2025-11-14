<?php

namespace Hametuha\Hooks;



use Hametuha\Master\AnnouncementMeta;
use Hametuha\ThePost\Announcement;
use WPametu\Pattern\Singleton;


/**
 * 告知の管理画面用フィールド
 *
 * @package hametuha
 */
class AnnouncementHooks extends Singleton {

	use AnnouncementMeta;

	protected function __construct() {
		add_filter( 'manage_announcement_posts_columns', [ $this, 'custom_column' ] );
		add_action( 'manage_announcement_posts_custom_column', [ $this, 'custom_column_content' ], 10, 2 );
	}


	/**
	 * カスタムカラムの内容
	 *
	 * @param $columns
	 * @return void
	 */
	public function custom_column( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'title' === $key ) {
				$new_columns['event_date']    = __( '開催日時', 'hametuha' );
				$new_columns['event_place']   = __( '開催場所', 'hametuha' );
				$new_columns['participation'] = __( '募集', 'hametuha' );
			}
		}
		return $new_columns;
	}

	public function custom_column_content( $column, $post_id ) {
		$event = new Announcement( get_post( $post_id ) );
		switch ( $column ) {
			case 'event_date':
				$event_start = get_post_meta( $post_id, '_event_start', true );
				if ( $event_start ) {
					echo hamenew_event_date( $event_start, get_post_meta( $post_id, '_event_end', true ) );
				} else {
					echo '---';
				}
				break;
			case 'event_place':
				$address = get_post_meta( $post_id, '_event_address', true );
				if ( $address ) {
					echo esc_html( $address . ' ' . get_post_meta( $post_id, '_event_bld', true ) );
				} else {
					echo '---';
				}
				break;
			case 'participation':
				if ( $event->can_participate() ) {
					echo esc_html( $event->get_participating_period() );
				} else {
					echo '---';
				}
		}
	}
}
