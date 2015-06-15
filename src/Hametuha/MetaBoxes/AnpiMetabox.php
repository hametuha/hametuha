<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\TaxonomySelect;
use WPametu\UI\Field\TokenInputPost;

class AnpiMetabox extends EditMetaBox
{

    protected $post_types = ['anpi'];

    protected $name = 'hametuha_anpi_format_helper';

    protected $label = '設定';

    protected $context = 'side';

    protected $fields = [
        'anpi_cat' => [
            'class' => TaxonomySelect::class,
            'label' => 'カテゴリー',
            'required' => true,
            'description' => '安否情報のカテゴリーを選んでください',
        ],
    ];
} 