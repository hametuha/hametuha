<?php

namespace Hametuha\WpApi;


use Hametuha\Model\CompiledFiles;
use WPametu\API\Rest\WpApi;

/**
 * ePub API
 *
 * @package hametuha
 * @property CompiledFiles $files
 */
class EpubFiles extends WpApi {

	protected function get_route() {
		return 'epub/file/(?P<file_id>\d+)/?';
	}

	/**
	 * Get arguments for method.
	 *
	 * @param string $method 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION'
	 *
	 * @return array
	 */
	protected function get_arguments( $method ) {
		$args = [
			'file_id' => [
				'required'          => true,
				'type'              => 'integer',
				'description'       => 'File ID to handle.',
				'validate_callback' => function( $file_id ) {
					if ( ! is_numeric( $file_id ) ) {
						return false;
					}
					try {
						return (bool) $this->files->validate_file( $file_id );
					} catch ( \Exception $e ) {
						return false;
					}
				},
			],
		];
		switch ( $method ) {
			case 'GET':
				$args['format'] = [
					'required'    => true,
					'default'     => 'file',
					'type'        => 'string',
					'description' => 'Result format. Actual file or report.',
					'enum'        => [ 'file', 'report' ],
				];
				break;
			case 'POST':
				$args = array_merge( $args, [

				] );
				break;
		}
		return $args;
	}

	/**
	 * Handle GET request.
	 *
	 * @throws \Exception
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_get( $request ) {
		$method = 'handle_get_' . strtolower( $request->get_param( 'format' ) );
		$file = $this->files->validate_file( $request->get_param( 'file_id' ) );
		return $this->{$method}( $file, $request );
	}

	/**
	 * Get file.
	 *
	 * @param \stdClass        $file
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_get_file( $file, $request ) {
		return $this->files->download( $file->file_id );
	}

	/**
	 * Get file report.
	 *
	 * @param \stdClass        $file
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_get_report( $file, $request ) {
		$result = $this->files->validate( $file->file_id );
		if ( is_wp_error( $result ) ) {
			return $result;
		} else {
			return new \WP_REST_Response( $result );
		}
	}

	/**
	 * Handle delete response.
	 *
	 * @throws \Exception
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_delete( $request ) {
		$file_id = $request->get_param( 'file_id' );
		$file    = $this->files->validate_file( $file_id );
		if ( ! $this->files->delete_file( $file_id ) ) {
			throw new \Exception( 'ファイルを削除できませんでした。', 500 );
		}
		return new \WP_REST_Response( [
			'message' => 'ePubファイルを削除しました。',
		] );
	}

	/**
	 * Permission check.
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		$file = $this->files->get_file( $request->get_param( 'file_id' ) );
		switch ( $request->get_method() ) {
			default:
				return current_user_can( 'get_epub', $file->file_id );
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
			case 'files':
				return CompiledFiles::get_instance();
			default:
				return parent::__get( $name );
		}
	}


}
