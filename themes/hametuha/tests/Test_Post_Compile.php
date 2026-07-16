<?php
/**
 * hampost compile の InDesign タグ付きテキスト変換をテストする
 *
 * 特に series の序文（_preface）を to_text() に通したときの
 * HTML → InDesign 段落スタイル変換を検証する。
 *
 * @feature-group series
 * @package Hametuha
 */

// phpunit の bootstrap は WP-CLI を読み込まないため、
// \WP_CLI_Command を継承する Command を生成できるようスタブを用意する。
if ( ! class_exists( 'WP_CLI_Command' ) ) {
	class WP_CLI_Command {}
}

/**
 * Test for Hametuha\Commands\Post::to_text()
 */
class Test_Post_Compile extends WP_UnitTestCase {

	/**
	 * @var \Hametuha\Commands\Post
	 */
	protected $command;

	/**
	 * @var ReflectionMethod
	 */
	protected $to_text;

	/**
	 * Set up
	 */
	public function setUp(): void {
		parent::setUp();
		$this->command = new \Hametuha\Commands\Post();
		$this->to_text = new ReflectionMethod( $this->command, 'to_text' );
		$this->to_text->setAccessible( true );
	}

	/**
	 * post_content を持つだけの WP_Post を to_text() に通す。
	 *
	 * compile-series の序文パス
	 * （ new \WP_Post( (object) [ 'post_content' => $preface ] ) ）を再現する。
	 *
	 * @param string $html
	 * @return string
	 */
	protected function convert( $html ) {
		// compile() の序文パスと同じ形（filter=raw で合成 WP_Post を作る）。
		$post = new WP_Post( (object) [ 'post_content' => $html, 'filter' => 'raw' ] );
		return $this->to_text->invoke( $this->command, $post );
	}

	/**
	 * blockquote が InDesign の BlockQuote 段落スタイルに変換されること。
	 */
	public function test_blockquote_converts_to_paragraph_style() {
		$html   = "<blockquote>\nこれは引用です。\n二行目の引用。\n</blockquote>\n本文の段落。";
		$result = $this->convert( $html );

		// 引用の各行が BlockQuote 段落スタイルになる
		$this->assertStringContainsString( '<ParaStyle:BlockQuote>これは引用です。', $result );
		$this->assertStringContainsString( '<ParaStyle:BlockQuote>二行目の引用。', $result );
		// 引用外の行は Normal 段落スタイルになる
		$this->assertStringContainsString( '<ParaStyle:Normal>本文の段落。', $result );
		// 生の HTML タグは残らない
		$this->assertStringNotContainsString( '<blockquote>', $result );
		$this->assertStringNotContainsString( '</blockquote>', $result );
	}

	/**
	 * blockquote 内でインライン装飾（strong/em）が併用できること。
	 */
	public function test_blockquote_keeps_inline_styles() {
		$html   = "<blockquote>\n<strong>強調</strong>された引用。\n</blockquote>";
		$result = $this->convert( $html );

		$this->assertStringContainsString( '<ParaStyle:BlockQuote>', $result );
		$this->assertStringContainsString( '<CharStyle:Strong>強調<CharStyle:>', $result );
	}

	/**
	 * 既知の制限: 属性付き blockquote（例 <blockquote class="...">）は
	 * 変換されず生タグが残る。編集者は素の <blockquote> を書く必要がある。
	 *
	 * この挙動は現状仕様の記録であり、将来 to_text() を拡張する際の
	 * 回帰チェックポイントとなる。
	 */
	public function test_blockquote_with_attribute_is_not_converted() {
		$html   = '<blockquote class="wp-block-quote">属性付き引用。</blockquote>';
		$result = $this->convert( $html );

		// 現状は変換されず、生タグが残る（＝BlockQuote 段落スタイルにならない）
		$this->assertStringNotContainsString( '<ParaStyle:BlockQuote>', $result );
	}
}
