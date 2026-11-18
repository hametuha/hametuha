<?php
/**
 * 公募の採点結果（hametuha_campaign_record）のテスト
 *
 * @package Hametuha
 */

/**
 * Campaign record test case.
 */
class Test_CampaignRecord extends WP_UnitTestCase {

	/**
	 * 評価テーブル（user_content_relationships）を作る。
	 *
	 * @param \WP_UnitTest_Factory $factory
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		global $wpdb;
		$table = $wpdb->prefix . 'user_content_relationships';
		// phpcs:ignore WordPress.DB
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS {$table} (
				ID bigint unsigned NOT NULL AUTO_INCREMENT,
				rel_type varchar(10) NOT NULL DEFAULT 'favorite',
				object_id bigint unsigned NOT NULL,
				user_id bigint unsigned NOT NULL,
				location decimal(10,9) NOT NULL,
				content text NOT NULL,
				updated datetime NOT NULL,
				PRIMARY KEY (ID),
				KEY user (rel_type,object_id,user_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
		);
	}

	/**
	 * Set up
	 */
	public function setUp(): void {
		parent::setUp();
		remove_action( 'transition_post_status', [ \Hametuha\Hooks\RecordEditActions::get_instance(), 'post_transition' ], 10 );
	}

	/**
	 * 採点せずコメントだけした人も、読み専の参加者としてキーが揃う。
	 */
	public function test_commenter_without_rating_has_all_keys() {
		$term_id = $this->factory->term->create( [ 'taxonomy' => 'campaign' ] );
		update_term_meta( $term_id, '_campaign_range_end', '2030-01-01' );
		$author  = $this->factory->user->create();
		$post_id = $this->factory->post->create( [
			'post_author' => $author,
			'post_status' => 'publish',
		] );
		wp_set_object_terms( $post_id, [ (int) $term_id ], 'campaign' );
		$commenter = $this->factory->user->create();
		$this->factory->comment->create( [
			'comment_post_ID' => $post_id,
			'user_id'         => $commenter,
		] );

		$record = hametuha_campaign_record( get_term( $term_id, 'campaign' ) );

		$this->assertArrayHasKey( $commenter, $record['participants'] );
		$row = $record['participants'][ $commenter ];
		$this->assertFalse( $row['author'] );
		$this->assertSame( 1, $row['comment_total'] );
		$this->assertSame( 0, $row['rate_total'] );
		$this->assertSame( [ $post_id => 0 ], $row['records'] );
		$this->assertTrue( $record['participants'][ $author ]['author'] );
	}
}
