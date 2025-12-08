<?php
/**
 * Sales payments page.
 *
 * React component will render inside #sales-container.
 * Accounting form stays as static HTML below.
 *
 * @var array $args
 */
$endpoint = $args['endpoint'] ?? '';
?>
<div
	id="sales-container"
	data-endpoint="<?php echo esc_attr( $endpoint ); ?>"
	data-slug="payments"
></div>

<hr />

<div class="payment-info">
	<h2><?php esc_html_e( '支払い調書の出力', 'hametuha' ); ?></h2>
	<p><?php esc_html_e( '確定申告に必要な支払い調書を以下のフォームから出力できます。前年度分を選択して印刷し、確定申告にお役立てください。', 'hametuha' ); ?></p>
	<form method="get" action="<?php echo home_url( '/accounting' ); ?>" target="_blank" rel="noopener noreferrer">
		<div class="mb-3">
			<label for="accounting-year" class="form-label">
				<?php esc_html_e( '年度を選んでください', 'hametuha' ); ?>
			</label>
			<select id="accounting-year" name="accounting-year" class="form-select">
				<?php
				$this_year = (int) date_i18n( 'Y' );
				for ( $i = $this_year; $i >= 2015; $i-- ) {
					printf( '<option value="%d">%s</option>', $i, sprintf( esc_html__( '%d年度', 'hametuha' ), $i ) );
				}
				?>
			</select>
		</div>
		<div class="mb-3">
			<button type="submit" class="btn btn-primary"><?php esc_html_e( '印刷する', 'hametuha' ); ?></button>
		</div>
	</form>
</div>