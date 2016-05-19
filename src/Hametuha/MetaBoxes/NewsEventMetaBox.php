<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\DateTime;
use WPametu\UI\Field\GeoChecker;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\Text;
use WPametu\UI\Field\TextArea;

class NewsEventMetaBox extends EditMetaBox {

	protected $post_types = [ 'news' ];

	protected $name = 'hametuha_news_event_helper';

	protected $label = 'イベント情報';

	protected $context = 'advanced';

	protected $fields = [
		'_event_title' => [
			'class'       => Text::class,
			'label'       => 'イベント名',
		    'description' => '空白の場合はイベント自体が表示されません',
		],
		'_event_start' => [
			'class'       => DateTime::class,
			'label'       => '開始',
			'description' => '空白の場合は表示されません。',
		],
		'_event_end' => [
			'class'       => DateTime::class,
			'label'       => '終了',
			'description' => '空白の場合は表示されません。複数日にまたがる場合、時間は表示されません。',
		],
		'_event_address' => [
			'class' => Text::class,
			'label' => '住所',
			'placeholder' => 'ex. 東京都千代田区永田町2-4-11',
			'description' => '建物を住所に含めると、正確に表示されません。建物名は「建物」に入れてください。',
		],
		'_event_bld' => [
			'class' => Text::class,
			'label' => '建物名',
			'placeholder' => 'ex. フレンドビル 3階',
		],
		'_event_point' => [
			'class' => GeoChecker::class,
			'label' => '住所チェック',
			'target' => '_event_address',
			'description' => '「住所」に入力した場所はこの地図のように表示されます。',
		],
	    '_event_desc' => [
		    'class' => TextArea::class,
	        'label' => '備考',
	        'rows' => 3,
	    ],
	];
}
