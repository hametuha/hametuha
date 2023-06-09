<?php

namespace Hametuha\Batches;


use Hametuha\User\Profile\Picture;
use WPametu\Tool\Batch;
use WPametu\Tool\BatchResult;

/**
 * 古いプロフィール画像を新しく移動するバッチ処理
 *
 * @package Hametuha\Batches
 */
class OldPicMove extends Batch {


	/**
	 * @var int
	 */
	protected $per_process = 10;

	/**
	 * Return title
	 *
	 * @return string
	 */
	protected function get_title() {
		return 'Old Pic Move';
	}

	/**
	 * Return description
	 *
	 * @return string
	 */
	protected function get_description() {
		return '古いプロフィール写真を新しい形式に移行する';
	}

	/**
	 * Do process
	 *
	 * @param $page
	 *
	 * @return int Next page. 0 means no more process.
	 * @throws \Exception
	 */
	public function process( $page ) {
		/** @var Picture $picture */
		$picture           = Picture::get_instance();
		$offset            = ( max( $page, 1 ) - 1 ) * $this->per_process;
		$base_dir          = $picture->get_dir();
		$total             = 0;
		$index             = 0;
		$maximum_processed = 0;
		foreach ( scandir( $base_dir ) as $dir ) {
			if ( is_numeric( $dir ) ) {
				$total++;
				$index++;
				if ( $offset < $index && $offset + $this->per_process >= $index ) {
					$user_id      = $dir;
					$picture_path = '';
					foreach ( preg_grep( '/profile\.(png|jpe?g|gif)$/u', scandir( $base_dir . "/{$user_id}" ) ) as $file ) {
						$picture_path = $base_dir . "/{$user_id}/{$file}";
					}
					if ( $picture_path && file_exists( $picture_path ) ) {
						$info  = getimagesize( $picture_path );
						$mime  = image_type_to_mime_type( $info['2'] );
						$files = [
							'name'     => basename( $picture_path ),
							'type'     => $mime,
							'tmp_name' => $picture_path,
							'error'    => 0,
							'size'     => filesize( $picture_path ),
						];
						$picture->upload( $files, $user_id );
					}
					$maximum_processed = max( $maximum_processed, $index );
				}
			}
		}

		$has_next = $total > $maximum_processed;
		return new BatchResult( $maximum_processed, $total, $has_next );
	}


}
