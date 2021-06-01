<?php

namespace Hametuha\MetaBoxes;


use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Field\Radio;
use WPametu\UI\Field\TaxonomySelect;
use WPametu\UI\Field\TokenInputPost;

class PostFormatMetabox extends EditMetaBox {

	protected $post_types = [ 'post' ];

	protected $name = 'hametuha_post_format_helper';

	protected $label = '作品の設定';

	protected $context = 'side';

	protected $priority = 'high';

	protected $fields = [
		'category'    => [
			'class'       => TaxonomySelect::class,
			'label'       => 'ジャンル',
			'required'    => true,
			'description' => 'この作品のジャンルを選んでください。',
		],
		'post_parent' => [
			'class'       => TokenInputPost::class,
			'label'       => '作品集',
			'description' => 'この作品が作品集に所属する場合、該当するものを選んでください。',
			'post_type'   => 'series',
			'max'         => 1,
		],
		'post_format' => [
			'class'       => Radio::class,
			'label'       => '表示形式',
			'options'     => [
				'0'     => '横書き',
				'image' => '縦書き',
			],
			'description' => 'この作品がどのように表示されるかを選んでください。<small>※縦書きレイアウトは一時的に停止しています。設定だけしておけば、そのうち有効になります。</small>',
		],
		'_noindex'    => [
			'class'   => Radio::class,
			'label'   => '検索エンジン',
			'options' => [
				''        => '表示する',
				'noindex' => '隠す',
			],
			'default' => 'この作品がGoogleをはじめとする検索エンジンで表示されないようにします。',
		],
	];

	/**
	 * Render meta box content
	 *
	 * @param \WP_Post $post
	 */
	public function render( \WP_Post $post ) {
		ob_start();
		parent::render( $post );
		$content = ob_get_contents();
		ob_end_clean();
		// 現在の投稿が作品集に所属しており
		// なおかつその所有権がない場合、表示を変更する
		if ( $post->post_parent && ! current_user_can( 'edit_others_posts' ) && ! current_user_can( 'edit_post', $post->post_parent ) ) {
			$content = preg_replace_callback(
				'#<input id="post_parent"[^>]+value="(\d*)"[^>]*?>#u',
				function( $matches ) use ( $post ) {
					$title = esc_html( get_the_title( $post->post_parent ) );
					$url   = get_permalink( $post->post_parent );
					return <<<HTML
					<input type="hidden" name="post_parent" value="{$post->post_parent}" />
					<p class="notice notice-warning">この投稿は作品集<a href="{$url}" target="_blank">「{$title}」</a>に紐づけられています。編集者以外は変更できません。変更を希望する場合はお問い合わせよりご連絡ください。</p>
HTML;
				},
				$content
			);
		}
		echo $content;
	}


}
