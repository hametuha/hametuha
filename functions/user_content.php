<?php
class Hametuha_User_Content{

	/**
	 * @var string
	 */
	private $version = '1.0';
	
	/**
	 * @var string
	 */
	public $table = 'user_content_relationships';
	
	/**
	 * @var string
	 */
	public $typeFavorite = 'favorite';
	
	/**
	 * @var string
	 */
	public $typeBookmark = 'bookmark';
	
	/**
	 * @var string
	 */
	public $typeRank = 'rank';
	
	/**
	 * @var string
	 */
	public $user_page_name = 'bookmarks';
	
	/**
	 * @global wpdb $wpdb 
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix.$this->table;
		//テーブルを作る
		add_action('admin_init', array($this, 'create_table'));
	}
	
	/**
	 * 管理者が管理画面に入ったときにテーブルを作成する
	 * @global wpdb $wpdb 
	 */
	public function create_table(){
		if(current_user_can('manage_options')){
			global $wpdb;
			//バージョンが低ければテーブルを作成し、バージョンを保存
			if(version_compare($this->version, get_option('_hametuha_user_content_relation_version')) > 0){
				$wpdb->show_errors();
				$char = defined('DB_CHARSET') ? DB_CHARSET : 'utf8';
				$sql = <<<EOS
					CREATE TABLE {$this->table} (
						ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						rel_type VARCHAR(10) NOT NULL DEFAULT '{$this->typeFavorite}',
						object_id BIGINT UNSIGNED NOT NULL,
						user_id BIGINT UNSIGNED NOT NULL,
						location DECIMAL(10,9) NOT NULL,
						content TEXT NOT NULL,
						updated DATETIME NOT NULL,
						PRIMARY KEY (ID),
						KEY user (rel_type,object_id,user_id),
						KEY favored_date (updated,object_id,user_id)
					) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
				//テーブルの作成
				require_once ABSPATH."wp-admin/includes/upgrade.php";
				dbDelta($sql);
				//バージョンを保存
				if(!empty($wpdb->last_error)){
					wp_die('データベースの'.$this->table.'テーブルを構築中にエラーが発生しました。');
				}
				update_option('_hametuha_user_content_relation_version', $this->version);
				//ページがなければ作る
				//ルートページ
				$user_root = get_page_by_path($this->user_page_name);
				if(!$user_root){
					global $user_ID;
					$user_root_id = wp_insert_post(array(
						'post_title' => 'ブックマーク',
						'post_name' => $this->user_page_name,
						'post_author' => $user_ID,
						'post_type' => 'page',
						'post_status' => 'publish'
					), true);
					if(is_wp_error($user_root_id)){
						wp_die('ユーザー用ブックマークページを作るのに失敗しました');
					}
				}
			}
		}
	}
}

global $hametuha_user_content_manager;
$hametuha_user_content_manager = new Hametuha_User_Content();

/**
 * 現在のユーザーのお気に入りフレーズを保存する
 * @global wpdb $wpdb
 * @global Hametuha_User_Content $hametuha_user_content_manager
 * @param int $post_id
 * @param string $frase
 * @param float $location
 * @return boolean 
 */
function add_current_user_favorite($post_id, $frase, $location){
	global $wpdb, $hametuha_user_content_manager;
	if(is_user_logged_in()){
		global $user_ID;
		$user_id = $user_ID;
	}else{
		$user_id = 0;
	}
	return $wpdb->insert(
		$hametuha_user_content_manager->table,
		array(
			'rel_type' => $hametuha_user_content_manager->typeFavorite,
			'object_id' => $post_id,
			'user_id' => $user_id,
			'content' => $frase,
			'location' => $location,
			'updated' => gmdate('Y-m-d H:i:s')
		), array(
			'%s', '%d', '%d', '%s', '%f', '%s'
		));
}

/**
 * 現在ログインしているユーザーの投稿に対する評価を保存する
 * @global int $user_ID
 * @global Hametuha_User_Content $hametuha_user_content_manager
 * @global wpdb $wpdb
 * @param int $post_id
 * @param int $rank 
 */
