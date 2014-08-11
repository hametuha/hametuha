<?php

namespace Hametuha\Rest;


use Hametuha\Model\Author;
use WPametu\API\Rest\RestTemplate;

/**
 * Become Author
 *
 * @package Hametuha\Rest
 * @property-read Author $authors
 */
class BecomeAuthor extends RestTemplate
{

    protected $action = 'hametuha_become_author';

    protected $title = '同人になる';

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

        $this->check();

        add_filter('body_class', function($classes){
            $classes[] = 'page-template-page-login-php';
            return $classes;
        });

        $this->set_data([
            'nonce' => $this->nonce_field('_wpnonce', true, false),
            'action' => $this->url('/register/', force_ssl_admin()),
            'name' => $this->user->display_name,
        ]);

        nocache_headers();
        $this->load_template('templates/form', 'become');

    }

    public function post_register(){
        try{
            $this->check(false);

            if( !$this->verify_nonce() ){
                $this->error('不正なアクセスです。', 403);
            }

            if( !$this->input->post('review_contract') ){
                $this->error('利用規約に同意していません。', 403);
            }

            // O.K. ユーザーを更新
            $error = wp_update_user(array(
                'ID' => get_current_user_id(),
                'role' => 'author'
            ));

            if( is_wp_error($error) ){
                $this->error($error->get_error_message(), 500);
            }
            $this->set_data([
                'success' => true,
                'message' => 'おめでとうございます。あなたは同人になりました。さっそくプロフィールを編集しましょう。（5秒後に自動的に移動します。）',
                'url' => admin_url('profile.php'),
            ]);

        }catch (\Exception $e){
            $this->set_data([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }finally{
            nocache_headers();
            $this->response();
        }
    }

    /**
     * 購読者かどうかチェックする
     */
    private function check($redirect = true){
        if( $redirect ){
            $this->auth_redirect();
        }elseif( !is_user_logged_in() ){
            $this->error('ログインしてください。', 404);
        }

        if( !current_user_can('read') ){
            $this->error(sprintf('あなたはまだメールアドレスの承認を済ませていないようです。<a href="%s">ログインページで承認を済ませてください。</a>', wp_login_url()), 403);
        }

        if( current_user_can('edit_posts') ){
            $this->error('あなたはすでに同人になっています。', 403);
        }
    }

    /**
     * スクリプトを読み込む
     *
     * @param string $page
     */
    public function enqueue_assets( $page = '' ){
        wp_enqueue_script('hametuha-become-author');
    }

} 