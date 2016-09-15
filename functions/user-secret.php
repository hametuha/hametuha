<?php
/**
 * 破滅派の一員としてではなく、電子書籍を発表したい人
 */

add_action( 'edit_user_profile', function( WP_User $user ) {
	if ( current_user_can( 'edit_users' ) ) {
		wp_nonce_field( 'secret_publisher', '_secretpublishernonce', false );
		?>
		<hr />
		<h2><span class="dashicons dashicons-businessman"></span> ゲスト・ユーザー</h2>
		<table class="form-table">
			<?php foreach ( [
				'publisher_name' => '発行者',
				'publisher_tel'  => '電話番号',
				'publisher_mail' => 'メールアドレス',
				'publisher_address' => '住所',
			] as $key => $label ) : ?>
				<tr>
					<th><label for="<?= $key ?>"><?= $label ?></label></th>
					<td>
						<input type="text" class="regular-text" name="<?= $key ?>" id="<?= $key ?>" value="<?= esc_attr( get_user_meta( $user->ID, '_'.$key, true ) ) ?>" />
						<?php if ( 'publisher_name' != $key ) : ?>
							<p class="description">
								<code>no</code>と入力すると、項目自体が出力されなくなります。
							</p>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}
}, 11 );



/**
 * メタ情報を保存する
 */
add_action( 'edit_user_profile_update', function ( $user_id ) {
	$input = \WPametu\Http\Input::get_instance();
	if ( ! $input->verify_nonce( 'secret_publisher', '_secretpublishernonce' ) || ! current_user_can( 'edit_users' ) ) {
		return;
	}
	// 発行者データ
	foreach ( [ 'publisher_name', 'publisher_tel', 'publisher_mail', 'publisher_address' ] as $key ) {
		update_user_meta( $user_id, '_'.$key, $input->post( $key ) );
	}
} );
