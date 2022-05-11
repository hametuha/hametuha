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

	protected $label = '参照情報';

	protected $context = 'advanced';

	protected $priority = 'low';

	protected $fields = [
		'_external_url' => [
			'class' => Text::class,
			'label' => '外部参照URL',
			'required' => false,
			'description' => '書籍化などで外部のURLで続きを読んで欲しい場合はこちらにURLを入力してください。投稿がチラ見せ状態になります。',
			'placeholder' => '例）https://amazon.co.jp/example',
		],
		'_external_url_limit' => [
			'class' => Text::class,
			'label' => '外部参照期限',
			'required' => false,
			'description' => '外部参照URLを設定し、その発売日まで投稿を読めるようにしておくには、ここに日付を入力してください。',
			'placeholder' => '例）2024-12-31',
		],
		'_first_collected' => [
			'class' => Text::class,
			'label' => '初出',
			'required' => false,
			'description' => '初出が破滅派以外の場合は名称を記載してください。',
			'placeholder' => '例）新潮2007年11月号',
		],
		'oldurl' => [
			'class' => Text::class,
			'label' => '初出URL',
			'required' => false,
			'description' => '初出がWebの場合はURLを入力してください。',
			'placeholder' => '例）https://ncode.syosetu.com/n5943h/',
		],
	];
}
