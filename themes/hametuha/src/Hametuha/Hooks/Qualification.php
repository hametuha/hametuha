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
			'label'             => __( '認定', 'hametuha' ),
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'hierarchical'      => false,
			'rewrite'           => [
				'with_front' => false,
				'slug'       => 'qualification',
			],
			'capabilities'      => [
				'manage_terms' => 'manage_options',
				'edit_terms'   => 'manage_options',
				'delete_terms' => 'manage_options',
				'assign_terms' => 'edit_others_posts',
			],
			'meta_box_cb'       => function ( \WP_Post $post ) {
				$terms = wp_get_post_terms( $post->ID, self::TAXONOMY );
				if ( current_user_can( 'edit_others_posts' ) ) {
					$options = get_terms( self::TAXONOMY, [
						'hide_empty' => false,
					] );
					$name    = sprintf( 'tax_input[%s]', self::TAXONOMY );
					$term    = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->term_id : '';
					?>
					<select name="<?php echo esc_attr( $name ); ?>">
						<option value="" <?php selected( '', $term ); ?>><?php esc_html_e( '指定なし', 'hametuha' ); ?></option>
						<?php
						foreach ( $options as $option ) {
							printf(
								'<option value="%s"%s>%s</option>',
								esc_attr( $option->name ),
								selected( $option->term_id, $term, false ),
								esc_attr( $option->name )
							);
						}
						?>
					</select>
					<?php
				} else {
					if ( $terms && ! is_wp_error( $terms ) ) {
						array_map( function ( $term ) {
							printf( '<p><strong><span style="color: green;" class="dashicons dashicons-yes"></span> %s</strong></p>', esc_html( $term->name ) );
						}, $terms );
					} else {
						sprintf(
							'<p class="description">%s</p>',
							esc_html__( 'この作品にはまだ認証はついていません。', 'hametuha' )
						);
					}
				}
			},
		] );
	}
}
