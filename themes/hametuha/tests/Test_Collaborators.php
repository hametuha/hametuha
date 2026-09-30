<?php
/**
 * 作品集の関係者のテスト
 *
 * @package Hametuha
 */

use Hametuha\Model\Collaborators;

/**
 * Collaborators::get_published_collaborators()
 */
class Test_Collaborators extends WP_UnitTestCase {

	public function test_child_post_author_has_writer_label() {
		$owner_id  = self::factory()->user->create( [ 'role' => 'author' ] );
		$writer_id = self::factory()->user->create( [ 'role' => 'author' ] );
		$series_id = $this->create_published( 'series', $owner_id, 0 );
		$this->create_published( 'post', $writer_id, $series_id );

		$users = Collaborators::get_instance()->get_published_collaborators( $series_id );
		$this->assertArrayHasKey( $writer_id, $users, '子投稿の作者が関係者に含まれる' );
		$this->assertSame( '著者', $users[ $writer_id ]->label );
	}

	/**
	 * 公開済みの投稿を作る
	 *
	 * publish に遷移させると計測フック（cookie-tasting プラグイン依存）が走るため、
	 * draft で作ってから DB を直接書き換える。
	 *
	 * @param string $post_type 投稿タイプ
	 * @param int    $author    作者
	 * @param int    $parent    親ID
	 * @return int
	 */
	protected function create_published( $post_type, $author, $parent ) {
		global $wpdb;
		$post_id = self::factory()->post->create( [
			'post_type'   => $post_type,
			'post_status' => 'draft',
			'post_author' => $author,
			'post_parent' => $parent,
		] );
		$wpdb->update( $wpdb->posts, [ 'post_status' => 'publish' ], [ 'ID' => $post_id ] );
		clean_post_cache( $post_id );
		return $post_id;
	}
}
