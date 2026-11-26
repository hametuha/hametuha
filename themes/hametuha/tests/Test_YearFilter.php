<?php
/**
 * 投稿年の絞り込み（year_from・year_to）のテスト
 *
 * @package Hametuha
 */

/**
 * hooks/search.php・functions/calculate.php（#281）
 */
class Test_YearFilter extends WP_UnitTestCase {

	/**
	 * @var int[] 年 => 投稿ID
	 */
	private $posts = [];

	public function set_up() {
		parent::set_up();
		remove_action( 'transition_post_status', [ \Hametuha\Hooks\RecordEditActions::get_instance(), 'post_transition' ], 10 );
		$dates = [
			2018 => '2018-12-31 23:59:59',
			2019 => '2019-01-01 00:00:00',
			2020 => '2020-06-15 12:00:00',
			2022 => '2022-01-01 00:00:00',
		];
		foreach ( $dates as $year => $date ) {
			$this->posts[ $year ] = self::factory()->post->create( [ 'post_date' => $date ] );
		}
	}

	/**
	 * 絞り込んだ結果の年
	 *
	 * @param array $vars Query vars.
	 * @return int[]
	 */
	private function years( array $vars ) {
		$query = new WP_Query( array_merge( [
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'fields'         => 'ids',
		], $vars ) );
		$flip = array_flip( $this->posts );
		return array_values( array_map( function ( $id ) use ( $flip ) {
			return $flip[ $id ];
		}, $query->posts ) );
	}

	public function test_range_is_inclusive() {
		$this->assertSame( [ 2019, 2020 ], $this->years( [ 'year_from' => 2019, 'year_to' => 2020 ] ), '両端の年を含む' );
	}

	public function test_one_side() {
		$this->assertSame( [ 2018, 2019 ], $this->years( [ 'year_to' => 2019 ] ), '2019年まで' );
		$this->assertSame( [ 2020, 2022 ], $this->years( [ 'year_from' => 2020 ] ), '2020年から' );
	}

	public function test_reversed_range() {
		$this->assertSame( [ 2019, 2020 ], $this->years( [ 'year_from' => 2020, 'year_to' => 2019 ] ), '逆なら入れ替える' );
	}

	public function test_invalid_year_is_ignored() {
		$this->assertSame( [ 2018, 2019, 2020, 2022 ], $this->years( [ 'year_from' => 'abc', 'year_to' => 1999 ] ), '選べない年は無視する' );
		$this->assertSame( [ 2018, 2019, 2020, 2022 ], $this->years( [ 'year_to' => '2019-12-31' ] ), '4桁の数字以外は無視する' );
	}

	public function test_and_with_existing_date_query() {
		$this->assertSame( [ 2019 ], $this->years( [
			'year_to'    => 2019,
			'date_query' => [ 'after' => '2019-01-01', 'inclusive' => true ],
		] ), '単一条件の形の既存条件も残る' );
		$this->assertSame( [ 2018, 2019 ], $this->years( [
			'year_to'    => 2019,
			'date_query' => [
				'relation' => 'OR',
				[ 'year' => 2018 ],
				[ 'year' => 2022 ],
				[ 'year' => 2019 ],
			],
		] ), 'OR の既存条件とは AND でつなぐ' );
	}
}
