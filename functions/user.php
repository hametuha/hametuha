<?php

/**
 * ユーザーページスラッグ 
 * @var array
 */
global $hametuha_userpage_slug;
$hametuha_userpage_slug = array(
	'your-favorites' => '保存したフレーズ',
	'your-comments' => 'あなたのコメント',
	'your-reviews' => 'レビューした作品'
);

/**
 * ユーザー用ページがなかったら作る 
 */
function _hametuha_create_user_page(){
	if(current_user_can('manage_options') && basename($_SERVER['SCRIPT_FILENAME']) == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'page'){
		global $hametuha_userpage_slug;
		$login = get_page_by_path('login');
		foreach($hametuha_userpage_slug as $slug => $name){
			$page = get_page_by_path("login/{$slug}");
			if(!$page){
				wp_insert_post(array(
					'post_author' => get_current_user_id(),
					'post_title' => $name,
					'post_name' => $slug,
					'post_type' => 'page',
					'post_status' => 'publish',
					'post_parent' => $login->ID
				));
			}
		}
	}
}
add_action('admin_init', '_hametuha_create_user_page');



/**
 * 会員専用ページか否か
 * @global object $post
 * @global wpdb $wpdb
 * @param boolean $include_login
 * @return boolean 
 */
function is_member_page($include_login = true){
	if(!is_page()){
		return false;
	}elseif(is_page('login')){
		return (true && $include_login);
	}else{
		global $post, $wpdb;
		return ($post->post_parent > 0 && $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_name = 'login' AND post_type = 'page' AND post_status = 'publish'", $post->post_parent)));
	}
}

/**
 * 会員用ページだったらリダイレクト 
 */
function _hametuha_auth_redirect(){
	
	if(!is_user_logged_in() && is_member_page(false)){
		auth_redirect();
	}
}
add_action('template_redirect', '_hametuha_auth_redirect', 1000);


/**
 * 投稿作者の役割を返す
 * @global WP_Roles $wp_roles
 * @param int $user_id
 * @return array 
 */
function get_the_author_roles($user_id = null){
	global $wp_roles;
	if(is_null($user_id)){
		$user_id = get_the_author_ID();
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
 * @global wpdb $wpdb
 * @param int $author_id
 * @return int
 */
function get_author_work_count($author_id){
	global $wpdb;
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
	$sql = <<<EOS
		SELECT post_date FROM {$wpdb->posts}
		WHERE post_status = 'publish' AND post_type = 'post'
		 AND  post_author = %d
		ORDER BY post_date DESC
		LIMIT 1
EOS;
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
 * @global wpdb $wpdb
 * @param int $doujin falseにすると投稿者以外のすべてのユーザー
 * @return int
 */
function get_author_count($doujin = true){
	global $wpdb;
	$sql = <<<EOS
		SELECT COUNT(ID) FROM {$wpdb->users} AS u
		LEFT JOIN {$wpdb->usermeta} AS um
		ON u.ID = um.user_id AND um.meta_key = '{$wpdb->prefix}user_level'
EOS;
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
 * @global wpdb $wpdb
 * @param int $user_id
 * @return boolean 
 */
function is_administrator($user_id){
	global $wpdb;
	$user_level = (int)$wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = '{$wpdb->prefix}user_level'", $user_id));
	return ($user_level == 10);
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
 * 投稿者のリストを返す
 * @global wpdb $wpdb
 * @global WP_Query $wp_query
 * @param boolean $doujin falseにすると投稿者以外のすべてのユーザー
 * @return array
 */
function get_author_list($doujin = true){
	global $wpdb, $wp_query;
	$per_page = get_option('posts_per_page');
	$paged = max(1, absint($wp_query->query_vars['paged']));
	$paged -= 1;
	$offset = $paged * $per_page;
	$sql = <<<EOS
		SELECT SQL_CALC_FOUND_ROWS u.*, um1.meta_value AS ruby, um3.meta_value AS description FROM {$wpdb->users} AS u
		LEFT JOIN {$wpdb->usermeta} AS um1
		ON u.ID = um1.user_id AND um1.meta_key = 'last_name'
		LEFT JOIN {$wpdb->usermeta} AS um3
		ON u.ID = um3.user_id AND um3.meta_key = 'description'
		LEFT JOIN (
			SELECT COUNT(ID) AS number, post_date, post_author FROM {$wpdb->posts} AS posts
			WHERE post_status = 'publish' AND post_type = 'post'
			GROUP BY post_author
			ORDER BY post_date DESC
		) AS p
		ON p.post_author = u.ID
EOS;
	$wheres = array();
	if($doujin){
		$sql .= <<<EOS
			LEFT JOIN {$wpdb->usermeta} AS um2
			ON u.ID = um2.user_id AND um2.meta_key = '{$wpdb->prefix}user_level'
EOS;
		$wheres[] = 'um2.meta_value > 0';
	}
	if(isset($_REQUEST['s']) && !empty($_REQUEST['s'])){
		$query = explode(' ', str_replace('　', ' ', (string)$_REQUEST['s']));
		foreach($query as $q){
			$wheres[] = $wpdb->prepare('( (u.display_name LIKE %s) OR (um1.meta_value LIKE %s) OR (um3.meta_value LIKE %s) )', "%{$q}%", "%{$q}%", "%{$q}%");
		}
	}
	if(!empty($wheres)){
		$wheres = ' WHERE '.implode(' AND ', $wheres);
	}else{
		$wheres = '';
	}
	//オーダー
	$order_by = (isset($_REQUEST['orderby'])) ? (string)$_REQUEST['orderby'] : 'name' ;
	switch($order_by){
		case 'registered':
			$order_by = 'u.user_registered';
			break;
		case 'posted':
			$order_by = 'p.post_date';
			break;
		case 'count':
			$order_by = 'p.number';
			break;
		default:
			$order_by = 'um1.meta_value';
			break;
	}
	$order = (!isset($_REQUEST['order']) || $_REQUEST['order'] == 'asc') ? 'ASC' : 'DESC';
	$sql .= <<<EOS
		{$wheres}
		ORDER BY {$order_by} {$order}
		LIMIT {$offset}, {$per_page}
EOS;
	return $wpdb->get_results($sql);
}


/**
 * 投稿者リストページでページネーションを行う
 * @global $WP_Query $wp_query
 * @param int $total
 * @param int $per_page 指定しない場合は投稿と同じ
 * @return void
 */
function author_pagination($total, $per_page = 0){
	global $wp_query;
	$paged = max(1, absint($wp_query->query_vars['paged']));
	$per_page = $per_page ?: get_option('posts_per_page');
	$total_pages = ceil($total / $per_page);
	if(class_exists('PageNavi_Core')){
		$options = wp_parse_args(array(), PageNavi_Core::$options->get());
		if($total_pages == 1){
			return;
		}else{
			$instance = new PageNavi_Call(array());
			extract($options);
			$pages_to_show = absint( $options['num_pages'] );
			$larger_page_to_show = absint( $options['num_larger_page_numbers'] );
			$larger_page_multiple = absint( $options['larger_page_numbers_multiple'] );
			$pages_to_show_minus_1 = $pages_to_show - 1;
			$half_page_start = floor( $pages_to_show_minus_1/2 );
			$half_page_end = ceil( $pages_to_show_minus_1/2 );
			$start_page = $paged - $half_page_start;
			if ( $start_page <= 0 )
				$start_page = 1;
			$end_page = $paged + $half_page_end;

			if ( ( $end_page - $start_page ) != $pages_to_show_minus_1 )
				$end_page = $start_page + $pages_to_show_minus_1;

			if ( $end_page > $total_pages ) {
				$start_page = $total_pages - $pages_to_show_minus_1;
				$end_page = $total_pages;
			}

			if ( $start_page < 1 )
				$start_page = 1;

			$out = '';
			if(!empty($pages_text)){
				$pages_text = str_replace(
					array( "%CURRENT_PAGE%", "%TOTAL_PAGES%" ),
					array( number_format_i18n( $paged ), number_format_i18n( $total_pages )),
					$pages_text
				);
				$out .= "<span class='pages'>{$pages_text}</span>";
			}
			
			if ( $start_page >= 2 && $pages_to_show < $total_pages ) {
				// First
				$first_text = str_replace( '%TOTAL_PAGES%', number_format_i18n( $total_pages ), $first_text );
				$out .= $instance->get_single( 1, 'first', $first_text, '%TOTAL_PAGES%' );

				// Previous
				if ( $paged > 1 && !empty( $prev_text ) )
					$out .= $instance->get_single( $paged - 1, 'previouspostslink', $prev_text );

				if ( !empty( $options['dotleft_text'] ) )
					$out .= "<span class='extend'>{$dotleft_text}</span>";
			}
			
			// Smaller pages
			$larger_pages_array = array();
			if ( $larger_page_multiple )
				for ( $i = $larger_page_multiple; $i <= $total_pages; $i+= $larger_page_multiple )
					$larger_pages_array[] = $i;

			$larger_page_start = 0;
			foreach ( $larger_pages_array as $larger_page ) {
				if ( $larger_page < ($start_page - $half_page_start) && $larger_page_start < $larger_page_to_show ) {
					$out .= $instance->get_single( $larger_page, 'smaller page', $page_text );
					$larger_page_start++;
				}
			}

			if ( $larger_page_start )
				$out .= "<span class='extend'>{$dotleft_text}</span>";

			// Page numbers
			$timeline = 'smaller';
			foreach ( range( $start_page, $end_page ) as $i ) {
				if ( $i == $paged && !empty( $current_text ) ) {
					$current_page_text = str_replace( '%PAGE_NUMBER%', number_format_i18n( $i ), $current_text );
					$out .= "<span class='current'>$current_page_text</span>";
					$timeline = 'larger';
				} else {
					$out .= $instance->get_single( $i, "page $timeline", $page_text );
				}
			}

			// Large pages
			$larger_page_end = 0;
			$larger_page_out = '';
			foreach ( $larger_pages_array as $larger_page ) {
				if ( $larger_page > ($end_page + $half_page_end) && $larger_page_end < $larger_page_to_show ) {
					$larger_page_out .= $instance->get_single( $larger_page, 'larger page', $options['page_text'] );
					$larger_page_end++;
				}
			}

			if ( $larger_page_out ) {
				$out .= "<span class='extend'>{$options['dotright_text']}</span>";
			}
			$out .= $larger_page_out;

			if ( $end_page < $total_pages ) {
				if ( !empty( $options['dotright_text'] ) )
					$out .= "<span class='extend'>{$options['dotright_text']}</span>";

				// Next
				if ( $paged < $total_pages && !empty( $options['next_text'] ) )
					$out .= $instance->get_single( $paged + 1, 'nextpostslink', $options['next_text'] );

				// Last
				$out .= $instance->get_single( $total_pages, 'last', $options['last_text'], '%TOTAL_PAGES%' );
			}
			$out = $before . "<div class='wp-pagenavi'>\n$out\n</div>" . $after;

			echo apply_filters( 'wp_pagenavi', $out );
		}
	}
}


/**
 * プロフィール編集画面でスクリプトを読み込む
 */
function _hametuha_profile_page($force = false){
	if(is_member_page() || $force === true){
		wp_enqueue_script('hametuha-profile', get_bloginfo('template_directory').'/js/profile-helper.js', array('jquery', 'fancybox'), HAMETUHA_THEME_VERSION, $force !== true);
		wp_localize_script('hametuha-profile', 'HametuhaProfile', array(
			'endpoint' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('profile_helper_'.  get_current_user_id()),
			'usernameCheck' => 'username_check',
			'deleteFavorite' => 'delete_favorite'
		));
	}
}
add_action('wp_enqueue_scripts', '_hametuha_profile_page');

/**
 * ユーザーが投稿者になるページのヘッダーで実行
 * @global int $user_ID 
 */
function _hametuha_become_author(){
	if(is_page('become-author')){
		if(!is_user_logged_in()){
			auth_redirect();
		}
		if(current_user_can('edit_posts')){
			header('Location: '.admin_url('profile.php'));
			exit;
		}
		global $user_ID;
		//処理を行う
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'become_author_'.$user_ID)){
			wp_update_user(array(
				'ID' => $user_ID,
				'role' => 'author'
			));
			header('Location: '.admin_url('profile.php'));
			exit;
		}
		//ページを表示する
		add_filter('the_content', '_hametuha_become_author_form');
	}
}
add_action('template_redirect', '_hametuha_become_author');

/**
 * 投稿者になるためのフォームを出力
 * @global array $errors
 * @global int $user_ID
 * @param string $content
 * @return string
 */
function _hametuha_become_author_form($content){
	global $wp_errors, $user_ID;
	$nonce = wp_create_nonce('become_author_'.$user_ID);
	$form = <<<EOS
	<form method="post">
		<input type="hidden" name="_wpnonce" value="{$nonce}" />
		<p class="submit center">
			<input type="submit" class="button-primary" value="規約に同意して投稿者になる" onclick="if(!confirm('破滅派の投稿規約に同意しますか？')) return false;" />
		</p>
	</form>
EOS;
	return '<div class="post-content">'.$content.'</div>'.$form;
}

/**
 * 現在のユーザーのコメントを返す 
 * @global int $paged
 * @global wpdb $wpdb
 * @return array 
 */
function get_current_user_comments(){
	global $paged, $wpdb;
	//$wpdb->show_errors();
	$page = intval($paged) ?: 1;
	$orderby = isset($_REQUEST['orderby']) ? (string)$_REQUEST['orderby'] : 'comment_date';
	$order = (isset($_REQUEST['order']) && $_REQUEST['order'] == 'ASC') ? 'ASC' : 'DESC'; 
	$per_page = get_option('posts_per_page');
	$offset = $per_page * max(0, $page - 1);
	if(is_user_logged_in()){
		$sql = <<<EOS
			SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->comments}
			WHERE user_id = %d AND comment_type = ''
			ORDER BY {$orderby} {$order}
			LIMIT {$offset}, {$per_page}
EOS;
		return $wpdb->get_results($wpdb->prepare($sql, get_current_user_id()));
	}else{
		return array();
	}
}


/**
 * 投稿数の多い同人を返す
 * @global wpdb $wpdb
 * @param int $period 遡る日数。0にするとすべての期間
 * @param int $num
 * @return array 
 */
function get_vigorous_author($period = 0, $num = 5){
	global $wpdb;
	$subquery = $period > 0 ? 'AND TO_DAYS(NOW()) - TO_DAYS(post_date) <= 30' : '';
	$sql = <<<EOS
		SELECT u.*, um1.meta_value AS ruby, um2.meta_value AS description, p.post_num AS num
		FROM {$wpdb->users} AS u
		LEFT JOIN {$wpdb->usermeta} AS um1
		ON u.ID = um1.user_id AND um1.meta_key = 'last_name'
		LEFT JOIN {$wpdb->usermeta} AS um2
		ON u.ID = um2.user_id AND um2.meta_key = 'description'
		INNER JOIN (
			SELECT COUNT(ID) AS post_num,post_author FROM {$wpdb->posts}
			WHERE post_type = 'post' AND post_status = 'publish'
			{$subquery}
			GROUP BY post_author
		) AS p
		ON u.ID = p.post_author
		ORDER BY p.post_num DESC
		LIMIT {$num}
EOS;
	return $wpdb->get_results($sql);
}


/**
 * ユーザーのプロフィール情報の埋まり具合を%で返す
 * @global wpdb $wpdb
 * @global WP_Gianism $gianism
 * @param int $user_id
 * @param boolean $doujin 
 */
function get_user_status_sufficient($user_id, $doujin = true){
	global $wpdb, $gianism;
	if($doujin){
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
		if(has_original_picture($user_id) || has_gravatar($user_id)){
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