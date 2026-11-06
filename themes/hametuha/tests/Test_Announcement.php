<?php
/**
 * 告知（参加型）の参加作品一覧をテストする
 *
 * @package Hametuha
 */

use Hametuha\ThePost\Announcement;

/**
 * Announcement::get_committed_posts() のテスト（#310）
 */
class Test_Announcement extends WP_UnitTestCase {

	/**
	 * Set up
	 */
	public function setUp(): void {
		parent::setUp();
		// 投稿作成時の GA イベント送信は Cookie Tasting に依存するので外す。
		remove_action( 'transition_post_status', [ \Hametuha\Hooks\RecordEditActions::get_instance(), 'post_transition' ], 10 );
	}

	/**
	 * 参加型の告知を作る
	 *
	 * @param array $meta 追加のメタ。
	 * @return Announcement
	 */
	protected function create_announcement( array $meta = [] ) {
		$post_id = self::factory()->post->create( [
			'post_type' => 'announcement',
			'post_date' => '2026-01-01 00:00:00',
		] );
		$meta    = array_merge( [
			Announcement::COMMIT_TYPE      => 2,
			Announcement::COMMIT_POST_TYPE => 'post',
			Announcement::COMMIT_START     => '2026-01-01 00:00:00',
			Announcement::COMMIT_END       => '2026-01-31 23:59:59',
		], $meta );
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
		return new Announcement( get_post( $post_id ) );
	}

	/**
	 * 期間内の投稿だけが返る
	 */
	public function test_period() {
		$in  = self::factory()->post->create( [ 'post_date' => '2026-01-15 12:00:00' ] );
		$out = self::factory()->post->create( [ 'post_date' => '2026-02-15 12:00:00' ] );

		$ids = wp_list_pluck( $this->create_announcement()->get_committed_posts(), 'ID' );
		$this->assertContains( $in, $ids );
		$this->assertNotContains( $out, $ids );
	}

	/**
	 * カテゴリー指定があると、そのカテゴリーの投稿だけが返る
	 */
	public function test_category() {
		$cat   = self::factory()->category->create();
		$other = self::factory()->category->create();
		$in    = self::factory()->post->create( [
			'post_date'     => '2026-01-15 12:00:00',
			'post_category' => [ $cat ],
		] );
		$out   = self::factory()->post->create( [
			'post_date'     => '2026-01-15 12:00:00',
			'post_category' => [ $other ],
		] );

		$announcement = $this->create_announcement( [
			Announcement::COMMIT_CATEGORY => [ (string) $cat ],
		] );
		$ids          = wp_list_pluck( $announcement->get_committed_posts(), 'ID' );
		$this->assertSame( [ $in ], $ids );
		$this->assertNotContains( $out, $ids );
	}
}
