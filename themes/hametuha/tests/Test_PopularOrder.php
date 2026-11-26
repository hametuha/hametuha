<?php
/**
 * 人気順（orderby=popular）のテスト
 *
 * @package Hametuha
 */

/**
 * functions/override.php
 */
class Test_PopularOrder extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// 投稿作成時の GA イベント送信は Cookie Tasting に依存するので外す（Test_Announcement と同じ）
		remove_action( 'transition_post_status', [ \Hametuha\Hooks\RecordEditActions::get_instance(), 'post_transition' ], 10 );
	}

	public function test_posts_without_pv_are_kept_and_last() {
		$low   = self::factory()->post->create( [ 'post_date' => '2020-01-01 00:00:00' ] );
		$high  = self::factory()->post->create( [ 'post_date' => '2020-01-02 00:00:00' ] );
		$fresh = self::factory()->post->create( [ 'post_date' => '2020-01-03 00:00:00' ] );
		update_post_meta( $low, '_current_pv', 5 );
		update_post_meta( $high, '_current_pv', 100 );
		// $fresh は PV が未集計

		$desc = new WP_Query( [
			'post_type' => 'post',
			'orderby'   => 'popular',
			'order'     => 'desc',
			'fields'    => 'ids',
		] );
		$this->assertSame( [ $high, $low, $fresh ], array_map( 'intval', $desc->posts ), 'PV の無い作品も消えず、降順では最後' );

		$asc = new WP_Query( [
			'post_type' => 'post',
			'orderby'   => 'popular',
			'order'     => 'asc',
			'fields'    => 'ids',
		] );
		$this->assertSame( [ $fresh, $low, $high ], array_map( 'intval', $asc->posts ), '昇順では先頭' );
	}
}
