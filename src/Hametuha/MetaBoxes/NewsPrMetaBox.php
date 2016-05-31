<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\Text;

class NewsPrMetaBox extends EditMetaBox {

	protected $post_types = [ 'news' ];

	protected $name = 'hametuha_news_pr_helper';

	protected $label = 'PR設定';

	protected $context = 'side';

	protected $priority = 'low';

	protected $fields = [
		'_advertiser' => [
			'class'       => Text::class,
			'label'       => '広告主',
			'description' => 'これが広告記事の場合は広告主の名前を入力してください。自社広告の場合は不要です。',
		],
	    '_is_owned_ad' => [
		    'class' => Radio::class,
	        'label' => '自社広告',
	        'options' => [
		        0 => '自社広告ではない',
	            1 => '破滅派の自社広告',
	        ],
	        'default' => 0,
	    ],
	];
}
