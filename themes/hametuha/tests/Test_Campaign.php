<?php
/**
 * キャンペーン応募の重複制御をテストする
 *
 * @package Hametuha
 * @feature-group campaign
 */

/**
 * Campaign duplicate submission test case.
 */
class Test_Campaign extends WP_UnitTestCase {

	/**
	 * Set up
	 */
	public function setUp(): void {
		parent::setUp();
		// 投稿の publish 遷移で走る分析記録はテスト環境に依存プラグイン
		// （cookie-tasting）が無く fatal になるため、フックを外す。
		remove_action( 'transition_post_status', [ \Hametuha\Hooks\RecordEditActions::get_instance(), 'post_transition' ], 10 );
	}

	/**
	 * 重複不可のキャンペーンでは、2作品目から campaign タームが剥がされる
	 */
	public function test_duplicate_not_allowed() {
		$user        = $this->factory->user->create();
		$campaign_id = $this->factory->term->create( [ 'taxonomy' => 'campaign' ] );
		// 重複応募を許可しない（初期状態）。

		// 1作品目: campaign が付く。
		$first = $this->factory->post->create( [
			'post_author' => $user,
			'post_status' => 'publish',
		] );
		wp_set_object_terms( $first, [ (int) $campaign_id ], 'campaign' );
		$this->assertTrue( has_term( (int) $campaign_id, 'campaign', $first ), '1作品目には応募できる' );

		// 2作品目: save_post 経由で campaign を付与しようとすると剥がされる。
		$second = $this->factory->post->create( [
			'post_author' => $user,
			'post_status' => 'publish',
			'tax_input'   => [ 'campaign' => [ (int) $campaign_id ] ],
		] );
		// factory が tax_input を確実に処理するよう明示的に再保存。
		wp_set_object_terms( $second, [ (int) $campaign_id ], 'campaign' );
		wp_update_post( [ 'ID' => $second ] );

		$this->assertFalse( has_term( (int) $campaign_id, 'campaign', $second ), '2作品目は重複応募として剥がされる' );
	}

	/**
	 * 重複許可のキャンペーンでは、2作品目も応募できる
	 */
	public function test_duplicate_allowed() {
		$user        = $this->factory->user->create();
		$campaign_id = $this->factory->term->create( [ 'taxonomy' => 'campaign' ] );
		update_term_meta( $campaign_id, '_allow_duplicate', '1' );

		$first = $this->factory->post->create( [
			'post_author' => $user,
			'post_status' => 'publish',
		] );
		wp_set_object_terms( $first, [ (int) $campaign_id ], 'campaign' );

		$second = $this->factory->post->create( [
			'post_author' => $user,
			'post_status' => 'publish',
		] );
		wp_set_object_terms( $second, [ (int) $campaign_id ], 'campaign' );
		wp_update_post( [ 'ID' => $second ] );

		$this->assertTrue( has_term( (int) $campaign_id, 'campaign', $first ), '1作品目は応募できる' );
		$this->assertTrue( has_term( (int) $campaign_id, 'campaign', $second ), '重複許可なら2作品目も応募できる' );
	}

	/**
	 * 同じ作品を再保存しても、自分自身は重複とみなされない
	 */
	public function test_resave_same_post() {
		$user        = $this->factory->user->create();
		$campaign_id = $this->factory->term->create( [ 'taxonomy' => 'campaign' ] );

		$post = $this->factory->post->create( [
			'post_author' => $user,
			'post_status' => 'publish',
		] );
		wp_set_object_terms( $post, [ (int) $campaign_id ], 'campaign' );

		// 再保存。
		wp_update_post( [ 'ID' => $post, 'post_title' => '更新後' ] );

		$this->assertTrue( has_term( (int) $campaign_id, 'campaign', $post ), '同じ作品の再保存では剥がされない' );
	}

	/**
	 * hametuha_user_applied_campaign のヘルパー単体テスト
	 */
	public function test_user_applied_campaign_helper() {
		$user        = $this->factory->user->create();
		$other_user  = $this->factory->user->create();
		$campaign_id = $this->factory->term->create( [ 'taxonomy' => 'campaign' ] );

		$post = $this->factory->post->create( [
			'post_author' => $user,
			'post_status' => 'publish',
		] );
		wp_set_object_terms( $post, [ (int) $campaign_id ], 'campaign' );

		// 投稿自身を除外すると、別作品はないので false。
		$this->assertFalse( hametuha_user_applied_campaign( $campaign_id, $user, $post ) );
		// 除外しなければ true。
		$this->assertTrue( hametuha_user_applied_campaign( $campaign_id, $user, 0 ) );
		// 別ユーザーは応募していない。
		$this->assertFalse( hametuha_user_applied_campaign( $campaign_id, $other_user, 0 ) );
	}

	/**
	 * hametuha_campaign_allows_duplicate のヘルパー単体テスト
	 */
	public function test_allows_duplicate_helper() {
		$campaign_id = $this->factory->term->create( [ 'taxonomy' => 'campaign' ] );
		$this->assertFalse( hametuha_campaign_allows_duplicate( $campaign_id ), '初期値は重複不可' );

		update_term_meta( $campaign_id, '_allow_duplicate', '1' );
		$this->assertTrue( hametuha_campaign_allows_duplicate( $campaign_id ), 'メタが立てば重複可' );
	}
}
