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
class Picture extends Singleton {

	use Path;

	/**
	 * wp-uploadsフォルダに作成するディレクトリ
	 *
	 * @var string
	 */
	private $dir = 'profile-picture';

	/**
	 * User meta key
	 *
	 * @var string
	 */
	private $user_meta_key = '_profile_picture';

	/**
	 * Post meta key
	 *
	 * @var string
	 */
	public $post_meta_key = '_is_profile_pic';

	/**
	 * アップロードできる最大サイズ
	 */
	const UPLOAD_MAX_SIZE = '2MB';


	/**
	 * アップロード用ディレクトリを返す
	 *
	 * @return string
	 */
	public function get_dir() {
		$dir = wp_upload_dir();

		return $dir['basedir'] . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR;
	}

	/**
	 * ユーザーのプロフィール保存ディレクトリを返す
	 *
	 * @param int $user_id
	 *
	 * @return string
	 */
	private function get_user_dir( $user_id ) {
		return $this->get_dir() . $user_id . DIRECTORY_SEPARATOR;
	}

	/**
	 * ディレクトリのURLを返す
	 *
	 * @return string
	 */
	private function get_url() {
		$dir = wp_upload_dir();
		$url = $dir['baseurl'] . '/' . $this->dir;
		if ( is_ssl() ) {
			$url = str_replace( 'http:', 'https:', $url );
		}
		if ( ! is_admin() ) {
			$url = str_replace( '://', '://s.', $url );
		}

		return $url;
	}

	/**
	 * ディレクトリが存在するかを返す
	 *
	 * @param int $user_id
	 * @param bool $deprecated trueにすると、昔のアップロード方法で取得
	 *
	 * @return int
	 */
	public function has_profile_pic( $user_id, $deprecated = false ) {
		if ( $deprecated ) {
			return (int) file_exists( $this->get_user_dir( $user_id ) );
		} else {
			return (int) get_user_meta( $user_id, $this->user_meta_key, true );
		}
	}

	/**
	 * ユーザーのアップロードディレクトリを返す
	 *
	 * @param int $user_id
	 *
	 * @return string
	 */
	private function get_user_url( $user_id ) {
		return $this->get_url() . '/' . $user_id . '/';
	}

	/**
	 * ファイルをアップロードする
	 *
	 * @param array $file
	 * @param int $user_id
	 *
	 * @throws \Exception
	 */
	public function upload( array $file, $user_id ) {
		$path = $file['tmp_name'];
		if ( filesize( $path ) > $this->get_allowed_size( true ) ) {
			throw new \Exception( sprintf( 'ファイルサイズが大き過ぎます。アップロードできるのは%sまでです。', $this->get_allowed_size() ), 500 );
		}
		if ( ! $this->image->mime->is_image( $path ) ) {
			throw new \Exception( 'アップロードされたファイルの形式が不正です。アップロードできるのはJPEG, GIF, PNGだけです。', 500 );
		}
		if ( ! is_writable( $this->get_dir() ) ) {
			throw new \Exception( 'ディレクトリに書き込みできません。管理者に連絡してください', 500 );
		}
		$this->image->include_wp_libs();
		$attachment_id = media_handle_sideload( $file, 0, '', [
			'post_author' => $user_id,
		] );
		if ( is_wp_error( $attachment_id ) || ! is_numeric( $attachment_id ) ) {
			throw new \Exception( '画像の保存に失敗しました。やり直してください。', 500 );
		}
		update_post_meta( $attachment_id, $this->post_meta_key, 1 );
		$this->assign_user_pic( $user_id, $attachment_id );
	}

