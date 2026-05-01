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
		return sprintf( '<p>%s</p>', esc_html__( '参加表明をするイベントの場合は条件を入力してください。', 'hametuha' ) );
	}

	/**
	 * メタボックスの内容を描画
	 *
	 * @param \WP_Post $post
	 */
	public function render( \WP_Post $post ) {
		parent::render( $post );
		$event = new Announcement( $post );
		if ( ! $event->can_participate() ) {
			return;
		}
		$participants = $event->get_participants();
		printf( '<h4 style="margin-top:1em;">参加者一覧（%d名）</h4>', count( $participants ) );
		if ( empty( $participants ) ) {
			echo '<p>まだ参加者はいません。</p>';
			return;
		}
		echo '<ul class="hametuha-participants-list" style="display:flex;flex-wrap:wrap;gap:6px;padding:0;margin:0;list-style:none;">';
		foreach ( $participants as $p ) {
			$user = get_userdata( $p['id'] );
			if ( ! $user ) {
				continue;
			}
			$url = home_url( '/doujin/detail/' . $user->user_nicename . '/' );
			printf(
				'<li style="margin:0;"><a href="%s" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:4px;padding:4px 8px;background:#f6f7f7;border:1px solid #ddd;border-radius:4px;text-decoration:none;">%s<span>%s</span></a></li>',
				esc_url( $url ),
				get_avatar( $user->ID, 24 ),
				esc_html( $user->display_name )
			);
		}
		echo '</ul>';
	}
}
