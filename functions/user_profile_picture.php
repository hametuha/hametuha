<?php

class Hametuha_Profile_Picture{
	
	/**
	 * バージョン
	 * @var string 
	 */
	private $version = '1.0';
	
	/**
	 * wp-uploadsフォルダに作成するディレクトリ
	 * @var string
	 */
	private $dir = 'profile-picture';
	
	/**
	 * アクション名
	 * @var string
	 */
	private $action = 'user_profile_picture';
	
	/**
	 * nonce用アクション名
	 * @var string
	 */
	private $nonce_name = 'user_profile_picture_edit';
	
	/**
	 * アップロードできる最大サイズ 
	 */
	const UPLOAD_MAX_SIZE = '2MB';
	
	/**
	 * コンストラクタ 
	 */
	public function __construct(){
		add_action('init', array($this, 'init'));
	}
	
	/**
	 * 初期化処理 
	 */
	public function init(){
		//アップロードディレクトリに書き込みが可能なら
		if((file_exists($this->get_dir()) && is_writable($this->get_dir())) || @mkdir($this->get_dir())){
			//プロフィールページにフォームを出力
			add_action('show_user_profile', array($this, 'show_profile'), 1);
			//Ajax用エンドポイント
			add_action('wp_ajax_'.$this->action, array($this, 'ajax'));
			//ユーザーが削除されたとき
			add_action('delete_user', array($this, 'delete_user'));
			//avatarのフィルター
			add_filter('get_avatar', array($this, 'get_avatar'), 10, 5);
		}
	}
	
	/**
	 * アップロード用ディレクトリを返す
	 * @return string 
	 */
	private function get_dir(){
		$dir = wp_upload_dir();
		return $dir['basedir'].DIRECTORY_SEPARATOR.$this->dir.DIRECTORY_SEPARATOR;
	}
	
	
	/**
	 * ユーザーのプロフィール保存ディレクトリを返す
	 * @param int $user_id
	 * @return string 
	 */
	private function get_user_dir($user_id){
		return $this->get_dir().$user_id.DIRECTORY_SEPARATOR;
	}
	
	/**
	 * ディレクトリのURLを返す
	 * @return string
	 */
	private function get_url(){
		$url = wp_upload_dir();
		return $url['baseurl'].'/'.$this->dir;
	}
	
	/**
	 * ディレクトリが存在するかを返す
	 * @param int $user_id
	 * @return boolean 
	 */
	public function has_profile_pic($user_id){
		return file_exists($this->get_user_dir($user_id));
	}
	
	/**
	 * ユーザーのアップロードディレクトリを返す
	 * @param int $user_id 
	 * @return string
	 */
	private function get_user_url($user_id){
		return $this->get_url().'/'.$user_id.'/';
	}
	
	/**
	 * アバターをフィルタリングする
	 * @param string $avatar
	 * @param string $id_or_email
	 * @param int $size
	 * @param string $default
	 * @param string $alt 
	 */
	public function get_avatar($avatar, $id_or_email, $size, $default, $alt){
		$user_id = 0;
		if(is_numeric($id_or_email)){
			$user_id = $id_or_email;
		}elseif(is_object($id_or_email)){
			if($id_or_email->user_id > 0){
				$user_id = $id_or_email->user_id;
			}
		}else{
			$user_id = email_exists($id_or_email);
		}
		if(file_exists($this->get_user_dir($user_id))){
			//オリジナルファイルの取得
			$file_lists = glob($this->get_user_dir($user_id)."profile.*");
			if(!empty($file_lists)){
				$orig_file = current($file_lists);
				$ext = preg_replace("/^.*\.(jpe?g|gif|png)$/i", '$1', $orig_file);
				if(!file_exists($this->get_user_dir($user_id)."profile-{$size}x{$size}.{$ext}")){
					//指定サイズがないので作る
					image_resize($orig_file, $size, $size, true, null);
				}
				//指定サイズのファイルが存在するので書き換え
				$avatar = preg_replace('/src=\'.*?\'/', 'src="'.$this->get_user_url($user_id)."profile-{$size}x{$size}.{$ext}\"", $avatar);
			}
		}
		return $avatar;
	}
	
