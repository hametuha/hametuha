<?php

/**
 * ユーザー連絡にイベント参加者を追加する
 */
add_filter( 'hamail_search_results', function( $results, $string ) {
	$events = get_posts( [
		'post_type' => [ 'announcement', 'news' ],
	    'post_status' => [ 'publish', 'private' ],
	    's' => $string,
	    'posts_per_page' => 10,
	    'meta_query' => [
	    	[
	    		'key' => '_hametuha_commit_type',
		        'value' => 1,
		    ],
	    ],
	] );
	if ( $events ) {
		foreach ( $events as $event ) {
			array_unshift( $results, (object) [
				'id'   => $event->ID,
			    'type' => 'participants',
			    'label' => "{$event->post_title}の参加者",
			    'data' => '',
			] );
		}
	}
	return $results;
}, 10, 2 );

/**
 * イベントの参加者を取得する
 */
add_filter( 'hamail_extra_search', function( $results, $type, $id ) {
	switch ( $type ) {
		case 'participants':
			if ( $post = get_post( $id ) ) {
				$event   = new \Hametuha\ThePost\Announcement( $post );
				$results = array_map( function ( $user ) {
					return [
						'user_id'      => $user['id'],
						'display_name' => $user['name'],
						'user_email'   => $user['mail'],
					];
				}, $event->get_participants( true ) );
			}
			break;
		default:
			// Do nothing
			break;
	}
	return $results;
}, 10, 3 );