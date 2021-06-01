<?php

namespace Hametuha\Rest;


use Hametuha\Model\Lists;
use WPametu\API\Rest\RestJSON;

/**
 * ListCreator
 *
 * @package Hametuha\Rest
 * @property-read Lists $lists
 */
class ListCreator extends RestJSON {


	/**
	 * @var string
	 */
	public static $prefix = 'list/edit';

	/**
	 * @var array
	 */
	protected $models = [
		'lists' => Lists::class,
	];

	/**
	 * リストを作成する
	 *
	 * @param int $post_id
	 * @return array
	 */
	public function post_create( $post_id = 0 ) {
		try {
			// nonceチェック
			if ( ! wp_verify_nonce( $this->input->post( '_wpnonce' ), 'list-edit' ) ) {
				$this->auth_error();
			}
			// 権限チェック
			if ( ( ! $post_id && ! current_user_can( 'read' ) ) || ( $post_id && ! current_user_can( 'edit_post', $post_id ) ) ) {
				$this->auth_error();
			}
			// リストチェック
			if ( $post_id ) {
				$list = get_post( $post_id );
				if ( 'lists' !== $list->post_type ) {
					throw new \Exception( '不正なリストを編集しようとしています。' );
				}
			}
			// タイトルチェック
			if ( ! $this->input->post( 'list-name' ) ) {
				throw new \Exception( 'タイトルは必須です。' );
			}
			// 保存
			$post_arr = [
				'post_author'  => get_current_user_id(),
				'post_title'   => $this->input->post( 'list-name' ),
				'post_excerpt' => $this->input->post( 'list-excerpt' ),
				'post_status'  => $this->input->post( 'list-status' ) == 'publish' ? 'publish' : 'private',
				'post_type'    => 'lists',
			];
			if ( $post_id ) {
				$post_arr['ID'] = $post_id;
				if ( is_wp_error( wp_update_post( $post_arr, true ) ) ) {
					throw new \Exception( '保存に失敗しました。後でやり直してください。' );
				}
			} else {
				$post_id = wp_insert_post( $post_arr, true );
				if ( is_wp_error( $post_id ) ) {
					throw new \Exception( '保存に失敗しました。後でやり直してください。' );
				}
			}
			// オススメかどうか
			if ( current_user_can( 'edit_others_posts' ) && 'recommended' == $this->input->post( 'list-editor-option' ) ) {
				$this->lists->mark_as_recommended( $post_id );
			} else {
				$this->lists->not_recommended( $post_id );
			}
			return [
				'success' => true,
				'post'    => get_post( $post_id ),
			];
		} catch ( \Exception $e ) {
			return [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}
	}

	/**
	 * 保存する
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function post_save( $post_id ) {
		try {
			// 権限チェック
			if ( ! current_user_can( 'read' ) || ! wp_verify_nonce( $this->input->post( '_wpnonce' ), 'list-save' ) ) {
				$this->auth_error();
			}
			// 投稿をチェック
			$post = get_post( $post_id );
			if ( ! $post || 'post' !== $post->post_type ) {
				throw new \Exception( '指定された作品はリストに追加できません。' );
			}
			// リストをチェック
			$lists = (array) $this->input->post( 'lists' );
			foreach ( $lists as $list_id ) {
				$list = get_post( $list_id );
				if ( 'lists' !== $list->post_type || ! $this->lists->user_can( $list->ID, get_current_user_id() ) ) {
					throw new \Exception( 'あなたにはこのリストを編集する権限がありません。' );
				}
			}
			// O.K.
			$this->lists->bulk_register( $post->ID, $lists );
			return [
				'success' => true,
				'message' => '作品をリストに保存しました。',
			];
		} catch ( \Exception $e ) {
			return [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}
	}

	/**
	 * 削除する
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function post_delete( $post_id ) {
		try {
			// 権限チェック
			if ( ! $post_id || ! wp_verify_nonce( $this->input->get( '_wpnonce' ), 'list-delete' ) || ! current_user_can( 'edit_post', $post_id ) ) {
				$this->auth_error();
			}
			// 投稿タイプチェック
			$list = get_post( $post_id );
			if ( ! $list || 'lists' !== $list->post_type ) {
				throw new \Exception( '指定された作品はリストに追加できません。' );
			}
			// 削除する
			if ( ! wp_delete_post( $post_id, true ) ) {
				throw new \Exception( '削除できませんでした。あとでやり直してください。' );
			}
			return [
				'success' => true,
				'message' => '削除しました。あなたのリスト一覧に移動します。',
				'url'     => home_url( '/your/lists/', 'https' ),
			];
		} catch ( \Exception $e ) {
			return [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}
	}


	/**
	 * 投稿から削除する
	 *
	 * @param int $list_id
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function post_deregister( $list_id, $post_id ) {
		try {
			// 権限チェック
			if ( ! $post_id || ! $list_id || ! wp_verify_nonce( $this->input->get( '_wpnonce' ), 'list-deregister' ) || ! current_user_can( 'edit_post', $list_id ) ) {
				$this->auth_error();
			}
			// 投稿タイプチェック
			$list = get_post( $list_id );
			if ( ! $list || 'lists' !== $list->post_type ) {
				throw new \Exception( 'リストの指定が不正です。。' );
			}
			// 削除する
			if ( ! $this->lists->deregister( $list_id, $post_id ) ) {
				throw new \Exception( 'リストから削除できませんでした。あとでやり直してください。' );
			}
			return [
				'success'  => true,
				'message'  => '削除しました。あなたのリスト一覧に移動します。',
				'home_url' => home_url( '/your/lists/', 'https' ),
			];
		} catch ( \Exception $e ) {
			return [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}
	}

	/**
	 * フォームを返す
	 *
	 * @param int $post_id
	 * @return array
	 */
	public function get_form( $post_id = 0 ) {
		nocache_headers();
		try {
			// 追加の場合、ログイン必須
			if ( ! $post_id && ! current_user_can( 'read' ) ) {
				$url = esc_url( wp_login_url( '/your/lists/' ) );
				throw new \Exception( 'リストを作成するには<a href="{$url}" class="alert-link">ログイン</a>する必要があります。すぐ終わりますので、ご検討ください。' );
			}
			// 編集の場合、権限と投稿タイプをチェック
			if ( $post_id ) {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					throw new \Exception( 'あなたにはこのリストを編集する権限がありません。' );
				}
				$list = get_post( $post_id );
				if ( 'lists' != $list->post_type ) {
					throw new \Exception( 'このリストは存在しません。' );
				}
				$title       = esc_attr( $list->post_title );
				$excerpt     = esc_textarea( $list->post_excerpt );
				$published   = 'publish' == $list->post_status ? ' selected' : '';
				$private     = 'private' == $list->post_status ? ' selected' : '';
				$recommended = $this->lists->is_recommended( $list->ID ) ? ' selected' : '';
				$label       = '更新';
			} else {
				$title = $except = $published = $private = $recommended = '';
				$label = '作成';
			}
			// フォームを作成する
			$nonce  = wp_nonce_field( 'list-edit', '_wpnonce', false, false );
			$action = home_url( 'list/edit/create' . ( $post_id ? '/' . $post_id : '' ) );

			$html = <<<HTML
<form action="{$action}" method="post" class="list-create-form">
{$nonce}
 <div class="form-group">
    <label for="list-name">リスト名 <span class="label label-danger">必須</span></label>
    <input type="text" class="form-control" id="list-name" name="list-name" placeholder="ex. マイベスト短編" value="{$title}">
  </div>
  <div class="form-group">
    <label for="list-excerpt">説明文</label>
    <textarea class="form-control" id="list-excerpt" name="list-excerpt" placeholder="ex. 私が一番いいと思う短編集だけ集めました。">{$excerpt}</textarea>
  </div>
  <div class="form-group">
  	<label for="list-status">公開状態</label>
  	<select class="form-control" id="list-status" name="list-status">
  		<option value="publish"{$published}>公開</option>
  		<option value="private"{$private}>非公開（自分専用）</option>
  	</select>
  </div>
HTML;
			if ( current_user_can( 'edit_others_posts' ) ) {
				$html .= <<<HTML
  <div class="form-group">
  	<label for="list-editor-option">オプション <span class="label label-default">編集者専用</span></label>
  	<select class="form-control" id="list-editor-option" name="list-editor-option">
  		<option value="">個人用のリストとして利用</option>
  		<option value="recommended"{$recommended}>おすすめリスト（トップページに公開）</option>
  	</select>
  </div>
HTML;
			}
			$html .= <<<HTML
	<p>
	<input type="submit" class="btn btn-primary btn-block btn-large" value="{$label}" />
	</p>
</form>
HTML;

		} catch ( \Exception $e ) {
			$html = sprintf( '<div class="alert alert-warning">%s</div>', $e->getMessage() );
		}
		if ( ! $post_id && current_user_can( 'read' ) ) {

		}
		return [ 'html' => $html ];
	}


