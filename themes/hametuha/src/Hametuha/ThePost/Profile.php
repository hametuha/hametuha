<?php

namespace Hametuha\ThePost;


use WPametu\Utility\PostHelper;

/**
 * Class Profile
 * @package Hametuha\ThePost
 * @property-read string $display_name
 * @property-read string $nicename
 * @property-read string $role
 * @property-read string $description
 * @property-read int $work_count
 */
class Profile extends PostHelper {



	/**
	 * 投稿者のプロフィールページを作成する
	 *
	 * @return string
	 */
	public function permalink() {
		// TODO: プロフィールページができるまでは投稿一覧
		//return home_url(sprintf('/profile/%s/', $this->nicename));
		return get_author_posts_url( $this->post->post_author );
	}

	/**
	 * get avatar
	 *
	 * @param int $size
	 * @return string
	 */
	public function avatar( $size ) {
		return get_avatar( $this->post->post_author, $size );
	}

	/**
	 * 登録日を返す
	 *
	 * @param bool $format
	 * @return int|string
	 */
	public function registered_date( $format = true ) {
		$date = get_the_author_meta( 'user_registered', $this->post->post_author );
		if ( $format ) {
			return mysql2date( get_option( 'date_format' ), $date );
		} else {
			return $date;
		}
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'furigana':
				$name = 'last_name';
			case 'display_name':
			case 'nicename':
			case 'description':
				return get_the_author_meta( $name, $this->post->post_author );
				break;
			case 'work_count':
			case 'score':
				return (int) $this->post->{$name};
				break;
			case 'role':
				return hametuha_user_role( $this->post->post_author );
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}
}
