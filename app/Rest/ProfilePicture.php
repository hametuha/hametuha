<?php

namespace Hametuha\Rest;


use Hametuha\Model\Author;
use Hametuha\User\Profile\Picture;
use WPametu\API\Rest\RestTemplate;

/**
 * Class LoginName
 * @package Hametuha\Rest
 * @property-read Author $authors
 * @property-read Picture $picture
 */
class ProfilePicture extends RestTemplate
{

    protected $action = 'hametuha_profile_picture';

    public static $prefix = 'account/picture';

    protected $title = 'プロフィール写真編集';

    /**
     * モデル
     *
     * @var array
     */
    protected $models = [
        'authors' => Author::class
    ];

    /**
     * ファイルのアップロード
     */
    public function post_upload(){
        if( !is_user_logged_in() ){
            $this->error('写真をアップロードするには、ログインしてください。', 403);
        }
        if( !$this->verify_nonce() ){
            $this->error('不正なアクセスです。', 403);
        }
        // ここから正常系
        $this->prg->start_session();
        try{
            $file = $this->input->file_info('new_picture');
            if( !$file ){
                $this->error($this->input->file_error_message('new_picture'), 500);
            }
            $this->picture->upload($file, get_current_user_id());
            $this->prg->addMessage('写真を変更しました。');
        }catch ( \Exception $e ){
            $this->prg->addErrorMessage($e->getMessage());
        }finally{
            nocache_headers();
            wp_safe_redirect($this->url('', force_ssl_admin()));
            exit;
        }
    }

    /**
     * 画像削除アクション
     */
    public function post_delete(){
        if( !is_user_logged_in() || !$this->verify_nonce() ){
            $this->error('不正なアクセスです。', 403);
        }
        $this->prg->start_session();
        try{
            if( !$this->input->post('delete_picture') ){
                throw new \Exception('確認のチェックが入れられていません。');
            }
            $this->picture->delete_user(get_current_user_id());
            $this->prg->addMessage('アップロードされた画像を削除しました');
        }catch ( \Exception $e){
            $this->prg->addErrorMessage($e->getMessage());
        }finally{
            nocache_headers();
            wp_safe_redirect($this->url('', force_ssl_admin()));
            exit;
        }
    }

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
            'upload_action' => $this->url('/upload/', force_ssl_admin()),
            'delete_action' => $this->url('/delete/', force_ssl_admin()),
            'has_gravatar' => has_gravatar($this->user->ID),
            'uploaded' => $this->picture->has_profile_pic($this->user->ID),
            'max_size' => $this->picture->get_allowed_size(),
        ]);

        nocache_headers();
        $this->prg->start_session();
        $this->load_template('templates/form', 'picture');

    }

    /**
     * スクリプトを読み込む
     *
     * @param string $page
     */
    public function enqueue_assets($page = ''){
        wp_enqueue_script('hametuha-login-changer');
    }


    /**
     * Getter
     *
     * @param string $name
     * @return mixed|null|\WP_User|\WPametu\Pattern\Singleton
     */
    public function __get($name){
        switch( $name ){
            case 'picture':
                return Picture::get_instance();
                break;
            default:
                return parent::__get($name);
                break;
        }
    }

} 