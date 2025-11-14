<?php

namespace Hametuha\Rest;


use Hametuha\Model\UserTags;
use WPametu\API\Rest\RestJSON;


/**
 * Class UserTag
 * @package Hametuha\Rest
 * @property-read UserTags $user_tag
 */
class UserTag extends RestJSON {


	/**
	 * Model classes
	 *
	 * @var array
	 */
	protected $models = [
		'user_tag' => UserTags::class,
	];

	/**
	 * Test if request is valid.
	 *
	 * @param int $post_id
	 * @param int $term_taxonomy_id
	 * @param bool $skip_taxonomy_id Default false.
	 */
	private function is_valid_request( $post_id, $term_taxonomy_id, $skip_taxonomy_id = false ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			$this->method_not_found();
		}
		if ( ! is_user_logged_in() ) {
			$this->auth_error();
		}
		if ( ! $skip_taxonomy_id ) {
			$term = get_term_by( 'term_taxonomy_id', $term_taxonomy_id, $this->user_tag->taxonomy );
			if ( ! $term || is_wp_error( $term ) ) {
				$this->method_not_found();
			}
		}
	}

	/**
	 * タグを追加する
	 *
	 * @param int $post_id
	 * @return array
	 */
	public function post_create( $post_id ) {
		$this->is_valid_request( $post_id, 0, true );
		$tag = $this->user_tag->create_term_if_not_exists( $this->input->post( 'term' ) );
		if ( ! $tag || is_wp_error( $tag ) ) {
			$this->method_not_found();
		}
		if ( $this->user_tag->add_user_tag( get_current_user_id(), $post_id, $tag->term_taxonomy_id ) < 0 ) {
			return [ 'success' => false, 'message' => 'このタグはすでに追加済みです。' ];
		} else {
			$tag = $this->user_tag->get_latest_tag( $post_id, $tag->term_taxonomy_id, get_current_user_id() );
			return [ 'success' => true, 'tag' => $tag ? $this->convert_tag( $tag ) : false ];
		}
	}

	/**
	 * タグを追加する
	 *
	 * @param int $post_id
	 * @return array
	 */
	public function post_add( $post_id ) {
		$this->is_valid_request( $post_id, $this->input->post( 'taxonomy_id' ) );
		if ( $this->user_tag->add_user_tag( get_current_user_id(), $post_id, $this->input->post( 'taxonomy_id' ) ) < 0 ) {
			return [ 'success' => false, 'message' => 'このタグはすでに追加済みです。' ];
		} else {
			$tag = $this->user_tag->get_latest_tag( $post_id, $this->input->post( 'taxonomy_id' ), get_current_user_id() );
			return [ 'success' => true, 'tag' => $tag ? $this->convert_tag( $tag ) : false ];
		}
	}

	/**
	 * タグを削除する
	 *
	 * @param int $post_id
	 * @return array
	 */
	public function post_remove( $post_id ) {
		$this->is_valid_request( $post_id, $this->input->post( 'taxonomy_id' ) );
		if ( ! $this->user_tag->remove_user_tag( get_current_user_id(), $post_id, $this->input->post( 'taxonomy_id' ) ) ) {
			$this->method_not_found();
		}
		$tag = $this->user_tag->get_latest_tag( $post_id, $this->input->post( 'taxonomy_id' ), get_current_user_id() );
		return [ 'success' => true, 'tag' => $tag ? $this->convert_tag( $tag ) : false ];
	}

	/**
	 * タグをBackbone用に変換
	 *
	 * @param \stdClass $tag
	 * @return array
	 */
	private function convert_tag( $tag ) {
		return [
			'me'          => (bool) $tag->owning,
			'name'        => $tag->name,
			'taxonomy_id' => $tag->term_taxonomy_id,
			'url'         => get_tag_link( $tag ),
			'number'      => (int) $tag->number,
		];
	}

	/**
	 * スクリプトを読み込む
	 */
	protected function lazy_scripts() {
		// タグのJS
		wp_enqueue_script( 'hametuha-user-tag', $this->get_theme_uri() . '/assets/js/dist/components/user-tag.js', [ 'backbone', 'jquery-ui-autocomplete' ], hametuha_version(), true );
		wp_localize_script('hametuha-user-tag', 'HametuhaUserTag', [
			'tagSearch' => $this->url( 'search' ),
			'tagAdd'    => $this->url( 'add/' . get_the_ID() ),
			'tagRemove' => $this->url( 'remove/' . get_the_ID() ),
			'tagCreate' => $this->url( 'create/' . get_the_ID() ),
		]);
	}

	/**
	 * タグを検索する
	 *
	 * @param int $offset
	 * @return array
	 */
	public function get_search( $offset = 0 ) {
		$query = $this->input->get( 'term' );
		if ( $query ) {
			$results = $this->user_tag->tag_search( $query, $offset );
			$return  = [];
			foreach ( $results as $term ) {
				$return[] = $term->name;
			}
			return $return;
		} else {
			return [];
		}
	}
}
