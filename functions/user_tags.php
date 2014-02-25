<?php
/**
 * ユーザーが特定の投稿にタグをつける機能
 * @package hametuha
 */
class Hametuha_User_Table_Manager{
	
	/**
	 * @var string 
	 */
	private $version = '1.0';
	
	/**
	 * @var string
	 */
	public $table = 'term_user_relationships';
	
	/**
	 * @var string
	 */
	public $review = 'review';
	
	/**
	 * @var string
	 */
	public $feedback_pagename = 'feedback';
	
	/**
	 * フィードバック用のタグ名
	 * @var array
	 */
	public $feedback_tags = array(
		'intelligence' => array('知的', 'バカ'),
		'completeness' => array('よくできてる', '破滅してる'),
		'readability' => array('わかりやすい', '前衛的'),
		'emotion' => array('泣ける', '笑える'),
		'mood' => array('生きたくなる', '死にたくなる'),
		'to_author' => array('作者を褒めたい', '作者を殴りたい')
	);
	
	/**
	 * コンストラクタ
	 * @global wpdb $wpdb
	 */
	public function __construct(){
		global $wpdb;
		$this->table = $wpdb->prefix.$this->table;
		//新しいタグを作る
		add_action('init', array($this, 'create_taxonomy'));
		//タグ検索時のフィルター
		add_action('pre_get_posts', array($this, 'pre_get_posts'));
		//テーブル作成
		add_action('admin_init', array($this, 'create_table'));
		//スクリプト読み込み
		add_action('wp_enqueue_scripts', array($this, 'load_script'));
		//コンテンツにレビュー用フォームを表示
		add_filter('the_content', array($this, 'review_form'));
		//レビュー用フォームを押されたときの処理
		add_action('template_redirect', array($this, 'review_add'));
		//Ajaxアクション
		add_action('wp_ajax_hametuha_add_tag', array($this, 'add_tag_by_ajax'));
		add_action('wp_ajax_hametuha_delete_tag', array($this, 'delete_tag_by_ajax'));
	}
	
	/**
	 * get_posts関数が実行される直前
	 * @param WP_Query $wp_query 
	 */
	public function pre_get_posts(&$wp_query){
		//JOIN
		add_filter('posts_join', array($this, 'join'));
		add_filter('posts_where', array($this, 'where'));
		add_filter('posts_orderby', array($this, 'order_by'));
	}
	
	/**
	 * JOIN節を書き換える
	 * @global wpdb $wpdb
	 * @param string $join
	 * @return string
	 */
	public function join($join){
		global $wpdb;
		$wpdb->show_errors();
		if(false !== strpos($join, "INNER JOIN {$wpdb->term_relationships}")){
			$sql = <<<EOS
				INNER JOIN (
					(SELECT term_taxonomy_id, object_id FROM {$wpdb->term_relationships})
					UNION ALL 
					(SELECT term_taxonomy_id, object_id FROM {$this->table})
				) AS {$wpdb->prefix}user_term_union
				ON ({$wpdb->posts}.ID = {$wpdb->prefix}user_term_union.object_id)
EOS;
			$join = str_replace("INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)", $sql, $join);
		}
		remove_filter('posts_join', array($this, 'join'));
		return $join;
	}
	
	/**
	 * WHERE節をカスタマイズ
	 * @param string $where 
	 * @return string
	 */
	public function where($where){
		global $wpdb;
		if(false !== strpos($where, "{$wpdb->term_relationships}.term_taxonomy_id")){
			$where = str_replace("{$wpdb->term_relationships}.term_taxonomy_id", "{$wpdb->prefix}user_term_union.term_taxonomy_id", $where);
		}
		remove_filter('posts_where', array($this, 'where'));
		return $where;
	}
	
	/**
	 * ORDER BY 節をカスタマイズ
	 * @param string $order_by
	 * @return string
	 */
	public function order_by($order_by){
		remove_filter('posts_orderby', array($this, 'order_by'));
		return $order_by;
	}
	
	/**
	 * 必要なスクリプトを読み込む
	 * @param boolean $in_footer 
	 */
	public function load_script($in_footer = true){
		if(is_singular('post') && is_user_logged_in() ){
			wp_enqueue_script('hametuha-user-tag-edit', get_bloginfo('template_directory').'/js/user-tags.js', array('jquery'), $this->version, true);
			wp_localize_script('hametuha-user-tag-edit', 'HametuhaUserTags', array(
				'endpoint' => admin_url('admin-ajax.php'),
				'addTag' => 'hametuha_add_tag',
				'deleteTag' => 'hametuha_delete_tag',
				'searchTag' => 'hametuha_incsearch_tag',
				'nonce' => wp_create_nonce('hametuha_user_tag'),
				'postID' => get_the_ID()
			));
		}
	}

