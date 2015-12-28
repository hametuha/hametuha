<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\Text;

class IdeaMetabox extends EditMetaBox {

	protected $post_types = [ 'ideas' ];

	protected $name = 'hametuha_idea_helper';

	protected $label = '設定';

	protected $context = 'normal';

	protected $fields = [
		'idea_source' => [
			'class'    => Text::class,
			'label'    => 'アイデアの投稿場所',
			'required' => false,
		],
		'idea_author' => [
			'class'    => Text::class,
			'label'    => 'アイデアの作者',
			'required' => false,
		],
	    'idea_id'     => [
		    'class'    => Text::class,
	        'label'    => 'ID',
	        'required' => false,
	    ],
	];
}
