<?php

namespace Hametuha\WpApi;


use Hametuha\Model\Lists;
use Hametuha\Model\Series;
use WPametu\API\Rest\WpApi;

/**
 * My contents
 *
 * @package hametuha
 * @property-read Series $series
 * @property-read Lists  $lists
 */
class MyContents extends WpApi {

	protected $models = [
		'series' => Series::class,
		'lists'  => Lists::class,
	];

	/**
	 * {@inheritDoc}
	 */
	protected function get_route() {
		return 'mine/(?P<post_type>[^/]+)';
	}

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
				'enum'        => [ 'post', 'series', 'list', 'reviews', 'comment' ],
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
	 * Handle GET request.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handle_get( \WP_REST_Request $request ) {
		$post_type = $request->get_param( 'post_type' );
		$paged     = (int) $request->get_param( 'paged' );
		switch ( $post_type ) {
			case 'post':
			case 'series':
				$args = [
					'post_type'           => $post_type,
					'post_status'         => 'any',
					'author'              => get_current_user_id(),
					'posts_per_page'      => 20,
					'paged'               => $paged,
					'ignore_sticky_posts' => true,
					'orderby'             => [ 'date' => 'DESC' ],
				];
				$s = $request->get_param( 's' );
				if ( $s ) {
					$args['s'] = $s;
				}
				$query = new \WP_Query( $args );
				return new \WP_REST_Response( [
					'posts'       => array_map( [ $this, 'convert_response' ], $query->posts ),
					'found_posts' => $query->found_posts,
					'total'       => $query->max_num_pages,
					'current'     => $paged,
				] );
			case 'list':
				$result = $this->lists->search_list( [
					'paged'          => $paged,
					'posts_per_page' => 20,
					'author'         => get_current_user_id(),
				] );
				return new \WP_REST_Response( [
					'posts'       => array_map( [ $this, 'convert_response' ], $result['posts'] ),
					'found_posts' => $result['found_posts'],
					'total'       => $result['total'],
					'current'     => $result['current'],
				] );
		}
	}

	/**
	 * Convert object.
	 *
	 * @param \WP_Post|\WP_Comment|\stdClass $object Object to convert.
	 * @return array
	 */
	protected function convert_response( $object ) {
		$format    = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		if ( is_a( $object, 'WP_Post' ) ) {
			/** @var \WP_Post $post */
			$post     = $object;
			$date     = new \DateTime( $post->post_date, wp_timezone() );
			$modified = new \DateTime( $post->post_modified, wp_timezone() );
			$diff     = $modified->diff( $date );
			$is_modified = ( 'publish' === $post->post_status ) && $diff && $diff->invert && 3 < $diff->days;
			$metas = [];
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
			return [
				'ID'       => $post->ID,
				'title'    => get_the_title( $post ),
				'url'      => get_permalink( $post ),
				'edit_url' => get_edit_post_link( $post, 'rest' ),
				'date'     => mysql2date( $format, $post->post_date ),
				'modified' => mysql2date( $format, $post->post_modified ),
				'updated'  => $is_modified,
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
		} elseif ( is_a( $object, 'WP_Comment') ) {

		}
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
}
