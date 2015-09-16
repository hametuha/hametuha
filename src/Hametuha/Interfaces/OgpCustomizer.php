<?php

namespace Hametuha\Interfaces;


interface OgpCustomizer
{

	/**
	 * Set OGP
	 *
	 * @param array $values 'image', 'title, 'url', 'type', 'desc', 'card', 'author'
	 *
	 * @return array
	 */
	public function ogp( array $values );
}