function update_current_user_rank($post_id, $rank){
	if(is_user_logged_in()){
		global $user_ID, $hametuha_user_content_manager, $wpdb;
		$sql = "SELECT ID FROM {$hametuha_user_content_manager->table} WHERE rel_type = %s AND user_id = %d AND object_id = %d";
		if($wpdb->get_var($wpdb->prepare($sql, $hametuha_user_content_manager->typeRank, $user_ID, $post_id))){
			$wpdb->update($hametuha_user_content_manager->table, array(
				'location' => $rank / 10
			), array(
				'rel_type' => $hametuha_user_content_manager->typeRank,
				'user_id' => $user_ID,
				'object_id' => $post_id
			), array('%f'), array('%s', '%d', '%d'));
		}else{
			$wpdb->insert($hametuha_user_content_manager->table, array(
				'rel_type' => $hametuha_user_content_manager->typeRank,
				'object_id' => $post_id,
				'user_id' => $user_ID,
				'location' => $rank / 10,
				'updated' => gmdate('Y-m-d H:i:s')
			), array('%s', '%d', '%d', '%f', '%s'));
		}
	}
}

/**
 * 現在ログインしているユーザーのレビューを取得する
 * @global Hametuha_User_Content $hametuha_user_content_manager
 * @global wpdb $wpdb
 * @param int $post_id
 * @return array 
 */
function get_current_user_reviews($post_id = 0){
	if(is_user_logged_in()){
		global $hametuha_user_content_manager, $wpdb;
		$sql = <<<EOS
			SELECT SQL_CALC_FOUND_ROWS
				r.ID, r.object_id AS post_id, CAST((r.location * 10) AS SIGNED) AS rank,
				r.updated, p.post_title
			FROM {$hametuha_user_content_manager->table} AS r
			INNER JOIN {$wpdb->posts} AS p
			ON r.object_id = p.ID
EOS;
		$wheres = array($wpdb->prepare("r.rel_type = %s", $hametuha_user_content_manager->typeRank));
		if($post_id > 0){
			$wheres[] = $wpdb->prepare("r.object_id = %d", $post_id);
		}
		$wheres[] = $wpdb->prepare("r.user_id = %d", get_current_user_id());
		$sql .= " WHERE ".implode(" AND ", $wheres);
		$page = intval($paged) ?: 1;
		$orderby = 'r.updated';
		if(isset($_REQUEST['orderby'])) switch($_REQUEST['orderby']){
			case "location":
			case "object_id":
				$orderby = "r.".$_REQUEST['orderby'];
				break;
		}
		$order = (isset($_REQUEST['order']) && $_REQUEST['order'] == 'ASC') ? 'ASC' : 'DESC'; 
		$per_page = get_option('posts_per_page');
		$offset = $per_page * max(0, $page - 1);
		$sql .= " ORDER BY {$orderby} {$order} LIMIT {$offset}, {$per_page}";
		return $wpdb->get_results($sql);
	}else{
		return array();
	}
}


/**
 * 現在のユーザーのお気に入りを取得する
 * @global wpdb $wpdb
 * @global Hametuha_User_Content $hametuha_user_content_manager
 * @global int $paged
 * @param int $post_id
 * @return array 
 */
