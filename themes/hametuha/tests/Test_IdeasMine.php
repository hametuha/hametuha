<?php
/**
 * idea/mine の REST 引数バリデーションのテスト
 *
 * @package Hametuha
 */

/**
 * Regression test for #337.
 *
 * REST の validate_callback は ( $value, $request, $param ) の3引数で呼ばれるため、
 * 1引数しか取らない 'is_numeric' を直接渡すと ArgumentCountError で Fatal になっていた。
 */
class Test_IdeasMine extends WP_UnitTestCase {

	/**
	 * offset の validate_callback が3引数で呼ばれても落ちず、正しく判定する
	 */
	public function test_offset_validate_callback_accepts_three_args() {
		$api = new \Hametuha\WpApi\IdeasMine();
		$ref = new ReflectionMethod( $api, 'get_arguments' );
		$ref->setAccessible( true );
		$args = $ref->invoke( $api, 'GET' );

		$this->assertArrayHasKey( 'offset', $args );
		$callback = $args['offset']['validate_callback'];
		$this->assertIsCallable( $callback );

		$request = new WP_REST_Request( 'GET', '/hametuha/v1/idea/mine' );
		// WordPress コアと同じく ( $value, $request, $param ) の3引数で呼び出す。
		$this->assertTrue( call_user_func( $callback, '5', $request, 'offset' ), '数値は通る' );
		$this->assertFalse( (bool) call_user_func( $callback, 'abc', $request, 'offset' ), '非数値は弾く' );
	}
}
