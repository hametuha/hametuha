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
	 * @var ReflectionMethod
	 */
	protected $inject;

	/**
	 * Set up
	 */
	public function setUp(): void {
		parent::setUp();
		$this->command = new \Hametuha\Commands\Post();
		$this->to_text = new ReflectionMethod( $this->command, 'to_text' );
		$this->to_text->setAccessible( true );
		$this->inject = new ReflectionMethod( $this->command, 'inject_link_footernotes' );
		$this->inject->setAccessible( true );
		$this->footernote = new ReflectionMethod( $this->command, 'to_footernote_text' );
		$this->footernote->setAccessible( true );
	}

	/**
	 * @var ReflectionMethod
	 */
	protected $footernote;

	/**
	 * 本文（リンク変換済み）から脚注タグ付きテキストを生成する。
	 *
	 * @param string      $html   変換前の本文 HTML。
	 * @param string|null $format 注番号書式（null で既定）。
	 * @return string
	 */
	protected function footernote_text( $html, $format = null ) {
		$post = new WP_Post( (object) [ 'post_content' => $this->inject( $html ), 'filter' => 'raw' ] );
		$args = [ $this->command, $post ];
		if ( null !== $format ) {
			$args[] = $format;
		}
		return $this->footernote->invokeArgs( $this->command, array_slice( $args, 1 ) );
	}

	/**
	 * inject_link_footernotes() を呼び出す。
	 *
	 * @param string $html
	 * @return string
	 */
	protected function inject( $html ) {
		return $this->inject->invoke( $this->command, $html );
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
	 * 注番号書式を指定して to_text() に通す。
	 *
	 * @param string $html
	 * @param string $format 注番号書式。
	 * @return string
	 */
	protected function convert_with_format( $html, $format ) {
		$post = new WP_Post( (object) [ 'post_content' => $this->inject( $html ), 'filter' => 'raw' ] );
		return $this->to_text->invoke( $this->command, $post, '', $format );
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

	/**
	 * リンクがアンカーテキスト＋脚注参照マーカーへ変換されること。
	 */
	public function test_link_becomes_footernote_ref() {
		$html   = '<a href="https://example.com/foo">破滅派</a>を参照。';
		$result = $this->inject( $html );

		$this->assertSame(
			'破滅派<small class="footernote-ref">https://example.com/foo</small>を参照。',
			$result
		);
	}

	/**
	 * URL 内の & が XML 安全にエスケープされること。
	 */
	public function test_link_url_is_xml_escaped() {
		$html   = '<a href="https://example.com/?a=1&b=2" target="_blank" rel="nofollow">記事</a>';
		$result = $this->inject( $html );

		$this->assertStringContainsString(
			'<small class="footernote-ref">https://example.com/?a=1&amp;b=2</small>',
			$result
		);
	}

	/**
	 * 内部アンカー（#foo）や href の無いリンクはテキストのみ残し、脚注化しないこと。
	 */
	public function test_internal_and_hrefless_links_are_not_footernoted() {
		$this->assertSame( '目次へ', $this->inject( '<a href="#toc">目次へ</a>' ) );
		$this->assertSame( '名前', $this->inject( '<a name="anchor">名前</a>' ) );
	}

	/**
	 * リンク由来の脚注が to_text() で *N に変換され、本文に脚注参照が出ること。
	 */
	public function test_link_footernote_appears_in_text_body() {
		$html   = '<a href="https://example.com/">リンク</a>のテスト。';
		$post   = new WP_Post( (object) [ 'post_content' => $this->inject( $html ), 'filter' => 'raw' ] );
		$result = $this->to_text->invoke( $this->command, $post );

		// アンカーテキストは残り、URL は *1 の注番号（半角スペース親文字＋ルビ）になる。
		$this->assertStringContainsString( 'リンク' . $this->body_ref( 1 ), $result );
		// 生のリンクタグや URL は本文に残らない。
		$this->assertStringNotContainsString( '<a ', $result );
		$this->assertStringNotContainsString( 'https://example.com', $result );
	}

	/**
	 * 本文中に出力される注番号（半角スペースを親文字にしたルビ）の期待値。
	 *
	 * @param int $n 注番号。
	 * @return string
	 */
	protected function body_ref( $n, $format = '＊%d' ) {
		return sprintf(
			'<cMojiRuby:0><cRuby:1><cRubyString:%s><CharStyle:FooterNoteRef> <CharStyle:><cMojiRuby:><cRuby:><cRubyString:>',
			sprintf( $format, $n )
		);
	}

	/**
	 * 既存の脚注とリンク脚注が文書順で一貫した連番になり、
	 * 本文の *N と脚注リストの項目数・順序が一致すること（整合性の核）。
	 */
	public function test_existing_footernote_and_link_share_sequential_numbering() {
		$html = implode( '', [
			'序文。<small class="footernote-ref">これは既存の脚注</small>',
			'途中に<a href="https://example.com/link">リンク</a>があり、',
			'最後にもう一つ<small class="footernote-ref">二つ目の脚注</small>。',
		] );

		$injected = $this->inject( $html );
		$post     = new WP_Post( (object) [ 'post_content' => $injected, 'filter' => 'raw' ] );

		// 本文側: 文書順で *1（既存脚注）→ *2（リンク）→ *3（既存脚注）。
		$body = $this->to_text->invoke( $this->command, $post );
		$this->assertStringContainsString( $this->body_ref( 1 ), $body );
		$this->assertStringContainsString( 'リンク' . $this->body_ref( 2 ), $body );
		$this->assertStringContainsString( $this->body_ref( 3 ), $body );

		// リスト側: 同じ文書順で 3 項目生成され、2 番目にリンク URL が入る。
		$notes = hametuha_get_footer_notes( $post );
		$this->assertStringContainsString( 'id="footernote-1"', $notes );
		$this->assertStringContainsString( 'これは既存の脚注', $notes );
		$this->assertStringContainsString( 'id="footernote-2"', $notes );
		$this->assertStringContainsString( 'https://example.com/link', $notes );
		$this->assertStringContainsString( 'id="footernote-3"', $notes );
		$this->assertStringContainsString( '二つ目の脚注', $notes );
	}

	/**
	 * 通常脚注の本文注番号が、半角スペースを親文字にしたルビ形式で出力されること。
	 *
	 * InDesign で注番号（組版）にするための形式。文字スタイルのみの旧形式は使わない。
	 */
	public function test_body_footernote_ref_is_ruby_over_space() {
		$html   = '本文<small class="footernote-ref">脚注</small>。';
		$post   = new WP_Post( (object) [ 'post_content' => $html, 'filter' => 'raw' ] );
		$result = $this->to_text->invoke( $this->command, $post );

		// 親文字＝半角スペース、ルビ＝＊1（既定書式）。
		$this->assertStringContainsString( '本文' . $this->body_ref( 1 ), $result );
		// 本文はルビ形式で出るため、文字スタイルのみの平坦な注番号は残らない。
		$this->assertStringNotContainsString( '<CharStyle:FooterNoteRef>＊1<CharStyle:>', $result );
		$this->assertStringNotContainsString( '<CharStyle:FooterNoteRef>*1<CharStyle:>', $result );
	}

	/**
	 * 脚注が XML ではなく InDesign タグ付きテキストで生成されること。
	 */
	public function test_footernote_is_tagged_text() {
		$html   = 'テキスト<small class="footernote-ref">これは脚注</small>。';
		$result = $this->footernote_text( $html );

		// タグ付きテキストのヘッダが付く。
		$this->assertStringStartsWith( '<UNICODE-MAC>', $result );
		// 段落スタイル + 脚注参照 + 本文。
		$this->assertStringContainsString( '<ParaStyle:FooterNote><CharStyle:FooterNoteRef>＊1<CharStyle:>これは脚注', $result );
		// XML の痕跡は残らない。
		$this->assertStringNotContainsString( '<?xml', $result );
		$this->assertStringNotContainsString( '<li', $result );
		$this->assertStringNotContainsString( '<ol', $result );
	}

	/**
	 * リンク由来の脚注が URL を本文として持つタグ付きテキストになること。
	 */
	public function test_link_footernote_tagged_text_contains_url() {
		$html   = '<a href="https://example.com/?a=1&b=2">記事</a>を参照。';
		$result = $this->footernote_text( $html );

		// & はエスケープされず実体に戻り、URL がそのまま脚注本文になる。
		$this->assertStringContainsString(
			'<ParaStyle:FooterNote><CharStyle:FooterNoteRef>＊1<CharStyle:>https://example.com/?a=1&b=2',
			$result
		);
	}

	/**
	 * 脚注本文中のインライン装飾が文字スタイルへ変換されること。
	 */
	public function test_footernote_inline_styles_converted() {
		$html   = '本文<small class="footernote-ref"><strong>強調</strong>付きの脚注</small>。';
		$result = $this->footernote_text( $html );

		$this->assertStringContainsString( '<CharStyle:Strong>強調<CharStyle:>付きの脚注', $result );
		// 生の HTML タグは残らない。
		$this->assertStringNotContainsString( '<strong>', $result );
	}

	/**
	 * 複数の脚注が文書順で連番のタグ付きテキスト行になること。
	 */
	public function test_multiple_footernotes_are_sequential_lines() {
		$html = implode( '', [
			'序<small class="footernote-ref">一</small>',
			'中<a href="https://example.com/x">リンク</a>',
			'末<small class="footernote-ref">三</small>',
		] );
		$result = $this->footernote_text( $html );

		$this->assertStringContainsString( '<CharStyle:FooterNoteRef>＊1<CharStyle:>一', $result );
		$this->assertStringContainsString( '<CharStyle:FooterNoteRef>＊2<CharStyle:>https://example.com/x', $result );
		$this->assertStringContainsString( '<CharStyle:FooterNoteRef>＊3<CharStyle:>三', $result );
	}

	/**
	 * 脚注が無い場合は空文字を返すこと。
	 */
	public function test_no_footernote_returns_empty() {
		$this->assertSame( '', $this->footernote_text( '脚注もリンクも無い本文。' ) );
	}

	/**
	 * 既定の注番号書式が全角アスタリスクであること（縦書き対応）。
	 */
	public function test_default_note_format_is_fullwidth_asterisk() {
		$body = $this->convert( '本文<small class="footernote-ref">脚注</small>。' );
		$this->assertStringContainsString( '<cRubyString:＊1>', $body );
		// 半角アスタリスクは使わない。
		$this->assertStringNotContainsString( '<cRubyString:*1>', $body );
	}

	/**
	 * --note-format 相当の書式指定が本文・脚注リストの双方に反映されること。
	 */
	public function test_note_format_option_applies_to_body_and_list() {
		$html = '本文<small class="footernote-ref">脚注</small>。';

		// 本文側: ルビが ［1］ になる。
		$body = $this->convert_with_format( $html, '［%d］' );
		$this->assertStringContainsString( $this->body_ref( 1, '［%d］' ), $body );
		$this->assertStringContainsString( '<cRubyString:［1］>', $body );

		// 脚注リスト側: 同じ書式で番号が出る。
		$notes = $this->footernote_text( $html, '［%d］' );
		$this->assertStringContainsString( '<CharStyle:FooterNoteRef>［1］<CharStyle:>脚注', $notes );
	}

	/**
	 * ダガー等の任意記号も書式指定できること。
	 */
	public function test_note_format_supports_arbitrary_symbol() {
		$body = $this->convert_with_format( '本文<small class="footernote-ref">脚注</small>。', '†%d' );
		$this->assertStringContainsString( '<cRubyString:†1>', $body );
	}
}
