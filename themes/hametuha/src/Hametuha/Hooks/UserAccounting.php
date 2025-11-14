<?php

namespace Hametuha\Hooks;

use Hametuha\Sharee\Master\Address;
use Hametuha\Sharee\Models\RevenueModel;
use WPametu\Pattern\Singleton;

/**
 * ユーザーの支払い調書関連
 */
class UserAccounting extends Singleton {

	/**
	 * @var string ページスラッグ
	 */
	protected $slug = 'accounting';

	/**
	 * {@inheritDoc}
	 */
	public function __construct( array $setting = array() ) {
		add_action( 'template_redirect', [ $this, 'redirect' ] );
		add_shortcode( 'your-accounting', [ $this, 'do_shortcode' ] );
	}

	/**
	 * Check if user is ok.
	 *
	 * @return void
	 */
	public function redirect() {
		if ( ! is_page( $this->slug ) ) {
			// 支払い調書ページだけ
			return;
		}
		nocache_headers();
		if ( ! is_user_logged_in() ) {
			auth_redirect();
			exit;
		}
		// CSSを読み込み
		wp_enqueue_style( 'hametuha-accounting-paper' );
		// タイトルを変更
		add_filter( 'single_post_title', function ( $title, $post ) {
			if ( 'accounting' !== $post->post_name ) {
				return $title;
			}
			// translators: %1$d is year, %2$s is title.
			return sprintf( __( '%1$d年度%2$s', 'hametuha' ), $this->year(), $title );
		}, 10, 2 );
	}

	/**
	 * Get year to retrieve.
	 *
	 * @return int
	 */
	protected function year() {
		return intval( filter_input( INPUT_GET, 'accounting-year' ) ?: date_i18n( 'Y' ) );
	}

	/**
	 * 調書を表示するショートコード
	 *
	 * @param array  $atts
	 * @param string $content
	 * @return string
	 */
	public function do_shortcode( $atts, $content = '' ) {
		$billings = RevenueModel::get_instance()->get_fixed_billing( $this->year(), 0, [], true, get_current_user_id() );
		if ( empty( $billings ) ) {
			return sprintf( '<p class="paper-no-result">%s</p>', esc_html__( '該当する支払い情報はありません。', 'hametuha' ) );
		}
		ob_start();
		?>
		<p class="text-right paper-issued"><?php printf( esc_html__( '発行日：%s', 'hametuha' ), date_i18n( get_option( 'date_format' ) ) ); ?></p>
		<table class="paper-table">
			<caption>309</caption>
			<?php
			$this->accounting_header( __( '支払を受ける者', 'hametuha' ), get_current_user_id() );
			$this->accounting_footer( __( '支払者', 'hametuha' ) );
			?>
			<tbody>
				<tr>
					<th>区分</th>
					<th>細目</th>
					<th>支払金額</th>
					<th>源泉徴収税額</th>
				</tr>
				<?php foreach ( $billings as $billing ) : ?>
				<tr>
					<td><?php esc_html_e( '報酬', 'hametuha' ); ?></td>
					<td><?php esc_html_e( '原稿料', 'hametuha' ); ?></td>
					<td class="text-right">&yen;<?php echo number_format_i18n( $billing->before_tax ); ?></td>
					<td class="text-right">&yen;<?php echo number_format_i18n( $billing->deducting ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		return ob_get_clean();
	}

	/**
	 * 調書のヘッダーを出力する
	 *
	 * @param string $title
	 * @param int    $user_id
	 * @return void
	 */
	protected function accounting_header( $title, $user_id ) {
		$address = new Address( $user_id );
		?>
		<thead class="paper-table-header">
			<tr>
				<th rowspan="2">
					<?php echo esc_html( $title ); ?>
				</th>
				<th><?php esc_html_e( '住所', 'hametuha' ); ?></th>
				<td colspan="2">
					<?php echo esc_html( $address->format_line() ); ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( '氏名・屋号', 'hametuha' ); ?></th>
				<td>
					<?php echo esc_html( $address->get_value( 'name' ) ); ?>
				</td>
				<td>
					<p><?php esc_html_e( '法人番号・個人番号', 'hametuha' ); ?></p>
					<code>
					<?php
					echo implode( '', array_map( function ( $i ) {
						return ' &nbsp;';
					}, range( 0, 9 ) ) );
					?>
					</code>
				</td>
			</tr>
		</thead>
		<?php
	}

	/**
	 * 支払い者（会社情報）を表示する
	 *
	 * @param string $title 名称。「支払い者」など
	 * @return void
	 */
	protected function accounting_footer( $title ) {
		?>
		<tfoot class="paper-table-footer">
		<tr>
			<th rowspan="2"><?php echo esc_html( $title ); ?></th>
			<th><?php esc_html_e( '住所', 'hametuha' ); ?></th>
			<td colspan="2">
				<?php esc_html_e( '東京都中央区銀座1-3-3 G1ビル7F 1211', 'hametuha' ); ?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( '名称', 'hametuha' ); ?></th>
			<td>
				<p><?php esc_html_e( '株式会社破滅派', 'hametuha' ); ?></p>
				<small><?php esc_html_e( '（電話）' ); ?>050-5532-8327</small>
			</td>
			<td>
				<p><?php esc_html_e( '法人番号', 'hametuha' ); ?></p>
				<code>1010401087592</code>
			</td>
		</tr>
		</tfoot>
		<?php
	}
}
