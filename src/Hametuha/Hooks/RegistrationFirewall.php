<?php

namespace Hametuha\Hooks;

use WPametu\Pattern\Singleton;

/**
 * 新規登録画面でスパムを予防する処理
 *
 * 1. 合言葉を入力してもらう
 * 2. ハニーポットを設置
 */
class RegistrationFirewall extends Singleton {

	const OPEN_SESAMI = 'おはめつ';

	public function __construct( array $setting = array() ) {
		add_action( 'register_form', [ $this, 'render_form' ], 1 );
		add_filter( 'registration_errors', [ $this, 'registration_errors' ], 10, 3 );
	}

	/**
	 * 新規登録画面にフォームを追加
	 *
	 * @return void
	 */
	public function render_form() {
		?>
		<p>
			<label for="user_message"><?php esc_html_e( '合言葉', 'hametuha' ); ?></label>
			<input type="email" name="user_message" id="user_message" class="input" value="" size="25" required placeholder="<?php esc_attr_e( '「おはめつ」と入力', 'hametuha' ); ?>" />
			<span class="description user-registration-description">「おはめつ」と入力するだけです。スパム登録防止にご協力ください。</span>
			<input type="text" name="user_message2" id="user_message2" class="input" value="" />
		</p>
		<?php
	}


	/**
	 * 合言葉をチェックする
	 *
	 * @param \WP_Error $errors
	 * @return \WP_Error
	 */
	public function registration_errors( $errors, $login, $email ) {
		// 合言葉をチェック
		$message = filter_input( INPUT_POST, 'user_message' );
		if ( $message !== self::OPEN_SESAMI ) {
			$errors->add( 'user_message', __( '<strong>エラー: </strong>合言葉が違います。「おはめつ」と入力してください。', 'hametuha' ) );
		}
		// ハニーポットをチェック
		$honey_pot = filter_input( INPUT_POST, 'user_message2' );
		if ( '' !== $honey_pot ) {
			$errors->add( 'user_honey_pot', __( '<strong>エラー: </strong>不正なアクセスです。もう一度やりなおしてください。', 'hametuha' ) );
		}
		return $errors;
	}

}