function get_current_user_favorites($post_id = 0){
	if(!is_user_logged_in()){
		return array();
	}
	global $wpdb, $hametuha_user_content_manager, $paged;
	$wpdb->show_errors();
	$sql = <<<EOS
		SELECT SQL_CALC_FOUND_ROWS
			f.ID, f.object_id AS post_id, f.location, f.content, f.updated,
			p.post_title
		FROM {$hametuha_user_content_manager->table} AS f
		INNER JOIN {$wpdb->posts} AS p
		ON f.object_id = p.ID
EOS;
	$wheres = array($wpdb->prepare("f.rel_type = %s", $hametuha_user_content_manager->typeFavorite));
	if($post_id > 0){
		$wheres[] = $wpdb->prepare("f.object_id= %d", $post_id);
	}
	$wheres[] = $wpdb->prepare("f.user_id = %d", get_current_user_id());
	
	$sql .= " WHERE ".implode(" AND ", $wheres);
	$page = intval($paged) ?: 1;
	$orderby = 'f.updated';
	if(isset($_REQUEST['orderby'])) switch($_REQUEST['orderby']){
		case "location":
		case "object_id":
			$orderby = "f.".$_REQUEST['orderby'];
			break;
	}
	$order = (isset($_REQUEST['order']) && $_REQUEST['order'] == 'ASC') ? 'ASC' : 'DESC'; 
	$per_page = get_option('posts_per_page');
	$offset = $per_page * max(0, $page - 1);
	$sql .= " ORDER BY {$orderby} {$order} LIMIT {$offset}, {$per_page}";
	return $wpdb->get_results($sql);
}

/**
 * Ajaxで指定されたお気に入りを削除する
 * @global wpdb $wpdb
 * @global Hametuha_User_Content $hametuha_user_content_manager 
 */
function _hametuha_ajax_delete_fav(){
	if(is_user_logged_in() && isset($_REQUEST['_wpnonce'], $_REQUEST['fav_id']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'profile_helper_'.  get_current_user_id())){
		global $wpdb, $hametuha_user_content_manager;
		$fav = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$hametuha_user_content_manager->table} WHERE ID = %d", $_REQUEST['fav_id']));
		$valid = true;
		$message = '';
		if($fav){
			if($fav->user_id = get_current_user_id()){
				$wpdb->query($wpdb->prepare("DELETE FROM {$hametuha_user_content_manager->table} WHERE ID = %d", $_REQUEST['fav_id']));
				$message = 'お気に入りフレーズを削除しました';
			}else{
				$valid = false;
				$message = 'このお気に入りフレーズは削除できません。';
			}
		}else{
			$valid = false;
			$message = '指定されたお気に入りフレーズは存在しません';
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(array(
			'status' => $valid,
			'message' => $message
		));
		exit;
	}
}
add_action('wp_ajax_delete_favorite', '_hametuha_ajax_delete_fav');

/**
 * 投稿が取得した☆の平均を返す
 *
 * @global object $post
 * @global wpdb $wpdb
 * @global Hametuha_User_Content $hametuha_user_content_manager
 * @param mixed $post
 * @return int 
 */
function get_post_rank($post = null){
	$post = get_post($post);
	if($post){
		global $wpdb, $hametuha_user_content_manager;
		$sql = <<<EOS
			SELECT AVG(location) FROM {$hametuha_user_content_manager->table}
			WHERE rel_type = %s AND 
EOS;
		if($post->post_type == 'series'){
			$sql .= <<<EOS
				object_id IN (
					SELECT ID FROM {$wpdb->posts}
					WHERE post_type = 'post' AND post_status = 'publish' AND post_parent = %d
				)
EOS;
		}else{
			$sql .= 'object_id = %d';
		}
		$avg = (float)$wpdb->get_var($wpdb->prepare($sql, $hametuha_user_content_manager->typeRank, $post->ID));
		return round($avg * 10, 1);
	}else{
		return 0;
	}
}


/**
 * お気に入りの数を返す
 * @global object $post
 * @global wpdb $wpdb
 * @global Hametuha_User_Content $hametuha_user_content_manager
 * @param mixed $post
 * @return int 
 */
function get_user_favorite_count($post = null){
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	if($post){
		global $wpdb, $hametuha_user_content_manager;
		$sql = <<<EOS
			SELECT COUNT(user_id) FROM {$hametuha_user_content_manager->table}
			WHERE rel_type = %s AND object_id = %d
EOS;
		return (int)$wpdb->get_var($wpdb->prepare($sql, $hametuha_user_content_manager->typeFavorite, $post->ID));
	}else{
		return 0;
	}
}