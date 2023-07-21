<?php

namespace Hametuha\WpApi\Pattern;


use Hametuha\Model\Lists;
use Hametuha\Model\Rating;
use Hametuha\Model\Review;
use Hametuha\Model\Series;
use WPametu\API\Rest\WpApi;

/**
 * REST API related to My contents.
 *
 * @property-read Series $series
 * @property-read Lists  $lists
 * @property-read Review $reviews
 * @property-read Rating $rating
 */
abstract class MyContentPattern extends WpApi {

	/**
	 * @var string[] Model classes.
	 */
	protected $models = [
		'series'  => Series::class,
		'lists'   => Lists::class,
		'reviews' => Review::class,
		'rating'  => Rating::class,
	];

	/**
	 * Return allowed post types.
	 *
	 * @return string[]
	 */
	abstract protected function allowed_types();

	/**
	 * Get arguments.
	 *
	 * @param string $method
	 * @return array
	 */
	protected function get_arguments( $method ) {
		return [
			'post_type' => [
				'type'        => 'string',
				'description' => __( '投稿タイプ名', 'hametuha' ),
				'required'    => true,
				'enum'        => $this->allowed_types(),
			],
			'paged' => [
				'type'              => 'integer',
				'description'       => __( 'ページ番号', 'hametuha' ),
				'default'           => 1,
				'sanitize_callback' => function( $num ) {
					return max( 1, (int) $num );
				},
			],
			's' => [
				'type' => 'string',
				'default' => '',
				'description' => __( '検索文字列', 'hametuha' ),
			],
		];
	}

	/**
	 * Get main term.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array[]
	 */
	protected function get_post_terms( $post ) {
		$taxonomy = '';
		switch ( $post->post_type ) {
			case 'post':
				$taxonomy = 'category';
				break;
			default:
				return [];
		}
		$terms = get_the_terms( $post, $taxonomy );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return [];
		}
		return array_map( function( $term ) {
			return [
				'name' => $term->name,
				'url'  => get_term_link( $term ),
			];
		}, $terms );
	}

	/**
	 * {@inheritDoc}
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}

	/**
	 * Convert object.
	 *
	 * @param \WP_Post|\WP_Comment|\stdClass $object Object to convert.
	 * @return array
	 */
	protected function convert_response( $object, $as = 'author' ) {
		$format    = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		$metas = [];
		if ( is_a( $object, 'WP_Post' ) ) {
			/** @var \WP_Post $post */
			$post     = $object;
			$date     = new \DateTime( $post->post_date, wp_timezone() );
			$modified = new \DateTime( $post->post_modified, wp_timezone() );
			$diff     = $modified->diff( $date );
			$is_modified = ( 'publish' === $post->post_status ) && $diff && $diff->invert && 3 < $diff->days;
			switch ( $post->post_type ) {
				case 'series':
					$metas['library_books'] = sprintf( __( '%d作品収録', 'hametuha' ), $this->series->get_total( $post ) );
					if ( $this->series->is_finished( $post->ID ) ) {
						$metas['done'] = __( '完結済み', 'hametuha' );
					}
					break;
				case 'lists':
					if ( isset( $post->object_id ) ) {
						$metas['bookmark'] = sprintf( __( '収録作「%s」', 'hametuha' ), get_the_title( $post->object_id ) );
					}
					$metas['person'] = get_the_author_meta( 'display_name', $post->post_author );
					break;
			}
			if ( ! empty( $post->reviewed_at) ) {
				// this is rating.
				$is_modified = false;
				$metas['star'] = sprintf( __(  '%d/5点', 'hametuha' ), $post->rating * 10 );
			}
			if ( 'reader' === $as ) {
				// Add author.
				$metas['person'] = get_the_author_meta( 'display_name', $post->post_author );
			}
			$return = [
				'ID'       => $post->ID,
				'title'    => get_the_title( $post ),
				'url'      => get_permalink( $post ),
				'edit_url' => get_edit_post_link( $post, 'rest' ),
				'date'     => mysql2date( $format, $post->reviewed_at ?? $post->post_date ),
				'modified' => mysql2date( $format, $post->post_modified ),
				'updated'  => $is_modified,
				'new'      => hametuha_is_new( $post->reviewed_at ?? $post->post_date ),
				'status'   => [
					'name'  => $post->post_status,
					'label' => get_post_status_object( $post->post_status )->label,
				],
				'parent'    => ( $post->post_parent ) ? [
					'title' => get_the_title( $post->post_parent ),
					'url'   => get_permalink( $post->post_parent ),
				] : null,
				'terms'     => $this->get_post_terms( $post ),
				'metas'     => $metas,
			];
			if ( ! empty( $post->reviewed_at) ) {
				// this is rating.
				$return['parent'] = [
					'title' => get_the_title( $post ),
					'url'   => get_permalink( $post ),
				];
				$return['edit_url'] = false;
				$title = '';
				$rate = (int) ( $post->rating * 10 );
				for ( $i = 1; $i <= 5; $i++ ) {
					$title .= ( $i <= $rate ) ? '★' : '☆';
				}
				$return['title'] = $title;
				if ( 'reader' !== $as ) {
					$return['terms'] = [];
				}
			}
			return $return;
		} elseif ( is_a( $object, 'WP_Comment') ) {
			/** @var \WP_Comment $comment */
			$comment = $object;
			if ( 'reader' === $as ) {
				$metas['person'] = get_the_author_meta( 'display_name', get_post( $comment->comment_post_ID )->post_author );
			} elseif( $comment->user_id ) {
				$metas['person'] = get_the_author_meta( 'display_name', $comment->user_id ) ?: __( '退会したユーザー', 'hametuha' );
			} else {
				$metas['person'] = $comment->comment_author;
			}
			return [
				'ID'       => $comment->ID,
				'title'    => trim_long_sentence( $comment->comment_content, 60 ),
				'url'      => get_comment_link( $comment ),
				'edit_url' => false,
				'date'     => mysql2date( $format, $comment->comment_date ),
				'modified' => mysql2date( $format, $comment->comment_date ),
				'updated'  => false,
				'new'      => hametuha_is_new( $comment->comment_date, 7 ),
				'status'   => [
					'name'  => ( $comment->comment_approved ) ? 'approved' : 'pending',
					'label' => ( $comment->comment_approved ) ? __( '承認済み', 'hametuha' ) : __( '未承認', 'hametuha' ),
				],
				'parent'    => ( $comment->comment_post_ID ) ? [
					'title' => get_the_title( $comment->comment_post_ID ),
					'url'   => get_permalink( $comment->comment_post_ID ),
				] : null,
				'terms'     => [],
				'metas'     => $metas,
			];
		} else {
			return $object;
		}
	}
}
