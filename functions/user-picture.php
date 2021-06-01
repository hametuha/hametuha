<?php

// フックを登録する
\Hametuha\User\Profile\Picture::register_hook();

/**
 * グラバターが有効か否かを調べる
 *
 * @global wpdb $wpdb
 *
 * @param int $user_id
 *
 * @return boolean
 */
function has_gravatar( $user_id ) {
	global $wpdb;
	$mail = $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM {$wpdb->users} WHERE ID = %d", $user_id ) );
	if ( $mail ) {
		$hash     = md5( strtolower( $mail ) );
		$endpoint = "http://www.gravatar.com/avatar/{$hash}?s=1&d=404";
		$ch       = curl_init();
		curl_setopt_array(
			$ch,
			array(
				CURLOPT_URL            => $endpoint,
				CURLOPT_HEADER         => true,
				CURLOPT_NOBODY         => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'],
				CURLOPT_TIMEOUT        => 5,
				CURLOPT_FOLLOWLOCATION => false,
			)
		);
		$header = explode( "(\r\n|\r|\n){2}", curl_exec( $ch ), 2 );
		curl_close( $ch );

		return ( false !== strpos( $header[0], '200 OK' ) );
	} else {
		return false;
	}
}

/**
 * ユーザーがオリジナルのプロファイル画像を設定しているか否か
 *
 * @param int $user_id
 *
 * @return boolean
 */
function has_original_picture( $user_id ) {
	/** @var \Hametuha\User\Profile\Picture $instance */
	$instance = \Hametuha\User\Profile\Picture::get_instance();

	return $instance->has_profile_pic( $user_id );
}


/**
 * プロフィール写真を変更する
 *
 * @param \WP_User $user
 */
add_action(
	'edit_user_profile',
	function ( \WP_User $user ) {
		/** @var \Hametuha\User\Profile\Picture $instance */
		$instance = \Hametuha\User\Profile\Picture::get_instance();

		$src = get_template_directory_uri() . '/assets/img/mystery-man.png';

		$avatar  = get_avatar( $user->ID, 80 );
		$new_img = preg_replace( '/class=\'[^\']+\'/u', 'class="new-img" data-src="' . $src . '"', $avatar );
		?>
	<hr/>

	<h3><span class="dashicons dashicons-id-alt"></span> プロフィール写真</h3>

	<table class="form-table">
		<tr>
			<th>プロフィール写真</th>
			<td class="image-picker">
				<p class="image-holder">
					<?php echo $avatar; ?>
					<span class="dashicons dashicons-arrow-right-alt"></span>
					<?php echo $new_img; ?>
				</p>
				<input type="hidden" name="profile_pick_id"
					   value="<?php echo $instance->has_profile_pic( $user->ID ) ?: ''; ?>"/>
				<a class="button-primary" href="#">変更</a>
				<a class="button" href="#">削除</a>
			</td>
		</tr>
	</table>

		<?php
	}
);

/**
 * プロフィール画像を代わりに更新する
 *
 * @param int $user_id
 * @param object $old_user_data
 */
add_action(
	'profile_update',
	function ( $user_id, $old_user_data ) {
		if ( isset( $_POST['profile_pick_id'] ) && current_user_can( 'edit_users' ) ) {

			/** @var \Hametuha\User\Profile\Picture $instance */
			$instance = \Hametuha\User\Profile\Picture::get_instance();

			$profile_pick_id = $_POST['profile_pick_id'];
			if ( is_numeric( $profile_pick_id ) ) {
				$attachment = get_post( $profile_pick_id );
				if ( $attachment && 'attachment' == $attachment->post_type ) {
					// 画像のID
					$instance->assign_user_pic( $user_id, $attachment->ID );
					wp_update_post(
						[
							'ID'          => $attachment->ID,
							'post_author' => $user_id,
						]
					);
					update_post_meta( $attachment->ID, $instance->post_meta_key, 1 );
				}
			} else {
				$instance->detach_user_pic( $user_id );
			}
		}
	},
	10,
	2
);

