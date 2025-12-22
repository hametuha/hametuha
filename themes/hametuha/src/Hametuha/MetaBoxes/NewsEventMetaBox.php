<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\DateTime;
use WPametu\UI\Field\GeoChecker;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\Select;
use WPametu\UI\Field\Text;
use WPametu\UI\Field\TextArea;

class NewsEventMetaBox extends EditMetaBox {

	protected $post_types = [ 'news', 'announcement' ];

	protected $name = 'hametuha_news_event_helper';

	protected $label = '文学賞・イベント';

	protected $context = 'advanced';

	protected $fields = [
		'_event_title'   => [
			'class'       => Text::class,
			'label'       => 'イベント名',
			'description' => '空白の場合はイベント自体が表示されません',
			'required'    => true,
		],
		'_event_type'    => [
			'class'   => Select::class,
			'label'   => '種別',
			'options' => [
				''  => 'リアルイベント',
				'1' => 'オンラインイベント',
				'2' => '公募',
				'3' => '文学賞',
			],
			'default' => '',
		],
		'_event_start'   => [
			'class'       => DateTime::class,
			'label'       => '開始',
			'description' => '空白の場合は表示されません。00:00の場合、時刻は表示されません。',
		],
		'_event_end'     => [
			'class'       => DateTime::class,
			'label'       => '終了',
			'description' => '空白の場合は表示されません。複数日にまたがる場合、00:00の場合、時刻は表示されません。',
		],
		'_event_address' => [
			'class'       => Text::class,
			'label'       => '住所',
			'placeholder' => 'ex. 東京都千代田区永田町2-4-11',
			'description' => '建物を住所に含めると、正確に表示されません。建物名は「建物」に入れてください。',
		],
		'_event_bld'     => [
			'class'       => Text::class,
			'label'       => '建物名',
			'placeholder' => 'ex. フレンドビル 3階',
		],
		'_event_point'   => [
			'class'       => GeoChecker::class,
			'label'       => '住所チェック',
			'target'      => '_event_address',
			'description' => '「住所」に入力した場所はこの地図のように表示されます。',
		],
		'_event_desc'    => [
			'class' => TextArea::class,
			'label' => '備考',
			'rows'  => 3,
		],
	];

	/**
	 * This meta fields description
	 */
	protected function desc() {
		echo <<<HTML
<p class="description">
	文学賞などの公募情報の場合、終了日だけを設定してください。
</p>
HTML;
	}
}
