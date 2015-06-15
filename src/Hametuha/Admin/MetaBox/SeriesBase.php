<?php

namespace Hametuha\Admin\MetaBox;


use Hametuha\Model\CompiledFiles;
use Hametuha\Model\Series;
use WPametu\UI\Admin\EmptyMetaBox;

/**
 * Series meta box's base
 *
 * @package Hametuha\Admin\MetaBox
 * @property-read Series $series
 * @property-read CompiledFiles $files
 */
abstract class SeriesBase extends EmptyMetaBox
{

	/**
	 * Only series
	 *
	 * @var array
	 */
	protected $post_types = ['series'];


	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get($name){
		switch( $name ){
			case 'series':
				return Series::get_instance();
				break;
			case 'files':
				return CompiledFiles::get_instance();
				break;
			default:
				return parent::__get($name);
				break;
		}
	}

}
