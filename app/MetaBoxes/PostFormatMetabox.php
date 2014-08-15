<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\TaxonomySelect;
use WPametu\UI\Field\TokenInputPost;

class PostFormatMetabox extends EditMetaBox
{

    protected $post_types = ['post'];

    protected $name = 'hametuha_post_format_helper';

    protected $label = '作品の設定';

    protected $context = 'side';

    protected $priority = 'high';

    protected $fields = [
        'category' => [
            'class' => TaxonomySelect::class,
            'label' => 'ジャンル',
            'required' => true,
            'description' => 'この作品のジャンルを選んでください。',
        ],
        'series' => [
            'class' => TokenInputPost::class,
            'label' => '作品集',
            'description' => 'この作品が作品集に所属する場合、該当するものを選んでください。',
            'post_type' => 'series',
            'max' => 1,
        ],
        'post_format' => [
            'class' => Radio::class,
            'label' => '表示形式',
            'options' => [
                '0' => '横書き',
                'image' => '縦書き'
            ],
            'description' => 'この作品がどのように表示されるかを選んでください。<small>※縦書きレイアウトは一時的に停止しています。設定だけしておけば、そのうち有効になります。</small>',
        ],
    ];
} 