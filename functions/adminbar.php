<?php

/**
 * 管理バーの表示を作成する
 * @global wpdb $wpdb
 * @param WP_Admin_Bar $wp_admin_bar 
 */
function _hametuha_adminbar_create($wp_admin_bar) {
	//ホーム
	$wp_admin_bar->add_menu(array(
		'id' => 'hametuha-logo',
		'title' => '<span class="ab-icon"></span><span class="ab-label">ホーム</span>',
		'href' => home_url('/', 'http')
	));
	//メニューを追加していく
	$location = get_nav_menu_locations();
	foreach(array(
		'hametuha_global_works' => array(
			'id' => 'hametuha-works',
			'title' => '<span class="ab-icon"></span><span class="ab-label">作品</span>',
			'href' => home_url('/latest/', 'http') ),
		'hametuha_global_about' => array(
			'id' => 'hametuha-about',
			'title' => '<span class="ab-icon"></span><span class="ab-label">破滅派とは</span>',
			'href' => home_url('/about/', 'http')),
		'hametuha_global_info' => array(
			'id' => 'hametuha-info',
			'title' => '<span class="ab-icon"></span><span class="ab-label">お知らせ</span>',
			'href' => home_url('/info/', 'http'))
	) as $location_name => $parent_menu){
		$wp_admin_bar->add_menu($parent_menu);
		$menu = wp_get_nav_menu_object($location[$location_name]);
		if($menu){
			$menu_items = wp_get_nav_menu_items($menu->term_id);
			foreach($menu_items as $key => $item){
				$wp_admin_bar->add_menu(array(
					'parent' => $parent_menu['id'],
					'id' => $location_name.'-menu-'.$key,
					'title' => $item->title,
					'href' => $item->url
				));
			}
		}
	}
	//投稿者の場合は追加用リンク
	if(current_user_can('edit_posts')){
		$wp_admin_bar->add_menu(array(
			'id' => 'hametuha-add-works',
			'title' => '<span class="ab-icon"></span><span class="ab-label">投稿</span>',
			'href' => admin_url('post-new.php')
		));
		$wp_admin_bar->add_menu(array(
			'id' => 'add-works',
			'title' => '新規投稿を追加',
			'href' => admin_url('post-new.php'),
			'parent' => 'hametuha-add-works'
		));
		$wp_admin_bar->add_menu(array(
			'id' => 'add-anpi',
			'title' => '安否を知らせる',
			'href' => admin_url('post-new.php?post_type=anpi'),
			'parent' => 'hametuha-add-works'
		));
		$wp_admin_bar->add_menu(array(
			'id' => 'add-announcement',
			'title' => '告知を行う',
			'href' => admin_url('post-new.php?post_type=announcement'),
			'parent' => 'hametuha-add-works'
		));
		$wp_admin_bar->add_menu(array(
			'id' => 'add-thread',
			'title' => 'BBSにスレ立て',
			'href' => get_post_type_archive_link('thread').'#thread-add',
			'parent' => 'hametuha-add-works'
		));
	}
}	
add_action( 'admin_bar_menu', '_hametuha_adminbar_create', 1);


/**
 * アドミンバーの表示を修正
 * @global array $hametuha_userpage_slug
 * @param WP_Admin_Bar $wp_adminb_bar
 */
