<?php

namespace Hametuha\WpApi;


use Hametuha\Master\JobStatus;
use Hametuha\Model\Jobs;
use WPametu\API\Rest\WpApi;

/**
 * Image generator
 *
 * @package Hametuha\WpApi
 * @property Jobs $jobs
 */
class ImageShare extends WpApi {

	/**
	 * Get route
	 *
	 * @return string
	 */
	protected function get_route() {
		return 'text-image/of/(?P<job_id>\\d+)';
	}

	/**
	 * Get arguments for method.
	 *
	 * @param string $method 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION'
	 *
	 * @return array
	 */
	protected function get_arguments( $method ) {
		switch ( $method ) {
			case 'POST':
				return [
					'job_id' => [
						'required' => true,
						'validate_callback' => function( $var ) {
							return is_numeric( $var );
						},
					],
					'id' => [
						'required' => true,
						'validate_callback' => function( $var ) {
							return (bool) get_post( $var );
						},
					],
					'success' => [
						'required' => true,
						'validate_callback' => function( $var ) {
							return false !== array_search( $var, [ 'true', 'false' ] );
						},
					],
					'url' => [
						'required' => false,
						'validate_callback' => function( $var ) {
							return preg_match( '#^https?://#u', $var );
						}
					],
					'msg' => [
						'required' => false,
						'default'  => '',
					],
				];
				break;
			default:
				return [];
				break;
		}
	}

	/**
	 * Register image gen
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_post( \WP_REST_Request $request ) {
		$job = $this->jobs->get( $request['job_id'] );
		if ( ! $job || JobStatus::ONGOING != $job->status ) {
			return new \WP_Error( 404, '該当するジョブがありません。', [
				'response' => 404,
			] );
		}
		if ( 'true' !== $request['success']  ) {
			$this->jobs->update_status( $job->job_id, JobStatus::FAILED );
			return new \WP_Error( 404, $request['msg'], [
				'response' => 404,
			] );
		}
		$image_url = $request['url'];
		$this->jobs->job_meta->add( $job->job_id, [
			'url' => $image_url,
		] );
		try {
			if ( ! function_exists( 'gianism_fb_page_api' ) ) {
				throw new \Exception( 'Gianism version is too low.', 500 );
			}
			$api = gianism_fb_page_api();
			if ( is_wp_error( $api ) ) {
				throw new \Exception( $api->get_error_code(), $api->get_error_message() );
			}
			// Post photo
			$user = get_userdata( $job->issuer_id );
			$display_name = $user->display_name;
			foreach ( [ '_wpg_twitter_screen_name', 'twitter' ] as $key ) {
				if ( $screen_name = get_user_meta( $user->ID, $key, true ) ) {
					$display_name = '@' . ltrim( $screen_name, '@' );
				}
			}
			$url  = get_permalink( $job->meta['post_id'] );
			$message = sprintf( '%s さんから %s', $display_name, $url );
			$response = $api->post( 'me/photos', [
				'url' => $image_url,
				'caption' => $message,
			] );
			if ( $edge_id = $response->getGraphNode()->getField( 'post_id' ) ) {
				$this->jobs->job_meta->add( $job->job_id, [
					'fb_edge_id' => $edge_id,
				] );
				$this->jobs->update_status( $job->job_id, JobStatus::SUCCESS );

				return new \WP_REST_Response( [
					'success' => true,
				] );
			} else {
				$this->jobs->update_status( $job->job_id, JobStatus::FAILED );
				throw new \Exception( '投稿に失敗しました。', '500' );
			}
		} catch ( \Exception $e ) {
			$this->jobs->update_status( $job->job_id, JobStatus::FAILED );
			return new \WP_Error( $e->getCode(), $e->getMessage(), [
				'response' => $e->getCode(),
			] );
		}
	}

	/**
	 * Parse permission
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return true;
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'jobs':
				return Jobs::get_instance();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}


}
