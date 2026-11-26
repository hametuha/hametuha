<?php
/**
 * 認定（qualification）のテスト
 *
 * @package Hametuha
 */

/**
 * Hooks/Qualification.php・hooks/search.php
 */
class Test_Qualification extends WP_UnitTestCase {

	/**
	 * @var int[] スラッグ => term_id
	 */
	private $terms = [];

	/**
	 * @var int[] 名前 => 投稿ID
	 */
	private $posts = [];

	public function set_up() {
		parent::set_up();
		remove_action( 'transition_post_status', [ \Hametuha\Hooks\RecordEditActions::get_instance(), 'post_transition' ], 10 );
		require_once ABSPATH . 'wp-admin/includes/post.php';
		foreach ( [
			'joint-review-winner' => '合評会優勝作品',
			'paper'               => '紙の破滅派掲載作品',
		] as $slug => $name ) {
			$this->terms[ $slug ] = self::factory()->term->create( [
				'taxonomy' => 'qualification',
				'slug'     => $slug,
				'name'     => $name,
			] );
		}
		foreach ( [ 'winner', 'paper', 'both', 'none' ] as $name ) {
			$this->posts[ $name ] = self::factory()->post->create( [ 'post_title' => $name ] );
		}
		wp_set_object_terms( $this->posts['winner'], $this->terms['joint-review-winner'], 'qualification' );
		wp_set_object_terms( $this->posts['paper'], $this->terms['paper'], 'qualification' );
		wp_set_object_terms( $this->posts['both'], array_values( $this->terms ), 'qualification' );
	}

	/**
	 * 絞り込んだ結果の投稿名
	 *
	 * @param mixed $qualified ?qualified の値
	 * @return string[]
	 */
	private function found( $qualified ) {
		$query = new WP_Query( [
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'qualified'      => $qualified,
		] );
		$flip  = array_flip( $this->posts );
		$names = array_map( function ( $id ) use ( $flip ) {
			return $flip[ $id ];
		}, $query->posts );
		sort( $names );
		return $names;
	}

	public function test_filter_by_qualification() {
		$this->assertSame( [ 'both', 'winner' ], $this->found( [ 'joint-review-winner' ] ), '1つ指定' );
		$this->assertSame( [ 'both', 'paper', 'winner' ], $this->found( [ 'joint-review-winner', 'paper' ] ), '複数は OR' );
		$this->assertSame( [ 'both', 'paper', 'winner' ], $this->found( 'joint-review-winner,paper' ), 'カンマ区切りも受ける' );
		$this->assertSame( [], $this->found( [ 'no-such-term' ] ), '無い認定だけなら何もヒットしない' );
		$this->assertSame( [ 'both', 'winner' ], $this->found( [ 'joint-review-winner', 'no-such-term' ] ), '無い認定は捨てる' );
	}

	public function test_filter_does_not_make_taxonomy_archive() {
		$this->go_to( add_query_arg( 'qualified', [ 'paper' ], home_url( '/' ) ) );
		$this->assertFalse( is_tax( 'qualification' ), '?qualified= ではタクソノミーの一覧にならない' );
	}

	public function test_archive_keeps_its_term() {
		$this->set_permalink_structure( '/%year%/%monthnum%/%postname%/' );
		create_initial_taxonomies();
		\Hametuha\Hooks\Qualification::get_instance()->register_taxonomy();
		flush_rewrite_rules();
		$archive = get_term_link( $this->terms['joint-review-winner'] );
		foreach ( [ 'paper', 'no-such-term' ] as $extra ) {
			$this->go_to( add_query_arg( 'qualified', [ $extra ], $archive ) );
			$this->assertTrue( is_tax( 'qualification' ), $extra );
			$this->assertFalse( is_404(), $extra . ' を足しても一覧は 404 にならない' );
			$this->assertSame( $this->terms['joint-review-winner'], get_queried_object_id(), $extra . ' を足しても一覧のタームは変わらない' );
		}
	}

	/**
	 * 管理画面の保存と同じ経路（edit_post）で送る
	 *
	 * @param int   $post_id 投稿ID
	 * @param mixed $value   tax_input[qualification] の値
	 */
	private function save_as_admin_form( $post_id, $value ) {
		edit_post( [
			'post_ID'   => $post_id,
			'post_type' => 'post',
			'tax_input' => [ 'qualification' => $value ],
		] );
		$ids = wp_get_object_terms( $post_id, 'qualification', [ 'fields' => 'ids' ] );
		sort( $ids );
		return $ids;
	}

	public function test_editor_can_assign_multiple() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
		$id       = $this->posts['none'];
		$expected = array_values( $this->terms );
		sort( $expected );
		// チェックボックス（0 は「すべて外した」ときのための空の値）
		$this->assertSame( $expected, $this->save_as_admin_form( $id, [ '0', (string) $this->terms['joint-review-winner'], (string) $this->terms['paper'] ] ), '複数付けられる' );
		$this->assertSame( [], $this->save_as_admin_form( $id, [ '0' ] ), 'すべて外せる' );
		// クイック編集は名前のカンマ区切り
		$this->assertSame( [ $this->terms['paper'] ], $this->save_as_admin_form( $id, '紙の破滅派掲載作品' ), 'クイック編集の名前でも付けられる' );
	}

	public function test_author_cannot_assign() {
		$author = self::factory()->user->create( [ 'role' => 'author' ] );
		wp_set_current_user( $author );
		$id = self::factory()->post->create( [ 'post_author' => $author ] );
		$this->assertSame( [], $this->save_as_admin_form( $id, [ (string) $this->terms['joint-review-winner'] ] ), '投稿者は自分で認定を付けられない' );
		wp_update_post( [
			'ID'        => $this->posts['both'],
			'post_title' => 'both',
		] );
		$this->assertCount( 2, wp_get_object_terms( $this->posts['both'], 'qualification' ) );
	}
}
