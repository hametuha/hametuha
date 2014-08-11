<?php

namespace Hametuha\Admin;



use WPametu\Pattern\Singleton;



class Announcement extends Singleton
{

    private $version = '1.0';

    private $nonce_name = 'hametuha_announcement';

    const START_OPTION = '_hametuha_announcement_start';

    const END_OPTION = '_hametuha_announcement_end';

    const NOTICE = '_hametuha_announcement_notice';


    const PLACE = '_hametuha_announcement_place';

    const ADDRESS = '_hametuha_announcement_address';

    const BUILDING = '_hametuha_announcement_building';

    const ACCESS = '_hametuha_announcement_access';

    const COMMIT_START = '_hametuha_commit_start';

    const COMMIT_END = '_hametuha_commit_end';

    const COMMIT_CONDITION = '_hametuha_commit_condition';

    const COMMIT_COST = '_hametuha_commit_cost';

    const COMMIT_LIMIT = '_hametuha_commit_limit';

    const COMMIT_TYPE = '_hametuha_commit_type';

    const COMMIT_TO = '_hametuha_commit_to';

    const COMMIT_FILE = '_hametuha_commit_file';

    const COMMIT_POST_TYPE = '_hametuha_commit_post_type';

    const COMMIT_CATEGORY = '_hametuha_commit_category';

