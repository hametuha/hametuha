<?php

class Hametuha_User_Change_Login{
	
	private $nonce_name = 'hametuha_change_login';
	
	public function __construct() {
		add_action('init', array($this, 'init'));
	}
	
	public function init(){
		add_action('wp_ajax_username_check', array($this, 'username_check'));
		add_action('wp_ajax_username_change', array($this, 'change_username'));
	}
	
	
	/**
	* ユーザー名が使用できるか否かを表示する
	*/
	function username_check(){
		if(isset($_REQUEST['_wpnonce'], $_REQUEST['user_login']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'profile_helper_'.  get_current_user_id())){
			$user_login = (string)$_REQUEST['user_login'];
			global $user_ID;
			$valid = false;
			switch($this->is_invalid_user_name($user_login, $user_ID)){
				case 0:
					$valid = true;
					$message = esc_attr($user_login).'は使用できます。';
					break;
				case 1:
					$valid = true;
					$message = esc_attr($user_login).'は現在あなたが使用中です。';
					break;
				case 2:
					$message = "指定したユーザー名はすでに存在します。\n別のユーザー名にしてください。";
					break;
				case 3:
					$message = esc_attr($user_login)."には不正な文字が含まれています。\n使用できるのは半角英数および_.-@です。";
					break;
			}
		}else{
			$valid = false;
			$message = '不正なアクセスです。';
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(array(
			'valid' => $valid,
			'message' => $message
		));
		die();
	}
	/**
	* 指定したユーザーがログイン名を使用できるか
	* @param string $user_login
	* @param int $current_user
	* @return int 0 誰にも使われていない 1 = 指定したユーザーが使用中 2 = 他のユーザーが使用中 3 = 不正な文字列
	*/
	private function is_invalid_user_name($user_login, $current_user = 0){
		if($current_user){
			$user = get_userdata($current_user);
			$login = $user->user_login;
		}
		if(username_exists($user_login)){
			if($current_user && $user_login == $login){
				return 1;
			}else{
				return 2;
			}
		}elseif(!preg_match("/^[a-z0-9_.\-@]+$/", $user_login)){
			return 3;
		}else{
			return 0;
		}
	}
	
	public function change_username(){
		if(is_user_logged_in()){
			global $user_ID, $wpdb;
			$action = $_REQUEST['change_login_action'] ?: '' ;
			switch($action){
				case 'change':
					if(isset($_REQUEST['_wpnonce'], $_REQUEST['user_login']) && wp_verify_nonce($_REQUEST['_wpnonce'], $this->nonce_name.'_'.get_current_user_id())){
						$user_login = sanitize_user((string)$_REQUEST['user_login'], true);
						switch($this->is_invalid_user_name($user_login, $user_ID)){
							case 0:
								wp_clear_auth_cookie();
								$wpdb->update(
									$wpdb->users,
									array(
										'user_login' => $user_login,
										'user_nicename' => $user_login
									),
									array('ID' => $user_ID),
									array('%s', "%s"),
									array("%d")
								);
								$this->redirect($user_login);
								break;
							case 1:
								$this->form(esc_html($user_login).'は現在あなたが使用中です。', 'notice');
								break;
							case 2:
								$this->form("指定したユーザー名はすでに存在します。別のユーザー名にしてください。", 'error');
								break;
							case 3:
								$this->form(esc_html($user_login)."には不正な文字が含まれています。使用できるのは半角英数および_.-@です。", 'error');
								break;
						}
					}else{
						$this->form('不正なアクセスです。', 'error');
					}
					break;
				default:
					$this->form();
					break;
			}
			exit;
		}else{
			wp_die('不正なアクセスです'); 
		}
	}
	
	
	private function redirect($user_login){
		$this->header();
		?>
		<p class="message success">
			ユーザー名を<strong><?php echo esc_html($user_login); ?></strong>に変更いたしました。再度ログインしてください。
			あと<em id="login-success-redirect">5</em>秒で<a href="<?php echo wp_login_url('', true); ?>" target="_parent">ログインページ</a>に移動します。
		</p>
		<?php
		$this->footer();
	}
	
	private function form($message = '', $class = 'success'){
		$current_user = wp_get_current_user();
		$this->header();
		?>
		<?php if(!empty($message)): ?>
			<p class="message <?php echo esc_attr($class); ?>"><?php echo esc_html($message); ?></p>
		<?php endif; ?>
		<form id="your-profile" method="post" action="<?php echo esc_attr(admin_url('admin-ajax.php?action=username_change'));?>">
			<table class="form-table">
				<tr>
					<th>現在のユーザー名</th>
					<td>
						<strong><?php echo $current_user->data->user_login;?></strong>
					</td>
				</tr>
				<tr>
					<th><label for="user_login">新しいユーザー名</label></th>
					<td>
						<input type="text" class="short" name="user_login" id="user_login" />
						<a class="small-button" id="check-username-valid" href="#">使用可能かチェック</a>
						<img class="loader" src="<?php echo get_template_directory_uri(); ?>/img/ajax-loader.gif" alt="Loading..." width="16" height="11" />
						
						<p class="no-indent small-message notice">
							ユーザー名として使用できるのは<strong>半角英数および_.-@</strong>です。変更した場合、再度ログインしていただく必要があります。
						</p>
						<?php if(current_user_can('edit_posts')): ?>
							<p class="no-indent small-message warning">
								すでに作品を公開されている方は、作品一覧ページのURLの一部が変わります。
								<br /><strong>http://hametuha.com/authors/xxxx</strong>（xxxxの部分）
							</p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<p class="submit center">
				<?php wp_nonce_field($this->nonce_name.'_'.  get_current_user_id());?>
				<input type="hidden" name="change_login_action" value="change" onclick="this.disabled = true;" />
				<input type="submit" class="button-primary" value="ユーザー名を変更する" />
			</p>
		</form>			
		<?php
		$this->footer();
	}
	
	private function header(){
		wp_enqueue_style('hametuha-core');
		_hametuha_profile_page(true);
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
		<?php
	}
	
	private function footer(){
		?>
		</div>
			<?php wp_footer(); ?>
		</body>
		</html>
		<?php
	}
}
new Hametuha_User_Change_Login();