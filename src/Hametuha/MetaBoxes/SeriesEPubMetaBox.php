<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\LeadMetaBox;
use WPametu\UI\Field\Text;
use WPametu\UI\Field\Textarea;
use WPametu\UI\Field\Radio;

class SeriesEPubMetaBox extends LeadMetaBox {

	protected $post_types = [ 'series' ];

	protected $name = 'hametuha_epub_format_helper';

	protected $label = 'ePub設定';

	protected $context = 'advanced';

	protected $priority = 'high';

	protected $fields = [
		'subtitle' => [
			'class'       => Text::class,
			'label'       => 'サブタイトル',
			'required'    => false,
			'description' => 'サブタイトルがあればつけてください。',
		],
		'excerpt'  => [
			'class'       => Textarea::class,
			'label'       => 'リード',
			'required'    => true,
			'rows'        => 5,
			'min'         => 20,
			'max'         => 200,
			'description' => 'リードは作品集のWebページおよび電子書籍販売ストアに一番最初に表示されます。作品を読むための非常に重要な要素なので、よく考えて入力してください。',
			'placeholder' => 'ex. 都内有数のお嬢様学校をゾンビの群れが襲撃する！　そのときたまたま編入した「俺」は今年から共学になったこの学校唯一の男子として、全校生徒を守るべく釘バット一本で立ち向かう。21世紀を代表する新たなゾンビ文学の金字塔、ここに爆誕。',
		],
		'_preface' => [
			'class'       => Textarea::class,
			'label'       => 'はじめに',
			'required'    => false,
			'rows'        => 10,
			'description' => '入力した場合、本文の前に挿入されます。序文や献辞としてお使いください。HTMLを使用することができます。',
		],
	];

}
