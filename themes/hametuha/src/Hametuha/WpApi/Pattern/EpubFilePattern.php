<?php

namespace Hametuha\WpApi\Pattern;


use Hametuha\Model\CompiledFileMeta;
use Hametuha\Model\CompiledFiles;
use Hametuha\Model\Series;
use WPametu\API\Rest\WpApi;

/**
 * REST API related to ePub files.
 *
 * @property-read Series           $series
 * @property-read CompiledFiles    $files
 */
abstract class EpubFilePattern extends WpApi {

	protected $models = [
		'series' => Series::class,
		'files'  => CompiledFiles::class,
	];
}