	/**
	 * リレーション用テーブルを作成する
	 * @global wpdb $wpdb 
	 * @global int $user_ID
	 */
	function create_table(){
		if(current_user_can('manage_options')){
			global $wpdb;
			//バージョンが低ければテーブルを作成し、バージョンを保存
			if(version_compare($this->version, get_option('_hametuha_user_tag_version')) > 0){
				$wpdb->show_errors();
				$char = defined('DB_CHARSET') ? DB_CHARSET : 'utf8';
				$sql = <<<EOS
					CREATE TABLE {$this->table} (
						user_id BIGINT UNSIGNED NOT NULL,
						object_id BIGINT UNSIGNED NOT NULL,
						term_taxonomy_id BIGINT UNSIGNED NOT NULL,
						updated DATETIME NOT NULL,
						KEY user (user_id,object_id),
						KEY tagged_date (updated,object_id,user_id)
					) ENGINE = MYISAM DEFAULT CHARSET = {$char};
EOS;
				//テーブルの作成
				require_once ABSPATH."wp-admin/includes/upgrade.php";
				dbDelta($sql);
				//バージョンを保存
				if(!empty($wpdb->last_error)){
					wp_die('データベースの'.$this->table.'テーブルを構築中にエラーが発生しました。');
				}
				update_option('_hametuha_user_tag_version', $this->version);
			}
			//レビューページがなければ作る
			$page = get_page_by_path($this->feedback_pagename);
			if(!$page){
				global $user_ID;
				wp_insert_post(array(
					'post_title' => 'フィードバック',
					'post_name' => $this->feedback_pagename,
					'post_type' => 'page',
					'post_author' => $user_ID
				));
			}
			foreach($this->feedback_tags as $reviews){
				foreach($reviews as $review){
					$this->create_term_if_not_exists($review, $this->review);
				}
			}
		}
	}
	
	/**
	 * 感想用タクソノミーを作成する 
	 */
	public function create_taxonomy(){
		register_taxonomy($this->review, 'post', array(
			'label' => 'レビューポイント',
			'hierarchical' => false,
			'show_ui' => false,
			'query_var' => true,
			'capabilities' => array(
				'manage_terms' => 'manage_options',
				'edit_terms' => 'manage_options',
				'delete_terms' => 'manage_options',
				'assign_terms' => 'manage_options'
			),
			'rewrite' => array('slug' => 'review')
		));
	}
	
	/**
	 * タグを追加する
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param int $post_id
	 * @param int $term_id
	 * @return int 
	 */
	function add_tag_by($user_id, $post_id, $term_id){
		global $wpdb;
		//タグを取得
		$term_taxonomy_id = (int)$wpdb->get_var($wpdb->prepare("SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id = %d", $term_id));
		if($term_taxonomy_id && (intval($user_id) > 0) && (intval($post_id) > 0) ){
			//同一のレコードが存在する場合はアップデート、なければ追加
			$existance = $wpdb->get_var($wpdb->prepare("SELECT term_taxonomy_id FROM {$this->table} WHERE user_id = %d AND object_id = %d AND term_taxonomy_id = %d", $user_id, $post_id, $term_taxonomy_id));
			if($existance){
				return -1;
			}else{
				$wpdb->show_errors();
				return (int)$wpdb->insert(
					$this->table,
					array(
						'user_id' => $user_id,
						'object_id' => $post_id,
						'term_taxonomy_id' => $term_taxonomy_id,
						'updated' => gmdate('Y-m-d H:i:s')
					),
					array('%d', '%d', '%d', '%s')
				);
			}
		}else{
			return 0;
		}
	}
	