	/**
	 * フォームをアップロードする 
	 */
	public function ajax(){
		if(is_user_logged_in()){
			global $user_ID;
			$action = isset($_REQUEST['profile_action']) ? (string)$_REQUEST['profile_action'] : '';
			switch($action){
				case "delete":
				case "upload":
					if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], $this->nonce_name.'_'.$user_ID)){
						$this->{$action}();
					}else{
						$this->form("不正なアクセスです", 'error');
					}
					break;
				default:
					$this->form();
					break;
			}
			exit;
		}else{
			wp_die('このページはログインユーザー専用です。', 'Error');
		}
	}
	
	/**
	 * 写真をアップロードする 
	 */
	private function upload(){
		global $user_ID;
		$flg = true;
		//ファイルのアップロードチェック
		if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0){
			//画像の情報を取得
			$info = getimagesize($_FILES['profile_picture']['tmp_name']);
			//ファイルのmime_typeチェック
			if(preg_match("/^image\/(jpeg|png|gif)$/", $info['mime'])){
				//ファイルサイズが指定したものより小さいか否か
				if(filesize($_FILES['profile_picture']['tmp_name']) < $this->get_allowed_size(true)){
					//以前のものを削除してディレクトリを作成
					$this->remove_dir($this->get_user_dir($user_ID));
					mkdir($this->get_user_dir($user_ID));
					//新しいファイル名を作成
					$ext = str_replace("jpeg", 'jpg', str_replace('image/', '', $info['mime']));
					$temp_file = 'profile.'.$ext;
					@move_uploaded_file($_FILES['profile_picture']['tmp_name'], $this->get_user_dir($user_ID).$temp_file);
					//ファイルの大きさをチェックし、150 x 150より小さかったら拡大
					if($info[0] < 150 || $info[1] < 150){
						$ratio = 150 / min(array($info[0], $info[1]));
						$oldWidth = $info[0];
						$oldHeight = $info[1];
						$newWidth = intval($info[0] * $ratio);
						$newHeight = intval($info[1] * $ratio);
						//画像をリサイズ
						$image = wp_load_image($this->get_user_dir($user_ID).$temp_file);
						$newimage = wp_imagecreatetruecolor( $newWidth, $newHeight );
						imagecopyresampled( $newimage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
						if ( IMAGETYPE_PNG == $info[2] && function_exists('imageistruecolor') && !imageistruecolor( $image ) ){
							imagetruecolortopalette( $newimage, false, imagecolorstotal( $image ) );
						}
						imagedestroy( $image );
						unlink($this->get_user_dir($user_ID).$temp_file);
						switch($info[2]){
							case IMAGETYPE_GIF:
								imagegif($newimage, $this->get_user_dir($user_ID).$temp_file);
								break;
							case IMAGETYPE_PNG:
								imagepng($newimage, $this->get_user_dir($user_ID).$temp_file);
								break;
							default:
								imagejpeg($newimage, $this->get_user_dir($user_ID).$temp_file);
								break;
						}
						imagedestroy($newimage);
					}
					//正方形にリサイズ
					$resize = image_resize($this->get_user_dir($user_ID).$temp_file, 150, 150, true, null);
					if(is_wp_error($resize)){
						$flg = false;
						$message = '画像のリサイズに失敗しました: '.$resize->get_error_message();
					}else{
						$message = '画像をアップロードしました';
					}
				}else{
					$flg = false;
					$message = '許可されたアップロードサイズ（'.$this->get_allowed_size().'）を超えています';
				}
			}else{
				$flg = false;
				$message = 'アップロードできるのはJPG, GIF, PNGのみです。';
			}
			//保存
		}else{
			$flg = false;
			switch($_FILES['profile_picture']['error']){
				case UPLOAD_ERR_FORM_SIZE:
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$message = '許可されたアップロードサイズ（'.$this->get_allowed_size().'）を超えています';
					break;
				case 5:
					$message = 'ファイルが指定されていません';
					break;
				default:
					$message = 'ファイルのアップロードに失敗しました';
					break;
			}
		}
		$this->form($message, ($flg ? 'success' : 'error'));
	}
	
	/**
	 * プロフィール写真を削除する 
	 */
	private function delete(){
		global $user_ID;
		if(file_exists($this->get_user_dir($user_ID))){
			$this->remove_dir($this->get_user_dir($user_ID));
			$this->form('プロフィール写真を削除しました');
		}else{
			$this->form('プロフィール写真は登録されていません');
		}
	}
	
	/**
	 * ユーザーが削除された時のフィルター
	 * @param int $user_id 
	 */
	public function delete_user($user_id){
		$this->remove_dir($this->get_user_dir($user_id));
	}


	/**
	 * ディレクトリを再帰的に削除する
	 * @param string $path 
	 */
	private function remove_dir($path){
		if(is_dir($path)){
			foreach(scandir($path) as $file){
				if($file != '.' && $file != '..'){
					if(is_dir("{$path}/{$file}")){
						$this->remove_dir("{$path}/{$file}");
					}else{
						unlink("{$path}/{$file}");
					}
				}
			}
			rmdir($path);
		}
	}
	
	/**
	 * 許可されたファイルサイズを指定する
	 * @return int
	 */
	private function get_allowed_size($in_bit = false){
		return $in_bit ? intval(self::UPLOAD_MAX_SIZE) * 1024 * 1024 : self::UPLOAD_MAX_SIZE;
	}
	
	/**
	 * フォームを出力する
	 * @param string $message
	 * @param string $class 
	 */
	private function form($message = '', $class = 'success'){
		global $user_ID;
			if(intval(get_cfg_var('post_max_size')) * 1024 * 1024 < intval($_SERVER['CONTENT_LENGTH'])){
				$message = '送信できる容量を超えました。ファイルサイズを'.$this->get_allowed_size().'よりも小さくしてください。';
				$class = 'error';
			}
			wp_enqueue_style('hametuha-core');
			
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml" xmlns:mixi="http://mixi-platform.com/ns#" xml:lang="ja" lang="ja">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php wp_title("|", true, 'right'); bloginfo('name'); if(is_front_page()) echo " | ".get_bloginfo('description'); ?></title>
			<?php wp_head(); ?>
			<link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/img/favicon.ico" />
		</head>
		<?php flush(); ?>
		<body <?php body_class("iframe"); ?>>
			<div class="post-content">
				<?php if(!empty($message)): ?>
				<p class="message <?php echo esc_attr($class); ?>"><?php echo esc_html($message); ?></p>
				<?php endif; ?>
				<form method="post" action="<?php echo esc_attr($this->get_endpoint());?>" enctype="multipart/form-data">
					<table class="form-table">
						<tr>
							<th>現在のプロフィール写真</th>
							<td>
								<?php echo get_avatar($user_ID, 60); ?>
								<?php if(count(glob($this->get_user_dir($user_ID).'profile-150x150.*')) > 0): ?>
									<p class="right">	
										<a class="small-button" href="<?php echo esc_attr(wp_nonce_url($this->get_endpoint().'&profile_action=delete', $this->nonce_name.'_'.$user_ID));?>">この写真を削除</a>
									</p>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th><label for="profile_picture">ファイル</label></th>
							<td>
								<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->get_allowed_size(true); ?>" />
								<input type="file" name="profile_picture" id="profile_picture" />
								<p class="description">
									形式はJPG, GIF, PNGに限ります。最大容量は<?php echo $this->get_allowed_size(); ?>です。
								</p>
							</td>
						</tr>
					</table>
					<p class="submit center">
						<?php wp_nonce_field($this->nonce_name.'_'.$user_ID);?>
						<input type="hidden" name="profile_action" value="upload" onclick="this.disabled = true;" />
						<input type="submit" class="button-primary" value="アップロード" />
					</p>
				</form>
			</div>
			<?php wp_footer(); ?>
		</body>
		</html>
		<?php
	}
	
	/**
	 * Ajax用エンドポイントを返す
	 * @return string
	 */
	private function get_endpoint(){
		return admin_url('admin-ajax.php?action='.$this->action);
	}
	
	/**
	 * プロフィール画面にフォームを表示する
	 * @param WP_User $current_user 
	 */
	public function show_profile($current_user){
		?>
		<h3>プロフィール写真</h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th>現在のプロフィール写真</th>
					<td>
						<?php echo get_avatar($current_user->ID, 60); ?>
						<?php if(file_exists($this->get_user_dir($current_user->ID))): ?>
							<p class="small-message success">プロフィール写真をアップロード済みです。</p>
						<?php elseif(has_gravatar($current_user->ID)): ?>
							<p class="small-message success">Gravatarが有効です。</p>
						<?php else: ?>
							<p class="small-message warning">プロフィール写真がありません。</p>
						<?php endif; ?>
						<span class="description">
							Gravatarを設定するか、ファイルをアップロードするとプロフィール写真が表示されます。
							あなたが同人として活動している場合は、なるべくプロフィール写真を設定しましょう。（<a href="<?php echo home_url('/faq/gravatar/');?>">詳しく</a>）
						</span><br />
						<a id="user-profile-picture-edit" title="プロフィール写真アップロード" class="button" href="<?php echo $this->get_endpoint(); ?>">編集</a>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
}
global $_hametuha_profile_picture;
$_hametuha_profile_picture = new Hametuha_Profile_Picture();


/**
 * グラバターが有効か否かを調べる
 * @global wpdb $wpdb
 * @param int $user_id 
 * @return boolean
 */
function has_gravatar($user_id){
	global $wpdb;
	$mail = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM {$wpdb->users} WHERE ID = %d", $user_id));
	if($mail){
		$hash = md5(strtolower($mail));
		$endpoint = "http://www.gravatar.com/avatar/{$hash}?s=1&d=404";
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $endpoint,
			CURLOPT_HEADER => true,
			CURLOPT_NOBODY => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
			CURLOPT_TIMEOUT => 5,
			CURLOPT_FOLLOWLOCATION => false
		));
		$header = split("(\r\n|\r|\n){2}", curl_exec($ch), 2);
		curl_close($ch);
		return false !== strpos($header[0], "200 OK");
	}else{
		return false;
	}
}

/**
 * ユーザーがオリジナルのプロファイル画像を設定しているか否か
 * @global Hametuha_Profile_Picture $_hametuha_profile_picture
 * @param int $user_id
 * @return boolean 
 */
function has_original_picture($user_id){
	global $_hametuha_profile_picture;
	return $_hametuha_profile_picture->has_profile_pic($user_id);
}