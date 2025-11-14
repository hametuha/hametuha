<?php

namespace Hametuha\Hooks;


use Hametuha\Model\Series;
use WPametu\Pattern\Singleton;

/**
 * 作品のステータスを管理する
 */
class StaleStatus extends Singleton {

	/**
	 * @var string[] Supported post types.
	 */
	protected $supported_post_types = [ 'post', 'series' ];

	/**
	 * Is post type supported?
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	protected function is_supported( $post_type ) {
		return in_array( $post_type, $this->supported_post_types, true );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function __construct( array $setting = array() ) {
		add_action( 'add_meta_boxes', [ $this, 'register_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
		add_filter( 'display_post_states', [ $this, 'display_status' ], 10, 2 );
	}

	/**
	 * Register meta box.
	 *
	 * @param string $post_type Post type name.
	 * @return void
	 */
	public function register_meta_box( $post_type ) {
		if ( $this->is_supported( $post_type ) ) {
			add_meta_box( 'stale_status', __( '作品のステータス', 'hametuha' ), [ $this, 'render_meta_box' ], $post_type, 'side', 'low' );
		}
	}

	/**
	 * Save stale date.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save_post( $post_id, $post ) {
		if ( ! $this->is_supported( $post->post_type ) ) {
			return;
		}
		if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_stale_status_nonce' ), 'stale_status' ) ) {
			return;
		}
		$staled = filter_input( INPUT_POST, 'post-is-stale' );
		if ( '1' === $staled ) {
			if ( ! get_post_meta( $post_id, '_stale', true ) ) {
				// No current value, save newly.
				update_post_meta( $post_id, '_stale', current_time( 'mysql' ) );
			}
		} else {
			// Delete staled flag.
			delete_post_meta( $post_id, '_stale' );
		}
	}

	/**
	 * Render meta box for post.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'stale_status', '_stale_status_nonce', false );
		$staled_at = get_post_meta( $post->ID, '_stale', true );
		?>
		<p>
			<label>
				<input type="checkbox" value="1" name="post-is-stale" <?php checked( ! empty( $staled_at ) ); ?> />
				<?php esc_html_e( '投稿を期限切れにする', 'hametuha' ); ?>
				<?php if ( $staled_at ) : ?>
				<small><?php printf( esc_html__( '%sに期限切れと判例', 'hametuha' ), mysql2date( get_option( 'date_format' ), $staled_at ) ); ?></small>
				<?php endif; ?>
			</label>
		</p>
		<p class="description">
			<?php esc_html_e( '一定期間の間、更新されなかった投稿は自動的に「期限切れ」になります。非公開や削除などを検討してください。', 'hametuha' ); ?>
		</p>
		<?php
	}

	/**
	 * Is post stabled?
	 *
	 * @param int|null|\WP_Post $post Post object.
	 * @return bool
	 */
	public static function is_stabled( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}
		$staled_at = get_post_meta( $post->ID, '_stale', true );
		return ! empty( $staled_at );
	}

	/**
	 * Display status at list table.
	 *
	 * @param string[] $statuses Statuses.
	 * @param \WP_Post $post     Post object.
	 * @return string[]
	 */
	public function display_status( $statuses, $post ) {
		if ( ! $this->is_supported( $post->post_type ) ) {
			return $statuses;
		}
		if ( self::is_stabled( $post ) ) {
			$statuses['post-is-staled'] = __( '期限切れ', 'hametuha' );
		}
		return $statuses;
	}

	/**
	 * 指定した期間以上過ぎている投稿を一括で期限切れにする
	 *
	 * @param int  $period  デフォルトは365日
	 * @parma bool $dry_run trueにすると投稿のリストを返す
	 * @return int|\WP_Post[]
	 */
	public function bulk_stale( $period = 365, $dry_run = false ) {
		$args               = [
			'post_type'      => [ 'post', 'series' ],
			'posts_per_page' => 100,
			'paged'          => 1,
			'post_status'    => 'draft',
			'meta_query'     => [
				'key'     => '_stable',
				'compare' => 'NOT EXISTS',
			],
		];
		$args['date_query'] = [
			[
				'before' => sprintf( '-%d days', $period ),
			],
		];
		$list               = [];
		$updated            = 0;
		$has_next           = true;
		while ( $has_next ) {
			$query = new \WP_Query( $args );
			if ( ! $query->have_posts() ) {
				$has_next = false;
				break;
			}
			// 件数を精査する
			foreach ( $query->posts as $post ) {
				$is_stale = false;
				switch ( $post->post_type ) {
					case 'post':
						// タイトルか本文が空
						$is_stale = ! $post->post_title || ! $post->post_content;
						break;
					case 'series':
						// 紐づけられた投稿が一件もない
						$is_stale = Series::get_instance()->get_total( $post ) < 1;
						break;
				}
				if ( ! $is_stale ) {
					// This is not stale.
					continue 1;
				}
				if ( $dry_run ) {
					$list[] = $post;
					continue 1;
				}
				update_post_meta( $post->ID, '_stale', current_time( 'mysql' ) );
				++$updated;
			}
			// ページを増やして次のループへ
			++$args['paged'];
		}
		return $dry_run ? $list : $updated;
	}
}
