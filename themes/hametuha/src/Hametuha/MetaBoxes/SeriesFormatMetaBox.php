<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\TextArea;

/**
 * 書籍の形態を指定するメタボックス
 *
 * 破滅派の外で制作した書籍をあとから電子化する場合、本文を入稿しないため
 * 子投稿が存在しない。ePubの設定ではなくWeb表示の話なので、ePub系の
 * メタボックスとは分けている。
 *
 * @package Hametuha\MetaBoxes
 */
class SeriesFormatMetaBox extends EditMetaBox {

	protected $post_types = [ 'series' ];

	protected $name = 'hametuha_series_format';

	protected $label = '書籍の形態';

	protected $context = 'advanced';

	protected $priority = 'high';

	protected $fields = [
		'_standalone_book' => [
			'class'       => Radio::class,
			'label'       => '形態',
			'options'     => [
				0 => '連載・作品集（破滅派に本文を入稿する）',
				1 => '単巻書籍（破滅派の外で制作し、本文を入稿しない）',
			],
			'default'     => 0,
			'description' => '単巻書籍にすると、収録作一覧のかわりに下の目次が表示され、作品数・文字数・連載期間は表示されなくなります。売上報告や執筆者一覧はどちらでも同じように機能します。',
		],
		'_book_toc'        => [
			'class'       => TextArea::class,
			'label'       => '目次',
			'required'    => false,
			'rows'        => 10,
			'description' => 'HTMLが使えます。<code>&lt;ol&gt;</code> や <code>&lt;ul&gt;</code> で目次を書いてください。空欄の場合、目次は表示されません。単巻書籍のときだけ使われます。',
		],
	];
}
