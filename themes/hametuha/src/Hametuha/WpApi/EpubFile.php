<?php

namespace Hametuha\WpApi;


use Hametuha\WpApi\Pattern\EpubFilePattern;

/**
 * ePub API
 *
 * @package hametuha
 */
class EpubFile extends EpubFilePattern {

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
				'validate_callback' => function ( $file_id ) {
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
					'published' => [
						'default'           => '',
						'type'              => 'string',
						'description'       => 'The datetime of publication',
						'validate_callback' => function ( $var ) {
							return empty( $var ) || ( 'DELETE' === $var ) || preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/u', $var );
						},
					],
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
		$file   = $this->files->validate_file( $request->get_param( 'file_id' ) );
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
	 * Update file data.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception
	 */
	public function handle_post( $request ) {
		$error    = new \WP_Error();
		$messages = [];
		$file_id  = $request->get_param( 'file_id' );
		foreach ( [ 'published' ] as $key ) {
			$value = $request->get_param( $key );
			switch ( $key ) {
				case 'published':
					if ( 'DELETE' === $value ) {
						// Delete file meta.
						if ( $this->files->meta->delete_meta( $file_id, 'published' ) ) {
							$messages[] = 'ファイルの公開日時を削除しました。';
						} else {
							$error->add( 'failed_to_update_file', 'ファイルの公開日時を削除できませんでした。', [
								'status' => 500,
							] );
						}
					} elseif ( $value ) {
						// Update published date.
						if ( $this->files->meta->update_meta( $file_id, 'published', $value ) ) {
							$messages[] = 'ファイルの公開日時を更新しました。';
						} else {
							$error->add( 'failed_to_update_file', 'ファイルの公開日時を更新できませんでした。', [
								'status' => 500,
							] );
						}
					}
					break;
			}
		}
		if ( $error->get_error_messages() ) {
			return $error;
		} else {
			return new \WP_REST_Response( [
				'success'  => true,
				'messages' => $messages,
			] );
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
			case 'GET':
				if ( 'report' === $request->get_param( 'format' ) ) {
					return current_user_can( 'edit_post', $file->post_id );
				} else {
					return current_user_can( 'get_epub', $file->file_id );
				}
			default:
				return current_user_can( 'get_epub', $file->file_id );
		}
	}
}
