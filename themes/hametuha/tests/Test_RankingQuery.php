<?php
/**
 * 期間ランキングのクエリをテストする
 *
 * @feature-group ranking
 * @package Hametuha
 */

/**
 * RankingQuery のテスト
 */
class Test_RankingQuery extends WP_UnitTestCase {

	/**
	 * PV を記録するテーブル（本番では Gianism の GA 連携が作る）
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Set up
	 */
	public function setUp(): void {
		parent::setUp();
		// 公開時の記録は Cookie Tasting に依存し、テストでは読み込まれていない
		remove_action( 'transition_post_status', [ \Hametuha\Hooks\RecordEditActions::get_instance(), 'post_transition' ], 10 );
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpg_ga_ranking';
		// テスト中の CREATE TABLE は一時テーブルになり、終わると消える
		$wpdb->query( "CREATE TABLE IF NOT EXISTS {$this->table} (
			ID bigint NOT NULL AUTO_INCREMENT,
			category varchar(64) NOT NULL,
			object_id bigint unsigned NOT NULL,
			object_value bigint NOT NULL,
			calc_date date NOT NULL,
			PRIMARY KEY (ID)
		)" );
	}

	/**
	 * PV を記録する
	 *
	 * @param int    $post_id
	 * @param int    $pv
	 * @param string $date
	 */
	protected function record( $post_id, $pv, $date ) {
		global $wpdb;
		$wpdb->insert( $this->table, [
			'category'     => 'general',
			'object_id'    => $post_id,
			'object_value' => $pv,
			'calc_date'    => $date,
		] );
	}

	/**
	 * 2回目の取得（WP_Query のキャッシュに当たる）でも PV と順位が残る
	 *
	 * 以前は2回目以降、SQL で足した pv が消えて 0PV と表示され、同点の順位も崩れていた。
	 */
	public function test_weekly_ranking_keeps_pv_on_second_query() {
		$posts = self::factory()->post->create_many( 3 );
		// 2026-11-09（月）〜 2026-11-15（日）の週。2本目と3本目は同点
		$this->record( $posts[0], 20, '2026-11-09' );
		$this->record( $posts[0], 10, '2026-11-15' );
		$this->record( $posts[1], 15, '2026-11-10' );
		$this->record( $posts[2], 15, '2026-11-12' );
		// 週の外は数えない
		$this->record( $posts[2], 100, '2026-11-16' );

		$args = [
			'ranking'  => 'weekly',
			'year'     => 2026,
			'monthnum' => 11,
			'day'      => 15,
		];
		$expected = [
			[ $posts[0], 30, 1 ],
			[ $posts[1], 15, 2 ],
			[ $posts[2], 15, 2 ],
		];
		foreach ( [ 1, 2 ] as $try ) {
			$query  = new WP_Query( $args );
			$actual = array_map( function ( $post ) {
				return [ $post->ID, (int) ( $post->pv ?? 0 ), $post->rank ?? null ];
			}, $query->posts );
			// 同点の並びは保証されないので、ID 順に並べて比べる
			usort( $actual, function ( $a, $b ) {
				return [ -$a[1], $a[0] ] <=> [ -$b[1], $b[0] ];
			} );
			$this->assertSame( $expected, $actual, "{$try}回目" );
		}
	}
}