	/**
	 * ユーザーのプロフィール写真を全部取得する
	 *
	 * @param int $user_id
	 * @param string $size
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_profile_pic( $user_id, $size = 'pinky', array $args = [] ) {
		$pictures = [];
		$query    = new \WP_Query( wp_parse_args( $args, [
			'post_type'      => 'attachment',
			'author'         => $user_id,
			'post_mime_type' => 'image',
			'posts_per_page' => - 1,
			'post_status'    => 'inherit',
			'meta_query'     => [
				[
					'key'   => $this->post_meta_key,
					'value' => 1
				]
			],
		] ) );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$img        = wp_get_attachment_image( get_the_ID(), $size );
				$guid       = get_the_guid();
				$pictures[] = [
					'attachment_id' => get_the_ID(),
					'guid'          => $guid,
					'img'           => $img,
					'src'           => preg_match( '/src="([^"]+)"/u', $img, $match ) ? $match[1] : $guid,
				];
			}
			wp_reset_postdata();
		}

		return $pictures;
	}

	/**
	 * ユーザーのプロフィール写真を更新する
	 *
	 * @param int $user_id
	 * @param int $attachment_id
	 *
	 * @return bool|int
	 */
	public function assign_user_pic( $user_id, $attachment_id ) {
		return update_user_meta( $user_id, $this->user_meta_key, $attachment_id );
	}

	/**
	 * ユーザーのプロフィール写真を外す
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function detach_user_pic( $user_id ) {
		return delete_user_meta( $user_id, $this->user_meta_key );
	}

	/**
	 * アバターのSRC属性を削除する
	 *
	 * @param string $avatar
	 *
	 * @return string
	 */
	public function get_avatar( $avatar ) {
		return preg_replace( '#srcset=["\'][^"\']+["\']#u', '', $avatar );
	}

	/**
	 * アバターをフィルタリングする
	 *
	 * @param string $args
	 * @param string|int|\WP_User $id_or_email
	 *
	 * @return string
	 */
	public function pre_get_avatar_data( $args, $id_or_email ) {
		$user_id = 0;
		if ( is_numeric( $id_or_email ) ) {
			$user_id = $id_or_email;
		} elseif ( is_object( $id_or_email ) ) {
			if ( $id_or_email->user_id > 0 ) {
				$user_id = $id_or_email->user_id;
			}
		} else {
			$user_id = email_exists( $id_or_email );
		}
		// ユーザーメタを取得
		if ( $user_id && $this->has_profile_pic( $user_id ) ) {
			$attachment_id = get_user_meta( $user_id, $this->user_meta_key, true );
			$size          = max( 160, $args['size'] );
			if ( $url = wp_get_attachment_image_url( $attachment_id, [ $size, $size ] ) ) {
				$args['url'] = $url;
			}
		}
		return $args;
	}

	/**
	 * ユーザーが削除された時のフィルター
	 *
	 * @param int $user_id
	 * @param int $attachment_id
	 *
	 * @return bool
	 */
	public function delete_user_pic( $user_id, $attachment_id ) {
		if ( ! $this->is_available_for( $user_id, $attachment_id ) ) {
			return false;
		}
		if ( $this->has_profile_pic( $user_id ) ) {
			delete_user_meta( $user_id, $this->user_meta_key, $attachment_id );
		}

		return delete_post_meta( $attachment_id, $this->post_meta_key, 1 );
	}


	/**
	 * ユーザーが画像を利用できるか
	 *
	 * @param int $user_id
	 * @param int $attachment_id
	 *
	 * @return bool
	 */
	public function is_available_for( $user_id, $attachment_id ) {
		$post = get_post( $attachment_id );
		if ( ! $post || $post->post_author != $user_id || ! get_post_meta( $attachment_id, $this->post_meta_key, true ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * 許可されたファイルサイズを指定する
	 *
	 * @param bool $in_bit
	 *
	 * @return int
	 */
	public function get_allowed_size( $in_bit = false ) {
		return $in_bit ? intval( self::UPLOAD_MAX_SIZE ) * 1024 * 1024 : self::UPLOAD_MAX_SIZE;
	}

	/**
	 * フックを登録する
	 */
	public static function register_hook() {
		static $hooked = false;
		if ( ! $hooked ) {
			$instance = self::get_instance();
			//avatarのフィルター
			add_filter( 'pre_get_avatar_data', [ $instance, 'pre_get_avatar_data' ], 10, 2 );
			//avatarのsrcsetを削除
			add_filter( 'get_avatar', [ $instance, 'get_avatar' ], 11 );
			$hooked = true;
		}
	}

	/**
	 * ゲッター
	 *
	 * @param string $name
	 *
	 * @return null|Singleton
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'image':
				return Image::get_instance();
				break;
			default:
				return null;
				break;
		}
	}
}
