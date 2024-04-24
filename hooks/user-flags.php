<?php
/**
 * ユーザーにフラグをつける
 */

/**
 * Dose user has specified flag?
 *
 * @param int|null|WP_Post $post Post object.
 * @return bool
 */
function hametuha_author_has_flag( $flag, $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	return hametuha_user_has_flag( $post->post_author, $flag );
}

/**
 * Is user has spam?
 *
 * @param int    $user_id User ID.
 * @param string $flag    Flag. spam, professional, certified
 * @return bool
 */
function hametuha_user_has_flag( $user_id, $flag ) {
	return (bool) get_user_meta( $user_id, 'flag_' . $flag, true );
}

/**
 * User flags.
 *
 * @param bool $exclude_negative Exclude negative flags.
 * @return array[]
 */
function hametuha_user_flags( $exclude_negative = false ) {
	$flags = [
		[
			'id'    => 'professional',
			'label' => __( 'プロ作家', 'hametuha' ),
			'admin' => true,
		],
		[
			'id'    => 'certified',
			'label' => __( '殿堂入り', 'hametuha' ),
			'admin' => true,
		],
	];
	if ( ! $exclude_negative ) {
		$flags[] = [
			'id'    => 'spam',
			'label' => __( '告知なし', 'hametuha' ),
			'admin' => false,
		];
	}
	return $flags;
}

/**
 * Update flags.
 *
 * @param int $user_id User ID.
 */
add_action( 'edit_user_profile_update', function( $user_id ) {
	foreach ( hametuha_user_flags() as $flag ) {
		$key = 'flag_' . $flag['id'];
		update_user_meta( $user_id, $key, (int) filter_input( INPUT_POST, $key ) );
	}
} );

/**
 * Add flag fields.
 *
 * @param WP_User $user User object.
 */
add_action( 'edit_user_profile', function ( $user ) {
	?>
	<hr />
	<h3><?php esc_html_e( 'ユーザー情報', 'hametuha' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'フラグ', 'hametuha' ); ?></th>
			<td>
				<?php foreach ( hametuha_user_flags() as $flag ) : ?>
				<p>
					<label>
						<input type="checkbox" value="1"
							name="<?php echo 'flag_' . esc_attr( $flag['id'] ); ?>" <?php checked( hametuha_user_has_flag( $user->ID, $flag['id'] ), true ); ?> />
						<?php echo esc_html( $flag['label'] ); ?>
					</label>
				</p>
				<?php endforeach; ?>
			</td>
		</tr>
	</table>
	<?php
}, 100 );

/**
 * Display user flags.
 *
 * @param WP_User $user
 */
add_action( 'show_user_profile', function( $user ) {
	?>
	<hr />
	<h3><?php esc_html_e( 'ユーザー情報', 'hametuha' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'フラグ', 'hametuha' ); ?></th>
			<td>
				<?php foreach ( hametuha_user_flags() as $flag ) : ?>
				<p>
					<?php
					if ( hametuha_user_has_flag( $user->ID, $flag['id'] ) ) {
						echo '<span class="dashicons dashicons-yes" style="color:green;"></span>';
					} else {
						echo '<span class="dashicons dashicons-no" style="color:lightgrey;"></span>';
					}
					echo esc_html( $flag['label'] );
					?>
				</p>
				<?php endforeach; ?>
			</td>
		</tr>
	</table>
	<?php
} );

/**
 * Render flags.
 */
add_action( 'hashboard_before_fields_rendered', function( $slug, $page, $name ) {
	if ( ( 'profile' !== $slug ) || ( '' !== $page ) || ( 'names' !== $name ) ) {
		return;
	}
	foreach ( hametuha_user_flags() as $flag ) {
		if ( ! hametuha_user_has_flag( get_current_user_id(), $flag['id'] ) ) {
			continue;
		}
		$style = $flag['admin'] ? 'success' : 'danger';
		?>
		<span style="margin-bottom: 20px;" class="badge badge-<?php echo esc_attr( $style ); ?>">
			<?php echo esc_html( $flag['label'] ); ?>
		</span>
		<?php
	}
}, 10, 3 );

/**
 * 作者の投稿数を返す
 */
add_action( 'save_post', function( $post_id, $post ) {
	if ( 'post' !== $post->post_type ) {
		return;
	}
	update_user_meta( $post->post_author, 'work_count', count_user_posts( $post->post_author, 'post', true ) );
}, 10, 2 );
