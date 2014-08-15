<?php

namespace Hametuha\User\Profile;


use WPametu\File\Image;
use WPametu\File\Path;
use WPametu\Pattern\Singleton;

/**
 * Class Picture
 *
 * @package Hametuha\User\Profile
 * @property-read Image $image
 */
class Picture extends Singleton
{

    use Path;

    /**
     * wp-uploadsフォルダに作成するディレクトリ
     *
     * @var string
     */
    private $dir = 'profile-picture';


    /**
     * アップロードできる最大サイズ
     */
    const UPLOAD_MAX_SIZE = '2MB';


    /**
     * アップロード用ディレクトリを返す
     *
     * @return string
     */
    private function get_dir(){
        $dir = wp_upload_dir();
        return $dir['basedir'].DIRECTORY_SEPARATOR.$this->dir.DIRECTORY_SEPARATOR;
    }

    /**
     * ユーザーのプロフィール保存ディレクトリを返す
     *
     * @param int $user_id
     * @return string
     */
    private function get_user_dir($user_id){
        return $this->get_dir().$user_id.DIRECTORY_SEPARATOR;
    }

    /**
     * ディレクトリのURLを返す
     *
     * @return string
     */
    private function get_url(){
        $dir = wp_upload_dir();
        $url = $dir['baseurl'].'/'.$this->dir;
        if( is_ssl() ){
            $url = str_replace('http:', 'https:', $url);
        }
        if( !is_admin() ){
            $url = str_replace('://', '://s.', $url);
        }
        return $url;
    }

    /**
     * ディレクトリが存在するかを返す
     *
     * @param int $user_id
     * @return boolean
     */
    public function has_profile_pic( $user_id ){
        return file_exists($this->get_user_dir($user_id));
    }

    /**
     * ユーザーのアップロードディレクトリを返す
     *
     * @param int $user_id
     * @return string
     */
    private function get_user_url($user_id){
        return $this->get_url().'/'.$user_id.'/';
    }

    /**
     * ファイルをアップロードする
     *
     * @param array $file
     * @param int $user_id
     * @throws \Exception
     */
    public function upload(array $file, $user_id){
        $path = $file['tmp_name'];
        if( filesize($path) > $this->get_allowed_size(true) ){
            throw new \Exception(sprintf('ファイルサイズが大き過ぎます。アップロードできるのは%sまでです。', $this->get_allowed_size()), 500);
        }
        if( !$this->image->mime->is_image($path) ){
            throw new \Exception('アップロードされたファイルの形式が不正です。アップロードできるのはJPEG, GIF, PNGだけです。', 500);
        }
        if( !is_writable($this->get_dir()) ){
            throw new \Exception('ディレクトリに書き込みできません。管理者に連絡してください', 500);
        }
        // 以前のものを削除してディレクトリを作成
        $this->remove_dir( $this->get_user_dir($user_id) );
        mkdir($this->get_user_dir($user_id));
        //新しいファイル名を作成し、移動
        $ext = $this->image->mime->get_extension($path);
        $dest_path = $this->get_user_dir($user_id).'profile.'.$ext;
        if( !move_uploaded_file($path, $dest_path) ){
            throw new \Exception('ファイルを保存できませんでした。時間をおいて試してみてください。', 500);
        }
        // ファイルの大きさをチェックし、300 x 300より小さかったら拡大して保存
        // 必要なければそのまま
        $size = $this->image->get_image_size($dest_path);
        if( $size[0] < 300 || $size[1] < 300){
            $resized = $this->image->fit($path,  $this->get_user_dir($user_id).'profile-300x300.'.$ext, 300, 300);
        }else{
            //正方形にリサイズ
            $resized = $this->image->trim($dest_path, 300, 300, true, null);
        }
        if( !$resized || is_wp_error($resized) ){
            throw new \Exception('画像のリサイズに失敗しました。');
        }
    }

    /**
     * アバターをフィルタリングする
     *
     * @param string $avatar
     * @param string|int $id_or_email
     * @param int $size
     * @param string $default
     * @param string $alt
     * @return string
     */
    public function get_avatar($avatar, $id_or_email, $size, $default, $alt){
        $user_id = 0;
        if( is_numeric($id_or_email) ){
            $user_id = $id_or_email;
        }elseif( is_object($id_or_email )){
            if( $id_or_email->user_id > 0 ){
                $user_id = $id_or_email->user_id;
            }
        }else{
            $user_id = email_exists($id_or_email);
        }
        if( file_exists($this->get_user_dir($user_id)) ){
            //オリジナルファイルの取得
            $file_lists = glob($this->get_user_dir($user_id)."profile.*");
            if( !empty($file_lists) ){
                $orig_file = current($file_lists);
                $ext = preg_replace("/^.*\.(jpe?g|gif|png)$/i", '$1', $orig_file);
                if( !file_exists($this->get_user_dir($user_id)."profile-{$size}x{$size}.{$ext}") ){
                    //指定サイズがないので作る
                    $this->image->trim($orig_file, $size, $size, true, null);
                }
                //指定サイズのファイルが存在するので書き換え
                $avatar = preg_replace('/src=\'.*?\'/', 'src="'.$this->get_user_url($user_id)."profile-{$size}x{$size}.{$ext}\"", $avatar);
            }
        }
        return $avatar;
    }

    /**
     * ユーザーが削除された時のフィルター
     *
     * @param int $user_id
     */
    public function delete_user($user_id){
        $this->remove_dir($this->get_user_dir($user_id));
    }

    /**
     * 許可されたファイルサイズを指定する
     *
     * @param bool $in_bit
     * @return int
     */
    public function get_allowed_size($in_bit = false){
        return $in_bit ? intval(self::UPLOAD_MAX_SIZE) * 1024 * 1024 : self::UPLOAD_MAX_SIZE;
    }

    /**
     * フックを登録する
     */
    public static function register_hook(){
        static $hooked = false;
        if( !$hooked ){
            $instance = self::get_instance();
            //ユーザーが削除されたとき
            add_action('delete_user', [$instance, 'delete_user']);
            //avatarのフィルター
            add_filter('get_avatar', [$instance, 'get_avatar'], 10, 5);
            $hooked = true;
        }
    }

    /**
     * ゲッター
     *
     * @param string $name
     * @return null|Singleton
     */
    public function __get( $name ){
        switch( $name ){
            case 'image':
                return Image::get_instance();
                break;
            default:
                return null;
                break;
        }
    }
}
