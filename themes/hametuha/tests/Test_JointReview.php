<?php
/**
 * 合評会 当日採点のテスト
 *
 * @package Hametuha
 * @feature-group joint-review
 */

/**
 * Joint review live scoring test case.
 */
class Test_JointReview extends WP_UnitTestCase {

	/**
	 * @var int campaign term id
	 */
	private $term_id;

	/**
	 * @var array post_id => author user_id
	 */
	private $works = [];

	/**
	 * @var int[] participant user ids（a1, a2, a3, reader）
	 */
	private $participants = [];

	/**
	 * テスト用に user_content_relationships テーブルを一度だけ作成する。
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
		// publish 遷移で走る分析記録（依存プラグインなし）でfatalになるためフックを外す。
		remove_action( 'transition_post_status', [ \Hametuha\Hooks\RecordEditActions::get_instance(), 'post_transition' ], 10 );

		// 採点のある公募（評価〆切あり）。
		$this->term_id = $this->factory->term->create( [ 'taxonomy' => 'campaign' ] );
		update_term_meta( $this->term_id, '_campaign_range_end', '2030-01-01' );

		// 著者3名＋作品3作、読み専1名 → 参加者4名（持ち点N=4）。
		$a1     = $this->factory->user->create();
		$a2     = $this->factory->user->create();
		$a3     = $this->factory->user->create();
		$reader = $this->factory->user->create();
		foreach ( [ $a1, $a2, $a3 ] as $author ) {
			$pid = $this->factory->post->create( [
				'post_author' => $author,
				'post_status' => 'publish',
			] );
			wp_set_object_terms( $pid, [ (int) $this->term_id ], 'campaign' );
			$this->works[ $pid ] = $author;
		}
		$this->participants = [ $a1, $a2, $a3, $reader ];
		hametuha_jr_set_participants( $this->term_id, $this->participants );
		hametuha_jr_set_state( $this->term_id, 'open' );
	}

	/**
	 * 状態・参加者・持ち点の基本動作
	 */
	public function test_state_participants_allotment() {
		$this->assertSame( 'open', hametuha_jr_state( $this->term_id ) );
		$this->assertSame( 4, hametuha_jr_allotment( $this->term_id ) );
		$this->assertCount( 3, hametuha_jr_works( $this->term_id ) );
		$this->assertTrue( hametuha_jr_is_participant( $this->term_id, $this->participants[0] ) );
		$this->assertFalse( hametuha_jr_is_participant( $this->term_id, 999999 ) );
	}

	/**
	 * 配分の検証ロジック
	 */
	public function test_validation() {
		$work_ids = array_keys( $this->works );
		$reader   = $this->participants[3];
		// 合計ちょうどN=4 → OK
		$ok = [ $work_ids[0] => 2, $work_ids[1] => 1, $work_ids[2] => 1 ];
		$this->assertTrue( hametuha_jr_validate_distribution( $this->term_id, $reader, $ok ) );

		// 合計が足りない → エラー
		$short = [ $work_ids[0] => 1, $work_ids[1] => 1, $work_ids[2] => 1 ];
		$this->assertWPError( hametuha_jr_validate_distribution( $this->term_id, $reader, $short ) );

		// 自作品に点を入れる → エラー（a1 が自分の作品 work_ids[0] に入れる）
		$a1  = $this->participants[0];
		$own = [ $work_ids[0] => 2, $work_ids[1] => 1, $work_ids[2] => 1 ];
		$this->assertWPError( hametuha_jr_validate_distribution( $this->term_id, $a1, $own ) );

		// 非参加者 → エラー
		$stranger = $this->factory->user->create();
		$this->assertWPError( hametuha_jr_validate_distribution( $this->term_id, $stranger, $ok ) );
	}

	/**
	 * 小数も許可される
	 */
	public function test_validation_allows_decimal() {
		$work_ids = array_keys( $this->works );
		$reader   = $this->participants[3];
		$decimal  = [ $work_ids[0] => 1.6, $work_ids[1] => 1.2, $work_ids[2] => 1.2 ];
		$this->assertTrue( hametuha_jr_validate_distribution( $this->term_id, $reader, $decimal ) );
	}

	/**
	 * 保存と集計（事前点数なし → 結果 = 当日点）
	 */
	public function test_save_and_result() {
		$work_ids = array_keys( $this->works );
		list( $w1, $w2, $w3 ) = $work_ids;
		list( $a1, $a2, $a3, $reader ) = $this->participants;

		$this->assertTrue( hametuha_jr_save_distribution( $this->term_id, $reader, [ $w1 => 2, $w2 => 1, $w3 => 1 ] ) );
		$this->assertTrue( hametuha_jr_save_distribution( $this->term_id, $a1, [ $w2 => 2, $w3 => 2 ] ) );
		$this->assertTrue( hametuha_jr_save_distribution( $this->term_id, $a2, [ $w1 => 2, $w3 => 2 ] ) );
		$this->assertTrue( hametuha_jr_save_distribution( $this->term_id, $a3, [ $w1 => 2, $w2 => 2 ] ) );

		$this->assertCount( 4, hametuha_jr_voters( $this->term_id ) );

		$result = hametuha_joint_review_result( $this->term_id );
		$this->assertFalse( is_wp_error( $result ) );
		// live: w1 = 2+2+2 = 6, w2 = 1+2+2 = 5, w3 = 1+2+2 = 5
		$this->assertEqualsWithDelta( 6, $result['live'][ $w1 ], 0.001 );
		$this->assertEqualsWithDelta( 5, $result['live'][ $w2 ], 0.001 );
		$this->assertEqualsWithDelta( 5, $result['live'][ $w3 ], 0.001 );
		// 事前点数なし → result == live
		$this->assertEqualsWithDelta( 6, $result['result'][ $w1 ], 0.001 );
		// 合計は持ち点 × 人数 = 16
		$this->assertEqualsWithDelta( 16, array_sum( $result['live'] ), 0.001 );
	}

	/**
	 * 再送信で上書きされる（重複しない）
	 */
	public function test_resubmit_overwrites() {
		$work_ids = array_keys( $this->works );
		list( $w1, $w2, $w3 ) = $work_ids;
		$reader = $this->participants[3];

		hametuha_jr_save_distribution( $this->term_id, $reader, [ $w1 => 4 ] );
		$dist = hametuha_jr_user_distribution( $this->term_id, $reader );
		$this->assertEqualsWithDelta( 4, $dist[ $w1 ], 0.001 );

		// 入れ直し
		hametuha_jr_save_distribution( $this->term_id, $reader, [ $w2 => 4 ] );
		$dist = hametuha_jr_user_distribution( $this->term_id, $reader );
		$this->assertEqualsWithDelta( 0, $dist[ $w1 ], 0.001 );
		$this->assertEqualsWithDelta( 4, $dist[ $w2 ], 0.001 );
	}

	/**
	 * モデルの小数往復（point/10で保存し10倍で読む）
	 */
	public function test_model_decimal_roundtrip() {
		$work_ids = array_keys( $this->works );
		$reader   = $this->participants[3];
		$model    = \Hametuha\Model\JointReview::get_instance();
		$model->set_point( $reader, $work_ids[0], 2.8 );
		$rows = $model->get_user_points( $reader, [ $work_ids[0] ] );
		$this->assertCount( 1, $rows );
		$this->assertEqualsWithDelta( 2.8, (float) $rows[0]->point, 0.001 );
	}
}
