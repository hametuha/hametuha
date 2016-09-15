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
	if ( current_user_can( 'edit_users' ) ) {
		wp_nonce_field( 'secret_publisher', '_secretpublishernonce', false );
		?>
		<hr/>
		<h2><span class="dashicons dashicons-businessman"></span> ゲスト・ユーザー</h2>
		<p>
			<label>
				<input type="checkbox" name="is_secret_publisher"
				       value="1"<?php checked( hametuha_is_secret_guest( $user->ID ) ) ?> />
				シークレットゲストにする
			</label>
		</p>
		<?php
	}
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
} );

/**
 * シークレットゲストの編集専用コーナー
 */
add_action( 'hametuha_profile_section', function ( $user ) {
	if ( ! hametuha_is_secret_guest( $user->ID ) ) {
		return;
	}
	wp_nonce_field( 'secret_guest_profile', '_secretguestprofile', false );
	?>
	<section class="secret-section">

		<h3><i class="icon-user7"></i> 発行人設定</h3>
		<p class="desdcription text-muted">
			あなたは自身の名義で電子書籍を発行することができます。
			以下の情報を入力すると、発行元を株式会社破滅派以外にできます。
		</p>
		<?php foreach ( [
			'publisher_name'    => '発行者',
			'publisher_tel'     => '電話番号',
			'publisher_mail'    => 'メールアドレス',
			'publisher_address' => '住所',
		] as $key => $label ) : ?>

			<div class="form-group">
				<label for="<?= $key ?>"><?= $label ?></label>
				<input type="text" class="form-control" name="<?= $key ?>" id="<?= $key ?>"
				       value="<?= esc_attr( get_user_meta( $user->ID, '_' . $key, true ) ) ?>"/>
				<?php if ( 'publisher_name' != $key ) : ?>
					<p class="help-block">
						<code>no</code>と入力すると、項目自体が出力されなくなります。
					</p>
				<?php endif; ?>

			</div>
		<?php endforeach; ?>


	</section>
	<?php
} );

/**
 * 自分のプロフィール編集画面
 */
add_action( 'personal_options_update', function($user_id) {
	$input = \WPametu\Http\Input::get_instance();
	if ( ! $input->verify_nonce( 'secret_guest_profile', '_secretguestprofile' ) ) {
		return;
	}
	foreach ( [ 'publisher_name', 'publisher_tel', 'publisher_mail', 'publisher_address' ] as $key ) {
		update_user_meta( $user_id, '_'.$key, $input->post( $key ) );
	}
} );

