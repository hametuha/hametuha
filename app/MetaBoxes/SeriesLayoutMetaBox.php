<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Radio;


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
		'downloadable' => [
			'class' => Radio::class,
			'label' => 'ダウンロード設定',
			'options' => [
				0 => '不可（本人のみ）',
				1 => '誰でも可能'
			],
			'default' => 0,
		]
	];

}