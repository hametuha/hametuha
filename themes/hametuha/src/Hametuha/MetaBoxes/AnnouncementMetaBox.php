<?php

namespace Hametuha\MetaBoxes;


use Hametuha\ThePost\Announcement;
use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\DateTime;
use WPametu\UI\Field\GeoChecker;
use WPametu\UI\Field\Number;
use WPametu\UI\Field\Text;
use WPametu\UI\Field\TextArea;
use WPametu\UI\Field\Radio;

/**
 * 告知用のテスト
 *
 * @package Hametuha\MetaBoxes
 */
class AnnouncementMetaBox extends EditMetaBox {

	protected $name = 'hametuha_announcement_detail';

	protected $label = '参加方法';

	protected $priority = 'high';

	protected $post_types = [ 'announcement' ];

	protected $fields = [
		Announcement::COMMIT_TYPE  => [
			'class'   => Radio::class,
			'label'   => '募集形式',
			'options' => [
				0 => '募集はしない',
				1 => '募集する',
			],
		],
		Announcement::COMMIT_START => [
			'class' => DateTime::class,
			'label' => '募集開始日時',
		],
		Announcement::COMMIT_END   => [
			'class' => DateTime::class,
			'label' => '募集終了日時',
		],
		Announcement::COMMIT_COST  => [
			'class'  => Number::class,
			'label'  => '参加費用',
			'step'   => 100,
			'suffix' => '円',
		],
		Announcement::COMMIT_LIMIT => [
			'class'  => Number::class,
			'label'  => '参加定員',
			'suffix' => '人',
		],
	];

	/**
	 * 詳細説明文
	 */
	protected function desc() {
		echo <<<HTML
        <p class="description">参加する何かの場合は入力してください<p>
HTML;
	}
}
