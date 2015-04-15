<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\Text;
use WPametu\UI\Field\Number;

/**
 * Meta box for ePub
 *
 * @package Hametuha\MetaBoxes
 */
class SeriesLayoutMetaBox extends EditMetaBox
{
	protected $post_types = ['series'];

	protected $name = 'hametuha_epub_layout_helper';

	protected $label = 'ePub表示設定';

	protected $context = 'side';

	protected $priority = 'low';

	protected $fields = [
		'orientation' => [
			'class' => Radio::class,
			'label' => '文字方向',
			'options' => [
				'vertical' => '縦書き',
				'horizontal' => '横書き'
			],
			'default' => 'vertical',
		],
		'_show_title' => [
			'class' => Radio::class,
		    'label' => '本文のタイトル',
		    'options' => [
			    2 => 'タイトルと筆名',
			    1 => 'タイトルのみ表示',
		        0 => '表示しない',
		    ],
		    'default' => 0,
		    'description' => 'それぞれの作品のタイトルを表示するか否か。連載作品の場合、タイトルはなくてもよいかもしれません。',
		],
		'_visibility' => [
			'class' => Number::class,
		    'label' => '閲覧設定',
		    'require' => true,
		    'min' => 0,
		    'default' => 0,
		    'description' => '1以上の値に設定すると、それ以降の作品を閲覧できなくなります。販売を開始した場合は必ず設定してください。',
		],
	];

}