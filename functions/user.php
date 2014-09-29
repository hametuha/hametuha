<?php



/**
 * 現在のユーザーの名前を返す
 *
 * @return string
 */
function hametuha_user_name(){
    $user = wp_get_current_user();
    if( $user->ID ){
        return $user->display_name;
    }else{
        return 'ゲスト';
    }
}

/**
 * 管理画面でユーザーを探せるようにする
 *
 */
add_filter('pre_user_query', function( WP_User_Query &$user_query ){
	/** @var wpdb $wpdb */
	global $wpdb;
	if( is_admin() && isset($user_query->query_vars['search']) && !empty($user_query->query_vars['search']) ){
		$where = str_replace('*', '%', $user_query->query_vars['search']);

		$user_query->query_from .= <<<SQL
		LEFT JOIN {$wpdb->usermeta} AS last_name
		ON {$wpdb->users}.ID = last_name.user_id AND last_name.meta_key = 'last_name'
SQL;

		$query = <<<SQL
		( {$wpdb->users}.user_login LIKE %s OR {$wpdb->users}.user_nicename LIKE %s OR {$wpdb->users}.display_name LIKE %s OR last_name.meta_value LIKE %s)
SQL;
		$user_query->query_where = preg_replace('/\(user_login LIKE \'%.*%\' OR user_nicename LIKE \'%.*%\'\)/u', $wpdb->prepare($query, $where, $where, $where, $where), $user_query->query_where);
	}
});


/**
 * ユーザーテーブルの名前表示を変更
 */
add_filter("manage_users_columns", function($columns){
	$new_column = array();
	foreach( $columns as $key => $val ){
		if( 'name' === $key ){
			$new_column['display_name'] = '表示名';
		}elseif( false !== array_search($columns, ['backwpup_role', 'ure_roles']) ) {
			// 邪魔なのは消す
		}else{
			$new_column[$key] = $val;
		}
	}
	return $new_column;
}, 200);


/**
 * 名前を表示する
 */
add_filter( 'manage_users_custom_column', function($td, $column, $user_id){
	if( 'display_name' == $column ){
		$ruby = (string)get_user_meta($user_id, 'last_name', true);
		$name = (string)get_the_author_meta('display_name', $user_id);
		return sprintf('<ruby>%s<rt>%s</rt></ruby>', esc_html($name), esc_html($ruby));
	}else{
		return $td;
	}
}, 20, 3);




/**
 * 現在のユーザーの登録日を返す
 *
 * @param string|bool $format
 * @return string
 */
function hametuha_user_registered($format = false){
    if( !$format ){
        $format = get_option('date_format');
    }
    $user = wp_get_current_user();
    if( $user->ID ){
        return mysql2date($format, $user->user_registered);
    }else{
        return '-';
    }
}


/**
 * 現在のユーザーの役割を返す
 *
 * @param null|WP_User $user
 * @return string
 */
function hametuha_user_role($user = null){
    if( is_null($user) ){
        $user = wp_get_current_user();
    }
    if( is_numeric($user) ){
	    $user = new WP_User($user);
        if( !$user ){
            return 'ゲスト';
        }
    }
	if( !method_exists($user, 'has_cap') ){
		return 'ゲスト';
	}
    if( $user->has_cap('manage_options') ){
        return '管理者';
    }elseif( $user->has_cap('edit_others_posts') ){
        return '編集者';
    }elseif( $user->has_cap('edit_posts') ){
        return '投稿者';
    }elseif( $user->has_cap('subscriber') ){
        return '読者';
    }else{
        return 'ゲスト';
    }
}





/**
 * 投稿作者の役割を返す
 * @global WP_Roles $wp_roles
 * @param int $user_id
 * @return array 
 */
function get_the_author_roles($user_id = null){
	global $wp_roles;
	if( is_null($user_id) ){
		$user_id = get_the_author_meta('ID');
	}else{
		$user_id = (int)$user_id;
	}
	$user = new WP_User($user_id);
	$role_name = array();
	foreach($user->roles as $role){
		$role_name[] = $wp_roles->role_names[$role];
	}
	return array_map('translate_user_role', $role_name);
}

/**
 * 投稿作者の役割を出力する
 * @param int $user_id
 * @param boolean $echo
 * @return string
 */
function the_author_roles($user_id = null, $echo = true){
	if(!is_null($user_id) && $user_id == 0){
		$roles = 'ゲスト';
	}else{
		$roles = get_the_author_roles($user_id);
		if(!empty($roles)){
			$roles = implode(', ', array_map('__', $roles));
		}else{
			$roles = '';
		}
	}
	if($echo){
		echo $roles;
	}
	return $roles;
}

/**
 * 指定した投稿者の投稿作品数を返す
 *
 * @param int $author_id 指定しない場合は現在の投稿の作者
 * @return int
 */
function get_author_work_count($author_id = null){
	global $wpdb;
    if( is_null($author_id) ){
        $author_id = get_the_author_meta('ID');
    }
	$sql = "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post' AND post_author = %d";
	return (int)$wpdb->get_var($wpdb->prepare($sql, $author_id));
}

/**
 * 指定した投稿者が最後に発表した日を返す
 * @global wpdb $wpdb
 * @param int $author_id
 * @return string
 */
function get_author_latest_published($author_id){
	global $wpdb;
	$sql = <<<SQL
		SELECT post_date FROM {$wpdb->posts}
		WHERE post_status = 'publish' AND post_type = 'post'
		 AND  post_author = %d
		ORDER BY post_date DESC
		LIMIT 1
SQL;
	return (string)$wpdb->get_var($wpdb->prepare($sql, $author_id));
}