	/**
	 * リスト作成フォームのリンクを返す
	 *
	 * @param int $post_id
	 * @return string
	 */
	public static function form_link( $post_id = 0 ) {
		return wp_nonce_url( home_url( self::$prefix . '/form' . ( $post_id ? '/' . intval( $post_id ) : '' ) ), 'list-form' );
	}

	/**
	 * リスト保存用のURLを返す
	 *
	 * @param int $post_id
	 *
	 * @return string|void
	 */
	public static function save_link( $post_id ) {
		return home_url( sprintf( '%s/save/%d/', self::$prefix, $post_id ) );
	}

	/**
	 * リスト削除用のリンクを返す
	 *
	 * @param int $post_id
	 *
	 * @return string|void
	 */
	public static function delete_link( $post_id ) {
		return wp_nonce_url( home_url( sprintf( '%s/delete/%d', self::$prefix, $post_id ) ), 'list-delete' );
	}

	/**
	 * リスト解除用のリンクを返す
	 *
	 * @param int $list_id
	 * @param int $post_id
	 *
	 * @return string
	 */
	public static function deregister_link( $list_id, $post_id ) {
		return wp_nonce_url( home_url( self::$prefix . '/deregister/' . $list_id . '/' . $post_id ), 'list-deregister' );
	}
}
