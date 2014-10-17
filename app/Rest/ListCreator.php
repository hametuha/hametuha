<?php

namespace Hametuha\Rest;


use WPametu\API\Rest\RestJSON;


class ListCreator extends RestJSON
{

	/**
	 * @var string
	 */
	public static $prefix = 'list/edit';


	/**
	 * リストを作成する
	 *
	 * @return array
	 */
	public function post_create(){
		try{
			// 権限チェック
			if( !current_user_can('read') || !wp_verify_nonce($this->input->post('_wpnonce'), 'list-add') ){
				$this->auth_error();
			}
			// タイトルチェック
			if( !$this->input->post('list-name') ){
				throw new \Exception('タイトルは必須です。');
			}
			// 保存
			$post_arr = [
				'post_author' => get_current_user_id(),
				'post_title' => $this->input->post('list-name'),
				'post_excerpt' => $this->input->post('list-excerpt'),
				'post_status' => $this->input->post('list-status') == 'publish' ? 'publish' : 'private',
				'post_type' => 'lists',
			];
			$post_id = wp_insert_post($post_arr, true);
			if( is_wp_error($post_id) ){
				throw new \Exception('保存に失敗しました。後でやり直してください。');
			}
			// オススメかどうか
			if( current_user_can('edit_others_post') && 'recommended' == $this->input->post('list-editor-option') ){
				update_post_meta($post_id, '_recommended_list', 1);
			}
			return [
				'success' => true,
				'post' => get_post($post_id),
			];
		}catch ( \Exception $e ){
			return [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}
	}

	public function post_save($post_id){

	}

	/**
	 * フォームを返す
	 *
	 * @return array
	 */
	public function get_form( $post_id = 0 ){
		if( current_user_can('read') ){
			$nonce = wp_nonce_field('list-add', '_wpnonce', false, false);
			$action = home_url('list/edit/create');
			$html = <<<HTML
<form action="{$action}" method="post" class="list-create-form">
{$nonce}
 <div class="form-group">
    <label for="list-name">リスト名 <span class="label label-danger">必須</span></label>
    <input type="text" class="form-control" id="list-name" name="list-name" placeholder="ex. マイベスト短編">
  </div>
  <div class="form-group">
    <label for="list-excerpt">説明文</label>
    <textarea class="form-control" id="list-excerpt" name="list-excerpt" placeholder="ex. 私が一番いいと思う短編集だけ集めました。"></textarea>
  </div>
  <div class="form-group">
  	<label for="list-status">公開状態</label>
  	<select class="form-control" id="list-status" name="list-status">
  		<option value="publish">公開</option>
  		<option value="private">非公開（自分専用）</option>
  	</select>
  </div>
HTML;
			if( current_user_can('edit_others_posts') ){
				$html .= <<<HTML
  <div class="form-group">
  	<label for="list-editor-option">オプション <span class="label label-default">編集者専用</span></label>
  	<select class="form-control" id="list-editor-option" name="list-editor-option">
  		<option value="">個人用のリストとして利用</option>
  		<option value="recommended">おすすめリスト（トップページに公開）</option>
  	</select>
  </div>
HTML;
			}
			$html .= <<<HTML
	<p>
	<input type="submit" class="btn btn-primary btn-block btn-large" value="作成" />
	</p>
</form>
HTML;

		}else{
			$url = esc_url(wp_login_url('/'));
			$html = <<<HTML
<div class="alert alert-warning">リストを作成するには<a href="{$url}" class="alert-link">ログイン</a>する必要があります。すぐ終わりますので、ご検討ください。</div>
HTML;
		}
		return ['html' => $html];
	}


	/**
	 * リスト作成フォームのリンクを返す
	 *
	 * @return string
	 */
	public static function form_link(){
		return wp_nonce_url(home_url(self::$prefix.'/form'), 'list-form');
	}

	/**
	 * リスト保存用のURLを返す
	 *
	 * @param int $post_id
	 *
	 * @return string|void
	 */
	public static function save_link( $post_id ){
		return home_url(sprintf('%s/save/%d/', self::$prefix, $post_id), 'https');
	}
}