	/**
	 * Ajaxでタグの追加を処理する
	 * @global int $user_ID 
	 */
	public function add_tag_by_ajax(){
		if(is_user_logged_in() && isset($_POST['term'], $_POST['post_id'], $_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'hametuha_user_tag')){
			global $user_ID;
			$term_id = $this->create_term_if_not_exists($_POST['term']);
			if($term_id){
				$code = $this->add_tag_by($user_ID, $_POST['post_id'], $term_id);
			}else{
				$code = 0;
			}
			$html = '';
			switch($code){
				case 0:
					$message = 'タグを追加できませんでした';
					break;
				case -1:
					$message = 'このタグはすでに登録済みです';
					break;
				default:
					$message = 'タグを追加しました';
					$html = $this->get_user_term_link(get_tag($term_id), 'delete');
					break;
			}
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array(
				'code' => $code,
				'message' => $message,
				'termId' => (int)$term_id,
				'html' => $html
			));
			die();
		}
	}
	
	/**
	 * 指定した名称のタグのIDを返す。なければ作る
	 * @global wpdb $wpdb
	 * @param string $term
	 * @param string $taxonomy
	 * @return int
	 */
	private function create_term_if_not_exists($term, $taxonomy = 'post_tag'){
		global $wpdb;
		$sql = <<<EOS
			SELECT t.term_id FROM {$wpdb->terms} AS t
			INNER JOIN {$wpdb->term_taxonomy} AS tt
			ON t.term_id = tt.term_id
			WHERE t.name = %s AND tt.taxonomy = %s
EOS;
		$term_id = $wpdb->get_var($wpdb->prepare($sql, $term, $taxonomy));
		if($term_id){
			return (int)$term_id;
		}else{
			$result = wp_insert_term($term, $taxonomy);
			if(is_wp_error($result)){
				return 0;
			}else{
				return (int)$result['term_id'];
			}
		}
	}
	
	/**
	 * タグをAjaxで削除する
	 * @global int $user_ID
	 * @global wpdb $wpdb 
	 */
	public function delete_tag_by_ajax(){
		if(is_user_logged_in() && isset($_POST['term_id'], $_POST['post_id'], $_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'hametuha_user_tag')){
			global $user_ID, $wpdb;
			$term_taxonomy_id = $wpdb->get_var($wpdb->prepare("SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id = %d", $_POST['term_id']));
			$status = false;
			$message = 'このタグはつけていません';
			if($term_taxonomy_id){
				$sql = "DELETE FROM {$this->table} WHERE user_id = %d AND object_id = %d AND term_taxonomy_id = %d";
				if($wpdb->query($wpdb->prepare($sql, $user_ID, $_POST['post_id'], $term_taxonomy_id))){
					$status = true;
					$message = 'タグを削除しました';
				}else{
					$message = 'タグを削除できませんでした';
				}
			}
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array(
				'status' => $status,
				'message' => $message
			));
			die();
		}
	}
	
	/**
	 * ユーザータグ一覧
	 * @param object $term
	 * @param string $icon
	 * @param string $style
	 * @return string
	 */
	public function get_user_term_link($term, $icon = '', $style = ''){
		switch($icon){
			case 'delete':
				$icon = '<small>×</small>';
				break;
			case 'add':
				$icon = '<small>＋</small>';
				break;
			default:
				$icon = '';
				break;
		}
		if(!empty($style)){
			$style = ' style="'.$style.'"';
		}
		$url = get_term_link($term->name, 'post_tag');
		if(!is_wp_error($url)){
			$tag = '<a href="'.$url.'" rel="keyword" class="user_tag" id="user_tag'.$term->term_id.'"'.$style.'>';
			$tag .= $term->name.$icon;
			$tag .= '</a>';
		}else{
			$tag = '';
		}
		return $tag;
	}
	
	/**
	 * レビュー用タグのキー名からラベルを返す
	 * @param string $key
	 * @return string 
	 */
	public function review_tag_label($key){
		switch ($key) {
			case 'intelligence':
				$label = '作品の知性';
				break;
			case 'completeness':
				$label = '作品の完成度';
				break;
			case 'readability':
				$label = '作品の構成';
				break;
			case 'emotion':
				$label = '作品から得た感情';
				break;
			case 'mood':
				$label = '作品を読んで';
				break;
			case 'to_author':
				$label = '作者の印象';
				break;
		}
		return $label;
	}
	
	
	/**
	 * ユーザーが指定したレビュータグを指定した投稿につけているか
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param int $post_id
	 * @param string $tag_name
	 * @return boolean 
	 */
	public function is_user_vote_for($user_id, $post_id, $tag_name){
		global $wpdb;
		$sql = <<<EOS
		SELECT r.object_id FROM {$this->table} AS r
		INNER JOIN {$wpdb->term_taxonomy} AS tt
		ON tt.term_taxonomy_id = r.term_taxonomy_id
		INNER JOIN {$wpdb->terms} AS t
		ON tt.term_id = t.term_id
		WHERE r.user_id = %d AND r.object_id = %d AND t.name = %s AND tt.taxonomy = '{$this->review}'
EOS;
		return (boolean)$wpdb->get_var($wpdb->prepare($sql, $user_id, $post_id, $tag_name));
	}
	
	/**
	 * レビュー用のフォームを表示する
	 * @global int $user_ID
	 * @param string $content
	 * @return string
	 */
	public function review_form($content){
		if(is_page($this->feedback_pagename)){
			global $user_ID;
			$user_id = is_user_logged_in() ? $user_ID : 0;
			$post_id = isset($_REQUEST['post_id']) ? (int)$_REQUEST['post_id'] : 0;
			if(!$post_id || !($post = wp_get_single_post($post_id)) || $post->post_status != 'publish'){
				//レビューする投稿が指定されていない
				$content = '<p class="message error">レビューをするための投稿が指定されていません</p>'.$content;
			}elseif(!is_wp_error($this->is_valid_review_action())){
				//nonceが正常（フォームが正しく送信されている）
				if($this->is_valid_review_action()){
					//フォームの値が正常
					$content = '<p class="message success">作品に対する評価を保存しました。ご協力ありがとうございます。</p>';
				}else{
					//なんらかのエラー
					$content = '<p class="message error">エラーが発生したため、作品に対する評価を保存できませんでした。大変申し訳ございません。</p>';
				}
			}else{
				//フォームを表示
				$nonce = wp_create_nonce('user_review_'.$user_id);
				$content .= <<<EOS
<form method="post" action="/{$this->feedback_pagename}/?iframe=true">
	<input type="hidden" name="_wpnonce" value="{$nonce}" />
	<input type="hidden" name="post_id" value="{$post_id}" />
	<table class="form-table">
		<tbody>
EOS;
				foreach($this->feedback_tags as $key => $tags){
					$label = $this->review_tag_label($key);
					$args = array(
						array('value' => 1, 'name' => $tags[0], 'checked' => false),
						array('value' => 0, 'name' => 'どちらでもない', 'checked' => false),
						array('value' => 2, 'name' => $tags[1], 'checked' => false),
					);
					if(is_user_logged_in()){
						if($this->is_user_vote_for($user_id, $post_id, $tags[0])){
							$args[0]['checked'] = true;
						}elseif($this->is_user_vote_for($user_id, $post_id, $tags[1])){
							$args[2]['checked'] = true;
						}else{
							$args[1]['checked'] = true;
						}
					}else{
						$args[1]['checked'] = true;
					}
					$content .= "<tr><th><label>{$label}</label></th>";
					foreach($args as $arg){
						$checked = $arg['checked'] ? ' checked="checked"' : '';
						$content .= <<<EOS
			<td>
				<label><input type="radio" name="{$key}" value="{$arg['value']}"{$checked} /> {$arg['name']}</label>
			</td>
EOS;
						
					}
					$content .= '</tr>';
				}
				$content .= <<<EOS
		<tr>
			<th><label>☆で評価</label></th>
			<td colspan="3">
EOS;
				if(is_user_logged_in()){
					$review = get_current_user_reviews($post_id);
					$rank = empty($review) ? 0 : current($review)->rank;
					$content .= <<<EOS
				<div class="ranker">
					<input type="hidden" name="rank" value="0" />
EOS;
					for($i = 1; $i <= 5; $i++){
						$class = ($rank >= $i) ? ' active' : '';
						$content .= <<<EOS
					<a class="qtip{$class}" href="#" title="星{$i}つ">{$i}</a>
EOS;
					}
					$content .= <<<EOS
				</div>
EOS;
					if(!empty($review)){
						$content .= '<p class="right"><small class="old">'.  mysql2date('Y-m-d', current($review)->updated).'</small></p>';
					}
				}else{
					$url = wp_login_url(get_permalink($post_id));
					$content .= <<<EOS
				<p class="message notice">星によるレーティングは<a href="{$url}" target="_blank">ログイン</a>したユーザー専用です。</p>
EOS;
				}
					$content .= <<<EOS
			</td>
		</tr>
		</tbody>
	</table>
EOS;
				
				$content .= <<<EOS
	<p class="center submit">
		<input type="submit" class="button-primary" value="レビューを送信" />
	</p>
</form>
EOS;
			}	
		}
		return $content;
	}
	
	
	/**
	 * レビューを追加する 
	 */
	public function review_add(){
		if(is_page($this->feedback_pagename) && isset($_REQUEST['_wpnonce'], $_GET['iframe'])){
			$test = $this->is_valid_review_action(true);
			if(is_wp_error($test)){
				wp_die($test->get_error_message());
			}
		}
	}
	
	/**
	 * レビュー送信アクションが有効なものか否か
	 * @global int $user_ID
	 * @global wpdb $wpdb
	 * @param boolean $save
	 * @return boolean|\WP_Error 
	 */
	private function is_valid_review_action($save = false){
		global $user_ID, $wpdb;
		$user_id = is_user_logged_in() ? $user_ID : 0;
		if(
			isset(
				$_REQUEST['_wpnonce'], $_REQUEST['post_id'],
				$_REQUEST['intelligence'], $_REQUEST['completeness'],
				$_REQUEST['readability'], $_REQUEST['emotion'],
				$_REQUEST['mood'], $_REQUEST['to_author']
			)
			&& wp_verify_nonce ($_REQUEST['_wpnonce'], 'user_review_'.$user_id)
			&& wp_get_single_post($_REQUEST['post_id'])
		){
			$post_id = (int)$_REQUEST['post_id'];
			if($save){
				$sql = <<<EOS
					SELECT tt.term_taxonomy_id FROM {$wpdb->term_taxonomy} AS tt
					INNER JOIN {$wpdb->terms} AS t
					ON tt.term_id = t.term_id
					WHERE tt.taxonomy = '{$this->review}' AND t.name = %s
EOS;
				foreach($this->feedback_tags as $key => $pear){
					$active_id = $wpdb->get_var($wpdb->prepare($sql, $pear[0]));
					$antonym_id = $wpdb->get_var($wpdb->prepare($sql, $pear[1]));
					if($user_id){
						//すでに存在する場合はいったん削除
						$wpdb->query($wpdb->prepare(
							"DELETE FROM {$this->table} WHERE user_id = %d AND object_id = %d AND term_taxonomy_id IN (%d, %d)",
							$user_id, $post_id, $active_id, $antonym_id
						));
					}
					if($_REQUEST[$key] === '1' || $_REQUEST[$key] === '2'){
						//新しいデータなので、挿入
						$term_id = $_REQUEST[$key] === '1' ? $active_id : $antonym_id;
						$wpdb->insert(
							$this->table,
							array(
								'user_id' => $user_id,
								'object_id' => $post_id,
								'term_taxonomy_id' => $term_id,
								'updated' => gmdate('Y-m-d H:i:s')
							),
							array('%d', '%d', '%d', '%s')
						);
					}
				}
				//ユーザーがログインしていた場合はランクも保存
				if($user_id && isset($_REQUEST['rank']) && $_REQUEST['rank'] < 6 && $_REQUEST['rank'] > 0){
					update_current_user_rank($post_id, $_REQUEST['rank']);
				}
				return true;
			}else{
				return true;
			}
		}else{
			return new WP_Error(500, '正しくない遷移が行われました');
		}
	}
}
global $hametuha_user_table_manager;
$hametuha_user_table_manager= new Hametuha_User_Table_Manager();

