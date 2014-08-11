<?php

namespace Hametuha\Rest;


use Hametuha\Model\Author;
use WPametu\API\Rest\RestTemplate;

/**
 * Class LoginName
 * @package Hametuha\Rest
 * @property-read Author $authors
 */
class LoginName extends RestTemplate
{

    protected $action = 'hametuha_change_login';

    public static $prefix = 'login/change';

    protected $title = 'ログイン名変更';

    /**
     * モデル
     *
     * @var array
     */
    protected $models = [
        'authors' => Author::class
    ];

    /**
     * フォーム表示
     *
     * @param int $page
     */
    public function pager($page = 1){

        if( $page != 1 ){
            $this->method_not_found();
        }

        $this->auth_redirect();

        add_filter('body_class', function($classes){
            $classes[] = 'page-template-page-login-php';
            return $classes;
        });

        $this->set_data([
            'nonce' => $this->nonce_field('_wpnonce', true, false),
            'action' => $this->url('/update/', force_ssl_admin()),
            'check_url' => $this->url('/check/', force_ssl_admin()),
            'login_name' => $this->user->display_name,
            'login' => $this->user->user_login,
            'nicename' => $this->user->user_nicename
        ]);

        nocache_headers();
        $this->load_template('templates/form', 'login');

    }

    /**
     * Ajaxでチェックする
     *
     */
    public function get_check(){
        if( !$this->verify_nonce() || !($query = $this->input->get('login')) ){
            $this->error('不正なアクセスです。', 403);
        }
        if( !$this->login_available($query) ){
            $this->error('このログイン名は利用できません', 403);
        }
        $this->set_data([
            'success' => true,
            'niceName' => sanitize_title($query),
        ]);
        $this->response();
    }

    /**
     * ログイン名を更新する
     */
    public function post_update(){
        if( !is_user_logged_in() ){
            $this->auth_error();
        }
        if( !$this->verify_nonce() || !($login = $this->input->post('login_name')) ){
            $this->error('不正なアクセスです。', 403);
        }
        if( !$this->login_available($login) ){
            $this->error('このログイン名は利用できません', 403);
        }
        // O.K. 更新。
        $nice_name = sanitize_title($login);
        if(!$this->authors->update_login($login, $nice_name, $this->user->ID)){
            $this->error('更新できませんでした。', 500);
        }
        $this->set_data([
            'message' => sprintf('ログイン名を変更しました。5秒後にログアウトしますので、ログインしなおしてください。（<a href="%s">いますぐログアウト</a>）', wp_logout_url()),
            'url' => wp_logout_url(),
        ]);
        nocache_headers();
        $this->response();
    }

    /**
     * ログイン名が利用可能か否か調べる
     *
     * @param string $login
     * @return bool
     */
    protected function login_available($login){
        if( username_exists($login) ){
            return false;
        }
        if( !validate_username($login) ){
            return false;
        }
        return true;
    }

    /**
     * スクリプトを読み込む
     *
     * @param string $page
     */
    public function enqueue_assets($page = ''){
        wp_enqueue_script('hametuha-login-changer');
    }

} 