    protected function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('save_post', array($this, 'save_post'), 10, 2);
        add_action('wp_ajax_hametuha_participate', array($this, 'ajax'));
    }

    public function admin_init(){
        add_meta_box('hametuha-announcement', '告知イベントの開催場所<small>（※必要な場合のみ）</small>', array($this, 'metabox_period'), 'announcement', 'normal', 'low');
        if( current_user_can('edit_others_posts') ){
            add_meta_box('hametuha-participate', '告知イベントの参加・募集形態<small>（※必要な場合のみ）</small>', array($this, 'metabox_participate'), 'announcement', 'normal', 'high');
        }
    }

    public function admin_enqueue_scripts( $page ){
        $screen = get_current_screen();
        if( 'post' == $screen->base && 'announcement' == $screen->post_type ){
            wp_enqueue_script('announcement-helper', get_template_directory_uri().'/assets/js/announcement-helper.js', array('gmap'), $this->version, false);
            wp_enqueue_style('jquery-ui-smoothness');
        }
    }

    /**
     * @param \WP_Post $post
     */
    public function metabox_period($post){
        wp_nonce_field($this->nonce_name, '_hametunonce', false);
        ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th><label for="announcement_notice">備考: </label></th>
                <td>
                    <textarea cols="80" rows="3" id="announcement_notice" name="announcement_notice"><?php echo esc_html(get_post_meta($post->ID, self::NOTICE, true)); ?></textarea>
                    <p class="description">
                        ※日時が複数日にまたがる場合は時刻が表示されません。<strong>展示などのイベントの場合は、定休日や開業時間などを記載してください。</strong><br />
                        ※備考欄にURLを含める場合は改行して1行にURLを入れてください。その場合だけリンクされます。<br />
                        ※特に期限を設けない場合は空白にしておいてください。終了のみ指定した場合は、投稿の公開日が開始日になります。
                    </p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="announcement_place">場所の名前: </label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="announcement_place" name="announcement_place" value="<?php echo esc_attr(get_post_meta($post->ID, self::PLACE, true));?>" />
                    <span class="description">※空白にすると地図は表示されません</span>
                </td>
            </tr>
            <tr>
                <th><label for="announcement_address">住所: </label></th>
                <td>
                    <input type="text" id="announcement_address" name="announcement_address" class="regular-text" value="<?php echo esc_attr(get_post_meta($post->ID, self::ADDRESS, true));?>" />
                </td>
            </tr>
            <tr>
                <th><label for="announcement_building">建物: </label></th>
                <td><input class="regular-text" type="text" id="announcement_building" name="announcement_building" value="<?php echo esc_attr(get_post_meta($post->ID, self::BUILDING, true));?>" /></td>
            </tr>
            <tr>
                <th><a id="announcement_address_search1" class="button" href="#">住所チェック</a></th>
                <td>
                    <p class="description">
                        マップ上のピンが正しく入力されているかを確認してください。
                        建物名などが入った住所はマップに表示されない可能性があります。
                        その場合は建物・施設名を「建物」に入力してください。
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
        <div id="announcement_map" style="height:300px;border:1px solid #ccc; background:#ddd;"></div>
    <?php
    }

    public function metabox_participate($post){
        ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th>募集形態</th>
                <td>
                    <label><input type="radio" name="commit_type" value="0" <?php if(!get_post_meta($post->ID, self::COMMIT_TYPE, true)) echo ' checked="checked"';?>/>募集はしない</label>
                    <label><input type="radio" name="commit_type" value="1" <?php if(1 == get_post_meta($post->ID, self::COMMIT_TYPE, true)) echo ' checked="checked"';?>/>メールでの応募</label>
                    <label><input type="radio" name="commit_type" value="2" <?php if(2 == get_post_meta($post->ID, self::COMMIT_TYPE, true)) echo ' checked="checked"';?>/>特定の投稿を期間内に行う</label>
                    <label><input type="radio" name="commit_type" value="3" <?php if(3 == get_post_meta($post->ID, self::COMMIT_TYPE, true)) echo ' checked="checked"';?>/>参加意志の表明</label>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="form-table" id="commit-conditions"<?php if(!get_post_meta($post->ID, self::COMMIT_TYPE, true)) echo ' style="display:none;"';?>>
            <tr>
                <th>募集期間</th>
                <td>
                    <label>開始: <input type="text" class="timepicker" name="commit_start" value="<?php echo esc_attr(get_post_meta($post->ID, self::COMMIT_START, true));?>" /></label>
                    <label>〜終了: <input type="text" class="timepicker" name="commit_end" value="<?php echo esc_attr(get_post_meta($post->ID, self::COMMIT_END, true));?>" /></label>
                    <p class="description">
                        募集が必要ない告知の場合は空白にしてください。
                    </p>
                </td>
            </tr>
            <tr>
                <th><lable for="commit_condition">応募条件詳細</lable></th>
                <td>
                    <textarea cols="80" rows="5" id="commit_condition" name="commit_condition"><?php echo esc_html(get_post_meta($post->ID, self::COMMIT_CONDITION, true)); ?></textarea>
                    <p class="description">
                        URLを含める場合は、改行してURLだけからなる行に収めるとリンクされます。
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="commit_cost">必要経費</label></th>
                <td><input class="small-text" type="text" name="commit_cost" id="commit_cost" value="<?php echo esc_attr(get_post_meta($post->ID, self::COMMIT_COST, true) ?: 0);?>" />円</td>
            </tr>
            <tr>
                <th><label for="commit_limit">参加人数</label></th>
                <td>
                    <input class="small-text" type="text" name="commit_limit" id="commit_limit" step="1" value="<?php echo esc_attr(get_post_meta($post->ID, self::COMMIT_LIMIT, true) ?: 0);?>" />
                    <span class="description">0にすると制限なしになります</span>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="form-table" id="commit_1"<?php if(1 != get_post_meta($post->ID, self::COMMIT_TYPE, true)) echo ' style="display:none;"';?>>
            <tbody>
            <tr>
                <th><label for="commit_to">宛先</label></th>
                <td><input class="regular-text" type="text" name="commit_to" id="commit_to" value="<?php echo esc_attr(get_post_meta($post->ID, self::COMMIT_TO, true)); ?>" /></td>
            </tr>
            <tr>
                <th>添付ファイル</th>
                <td><label><input type="checkbox" name="commit_file" id="commit_file" value="1"<?php if(get_post_meta($post->ID, self::COMMIT_FILE, true)) echo ' checked="checked"';?> />添付ファイルを必須にする</label></td>
            </tr>
            </tbody>
        </table>
        </table>
        <table class="form-table" id="commit_2"<?php if(2 != get_post_meta($post->ID, self::COMMIT_TYPE, true)) echo ' style="display:none;"';?>>
            <tbody>
            <tr>
                <th>指定する投稿タイプ</th>
                <td>
                    <label><input type="radio" name="commit_post_type" value="post" <?php if('post' == get_post_meta($post->ID, self::COMMIT_POST_TYPE, true)) echo 'checked="checked" ';?>/>投稿</label>
                    <label><input type="radio" name="commit_post_type" value="anpi" <?php if('anpi' == get_post_meta($post->ID, self::COMMIT_POST_TYPE, true)) echo 'checked="checked" ';?>/>安否情報</label>
                </td>
            </tr>
            <tr>
                <th>指定するカテゴリー</th>
                <td>
                    <?php $categories = get_post_meta($post->ID, self::COMMIT_CATEGORY, true); foreach(array('post' => 'category', 'anpi' => 'anpi_cat') as $post_type  => $cateogry):?>
                        <p id="commit_cat_<?php echo $post_type;?>"<?php if($post_type != get_post_meta($post->ID, self::COMMIT_POST_TYPE, true)) echo ' style="display:none;"';?>>
                            <?php foreach(get_terms($cateogry, array('hide_empty' => false)) as $tax): ?>
                                <label><input type="checkbox" name="commit_<?php echo $post_type; ?>[]" value="<?php echo $tax->term_id; ?>" <?php if(is_array($categories) && false !== array_search($tax->term_id, $categories)) echo 'checked="checked"';?>/><?php echo $tax->name;?></label>&nbsp;
                            <?php endforeach;?>
                        </p>
                    <?php endforeach; ?>
                    <p class="desdcription">
                        カテゴリーを指定しない場合は条件として表示されません。
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="form-table" id="commit_3"<?php if(3 != get_post_meta($post->ID, self::COMMIT_TYPE, true)) echo ' style="display:none;"';?>>
            <tbody>
            <tr>
                <th>参加者へのメール送信</th>
                <td>
                    <textarea cols="80" rows="10"></textarea>
                    <p style="text-align: right;">
                        <small><img src="<?php echo get_template_directory_uri(); ?>/img/ajax-loader.gif" width="16" height="11" alt="Sending..." />&nbsp;送信中...</small>
                        <a href="#" id="hametuha-commmit-notify" class="button-primary">メール送信</a>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
    <?php
    }

    public function save_post($post_id){
        //自動保存の場合は駄目
        if(defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE){
            return;
        }
        //投稿タイプをチェック
        if(get_post_type($post_id) != 'announcement'){
            return;
        }
        //nonceチェック
        if(!isset($_POST['_hametunonce']) || !wp_verify_nonce($_POST['_hametunonce'], $this->nonce_name)){
            return;
        }
        //開始日時を保存
        if (isset($_POST['annnoucement_start'])){
            if($this->is_datetime($_POST['annnoucement_start'])){
                update_post_meta($post_id, self::START_OPTION, $_POST['annnoucement_start']);
            }else{
                delete_post_meta($post_id, self::START_OPTION);
            }
        }
        //終了日時を保存
        if (isset($_POST['annnoucement_end'])){
            if($this->is_datetime($_POST['annnoucement_end'])){
                update_post_meta($post_id, self::END_OPTION, $_POST['annnoucement_end']);
            }else{
                delete_post_meta($post_id, self::END_OPTION);
            }
        }
        //注記を保存
        if (isset($_POST['announcement_notice'])){
            if(!empty($_POST['announcement_notice'])){
                update_post_meta($post_id, self::NOTICE, $_POST['announcement_notice']);
            }else{
                delete_post_meta($post_id, self::NOTICE);
            }
        }
        //開催場所を保存
        if (isset($_POST['announcement_place'])){
            if(!empty($_POST['announcement_place'])){
                update_post_meta($post_id, self::PLACE, $_POST['announcement_place']);
            }else{
                delete_post_meta($post_id, self::PLACE);
            }
        }
        //住所を保存
        if (isset($_POST['announcement_address'])){
            if(!empty($_POST['announcement_address'])){
                update_post_meta($post_id, self::ADDRESS, $_POST['announcement_address']);
            }else{
                delete_post_meta($post_id, self::ADDRESS);
            }
        }
        //建物名を保存
        if (isset($_POST['announcement_building'])){
            if(!empty($_POST['announcement_building'])){
                update_post_meta($post_id, self::BUILDING, $_POST['announcement_building']);
            }else{
                delete_post_meta($post_id, self::BUILDING);
            }
        }
        //応募形態
        if(isset($_POST['commit_type']) && current_user_can('edit_others_posts')){
            if(is_numeric($_POST['commit_type']) && false !== array_search($_POST['commit_type'], range(1, 3))){
                update_post_meta($post_id, self::COMMIT_TYPE, $_POST['commit_type']);
                //募集期間
                if($this->is_datetime($_POST['commit_start'])){
                    update_post_meta($post_id, self::COMMIT_START, $_POST['commit_start']);
                }else{
                    delete_post_meta($post_id, self::COMMIT_START);
                }
                if($this->is_datetime($_POST['commit_end'])){
                    update_post_meta($post_id, self::COMMIT_END, $_POST['commit_end']);
                }else{
                    delete_post_meta($post_id, self::COMMIT_END);
                }
                //条件
                if(!empty($_POST['commit_condition'])){
                    update_post_meta($post_id, self::COMMIT_CONDITION, $_POST['commit_condition']);
                }else{
                    delete_post_meta($post_id, self::COMMIT_CONDITION);
                }
                //経費
                if(is_numeric($_POST['commit_cost'])){
                    update_post_meta($post_id, self::COMMIT_COST, absint($_POST['commit_cost']));
                }else{
                    delete_post_meta($post_id, self::COMMIT_COST);
                }
                //人数
                if(is_numeric($_POST['commit_limit'])){
                    update_post_meta($post_id, self::COMMIT_LIMIT, absint($_POST['commit_limit']));
                }else{
                    delete_post_meta($post_id, self::COMMIT_LIMIT);
                }
                switch($_POST['commit_type']){
                    case 1: //応募
                        if(is_email($_POST['commit_to'])){
                            update_post_meta($post_id, self::COMMIT_TO, $_POST['commit_to']);
                        }else{
                            delete_post_meta($post_id, self::COMMIT_TO);
                        }
                        if(isset($_POST['commit_file']) && $_POST['commit_file']){
                            update_post_meta($post_id, self::COMMIT_FILE, 1);
                        }else{
                            delete_post_meta($post_id, self::COMMIT_FILE);
                        }
                        break;
                    case 2: //投稿
                        if(isset($_POST['commit_post_type'])){
                            update_post_meta($post_id, self::COMMIT_POST_TYPE, $_POST['commit_post_type']);
                            if(isset($_POST['commit_'.$_POST['commit_post_type']]) && is_array($_POST['commit_'.$_POST['commit_post_type']])){
                                update_post_meta($post_id, self::COMMIT_CATEGORY, $_POST['commit_'.$_POST['commit_post_type']]);
                            }
                        }else{
                            delete_post_meta($post_id, self::COMMIT_POST_TYPE);
                        }
                        break;
                    case 3: //意志表明
                        break;
                }
            }else{
                delete_post_meta($post_id, self::COMMIT_TYPE);
                delete_post_meta($post_id, self::COMMIT_START);
                delete_post_meta($post_id, self::COMMIT_END);
                delete_post_meta($post_id, self::COMMIT_CONDITION);
                delete_post_meta($post_id, self::COMMIT_COST);
                delete_post_meta($post_id, self::COMMIT_LIMIT);
            }
        }
    }

    public function ajax(){
        if(isset($_POST['post_id'], $_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'hametuha_participate_'.get_current_user_id()) && ($post = get_post($_POST['post_id'])) && $post->post_type == 'announcement'){
            $redirect = get_permalink($post->ID);
            if(false === array_search(get_post_meta($post->ID, self::COMMIT_TYPE, true), range(1, 3))){
                //そもそも参加できない
                $redirect .= "?error-message=0";
            }elseif(left_second_to_participate($post) == 0){
                //期限切れで参加不可能
                $redirect .= '?error-message=1';
            }elseif(get_post_meta ($post->ID, self::COMMIT_LIMIT, true) > 0 && false){
                //定員オーバーで参加不可
                $redirect .= "?error-message=2";
            }elseif(false){
                //参加済みなので参加不可
                $redirect .= "?error-message=3";
            }else{
                //参加オーケー
                switch (get_post_meta($post->ID, self::COMMIT_TYPE, true)) {
                    case 1:  //メール送信
                        $to = get_post_meta($post->ID, self::COMMIT_TO, true);
                        $header = 'From: 破滅派イベント管理システム <'.get_option('admin_email').'>'."\r\n";
                        $subject = "破滅派応募: ".$post->post_title;
                        //本文をチェック
                        if(isset($_POST['mail_body'])){
                            $body = (string)$_POST['mail_body'];
                        }else{
                            _hametuha_wp_die('不正なアクセスです。メール本文が入力されていません。');
                            break;
                        }
                        global $user_identity, $user_level;
                        $user_type = $user_level > 0 ? '同人' : '読者';
                        $body = <<<EOS
イベント開催者様


めつかれさまです。破滅派です。
開催された下記のイベントに応募がありました。

---------
イベント名:
{$post->post_title}
{$redirect}

応募者名: {$user_identity}
応募者種別:{$user_type}
---------

[応募者からのメッセージ]
{$body}

※このメールは自動送信です。
不明な点があれば、破滅派お問い合わせページよりご連絡ください。
http://hametuha.com/inquiry
EOS;
                        $attachments = array();
                        //ファイル必須なので、ファイルをチェック
                        if(get_post_meta($post->ID, self::COMMIT_FILE, true)){
                            if(!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name']) || $_FILES['file']['error'] != 0){
                                $redirect .= '?error-message=4';
                                break;
                            }else{
                                $dir = wp_upload_dir();
                                $path = $dir['basedir'].DIRECTORY_SEPARATOR.sanitize_file_name(basename($_FILES['file']['name']));
                                if(!move_uploaded_file($_FILES['file']['tmp_name'], $path)){
                                    $redirect .= '?error-message=5';
                                    break;
                                }else{
                                    $attachments[] = $path;
                                }
                            }
                        }
                        if(wp_mail($to, $subject, $body, $header, $attachments)){
                            $redirect .= '?message=1';
                        }else{
                            $redirect .= '?error-message=6';
                        }
                        if(isset($path) && file_exists($path)){
                            unlink($path);
                        }
                        break;
                    case 2:
                        break;
                    case 3:
                        break;
                }
            }
            header('Location: '.$redirect."#participating-form");
        }else{
            _hametuha_wp_die('不正なアクセスです。');
        }
        exit;
    }

    /**
     * 文字列がDateTime形式か否かを判別する
     * @param string $str
     * @return boolean
     */
    private function is_datetime($str){
        return (boolean)preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $str);
    }
}
