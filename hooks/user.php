<?php


/**
 * 管理画面でユーザーを探せるようにする
 *
 */
add_filter( 'pre_user_query', function ( WP_User_Query &$user_query ) {
	/** @var wpdb $wpdb */
	global $wpdb;
	if ( is_admin() && isset( $user_query->query_vars['search'] ) && ! empty( $user_query->query_vars['search'] ) ) {
		$where = str_replace( '*', '%', $user_query->query_vars['search'] );

		$user_query->query_from .= <<<SQL
		LEFT JOIN {$wpdb->usermeta} AS last_name
		ON {$wpdb->users}.ID = last_name.user_id AND last_name.meta_key = 'last_name'
SQL;

		$query                   = <<<SQL
		( {$wpdb->users}.user_login LIKE %s OR {$wpdb->users}.user_nicename LIKE %s OR {$wpdb->users}.display_name LIKE %s OR last_name.meta_value LIKE %s)
SQL;
		$user_query->query_where = preg_replace( '/\(user_login LIKE \'%.*%\' OR user_nicename LIKE \'%.*%\'\)/u', $wpdb->prepare( $query, $where, $where, $where, $where ), $user_query->query_where );
	}
} );

/**
 * ユーザーテーブルの名前表示を変更
 */
add_filter( 'manage_users_columns', function ( $columns ) {
	$new_column = array();
	foreach ( $columns as $key => $val ) {
		if ( 'name' === $key ) {
			$new_column['display_name'] = '表示名';
		} elseif ( false !== array_search( $columns, [ 'backwpup_role', 'ure_roles' ] ) ) {
			// 邪魔なのは消す
		} else {
			$new_column[ $key ] = $val;
		}
	}

	return $new_column;
}, 200 );

/**
 * 名前を表示する
 */
add_filter( 'manage_users_custom_column', function ( $td, $column, $user_id ) {
	if ( 'display_name' == $column ) {
		$ruby = (string) get_user_meta( $user_id, 'last_name', true );
		$name = (string) get_the_author_meta( 'display_name', $user_id );
		$roles = [];
		if ( hametuha_is_secret_guest( $user_id ) ) {
			$roles[] = 'シークレット';
		}
		foreach ( hametuha_user_flags() as $flag ) {
			if ( hametuha_user_has_flag( $user_id, $flag['id' ] ) ) {
				$roles[] = $flag['label'];
			}
		}
		$role = implode( '', array_map( function( $r ) {
			return sprintf( ' - <strong>%s</strong>', esc_html( $r ) );
		}, $roles ) );
		return sprintf( '<ruby>%s<rt>%s</rt></ruby>%s', esc_html( $name ), esc_html( $ruby ), $role );
	} else {
		return $td;
	}
}, 20, 3 );

/**
 * Add extra values to user cookie.
 */
add_filter( 'cookie_tasting_values', function( $values, $user_id ) {
	$values['role']      = hametuha_user_role( $user_id );
	$values['is_author'] = user_can( $user_id, 'edit_posts' ) ? 'true' : '';
	return $values;
}, 10, 2 );


/**
 * If user is editor, then can't resign.
 */
add_filter( 'nlmg_validate_user', function( WP_Error $error, $user_id ) {
	if ( user_can( $user_id, 'edit_others_posts' ) ) {
		$error->add( 'editor_cannot_resign', '編集者は退会できません。何か嫌なことがありましたか？　話し合いましょう。' );
	}
	return $error;
}, 10, 2 );

/**
 * Move news to anonymous user.
 */
add_action( 'nlmg_before_leave', function( $user_id ) {
	$anoymous = hametuha_get_anonymous_user();
	global $wpdb;
	$query = <<<SQL
		UPDATE {$wpdb->posts} SET post_author=%d
		WHERE post_type   = 'news'
          AND post_author = %d
SQL;
	$wpdb->query( $wpdb->prepare( $anoymous->ID, $user_id ) );
} );
