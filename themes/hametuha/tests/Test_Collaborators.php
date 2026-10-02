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

	public function test_collaborator_exists_checks_relation_type() {
		global $wpdb;
		$wpdb->query( <<<SQL
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}user_content_relationships (
				ID bigint unsigned NOT NULL AUTO_INCREMENT,
				rel_type varchar(10) NOT NULL DEFAULT 'favorite',
				object_id bigint unsigned NOT NULL,
				user_id bigint unsigned NOT NULL,
				location decimal(10,9) NOT NULL,
				content text NOT NULL,
				updated datetime NOT NULL,
				PRIMARY KEY (ID)
			)
SQL
		);
		$series_id     = $this->create_published( 'series', self::factory()->user->create(), 0 );
		$user_id       = self::factory()->user->create();
		$collaborators = Collaborators::get_instance();
		$relation      = [
			'object_id' => $series_id,
			'user_id'   => $user_id,
			'location'  => 0.1,
			'content'   => '',
			'updated'   => current_time( 'mysql' ),
		];

		// 評価（rank）しただけのユーザーは協力者ではない
		$wpdb->insert( "{$wpdb->prefix}user_content_relationships", array_merge( $relation, [ 'rel_type' => 'rank' ] ) );
		$this->assertFalse( $collaborators->collaborator_exists( $series_id, $user_id ) );

		$wpdb->insert( "{$wpdb->prefix}user_content_relationships", array_merge( $relation, [ 'rel_type' => 'collabo' ] ) );
		$this->assertTrue( $collaborators->collaborator_exists( $series_id, $user_id ) );
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
