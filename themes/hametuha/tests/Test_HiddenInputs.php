<?php
/**
 * hametuha_hidden_inputs() のテスト
 *
 * @package Hametuha
 */

/**
 * functions/utility.php
 */
class Test_HiddenInputs extends WP_UnitTestCase {

	/**
	 * 出力した hidden を、ブラウザが送るクエリ文字列に戻す
	 *
	 * @param array $params Params.
	 * @return array
	 */
	private function round_trip( array $params ) {
		ob_start();
		hametuha_hidden_inputs( $params );
		$html = ob_get_clean();
		preg_match_all( '#<input type="hidden" name="([^"]*)" value="([^"]*)" />#', $html, $matches, PREG_SET_ORDER );
		$pairs = array_map( function ( $m ) {
			return rawurlencode( html_entity_decode( $m[1] ) ) . '=' . rawurlencode( html_entity_decode( $m[2] ) );
		}, $matches );
		parse_str( implode( '&', $pairs ), $result );
		return $result;
	}

	public function test_round_trip() {
		$params = [
			's'      => '"愛" & <檸檬>',
			'entity' => 'Tom &amp; Jerry',
			'length' => [ 'short', 'long' ],
			't'      => [ 'SF', 'ミステリー' ],
			'nested' => [ 'a' => '1', 'b' => [ 'x', 'y' ] ],
			'rows'   => [
				[ 'a' => '1', 'b' => '2' ],
				[ 'a' => '3', 'b' => '4' ],
			],
		];
		$this->assertSame( $params, $this->round_trip( $params ), 'フォームで送ると元のクエリに戻る' );
	}

	public function test_escape() {
		ob_start();
		hametuha_hidden_inputs( [ 'q' => '"><script>' ] );
		$html = ob_get_clean();
		$this->assertStringNotContainsString( '<script>', $html );
	}
}