/**
 * ユーザーが投稿に対して取得しているタグを配列で返す
 * @global wpdb $wpdb
 * @global Hametuha_User_Table_Manager $hametuha_user_table_manager
 * @param int $user_id
 * @param int $post_id
 * @return array
 */
function get_user_tag_for_post($user_id, $post_id){
	global $wpdb, $hametuha_user_table_manager;
	$sql = <<<EOS
		SELECT t.*,term.*,COUNT(r.user_id) AS count
		FROM {$hametuha_user_table_manager->table} AS r
		INNER JOIN {$wpdb->term_taxonomy} AS t
		ON r.term_taxonomy_id = t.term_taxonomy_id
		INNER JOIN {$wpdb->terms} AS term
		ON t.term_id = term.term_id
EOS;
	//Where section
	if($user_id){
		$sql .= " WHERE r.user_id = %d AND r.object_id = %d AND t.taxonomy = 'post_tag'";
		$sql = $wpdb->prepare($sql, $user_id, $post_id);
	}else{
		$sql .= " WHERE r.object_id = %d AND t.taxonomy = 'post_tag'";
		$sql = $wpdb->prepare($sql, $post_id);
	}
	//Group
	$sql .= " GROUP BY r.term_taxonomy_id";
	//order
	$sql .= " ORDER BY r.updated DESC";
	return $wpdb->get_results($sql);
}

