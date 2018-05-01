<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Text;

/**
 * 続きを読むが存在する場合、リンクを表示できる。
 *
 * @package hametuha
 */
class PostReadMore extends EditMetaBox {

	protected $post_types = [ 'post' ];

	protected $name = 'hametuha_post_readmore';

	protected $label = '外部参照';

	protected $context = 'advanced';

	protected $priority = 'low';

	protected $fields = [
		'_external_url'    => [
			'class'       => Text::class,
			'label'       => 'URL',
			'required'    => false,
			'description' => '書籍化などで外部のURLで続きを読んで欲しい場合はこちらにURLを入力してください。',
		],
	];
} 