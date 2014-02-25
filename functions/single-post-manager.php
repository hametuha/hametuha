<?php
/**
 * 投稿ページの出力を管理するクラス
 */
class Hametuha_Single_Post_Manager {

	/**
	 * @var string
	 */
	private $version = '1.11';
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		add_action('template_redirect', array($this, 'template_redirect'));
		add_action('wp_ajax_nopriv_hametuha_get_content', array($this, 'get_content'));
		add_action('wp_ajax_hametuha_get_content', array($this, 'get_content'));
		add_action('wp_ajax_nopriv_hametuha_add_favorite', array($this, 'add_favorite'));
		add_action('wp_ajax_hametuha_add_favorite', array($this, 'add_favorite'));
		add_action('wp_footer', array($this, 'preview'));
	}
	
	/**
	 * テンプレートの分岐
	 * @global Object $post
	 */
	public function template_redirect(){
		if(is_reading_page()){
			global $post;
			$author = get_userdata($post->post_author);
			$swf_pass = (isset($_GET['debug']) && $_GET['debug']) ? '/swf/debug/reader.swf?version='.$this->version.'&timestamp='.time() : '/swf/reader.swf?version='.$this->version;
			wp_enqueue_script('hametuha-reading', get_template_directory_uri().'/js/reading-manager.js', array('jquery', 'swfobject', 'fancybox'), $this->version, true);
			wp_localize_script('hametuha-reading', 'HametuhaVars', array(
				'postID' => get_the_ID(),
				'userID' => $this->get_current_user_id(),
				'swf' => get_template_directory_uri().$swf_pass,
				'expressInstall' => get_template_directory_uri()."/swf/expressInstall.swf",
				'fontSwf' => get_template_directory_uri().'/swf/KodsukaMincho.swf',
				'nonce' => wp_create_nonce('hametuha_reading_'.$this->get_current_user_id()),
				'endpoint' => admin_url('admin-ajax.php'),
				'feedbackURL' => get_feedback_page_url(),
				'getContent' => 'hametuha_get_content',
				'addFavorite' => 'hametuha_add_favorite',
				'postTitle' => get_the_title(),
				'postAuthor' => $author->display_name,
				'isPreview' => (int)($post->post_status == 'publish' && (isset($_GET['preview_id']) && $_GET['preview_id'] == $post->ID) )
			));
			add_action('wp_footer', 'the_reading_contents');
		}
	}
	
	/**
	 * コンテンツを返す
	 * @global wpdb $wpdb
	 */
	public function get_content(){
		if(isset($_REQUEST['post_id'])){
			$post_id = intval($_REQUEST['post_id']);
			if(isset($_REQUEST['is_preview']) && $_REQUEST['is_preview']){
				global $wpdb;
				$sql = "SELECT * FROM {$wpdb->posts} WHERE post_parent = %d AND post_type IN ('post', 'revision') AND post_name = %s ORDER BY post_date DESC";
				$post = $wpdb->get_row($wpdb->prepare($sql, $post_id, $post_id.'-autosave'));
				if(!current_user_can('edit_others_posts') && $post->post_author != get_current_user_id()){
					$post->post_content = '下書きを取得できませんでした';
				}
			}else{
				$post = wp_get_single_post($post_id);
			}
			if($post){
				$type = 'flash';
				switch($type){
					default:
						header('Content-Type: text/xml; charset=utf-8');
						add_filter('the_content', array($this, 'convert_to_xml'));
						//TODO: nextpageで改ページ要素を入れる
						echo apply_filters('the_content', $post->post_content);
						remove_filter('the_content', array($this, 'convert_to_xml'));
						exit;
						break;
				}
			}
		}
	}
	
	/**
	 * お気に入りを保存する
	 */
	public function add_favorite(){
		if(
			isset($_REQUEST['post_id'], $_REQUEST['_wpnonce'], $_REQUEST['location'], $_REQUEST['frase'])
			&& wp_verify_nonce($_REQUEST['_wpnonce'], 'hametuha_reading_'.$this->get_current_user_id())
			&& ($post = wp_get_single_post($_REQUEST['post_id']))
			&& is_numeric($_REQUEST['location']) && $_REQUEST['location'] <= 1
			&& 0 < mb_strlen($_REQUEST['frase'], 'utf-8')
		){
			$post_id = intval($_REQUEST['post_id']);
			$location = (float)$_REQUEST['location'];
			$frase = (string)$_REQUEST['frase'];
			$status = (boolean)add_current_user_favorite($post_id, $frase, $location);
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array(
				'status' => $status
			));
			exit;
		}
	}
	
	/**
	 * コンテンツをXMLに整形する
	 * @param string $content
	 * @return string 
	 */
	public function convert_to_xml($content){
		$head = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
		$xml = $head.'<TextFlow>'.$content.'</TextFlow>';
		// TODO: DOM処理
		return $xml;
	}
	
	public function preview(){
		if(is_preview()){
			?>
			<div id="preview-watermark">プレビュー</div>
			<?php
		}
	}
	
	/**
	 * 現在ログインしているユーザーのIDを返す
	 * @global int $user_ID
	 * @return int
	 */
	private function get_current_user_id(){
		global $user_ID;
		return is_user_logged_in() ? $user_ID : 0;
	}
	
}
global $hametuha_single_post_manager;
$hametuha_single_post_manager = new Hametuha_Single_Post_Manager();

/**
 * 投稿を読むページか否かを返す
 * @return boolean
 */
function is_reading_page(){
	global $post;
	return (is_singular('post') && !get_post_format($post) && false === strpos($post->post_content, '[flash'));
}

/**
 * リーダーを出力する
 */
function the_reading_contents(){
	?>
	<div id="reading-container"<?php if(!should_viewer_ready()) echo ' style="left:-100%;"';?>>
		<?php if(wp_is_mobile()): ?>
		<div class="inner"></div>
		<?php else: ?>
		<div id="reader">
			<p class="invalid-setting">
				このコンテンツを閲覧するには<strong>Flash Player 10.2以上</strong>が必要です。
				Adobeのサイトからダウンロードしてください。
				<a href="http://get.adobe.com/jp/flashplayer/" taget="_blank">ダウンロード&raquo;</a>
			</p>
			<noscript>
			<p class="invalid-setting"><strong>Javascriptが有効になっていない</strong>ので、このコンテンツを見られません！<a href="#">もっと詳しく&raquo;</a></p>
			</noscript>
		</div>
		<form id="search-box" class="clearfix">
			<input id="search" type="text" value="" placeholder="書籍内検索" />
			<input id="submit" type="submit" value="検索" title="検索" />
		</form>
		<?php endif; ?>
		<a id="meta-drawer" href="#open" class="toggle" title="閉じる">×</a>
	</div>
	<?php
}

/**
 * ビューアーを初期状態で開いておくべきか
 * @return boolean 
 */
function should_viewer_ready(){
	return is_preview() || (isset($_GET['location']) && is_numeric($_GET['location'])) || (isset($_GET['debug']) && $_GET['debug']) ;
}