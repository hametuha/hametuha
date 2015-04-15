<?php

namespace Hametuha\Admin\MetaBox;


use Hametuha\Model\Series;
use WPametu\UI\Admin\EmptyMetaBox;

/**
 * Series meta box's base
 *
 * @package Hametuha\Admin\MetaBox
 * @property-read Series $series
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
		if( 'series' === $name ){
			return Series::get_instance();
		}else{
			return parent::__get($name);
		}
	}

}
