<?php

namespace Hametuha\Hooks;

use WPametu\Pattern\Singleton;

/**
 * ログイン中ユーザーへの緊急告知
 *
 * 設定 → 緊急告知 で入力した内容を option に保存し、保存のたびに
 * uploads/notification.json へ書き出す（空なら削除する）。
 * フロントと hashboard では、ログイン中のユーザーがこのファイルを
 * 120秒ごとに取得してトーストで表示する（assets-src/js/components/emergency-notice.js）。
 *
 * 正は option。ファイルは公開用の書き出しにすぎないので、消えても「保存」し直せば戻る。
 *
 * @feature-group emergency-notice
 * @see https://github.com/hametuha/hametuha/issues/452
 */
class EmergencyNotice extends Singleton {

	/**
	 * option 名
	 */
	const OPTION = 'hametuha_emergency_notice';

	/**
	 * 設定画面のスラッグ
	 */
	const PAGE = 'hametuha-emergency-notice';

	/**
	 * 書き出すファイル名（uploads 直下）
	 */
	const FILE = 'notification.json';

	/**
	 * ポーリング間隔（秒）
	 */
	const INTERVAL = 120;

	/**
	 * {@inheritDoc}
	 */
	protected function __construct( array $setting = array() ) {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_setting' ] );
		add_action( 'add_option_' . self::OPTION, [ $this, 'on_add_option' ], 10, 2 );
		add_action( 'update_option_' . self::OPTION, [ $this, 'on_update_option' ], 10, 2 );
		add_filter( 'pre_update_option_' . self::OPTION, [ $this, 'on_unchanged_option' ], 10, 2 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * 既定値
	 *
	 * @return array{id: string, title: string, message: string, until: string}
	 */
	public static function defaults() {
		return [
			'id'      => '',
			'title'   => '',
			'message' => '',
			'until'   => '',
		];
	}

	/**
	 * 保存されている告知
	 *
	 * @return array{id: string, title: string, message: string, until: string}
	 */
	public static function get() {
		return wp_parse_args( (array) get_option( self::OPTION, [] ), self::defaults() );
	}

	/**
	 * 書き出し先のパス
	 *
	 * @return string
	 */
	public static function path() {
		return wp_upload_dir()['basedir'] . '/' . self::FILE;
	}

	/**
	 * 公開 URL（表示中のページのスキームに合わせる）
	 *
	 * @return string
	 */
	public static function url() {
		return set_url_scheme( wp_upload_dir()['baseurl'] . '/' . self::FILE );
	}

	/**
	 * ログイン中ユーザーにポーリング用のスクリプトを読み込む
	 */
	public function enqueue() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		wp_enqueue_script( 'hametuha-emergency-notice' );
		wp_localize_script( 'hametuha-emergency-notice', 'HametuhaEmergencyNotice', [
			'url'      => self::url(),
			'interval' => self::INTERVAL,
		] );
	}

	/**
	 * 設定 → 緊急告知
	 */
	public function admin_menu() {
		add_options_page( '緊急告知', '緊急告知', 'manage_options', self::PAGE, [ $this, 'render' ] );
	}

	/**
	 * Settings API に登録する
	 */
	public function register_setting() {
		register_setting( self::PAGE, self::OPTION, [
			'type'              => 'array',
			'sanitize_callback' => [ $this, 'sanitize' ],
			'default'           => self::defaults(),
		] );
	}

	/**
	 * 入力を整える
	 *
	 * 文面（タイトル・本文）が変わったときだけ id を振り直す。id が変わると、
	 * 一度閉じた人にももう一度表示される。期限だけ直した場合は出し直さない。
	 *
	 * @param mixed $input フォームの値。
	 * @return array{id: string, title: string, message: string, until: string}
	 */
	public function sanitize( $input ) {
		$input = is_array( $input ) ? $input : [];
		if ( ! empty( $input['clear'] ) ) {
			return self::defaults();
		}
		$value = [
			'id'      => '',
			'title'   => sanitize_text_field( $input['title'] ?? '' ),
			'message' => sanitize_textarea_field( $input['message'] ?? '' ),
			'until'   => self::sanitize_until( $input['until'] ?? '' ),
		];
		if ( '' === $value['message'] ) {
			return self::defaults();
		}
		$current = self::get();
		if ( $current['id'] && $current['title'] === $value['title'] && $current['message'] === $value['message'] ) {
			$value['id'] = $current['id'];
		} else {
			$value['id'] = wp_date( 'Ymd-His' );
		}
		return $value;
	}

	/**
	 * 期限（datetime-local の値。サイトのタイムゾーン）を ISO 8601 にする
	 *
	 * @param string $until 入力値。
	 * @return string 不正・空なら空文字。
	 */
	public static function sanitize_until( $until ) {
		$until = trim( (string) $until );
		if ( '' === $until ) {
			return '';
		}
		foreach ( [ 'Y-m-d\TH:i', 'Y-m-d\TH:i:s', DATE_ATOM ] as $format ) {
			$date = \DateTimeImmutable::createFromFormat( $format, $until, wp_timezone() );
			if ( $date ) {
				return $date->format( DATE_ATOM );
			}
		}
		return '';
	}

	/**
	 * 初めて保存されたとき
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  New value.
	 */
	public function on_add_option( $option, $value ) {
		$this->publish( $value );
	}

	/**
	 * 保存されたとき（値が変わらないときは呼ばれない）
	 *
	 * @param mixed $old_value Old value.
	 * @param mixed $value     New value.
	 */
	public function on_update_option( $old_value, $value ) {
		$this->publish( $value );
	}

	/**
	 * 値が変わらないまま保存されたとき
	 *
	 * update_option() は値が同じだと update_option_{$option} を呼ばない。
	 * ファイルが消えたときに「保存」し直して書き出せるよう、ここで書き出す。
	 *
	 * @param mixed $value     New value.
	 * @param mixed $old_value Old value.
	 * @return mixed
	 */
	public function on_unchanged_option( $value, $old_value ) {
		if ( $value === $old_value ) {
			$this->publish( $value );
		}
		return $value;
	}

	/**
	 * option の内容を notification.json に書き出す。本文が空ならファイルを消す
	 *
	 * @param mixed $value Option value.
	 * @return bool 成功したら true。
	 */
	public function publish( $value ) {
		$value = wp_parse_args( (array) $value, self::defaults() );
		$path  = self::path();
		if ( '' === $value['message'] ) {
			if ( ! file_exists( $path ) ) {
				return true;
			}
			$result = wp_delete_file_from_directory( $path, wp_upload_dir()['basedir'] );
		} else {
			$json   = wp_json_encode( array_filter( $value, 'strlen' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			$result = false !== file_put_contents( $path, $json ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
		if ( ! $result && function_exists( 'add_settings_error' ) ) {
			add_settings_error( self::OPTION, 'publish-failed', sprintf( '%s を書き出せませんでした。権限を確認してください。', $path ) );
		}
		return (bool) $result;
	}

	/**
	 * いま公開されている告知（ファイルの中身）
	 *
	 * @return array|null ファイルが無ければ null。
	 */
	public static function published() {
		$path = self::path();
		if ( ! file_exists( $path ) ) {
			return null;
		}
		$json = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return is_array( $json ) ? $json : [];
	}

	/**
	 * 設定画面
	 */
	public function render() {
		$value     = self::get();
		$published = self::published();
		$until     = '';
		if ( $value['until'] ) {
			$until = ( new \DateTimeImmutable( $value['until'] ) )->setTimezone( wp_timezone() )->format( 'Y-m-d\TH:i' );
		}
		$expired = $value['until'] && strtotime( $value['until'] ) < time();
		?>
		<div class="wrap">
			<h1>緊急告知</h1>
			<p>
				サーバーの再起動など、一瞬サイトが止まる作業の前に、<strong>いまサイトを見ているログイン中のユーザー</strong>へ知らせます。
				フロントと hashboard で、最大<?php echo esc_html( self::INTERVAL ); ?>秒遅れてトーストが出ます（管理画面には出ません）。
			</p>

			<h2>いまの状態</h2>
			<?php if ( null === $published ) : ?>
				<?php if ( $value['message'] ) : ?>
					<div class="notice notice-error inline"><p>告知が保存されていますが、<code><?php echo esc_html( self::FILE ); ?></code> がありません。「保存」し直すと書き出します。</p></div>
				<?php else : ?>
					<p>告知は出ていません（通知不要）。</p>
				<?php endif; ?>
			<?php elseif ( $expired ) : ?>
				<div class="notice notice-warning inline"><p>期限を過ぎているため、表示されていません。「告知を取り下げる」で消してください。</p></div>
			<?php else : ?>
				<div class="notice notice-info inline"><p>告知を出しています（ID: <code><?php echo esc_html( $published['id'] ?? '' ); ?></code>）。</p></div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( self::PAGE ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="emergency-notice-title">タイトル</label></th>
						<td>
							<input type="text" class="regular-text" id="emergency-notice-title" name="<?php echo esc_attr( self::OPTION ); ?>[title]" value="<?php echo esc_attr( $value['title'] ); ?>" placeholder="お知らせ" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="emergency-notice-message">本文</label></th>
						<td>
							<textarea class="large-text" rows="3" id="emergency-notice-message" name="<?php echo esc_attr( self::OPTION ); ?>[message]"><?php echo esc_textarea( $value['message'] ); ?></textarea>
							<p class="description">空にして保存すると告知を取り下げます。HTML は使えません。公開 URL に置かれるので、機微な内容は書かないでください。</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="emergency-notice-until">期限</label></th>
						<td>
							<input type="datetime-local" id="emergency-notice-until" name="<?php echo esc_attr( self::OPTION ); ?>[until]" value="<?php echo esc_attr( $until ); ?>" />
							<p class="description">この日時を過ぎたら表示しません（消し忘れ対策）。タイムゾーンはサイトの設定（<?php echo esc_html( wp_timezone_string() ); ?>）です。</p>
						</td>
					</tr>
				</table>
				<p class="description">タイトルか本文を変えると、一度閉じた人にももう一度表示されます。期限だけの変更では表示し直しません。</p>
				<p class="submit">
					<?php submit_button( '保存して告知する', 'primary', 'submit', false ); ?>
					<?php if ( $value['message'] ) : ?>
						<button type="submit" class="button" name="<?php echo esc_attr( self::OPTION ); ?>[clear]" value="1">告知を取り下げる</button>
					<?php endif; ?>
				</p>
			</form>
		</div>
		<?php
	}
}
