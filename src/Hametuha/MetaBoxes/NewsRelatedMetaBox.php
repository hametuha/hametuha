<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\DateTime;
use WPametu\UI\Field\GeoChecker;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\Text;
use WPametu\UI\Field\TextArea;

class NewsRelatedMetaBox extends EditMetaBox {

	protected $post_types = [ 'news' ];

	protected $name = 'hametuha_news_related_helper';

	protected $label = '関連リンク情報';

	protected $context = 'advanced';

	protected $priority = 'high';
	
	protected $fields = [
		'_news_related_links' => [
			'class'       => TextArea::class,
			'label'       => '関連リンク',
		    'description' => 'ニュースソース、引用元などの重要なリンクは本文中に書かず、こちらに記載してください。URLとタイトルをパイプ（|）でつなげてください。1行ごとに一つのリンクと判断されます。',
		    'rows'        => 3,
		    'placeholder' => 'http://example.jp/|関連するサイトの例',
		],
		'_news_related_books' => [
			'class'       => TextArea::class,
			'label'       => '関連書籍',
			'description' => 'ASINを1行に1つ入力してください。ASINはAmazonの商品コードで、Amazonサイトで調べることができます。',
			'rows'        => 3,
			'placeholder' => 'B00ZQ66UG6',
		],
	];
}
