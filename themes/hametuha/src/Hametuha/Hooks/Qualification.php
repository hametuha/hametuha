<?php

namespace Hametuha\Hooks;


use WPametu\Pattern\Singleton;

/**
 * 作品の格付けを行う処理
 */
class Qualification extends Singleton {

	const TAXONOMY = 'qualification';

	/**
	 * {@inheritDoc}
	 */
	public function __construct( array $setting = array() ) {
		add_action( 'init', [ $this, 'register_taxonomy' ] );
	}

	/**
	 * Register taxonomy
	 */
	public function register_taxonomy() {
		\register_taxonomy( self::TAXONOMY, [ 'post' ], [
			'label'                => __( '認定', 'hametuha' ),
			'public'               => true,
			'show_ui'              => true,
			'show_admin_column'    => true,
			'show_in_nav_menus'    => false,
			'show_tagcloud'        => false,
			'hierarchical'         => false,
			'rewrite'              => [
				'with_front' => false,
				'slug'       => 'qualification',
			],
			'capabilities'         => [
				'manage_terms' => 'manage_options',
				'edit_terms'   => 'manage_options',
				'delete_terms' => 'manage_options',
				'assign_terms' => 'edit_others_posts',
			],
			// 複数の認定を付けられるよう、チェックボックスで term_id を送る
			'meta_box_cb'          => [ $this, 'meta_box' ],
			'meta_box_sanitize_cb' => [ $this, 'sanitize_input' ],
		] );
	}

	/**
	 * 送られてきた認定を整える
	 *
	 * 編集画面のチェックボックスは term_id の配列、クイック編集は名前のカンマ区切りで来る。
	 *
	 * @param string       $taxonomy タクソノミー
	 * @param array|string $terms    送られてきた値
	 * @return array
	 */
	public function sanitize_input( $taxonomy, $terms ) {
		if ( is_array( $terms ) ) {
			return taxonomy_meta_box_sanitize_cb_checkboxes( $taxonomy, $terms );
		}
		return taxonomy_meta_box_sanitize_cb_input( $taxonomy, $terms );
	}

	/**
	 * 編集画面の「認定」ボックス
	 *
	 * 付けられるのは編集者以上（assign_terms）。それ以外の人には付いている認定を見せるだけ。
	 *
	 * @param \WP_Post $post 投稿
	 */
	public function meta_box( \WP_Post $post ) {
		$current = wp_get_object_terms( $post->ID, self::TAXONOMY, [ 'fields' => 'ids' ] );
		$current = is_wp_error( $current ) ? [] : array_map( 'intval', $current );
		if ( ! current_user_can( get_taxonomy( self::TAXONOMY )->cap->assign_terms ) ) {
			if ( $current ) {
				foreach ( $current as $term_id ) {
					printf( '<p><strong><span style="color: green;" class="dashicons dashicons-yes"></span> %s</strong></p>', esc_html( get_term( $term_id )->name ) );
				}
			} else {
				printf( '<p class="description">%s</p>', esc_html__( 'この作品にはまだ認定はついていません。', 'hametuha' ) );
			}
			return;
		}
		$name = sprintf( 'tax_input[%s][]', self::TAXONOMY );
		// すべて外したときも送られるように（0 はコアが取り除くので、認定が空になる）
		printf( '<input type="hidden" name="%s" value="0" />', esc_attr( $name ) );
		$terms = get_terms( [
			'taxonomy'   => self::TAXONOMY,
			'hide_empty' => false,
		] );
		if ( ! $terms || is_wp_error( $terms ) ) {
			printf( '<p class="description">%s</p>', esc_html__( '認定がまだ登録されていません。', 'hametuha' ) );
			return;
		}
		foreach ( $terms as $term ) {
			printf(
				'<label style="display: block; margin-bottom: 4px;"><input type="checkbox" name="%s" value="%d"%s /> %s</label>',
				esc_attr( $name ),
				$term->term_id,
				checked( in_array( $term->term_id, $current, true ), true, false ),
				esc_html( $term->name )
			);
		}
	}
}