function _hametuha_adminbar_fix($wp_admin_bar){
	//デフォルトのものを非表示にする
	$wp_admin_bar->remove_menu('wp-logo'); //WordPressロゴ
	$wp_admin_bar->remove_menu('new-content'); //新規追加
	$wp_admin_bar->remove_menu('site-name'); //サイト名
	$wp_admin_bar->remove_menu('comments'); //コメント
	//ログインしていないユーザー
	if(!is_user_logged_in()){
		$wp_admin_bar->add_menu(array(
			'parent' => 'top-secondary',
			'id' => 'my-account',
			'title' => 'こんにちはゲストさん！'
		));
		$wp_admin_bar->add_group(array(
			'parent' => 'my-account',
			'id' => 'user-actions'
		));
		if(is_singular()){
			$url = wp_login_url(get_permalink());
		}else{
			$url = wp_login_url();
		}
		$wp_admin_bar->add_menu(array(
			'id' => 'adminbar-login',
			'parent' => 'user-actions',
			'title' => 'ログインして破滅する',
			'href' => $url
		));
		$wp_admin_bar->add_menu(array(
			'id' => 'adminbar-register',
			'parent' => 'user-actions',
			'title' => 'はじめての方は新規登録',
			'href' => preg_replace("/^.*href=\"([^\"]+)\".*$/", "$1", wp_register('', '', false))
		));
	}else{
		//ログインしているユーザー
		if(current_user_can('edit_posts')){
			$wp_admin_bar->add_menu(array(
				'id' => 'dashboard-shotrlink',
				'title' => 'ダッシュボード',
				'href' => admin_url(),
				'parent' => 'user-actions'
			));
		}
		//投稿者になる
		if(!current_user_can('edit_posts') && !is_pending_user()){
			$wp_admin_bar->add_menu(array(
				'id' => 'become-author',
				'title' => '投稿者になる',
				'href' => home_url('/become-author/'),
				'parent' => 'user-actions'
			));
		}
		//アカウント承認
		if(is_pending_user()){
			$wp_admin_bar->add_menu(array(
				'id' => 'reconfirm',
				'title' => 'アカウントが承認されていません',
				'href' => home_url('/faq/how-to-register/'),
				'parent' => 'user_actions'
			));
		}
		//ユーザーのコンテンツ
		if(!is_pending_user()){
			global $hametuha_userpage_slug;
			$wp_admin_bar->add_group(array(
				'parent' => 'my-account',
				'id' => 'user-contents'
			));
			if(current_user_can('edit_posts')){
				$wp_admin_bar->add_menu(array(
					'id' => 'your-works',
					'title' => 'あなたの作品一覧',
					'href' => get_author_posts_url(get_current_user_id()),
					'parent' => 'user-contents'
				));
			}
			foreach($hametuha_userpage_slug as $slug => $name){
				$wp_admin_bar->add_menu(array(
					'id' => $slug,
					'title' => $name,
					'href' => home_url("/login/{$slug}/"),
					'parent' => 'user-contents'
				));
			}
		}
		//お知らせ
		$info = array();
		if(current_user_can('edit_posts')){
			//同人のみ
			$sufficience = get_user_status_sufficient(get_current_user_id());
			if(50 > $sufficience){
				//プロフィールの充実度が低い場合
				$info['profile'] = true;
			}
			if(!has_recent_post(get_current_user_id(), 'post')){
				//最近投稿を書いていなかったら
				$info['post'] = true;
			}
			if(!has_recent_post(get_current_user_id(), 'anpi', 60)){
				//最近安否情報を書いていなかったら
				$info['anpi'] = true;
			}
		}
		if(!empty($info)){
			$wp_admin_bar->add_menu( array(
				'parent' => 'top-secondary',
				'id'     => 'hametuha-notify',
				'title' => '<span class="notification-count mono">'.count($info).'</span>'
			));
			foreach($info as $key => $var){
				switch($key){
					case 'anpi':
						$wp_admin_bar->add_menu(array(
							'id' => 'write_anpi',
							'title' => '安否をお伝えしませんか',
							'href' => admin_url('post-new.php?post_type=anpi'),
							'parent' => 'hametuha-notify'
						));
						break;
					case 'post':
						$wp_admin_bar->add_menu(array(
							'id' => 'write_post',
							'title' => '30日以上作品を発表していません',
							'href' => admin_url('post-new.php?post_type=post'),
							'parent' => 'hametuha-notify'
						));
						break;
					case 'profile':
						$wp_admin_bar->add_menu(array(
							'id' => 'sutisfy_profile',
							'title' => 'プロフィール充実度: 現在'.$sufficience. '%',
							'href' => get_edit_profile_url(get_current_user_id()),
							'parent' => 'hametuha-notify'
						));
						break;
				}
			}
		}
	}
}
add_action( 'admin_bar_menu', '_hametuha_adminbar_fix', 10000);


/**
 * アドミンバーの表示非表示
 * @return boolean
 */
function _hametuha_show_admin_bar(){
	return !((isset($_GET['iframe']) && $_GET['iframe']) || isset($_GET['lwp']));
}
add_filter( 'show_admin_bar', '_hametuha_show_admin_bar' , 1000 );