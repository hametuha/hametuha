<?php

namespace Hametuha\Admin\MetaBox;


use WPametu\UI\Admin\EmptyMetaBox;

/**
 * ニュースの関連リンク
 *
 * @package Hametuha\Admin\MetaBox
 */
class NewsRelated extends EmptyMetaBox
{

	protected $post_types = [ 'news' ];

	protected $context = 'normal';

	protected $priority = 'high';

	protected $title = '関連リンク';

	public function doMetaBox( \WP_Post $post, array $screen ) {
		foreach ( [
				'link' => [ 'リンク' ],
		        'book' => [ '商品' ],
		] as $key => list( $label ) ) :
		?>
		<div class="news_related">

			<h3 class="news_related__title">
				<button data-target="" data-template="">追加</button>
				関連リンク
				<br style="clear: both;" />
			</h3>

			<ul></ul>

		</div>

		<?php
		endforeach;
	}
}
