<?php
/**
 * ユーザーの報酬に関するもの
 *
 *
 */



/**
 * 銀行口座情報を取得する
 *
 * @param int $user_id
 *
 * @return array
 */
function hametuha_bank_account( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	$account = [];
	foreach ( [ 'group', 'branch', 'type', 'number', 'name' ] as $key ) {
		$meta_key = "_bank_{$key}";
		$account[ $key ] = get_user_meta( $user_id, $meta_key, true );
	}
	return $account;
}

/**
 * 支払先情報を取得する
 *
 * @param int $user_id
 *
 * @return array
 */
function hametuha_billing_address( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	$address = [];
	foreach ( [ 'name', 'number', 'address' ] as $key ) {
		$meta_key = "_billing_{$key}";
		$address[ $key ] = get_user_meta( $user_id, $meta_key, true );
	}
	return $address;
}

/**
 * ユーザーの銀行口座がオッケーか否かを返す
 *
 * @param int $user_id
 *
 * @return bool
 */
function hametuha_bank_ready( $user_id = 0 ) {
	$account = hametuha_bank_account( $user_id );
	if ( ! $account ) {
		return false;
	}
	foreach ( $account as $value ) {
		if ( ! $value ) {
			return false;
		}
	}
	return true;
}

/**
 * ユーザーの支払い先がオッケーかを返す
 *
 * @param int $user_id
 * @return boolean
 */
function hametuha_billing_ready( $user_id = 0 ) {
	$account = hametuha_billing_address( $user_id );
	if ( ! $account ) {
		return false;
	}
	foreach ( $account as $value ) {
		if ( ! $value ) {
			return false;
		}
	}
	return true;
}

/**
 * プロフィールをオーバーライドする
 */
add_action( 'edit_user_profile', function( WP_User $user ) {
	if ( current_user_can( 'administrator' ) ) {
		wp_nonce_field( 'override_publisher', '_publishernonce', false );
		?>
		<hr />
		<h2><span class="dashicons dashicons-money"></span> 報酬設定</h2>
		<table class="form-table">
			<tr>
				<th><label for="news_guarantee">ニュース最低保証</label></th>
				<td>
					<input class="regular-text" type="number" name="news_guarantee" id="news_guarantee" value="<?= esc_attr( \Hametuha\Model\Sales::get_instance()->get_guarantee( $user->ID, 'news' ) ) ?>" />円
				</td>
			</tr>
		</table>
		<?php
	}
} );

/**
 * メタ情報を保存する
 */
add_action( 'edit_user_profile_update', function ( $user_id ) {
	$input = \WPametu\Http\Input::get_instance();
	if ( ! $input->verify_nonce( 'override_publisher', '_publishernonce' ) || ! current_user_can( 'edit_users' ) ) {
		return;
	}
	// 報酬データ
	foreach ( [ 'news' ] as $type ) {
		$key = "{$type}_guarantee";
		update_user_meta( $user_id, "_{$key}", $input->post( $key ) );
	}
} );