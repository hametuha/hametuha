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
class AnnouncementMetaBox extends EditMetaBox
{

    protected $name = 'hametuha_announcement_detail';

    protected $label = 'イベント開催場所情報';

    protected $priority  = 'high';

    protected $post_types = ['announcement'];

    protected $fields = [
        'excerpt' => [
            'class' => TextArea::class,
            'label' => 'キャッチコピー',
            'rows' => 5,
            'max' => 200,
            'description' => 'この告知の概要',
            'placeholder' => 'ex. どんな寂しい人間にも友達ができるイベント'
        ],
        Announcement::PLACE => [
            'class' => Text::class,
            'label' => '場所の名前',
            'min' => 1,
            'max' => 20,
            'description' => '※空白にすると地図は表示されません',
            'placeholder' => 'ex. 破滅ラボ'
        ],
        Announcement::ADDRESS => [
            'class' => Text::class,
            'label' => '住所',
            'placeholder' => 'ex. 東京都千代田区永田町2-4-11',
            'description' => '建物を住所に含めると、正確に表示されません。建物名は「建物」に入れてください。',
        ],
        Announcement::BUILDING => [
            'class' => Text::class,
            'label' => '建物',
            'placeholder' => 'ex. フレンドビル 3階',
        ],
        Announcement::POINT => [
            'class' => GeoChecker::class,
            'label' => '住所チェック',
            'target' => Announcement::ADDRESS,
            'description' => '「住所」に入力した場所はこの地図のように表示されます。',
        ],
	    Announcement::COMMIT_TYPE => [
		    'class' => Radio::class,
		    'label' => '募集形式',
		    'options' => [
			    0 => '募集はしない',
			    1 => '募集する',
		    ]
	    ],
	    Announcement::COMMIT_START => [
		    'class' => DateTime::class,
		    'label' => '募集開始日時',
	    ],
	    Announcement::COMMIT_END => [
		    'class' => DateTime::class,
		    'label' => '募集終了日時',
	    ],
	    Announcement::COMMIT_COST => [
		    'class' => Number::class,
		    'label' => '参加費用',
		    'step'  => 100,
		    'suffix' => '円',
	    ],
	    Announcement::COMMIT_LIMIT => [
		    'class' => Number::class,
		    'label' => '参加定員',
		    'suffix' => '人',
	    ],
        Announcement::NOTICE => [
            'class' => TextArea:: class,
            'label' => '備考',
            'placeholder' => 'ex. 開場時間の注意点、持ち物など',
            'rows' => 5,
        ],
    ];

    protected function desc(){
        echo <<<HTML
        <p class="description">告知イベントの開催場所です。必要な場合のみ記入してください。</p>
HTML;

    }
}
