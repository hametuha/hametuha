<?php

namespace Hametuha\Dashboard;


use Hametuha\Hashboard\Pattern\Screen;

/**
 * List of your works.
 */
class Works extends Screen {

	protected $icon = 'history_edu';

	/**
	 * Should return unique URL slug.
	 *
	 * @return string
	 */
	public function slug() {
		return 'works';
	}

	/**
	 * Should return string.
	 *
	 * @return string
	 */
	public function label() {
		return __( 'あなたの作品', 'hametuha' );
	}

	/**
	 * Get description of this screen.
	 *
	 * @param string $page
	 * @return string
	 */
	public function description( $page = '' ) {
		switch ( $page ) {
			case 'series':
				return __( 'あなたの登録している作品集です。', 'hametuha' );
			case 'comments':
				return __( 'あなたの作品が受けとったコメントです。', 'hametuha' );
			case 'ratings':
				return __( 'これまで受け取った星によるレーティングです。', 'hametuha' );
			case 'lists':
				return __( 'あなたの作品が含まれているリストの一覧です。', 'hametuha' );
			default:
				return __( 'あなたが破滅派に登録した作品です。', 'hametuha' );
		}
	}

	/**
	 * Set children.
	 */
	protected function default_children() {
		return [
			'works'    => '投稿',
			'series'   => '作品集',
			'comments' => 'コメント',
			'reviews'  => 'レビュー',
			'lists'    => 'リスト',
		];
	}

	/**
	 * Render HTML
	 *
	 * @param string $page
	 */
	public function render( $page = '' ) {
		wp_enqueue_script( 'hametuha-hb-posts' );
		switch ( $page ) {
			case 'comments':
				$args['post_type'] = 'comment';
				break;
			case 'reviews':
				$args['post_type'] = 'review';
				break;
			case 'series':
				$args['post_type'] = 'series';
				break;
			case 'lists':
				$args['post_type'] = 'list';
				break;
			default:
				$args['post_type'] = 'post';
				break;
		}
		$args['as'] = 'author';
		hameplate( 'templates/dashboard/post-list', '', $args );
		hameplate( 'templates/dashboard/footer', '', [
			'slug' => 'dashboard-posts-footer',
		] );
	}


}
