<?php
/**
 * Class SampleTest
 *
 * @package Hametuha
 */

/**
 * Sample test case.
 */
class StringTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	public function test_markdown() {
		$source = <<<MD
## タイトル
- リスト1
- リスト2
- リスト3
MD;
		$expected = <<<HTML
<h2>タイトル</h2>
<ul>
<li>リスト1</li>
<li>リスト2</li>
<li>リスト3</li>
</ul>

HTML;

		$markdown = hametuha_parse_markdown( $source );


		$this->assertEquals( $expected, $markdown );
	}
}
