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
		$this->create_relationships_table();
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

	public function test_add_collaborator_saves_type() {
		$this->create_relationships_table();
		$series_id     = $this->create_published( 'series', self::factory()->user->create( [ 'role' => 'author' ] ), 0 );
		$user_id       = self::factory()->user->create( [ 'role' => 'author' ] );
		$collaborators = Collaborators::get_instance();

		$added = $collaborators->add_collaborator( $series_id, $user_id, 'illustrator', -0.1 );
		$this->assertInstanceOf( WP_User::class, $added );
		$this->assertSame( 'illustrator', $added->collaboration_type );
	}

	public function test_add_collaborator_rejects_unknown_type() {
		$this->create_relationships_table();
		$series_id = $this->create_published( 'series', self::factory()->user->create( [ 'role' => 'author' ] ), 0 );
		$user_id   = self::factory()->user->create( [ 'role' => 'author' ] );

		$result = Collaborators::get_instance()->add_collaborator( $series_id, $user_id, 'unknown', -0.1 );
		$this->assertWPError( $result );
		$this->assertSame( 'invalid_collaborator_type', $result->get_error_code() );
	}

	public function test_update_collaborator_keeps_invitation_waiting() {
		$this->create_relationships_table();
		$series_id     = $this->create_published( 'series', self::factory()->user->create( [ 'role' => 'author' ] ), 0 );
		$user_id       = self::factory()->user->create( [ 'role' => 'author' ] );
		$collaborators = Collaborators::get_instance();
		$collaborators->add_collaborator( $series_id, $user_id, 'writer', -0.1 );

		// 承認待ちのまま役割と報酬を変える。承認済みになってはいけない
		$this->assertTrue( $collaborators->update_collaborator( $series_id, $user_id, 20, 'translator' ) );
		$updated = $collaborators->collaborator( $series_id, $user_id );
		$this->assertSame( 'translator', $updated->collaboration_type );
		$this->assertEqualsWithDelta( -0.2, (float) $updated->ratio, 0.0001 );
		$this->assertFalse( $collaborators->collaborator_exists( $series_id, $user_id, true ) );
	}

	public function test_update_collaborator_changes_type_only() {
		$this->create_relationships_table();
		$series_id     = $this->create_published( 'series', self::factory()->user->create( [ 'role' => 'author' ] ), 0 );
		$user_id       = self::factory()->user->create( [ 'role' => 'author' ] );
		$collaborators = Collaborators::get_instance();
		$collaborators->add_collaborator( $series_id, $user_id, 'writer', -0.1 );
		$collaborators->confirm_invitation( $series_id, $user_id );

		// 報酬はそのまま、役割だけ変える（DB 上は「変更なし」ではないが、報酬が同じでもエラーにならないこと）
		$this->assertTrue( $collaborators->update_collaborator( $series_id, $user_id, 10, 'illustrator' ) );
		// まったく同じ内容で更新しても成功する
		$this->assertTrue( $collaborators->update_collaborator( $series_id, $user_id, 10, 'illustrator' ) );

		$users = $collaborators->get_published_collaborators( $series_id );
		$this->assertSame( 'イラストレーター', $users[ $user_id ]->label );
		$this->assertWPError( $collaborators->update_collaborator( $series_id, $user_id, 10, 'unknown' ) );
	}

	/**
	 * 関係のテーブルを作る
	 */
	protected function create_relationships_table() {
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