/**
 * デフォルトのコンタクトフィールドを削除する
 * 
 * @param array $contactmethods
 * @return array
 * @author WP Beginners
 * @url http://www.wpbeginner.com/wp-tutorials/how-to-remove-default-author-profile-fields-in-wordpress/
 */
function _hide_profile_fields( $contactmethods ) {
	$contactmethods['aim'] = 'Webサイト名';
	unset($contactmethods['jabber']);
	unset($contactmethods['yim']);
	return $contactmethods;
}
add_filter('user_contactmethods','_hide_profile_fields',10,1);


/**
 * 登録されている投稿者の数を返す
 *
 * @param bool $doujin falseにすると投稿者以外のすべてのユーザー
 * @return int
 */
function get_author_count($doujin = true){
	global $wpdb;
	$sql = <<<SQL
		SELECT COUNT(ID) FROM {$wpdb->users} AS u
		LEFT JOIN {$wpdb->usermeta} AS um
		ON u.ID = um.user_id AND um.meta_key = '{$wpdb->prefix}user_level'
SQL;
	if($doujin){
		$sql .= ' WHERE um.meta_value > 0';
	}
	return (int)$wpdb->get_var($sql);
}


/**
 * 編集者か否かを返す
 * @global wpdb $wpdb
 * @param int $user_id
 * @return boolean 
 */
function is_editor($user_id){
	global $wpdb;
	$user_level = (int)$wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = '{$wpdb->prefix}user_level'", $user_id));
	return ($user_level == 5);
}

/**
 * ペンディング中のユーザーか否か
 * @return boolean 
 */
function is_pending_user(){
	$user_id = get_current_user_id();
	if($user_id){
		return (boolean)(false !== array_search('pending', get_userdata($user_id)->roles));
	}else{
		return false;
	}
}

/**
 * 管理者か否かを返す
 *
 * @param int $user_id
 * @return boolean 
 */
function is_administrator($user_id){
	return user_can($user_id, 'administrator');
}

/**
 * 最近追加されたユーザーを返す
 * @global wpdb $wpdb
 * @param int $num
 * @return array
 */
function get_recent_authors($num = 5){
	global $wpdb;
	$sql = <<<EOS
		SELECT u.*, um1.meta_value AS ruby, posts.post_title, posts.ID AS post_id, um2.meta_value AS description
		FROM {$wpdb->users} AS u
		LEFT JOIN {$wpdb->usermeta} AS um1
		ON u.ID = um1.user_id AND um1.meta_key = 'last_name'
		LEFT JOIN {$wpdb->usermeta} AS um2
		ON u.ID = um2.user_id AND um2.meta_key = 'description'
		LEFT JOIN (
			SELECT p.post_title, p.ID, p.post_author FROM {$wpdb->posts} AS p
			WHERE p.post_status = 'publish' AND p.post_type = 'post'
			GROUP BY p.post_author
			ORDER BY p.post_date DESC
		) AS posts
		ON u.ID = posts.post_author
		WHERE posts.post_title IS NOT NULL
		ORDER BY u.user_registered DESC
		LIMIT %d
EOS;
	return $wpdb->get_results($wpdb->prepare($sql, $num));
}


/**
 * 投稿数の多い同人を返す
 *
 * @param int $period 遡る日数。0にするとすべての期間
 * @param int $num
 * @return array 
 */
function get_vigorous_author($period = 0, $num = 5){
    /** @var wpdb $wpdb */
	global $wpdb;
	$subquery = $period > 0 ? 'AND TO_DAYS(NOW()) - TO_DAYS(p.post_date) <= 30' : '';
	$sql = <<<SQL
		SELECT DISTINCT u.*, COUNT(p.ID) AS count, SUM(CHAR_LENGTH(p.post_content)) AS length
		FROM {$wpdb->users} AS u
		LEFT JOIN {$wpdb->posts} AS p
		ON u.ID = p.post_author
		WHERE p.post_type = 'post'
		  AND p.post_status = 'publish'
		  {$subquery}
		GROUP BY u.ID
		ORDER BY length DESC
		LIMIT 0, {$num}
SQL;
	return $wpdb->get_results($sql);
}


/**
 * ユーザーのプロフィール情報の埋まり具合を%で返す
 *
 * @param int $user_id
 * @param boolean $doujin
 * @return int
 */
function get_user_status_sufficient($user_id, $doujin = true){
	global $wpdb, $gianism;
	if( $doujin ){
		$total = 1;
		$filled = 1;
		//メタキーを数える
		$meta_keys = array('last_name', 'description');
		$placeholders = array();
		foreach ($meta_keys as $key){
			$placeholders[] = '%s';
		}
		$args = array(
			"SELECT COUNT(umeta_id) FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key IN (".implode(', ', $placeholders).") AND meta_value != ''",
			$user_id
		);
		$meta_key_found = $wpdb->get_var(call_user_func_array(array($wpdb, 'prepare'), array_merge($args, $meta_keys)));
		//プロフィール写真
		$total++;
		if( has_original_picture($user_id) || has_gravatar($user_id) ){
			$filled++;
		}
		//パーセントを計算
		$total += count($meta_keys);
		$filled += $meta_key_found;
		return min(100, round($filled / $total * 100));
	}else{
		return 100;
	}
}
