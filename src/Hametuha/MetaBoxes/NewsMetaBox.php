<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\LeadMetaBox;
use WPametu\UI\Field\Text;
use WPametu\UI\Field\TextArea;

class NewsMetaBox extends LeadMetaBox {

	protected $post_types = [ 'news' ];

	protected $name = 'hametuha_news_meta_helper';

	protected $label = '設定';

	protected $context = 'side';

	protected $fields = [
		'excerpt' => [
			'class'       => TextArea::class,
			'label'       => '煽り文',
			'required'    => true,
			'description' => 'ニュースの概要です。SNSなどで使われるので、続きが読みたくなるような概要を書いてください。',
			'rows'        => 3,
			'min'         => 40,
			'max'         => 200,
		],
	];
}