/**
 * 現在ログインしているユーザーがつけたタグを返す
 * @global int $user_ID
 * @param object $post
 * @param string $sep
 * @return boolean
 */
function the_current_user_tags($icon = '', $post = null, $sep = ''){
	if(is_null($post)){
		$post_id = get_the_ID();
	}else{
		$post = get_post($post);
		$post_id = $post->ID;
	}
	if(is_user_logged_in()){
		global $user_ID, $hametuha_user_table_manager;
		$tags = get_user_tag_for_post($user_ID, $post_id);
		if(!empty($tags)){
			$html = array();
			foreach($tags as $tag){
				$html[] = $hametuha_user_table_manager->get_user_term_link($tag, $icon);
			}
			echo implode($sep, $html);
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}


/**
 * 投稿につけられたタグを出力する
 * @global Hametuha_User_Table_Manager $hametuha_user_table_manager
 * @param object $post
 * @param string $sep
 * @return boolean
 */
function the_user_tags($before = '<p>', $after = '</p>', $post = null, $sep = ''){
	if(is_null($post)){
		$post_id = get_the_ID();
		$max = get_user_tag_count_max(false, $post_id);
	}else{
		$post = get_post($post);
		$post_id = $post->ID;
		$max = get_user_tag_count_max(false, $post);
	}
	global $hametuha_user_table_manager;
	$tags = get_user_tag_for_post(0, $post_id);
	if(!empty($tags)){
		$html = array();
		$icon = is_user_logged_in() ? 'add' : '';
		foreach($tags as $tag){
			$style = get_user_tag_style($tag->count, $max);
			$html[] = $hametuha_user_table_manager->get_user_term_link($tag, $icon, $style);
		}
		echo $before.implode($sep, $html).$after;
		return true;
	}else{
		return false;
	}
}

/**
 * 投稿に付与されたタグの最大件数を返す
 * @global wpdb $wpdb
 * @global Hametuha_User_Table_Manager $hametuha_user_table_manager
 * @param boolean $consider_author_tag
 * @param object $post
 * @return int 
 */
function get_user_tag_count_max($consider_author_tag = false, $post = null){
	if(is_null($post)){
		$post_id = get_the_ID();
	}else{
		$post = get_post($post);
		$post_id = $post->ID;
	}
	global $wpdb, $hametuha_user_table_manager;
	if($consider_author_tag){
		$from = <<<EOS
			(
				(SELECT object_id, term_taxonomy_id, 0 AS user_id FROM {$wpdb->term_relationships})
				UNION ALL
				(SELECT object_id, term_taxonomy_id, user_id FROM {$hametuha_user_table_manager->table})
			)
EOS;
	}else{
		$from = $hametuha_user_table_manager->table;
	}
	$sql = <<<EOS
		SELECT count(r.term_taxonomy_id) AS tag_num
		FROM {$from} AS r
		INNER JOIN {$wpdb->term_taxonomy} AS t
		ON r.term_taxonomy_id = t.term_taxonomy_id
		WHERE r.object_id = %d AND t.taxonomy = 'post_tag'
		GROUP BY r.term_taxonomy_id
		ORDER BY tag_num DESC
		LIMIT 1
EOS;
	return (int) $wpdb->get_var($wpdb->prepare($sql, $post_id));
}


/**
 * ユーザータグ一覧ページならtrue
 * @return boolean
 */
function is_user_tag(){
	return is_tag() && isset($_GET['user_tag_rewrite']) && $_GET['user_tag_rewrite'];
}

/**
 * 投稿に属するタグを出力する
 * @global Hametuha_User_Table_Manager $hametuha_user_table_manager
 * @param string $sep
 * @param boolean $show_icon_possible
 * @param string $before
 * @param string $after
 * @param object $post
 * @return boolean 
 */
function the_all_tags($sep = '', $icon = '', $before = '<p>', $after = '</p>', $post = null){
	global $hametuha_user_table_manager;
	$terms = get_the_all_tags($post);
	if(!empty($terms)){
		$html = array();
		$max = get_user_tag_count_max(true, $post);
		foreach($terms as $term){
			$html[] = $hametuha_user_table_manager->get_user_term_link($term, $icon, get_user_tag_style($term->count, $max, 'color'));
		}
		echo $before.implode($sep, $html).$after;
		return true;
	}else{
		return false;
	}
}

/**
 * 投稿に属するタグをすべて返す
 * @global wpdb $wpdb
 * @global Hametuha_User_Table_Manager $hametuha_user_table_manager
 * @param object $post
 * @return array
 */
function get_the_all_tags($post = null){
	if(is_null($post)){
		$post_id = get_the_ID();
	}else{
		$post = get_post($post);
		$post_id = $post->ID;
	}
	global $wpdb, $hametuha_user_table_manager;
	$terms = get_object_term_cache($post_id, 'user_tag');
	// TODO: キャッシュを削除するタイミングを調べる
	if(false === $terms){
		$sql = <<<EOS
			SELECT
				t.term_id, t.name, t.slug,
				tt.parent, tt.description,
				count(r.user_id) AS count
			FROM (
				(SELECT object_id, term_taxonomy_id, 0 AS user_id FROM {$wpdb->term_relationships})
				UNION ALL
				(SELECT object_id, term_taxonomy_id, user_id FROM {$hametuha_user_table_manager->table})
			) AS r
			INNER JOIN {$wpdb->term_taxonomy} AS tt
			ON r.term_taxonomy_id = tt.term_taxonomy_id
			INNER JOIN {$wpdb->terms} AS t
			ON tt.term_id = t.term_id
			WHERE r.object_id = %d AND tt.taxonomy = 'post_tag'
			GROUP BY r.term_taxonomy_id
EOS;
		$terms = $wpdb->get_results($wpdb->prepare($sql, $post_id));
		wp_cache_add($post_id, $terms, 'user_tag_relationships');
	}
	return $terms;
}


/**
 * タグの最大数に応じたスタイル属性値を返す
 * @param int $current
 * @param int $max
 * @param スタイル $style color, sizeのいずれか
 * @return string
 */
function get_user_tag_style($current, $max, $style = 'color'){
	switch($style){
		case "color":
			$percent = (1 - $current / $max);
			$r = round(204 * $percent);
			$g = round(102 * $percent + 102);
			return "background-color:rgb({$r},{$g},204)";
			break;
		case "size":
			$font_size = round(26 * $current / $max) + 9;
			return 'font-size:'.$font_size.'px';
			break;
	}
}

/**
 * フィードバックページのURLを返す
 * @global Hametuha_User_Table_Manager $hametuha_user_table_manager 
 * @param boolean $iframe
 * @return string
 */
function get_feedback_page_url($iframe = true){
	global $hametuha_user_table_manager;
	$url = home_url('/'.$hametuha_user_table_manager->feedback_pagename.'/');
	if($iframe){
		$url .= '?iframe=true';
	}
	return $url;
}

/**
 * 特定のユーザーが投稿に対してつけたレビュータグを出力する
 * @global Hametuha_User_Table_Manager $hametuha_user_table_manager
 * @global wpdb $wpdb
 * @global object $post
 * @param int $user_id
 * @param int $post_id
 * @param string $tr
 * @return type 
 */
function the_review_table($user_id, $post_id = null){
	global $hametuha_user_table_manager, $wpdb;
	if(is_null($post_id)){
		global $post;
		$post_id = $post->ID;
	}
	$tr_head = array();
	$tr_index = array();
	$trs = array(array(), array(), array());
	foreach($hametuha_user_table_manager->feedback_tags as $genre => $tags){
		$tr_head[] = $hametuha_user_table_manager->review_tag_label($genre);
		$trs[0][] = $tags[0];
		$trs[1][] = 'どちらでもない';
		$trs[2][] = $tags[1];
		//ポジティブタグつけてたら
		if($hametuha_user_table_manager->is_user_vote_for($user_id, $post_id,$tags[0])){
			$tr_index[] = 0;
		}elseif($hametuha_user_table_manager->is_user_vote_for($user_id, $post_id, $tags[1])){
			$tr_index[] = 2;
		}else{
			$tr_index[] = 1;
		}
	}
	echo '<table class="user-review-table"><thead><tr>';
	foreach($tr_head as $index => $cell){
		echo ($index % 2 == 0) ? '<th class="even">' : '<th class="odd">';
		echo $cell.'</th>';
	}
	echo '</tr></thead><tbody>';
	foreach($trs as $index => $tr){
		echo '<tr class="row-'.$index.'">';
		foreach($tr as $i => $cell){
			echo ($i % 2 == 0) ? '<td class="even">' : '<td class="odd">';
			if($tr_index[$i] == $index){
				echo '<strong>'.$cell.'</strong>';
			}else{
				echo '<span>'.$cell.'</span>';
			}
			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table>';
}

/**
 * 投稿につけられたレビューの件数を返す
 * @global wpdb $wpdb
 * @global Hametuha_User_Table_Manager $hametuha_user_table_manager
 * @param int $post_id
 * @param string $term
 * @param boolean $parent 初期値はfalse。trueにすると子投稿を集計
 * @return int 
 */
function get_post_chart_point($post_id, $term, $parent = false){
	global $wpdb, $hametuha_user_table_manager;
	$sql = <<<EOS
		SELECT COUNT(u.user_id) FROM {$hametuha_user_table_manager->table} AS u
		INNER JOIN {$wpdb->term_taxonomy} AS tt
		ON u.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = '{$hametuha_user_table_manager->review}'
		INNER JOIN {$wpdb->terms} AS t
		ON tt.term_id = t.term_id
		WHERE t.name = %s AND 
EOS;
	if($parent){
		$sql .= <<<EOS
			u.object_id IN (
				SELECT ID FROM {$wpdb->posts}
				WHERE post_type = 'post' AND post_status = 'publish' AND post_parent = %d
			)
EOS;
	}else{
		$sql .= 'u.object_id = %d';
	}
	return (int)$wpdb->get_var($wpdb->prepare($sql, $term, $post_id));
}

/**
 * 投稿につけられるレビューの平均的な数を返す
 * @global wpdb $wpdb
 * @global Hametuha_User_Table_Manager $hametuha_user_table_manager
 * @param string $term
 * @return int 
 */
function get_review_average($term){
	global $wpdb, $hametuha_user_table_manager;
	$sql = <<<EOS
		SELECT AVG(IFNULL(ct.c, 0)) FROM {$wpdb->posts} AS p
		LEFT JOIN (
			SELECT u.object_id, COUNT(u.user_id) AS c
			FROM {$hametuha_user_table_manager->table} AS u
			INNER JOIN {$wpdb->term_taxonomy} AS tt
			ON u.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = '{$hametuha_user_table_manager->review}'
			INNER JOIN {$wpdb->terms} AS t
			ON tt.term_id = t.term_id
			WHERE t.name = %s
			GROUP BY u.object_id
		) AS ct
		ON ct.object_id = p.ID
		WHERE p.post_status = 'publish' AND p.post_type = 'post'
EOS;
	return (float)$wpdb->get_var($wpdb->prepare($sql, $term));
}

/**
 * 投稿のチャートを出力する
 * @global object $post
 * @param mixed $post 
 */
function the_post_chart($post = null){
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	global $hametuha_user_table_manager;
	$points = array(0 => array(), 1 => array());
	foreach($hametuha_user_table_manager->feedback_tags as $key => $val){
		for($i = 0, $l = count($val); $i < $l; $i++){
			$point = get_post_chart_point($post->ID, $val[$i], ($post->post_type == 'series'));
			$points[$i][] = min($point * 20, 100);
			/* TODO: 投稿数が少な過ぎてたぶん意味ないので、平均は取らない
			$avg = get_review_average($val[$i]);
			$points[$i][] = ($point > $avg * 2) ? 100 : round($point / $avg * 50) ;
			 */
		}
	}
	$points = implode('|', array_map(function($arr){
		return implode(',', $arr);
	}, array_map(function($arr){
		$arr[] = $arr[0];
		return $arr;
	}, $points)));
	$src = 'http://chart.googleapis.com/chart?';
	$params = array('cht' => 'r');
	$params['chs'] = "200x200";
	$params['chd'] = 't:'.$points;
	$params['chco'] = '0000FF,FF0000';
	$params['chm'] = 'B,0000FF50,0,5,0|B,FF000050,1,5,0';
	$params['chxt'] = 'x';
	$params['chxl'] = '0:|知性|完成度|作品構成|読後感|作品の印象|作者の印象';
	$params['chdl'] = '健全指数|破滅指数';
	$params['chdlp'] = 't';
	$params['chtt'] = '破滅チャート';
	$query = array();
	foreach($params as $key => $val){
		$query[] = $key.'='.rawurlencode($val);
	}
	$src .= implode('&amp;', $query);
	echo '<img src="'.$src.'" id="hametu-chart" alt="破滅チャート" />';
}
