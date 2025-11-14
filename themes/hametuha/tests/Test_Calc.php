<?php
/**
 * 計算系のテスト
 *
 */
class Test_Calc extends WP_UnitTestCase {

	/**
	 * 連続する範囲のテスト
	 */
	public function test_continuous_ranges() {
		// flash (0-2000) と short (2000-16000) は連続しているので統合される
		$ranges = hametuha_length_ranges( [ 'flash', 'short' ] );
		$this->assertSame(
			[
				[
					'min' => 0,
					'max' => 16000,
				],
			],
			$ranges
		);
	}

	/**
	 * 不連続な範囲のテスト
	 */
	public function test_discontinuous_ranges() {
		// short (2000-16000) と novella (40000-80000) は連続していないので分割される
		$ranges = hametuha_length_ranges( [ 'short', 'novella' ] );
		$this->assertSame(
			[
				[
					'min' => 2000,
					'max' => 16000,
				],
				[
					'min' => 40000,
					'max' => 80000,
				],
			],
			$ranges
		);
	}

	/**
	 * 単一範囲のテスト
	 */
	public function test_single_range() {
		$ranges = hametuha_length_ranges( [ 'novelette' ] );
		$this->assertSame(
			[
				[
					'min' => 16000,
					'max' => 40000,
				],
			],
			$ranges
		);
	}

	/**
	 * 空配列のテスト
	 */
	public function test_empty_array() {
		$ranges = hametuha_length_ranges( [] );
		$this->assertSame( [], $ranges );
	}

	/**
	 * 無効なカテゴリーのテスト
	 */
	public function test_invalid_categories() {
		$ranges = hametuha_length_ranges( [ 'invalid', 'notexist' ] );
		$this->assertSame( [], $ranges );
	}

	/**
	 * すべての範囲が連続しているテスト
	 */
	public function test_all_continuous() {
		// flash, short, novelette は連続している
		$ranges = hametuha_length_ranges( [ 'flash', 'short', 'novelette' ] );
		$this->assertSame(
			[
				[
					'min' => 0,
					'max' => 40000,
				],
			],
			$ranges
		);
	}

	/**
	 * 順序がバラバラでもソートされるテスト
	 */
	public function test_unsorted_input() {
		// 入力順序に関わらず、min値でソートされる
		$ranges = hametuha_length_ranges( [ 'novelette', 'flash', 'short' ] );
		$this->assertSame(
			[
				[
					'min' => 0,
					'max' => 40000,
				],
			],
			$ranges
		);
	}
}
