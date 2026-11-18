<?php
/**
 * タームの実体が無いタームアーカイブを404にする処理（hooks/term.php の pre_handle_404）のテスト
 *
 * URL の解析を経ず、同じクエリ変数で WP_Query を作ってフィルターに渡す。
 *
 * @package Hametuha
 */

/**
 * Term archive 404 test case.
 */
class Test_TermArchive404 extends WP_UnitTestCase {

	/**
	 * @var string
	 */
	private $tag_slug = 'live-tag';

	/**
	 * Set up
	 */
	public function setUp(): void {
		parent::setUp();
		remove_action( 'transition_post_status', [ \Hametuha\Hooks\RecordEditActions::get_instance(), 'post_transition' ], 10 );
		$post_id = $this->factory->post->create( [ 'post_status' => 'publish' ] );
		wp_set_post_tags( $post_id, [ $this->tag_slug ] );
	}

	/**
	 * クエリを作り、404 処理にかける
	 *
	 * @param array $vars Query vars.
	 * @return array [ フィルターの戻り値, WP_Query ]
	 */
	private function handle( $vars ) {
		$query = new WP_Query( $vars );
		return [ apply_filters( 'pre_handle_404', false, $query ), $query ];
	}

	/**
	 * /ranking/0/00/18/ と同じクエリ。最後の区切りが「0」で絞り込みが捨てられるので404にし、記事も空にする。
	 */
	public function test_category_path_ending_with_zero_is_404() {
		list( $preempt, $query ) = $this->handle( [
			'category_name' => 'ranking/0',
			'p'             => '00',
			'page'          => '18',
		] );
		$this->assertTrue( $preempt );
		$this->assertTrue( $query->is_404() );
		$this->assertSame( 0, $query->post_count );
	}

	/**
	 * 実在するタグのアーカイブは404にしない。
	 */
	public function test_existing_tag_is_not_404() {
		list( $preempt, $query ) = $this->handle( [ 'tag' => $this->tag_slug ] );
		$this->assertFalse( $preempt );
		$this->assertFalse( $query->is_404() );
	}

	/**
	 * 複数タグ（OR）の1つでも実在すれば404にしない。並び順にもよらない。
	 */
	public function test_multiple_tags_with_one_missing_is_not_404() {
		foreach ( [ 'aaa-missing,' . $this->tag_slug, $this->tag_slug . ',aaa-missing' ] as $tags ) {
			list( $preempt, $query ) = $this->handle( [ 'tag' => $tags ] );
			$this->assertFalse( $preempt, $tags );
			$this->assertSame( 1, $query->post_count, $tags );
		}
	}

	/**
	 * タグ付きの検索は、存在しないタグでもこの処理では404にしない。
	 */
	public function test_search_with_missing_tag_is_left_to_core() {
		list( $preempt ) = $this->handle( [
			's'   => 'foo',
			'tag' => 'aaa-missing',
		] );
		$this->assertFalse( $preempt );
	}
}
