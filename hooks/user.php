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
		$role = hametuha_is_secret_guest( $user_id ) ? ' - <strong>シークレット</strong>' : '';
		return sprintf( '<ruby>%s<rt>%s</rt></ruby>%s', esc_html( $name ), esc_html( $ruby ), $role );
	} else {
		return $td;
	}
}, 20, 3 );


/**
 * デフォルトのコンタクトフィールドを削除する
 *
 * @param array $contactmethods
 *
 * @return array
 * @author WP Beginners
 * @url http://www.wpbeginner.com/wp-tutorials/how-to-remove-default-author-profile-fields-in-wordpress/
 */
add_filter( 'user_contactmethods', function ( $contact_methods ) {
	$contact_methods['aim'] = '<i class="icon-tag"></i> Webサイト名';
	unset( $contact_methods['jabber'] );
	unset( $contact_methods['yim'] );
	$contact_methods['twitter']          = '<i class="icon-twitter"></i> twitterアカウント';
	$contact_methods['location']         = '<i class="icon-location4"></i> 場所';
	$contact_methods['birth_place']      = '<i class="icon-compass"></i> 出身地';
	$contact_methods['favorite_authors'] = '<i class="icon-reading"></i> 好きな作家';
	$contact_methods['favorite_words']   = '<i class="icon-pen5"></i> 好きな言葉';

	return $contact_methods;
}, '_hide_profile_fields', 10, 1 );
