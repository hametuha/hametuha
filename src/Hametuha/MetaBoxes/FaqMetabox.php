<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Radio;

class FaqMetabox extends EditMetaBox {

	protected $post_types = [ 'faq' ];

	protected $name = 'hametuha_faq_access_helper';

	protected $label = '閲覧設定';

	protected $context = 'side';

	protected $priority = 'low';

	protected $fields = [
		'_accessibility' => [
			'class'       => Radio::class,
			'label'       => '閲覧権限',
			'required'    => true,
			'description' => 'このFAQを見ることができる権限です。',
			'options'     => [
				''       => '制限なし',
				'writer' => '投稿者のみ',
				'editor' => '編集者のみ',
			],
			'default'     => '',
		],
	];
}
