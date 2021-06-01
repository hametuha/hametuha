<?php
/**
 * ユーザーの報酬に関するもの
 *
 *
 */





/**
 * 最低支払い金額
 *
 * @return int
 */
function hametuha_minimum_payment() {
	return 3000;
}


/**
 * プロフィールをオーバーライドする
 */
add_action(
	'edit_user_profile',
	function( WP_User $user ) {
		if ( current_user_can( 'administrator' ) ) {
			wp_nonce_field( 'override_publisher', '_publishernonce', false );
			?>
		<hr />
		<h2><span class="dashicons dashicons-money"></span> 報酬設定</h2>
		<table class="form-table">
			<tr>
				<th><label for="news_guarantee">ニュース最低保証</label></th>
				<td>
					<input class="regular-text" type="number" name="news_guarantee" id="news_guarantee" value="<?php echo esc_attr( \Hametuha\Model\Sales::get_instance()->get_guarantee( $user->ID, 'news' ) ); ?>" />円
				</td>
			</tr>
		</table>
			<?php
		}
	}
);

/**
 * メタ情報を保存する
 */
add_action(
	'edit_user_profile_update',
	function ( $user_id ) {
		$input = \WPametu\Http\Input::get_instance();
		if ( ! $input->verify_nonce( 'override_publisher', '_publishernonce' ) || ! current_user_can( 'edit_users' ) ) {
			return;
		}
		// 報酬データ
		foreach ( [ 'news' ] as $type ) {
			$key = "{$type}_guarantee";
			update_user_meta( $user_id, "_{$key}", $input->post( $key ) );
		}
	}
);
