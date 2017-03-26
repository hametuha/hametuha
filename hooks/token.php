<?php
/**
 * Add token functionality
 *
 * @package hametuha
 */

/**
 * Register post type
 */
add_action( 'init', function() {
	register_post_type( 'web-hook', [
		'label'  => 'Webフック',
	    'public' => false,
	    'show_ui' => true,
	    'show_in_nav_menus' => false,
	    'show_in_menu' => current_user_can( 'list_users' ) ? 'users.php' : 'profile.php',
	    'show_in_admin_bar' => false,
	    'supports' => [ 'title', 'author', 'slug' ],
	] );
} );

/**
 * Add meta box for token
 */
add_action( 'add_meta_boxes', function( $post_type ) {
	if ( 'web-hook' !== $post_type ) {
		return;
	}
	add_meta_box( 'web-hook-info', 'Webフック設定', function( WP_Post $post ) {
		wp_nonce_field( 'webhook', '_webhook_nonce', false );
		?>
		<table class="form-table">
			<tr>
				<th>
					<label for="webhook_token">Webフック</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="webhook_token" id="webhook_token"
					       value="<?= esc_attr( get_post_meta( $post->ID, '_webhook_token', true ) ) ?>"
					       placeholder="まだ生成されていません" readonly />
				</td>
			</tr>
			<tr>
				<th>再生成</th>
				<td>
					<label>
						<input type="checkbox" name="webhook_regen" value="1" />
						新しいWebフックを再生成する
					</label>
				</td>
			</tr>
		</table>
		<?php
	}, $post_type );
} );

/**
 * Save or regenerate token
 *
 * @param int $post_id
 * @param WP_Post $post
 */
add_action( 'save_post', function( $post_id, $post ) {
	if ( 'web-hook' !== $post->post_type ) {
		return;
	}
	if ( ! hametuha_check_nonce( 'webhook', '_webhook_nonce' ) ) {
		return;
	}
	$old_nonce = get_post_meta( $post_id, '_webhook_token', true );
	if ( ! $old_nonce || ( isset( $_POST['webhook_regen'] ) && $_POST['webhook_regen'] ) ) {
		// Regen new token
		$token = hametuha_unique_id( 16 );
		update_post_meta( $post_id, '_webhook_token', $token );
	}
}, 10, 2 );

/**
 * Add columns
 */
add_filter( 'manage_web-hook_posts_columns', function( $column ) {
	$new_column = [];
	foreach ( $column as $key => $value ) {
		$new_column[ $key ] = $value;
		if ( 'title' == $key ) {
			$new_column['slug']  = 'スラッグ';
			$new_column['token'] = 'トークン';
		}
	}
	return $new_column;
} );


add_action( 'manage_web-hook_posts_custom_column', function( $column, $post_id ) {
	switch ( $column ) {
		case 'slug':
			echo esc_html( get_post( $post_id )->post_name );
			break;
		case 'token':
			$token = get_post_meta( $post_id, '_webhook_token', true );
			if ( $token ) {
				printf( '<code>%s</code>', $token );
			} else {
				echo '<span style="color:lightgrey"><span class="dashicons dashicons-no"></span> まだ生成されていません</span>';
			}
			break;
		default:
			// Do nothing.
			break;
	}
}, 10, 2 );
