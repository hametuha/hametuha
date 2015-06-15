<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\LeadMetaBox;
use WPametu\UI\Field\TextArea;
use WPametu\UI\Field\Select;

class PostExcerptMetaBox extends LeadMetaBox
{

    protected $post_types = ['post'];

    protected $name = 'hametuha_post_helper';

    protected $label = 'リード';

    protected $fields = [
        'excerpt' => [
            'class' => TextArea::class,
            'label' => 'リード',
            'required' => true,
            'rows' => 5,
            'min' => 20,
            'max' => 200,
            'description' => 'リードは読者があなたの作品を読もうと思う重要な要素の一つです。魅力的なリードを考えてください。',
            'placeholder' => 'ex. この作品はほんとうに素晴らしいんです。読んでください！'
        ],
      ];
} 