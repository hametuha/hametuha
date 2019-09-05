<?php
/**
 * 破滅派の一員としてではなく、電子書籍を発表したい人
 */



/**
 * シークレットゲスト
 *
 * @param int $user_id
 *
 * @return bool
 */
function hametuha_is_secret_guest( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	return (bool) get_user_meta( $user_id, '_is_secret_guest', false );
}

/**
 * シークレットブックであるか否か
 *
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function hametuha_is_secret_book( $post = null ) {
	$post = get_post( $post );
	return (bool) get_post_meta( $post->ID, '_is_secret_book', true );
}

/**
 * シークレットゲストとして変更する
 */
add_action( 'edit_user_profile', function ( WP_User $user ) {
	if ( ! current_user_can( 'edit_users' ) ) {
	    return;
    }
	wp_nonce_field( 'secret_publisher', '_secretpublishernonce', false );
	?>
    <hr/>
    <h2><span class="dashicons dashicons-businessman"></span> ゲスト・ユーザー</h2>
    <p>
        シークレットゲストは自身の名義で電子書籍を発行することができます。
        以下の情報を入力すると、発行元を株式会社破滅派以外にできます。
        値を<code>no</code>にすると、項目自体が表示されません。
    </p>
    <table class="form-table">
        <tr>
            <th><label for="is_secret_publisher">ステータス</label></th>
            <td>
                <label>
                    <input type="checkbox" name="is_secret_publisher" id="is_secret_publisher"
                           value="1"<?php checked( hametuha_is_secret_guest( $user->ID ) ) ?> />
                    シークレットゲストにする
                </label>
            </td>
        </tr>
		<?php foreach ( [
							'publisher_name' => '発行者',
							'publisher_tel' => '電話番号',
							'publisher_mail' => 'メールアドレス',
							'publisher_address' => '住所',
						] as $key => $label ) : ?>
            <tr>
                <th><label for="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $label ); ?></label></th>
                <td>
                    <input class="regular-text" type="text" name="<?php echo esc_attr( $key ) ?>"
                           id="<?php echo esc_attr( $key ) ?>"
                           value="<?php echo esc_html( get_user_meta( $user->ID, '_' . $key, true ) ) ?>"
                    />
                </td>
            </tr>
		<?php endforeach; ?>
    </table>
	<?php
}, 11 );

/**
 * ユーザーの編集画面
 */
add_action( 'edit_user_profile_update', function ( $user_id ) {
	$input = \WPametu\Http\Input::get_instance();
	if ( ! $input->verify_nonce( 'secret_publisher', '_secretpublishernonce' ) || ! current_user_can( 'edit_users' ) ) {
		return;
	}
	// 発行者データ
	if ( $input->post( 'is_secret_publisher' ) ) {
		update_user_meta( $user_id, '_is_secret_guest', $input->post( 'is_secret_publisher' ) );
	} else {
		delete_user_meta( $user_id, '_is_secret_guest' );
	}
	// 発行者名など
	foreach ( [ 'publisher_name', 'publisher_tel', 'publisher_mail', 'publisher_address' ] as $key ) {
		update_user_meta( $user_id, '_'.$key, $input->post( $key ) );
	}
} );
