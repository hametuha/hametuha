<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\LeadMetaBox;
use WPametu\UI\Field\Text;
use WPametu\UI\Field\TextArea;

class SeriesEPubMetaBox extends LeadMetaBox
{


	protected $post_types = ['series'];

	protected $name = 'hametuha_epub_format_helper';

	protected $label = 'ePub設定';

	protected $context = 'advanced';

	protected $priority = 'high';

	protected $fields = [
		'subtitle' => [
			'class' => Text::class,
			'label' => 'サブタイトル',
			'required' => false,
			'description' => 'サブタイトルがあればつけてください。',
		],
		'excerpt' => [
			'class' => TextArea::class,
			'label' => 'リード',
			'required' => true,
			'rows' => 5,
			'min' => 20,
			'max' => 200,
			'description' => 'リードは作品集のWebページに表示されます。',
			'placeholder' => 'ex. この作品はほんとうに素晴らしいんです。読んでください！'
		],
		'_preface' => [
			'class' => TextArea::class,
			'label' => '序文',
			'required' => false,
			'rows' => 10,
			'description' => '入力した場合、序文としてePubの先頭に挿入されます。',
		],
		'asin' => [
			'class' => Text::class,
			'label' => 'ASIN',
			'required' => false,
			'description' => '任意（Kindle Direct Publishingなどで発行すると発行されます）。破滅派編集部が設定します。',
		],
	];

}