<?php
/**
 * Page on
 *
 */
$endpoint = $args['endpoint'] ?? '';
?>
<div id="sales-container" data-endpoint="<?php echo esc_url( $endpoint ); ?>" data-slug="payments">
	<div class="form-group">
		<select class="form-control" v-on:change="getPayments" v-model="currentYear">
			<?php $curYear = date_i18n( 'Y' ); for ( $i = $curYear, $l = 2015; $i >= $l; $i-- ) : ?>
				<option value="<?php echo $i; ?>"<?php selected( $i, $curYear ); ?>><?php echo $i; ?>年</option>
			<?php endfor; ?>
		</select>
	</div>
	<table :class="{table: true, 'table-striped': true, loading: loading, highlight: true}">
		<thead>
		<tr>
			<th class="cell-2">月</th>
			<th class="cell-2">日</th>
			<th class="text-left">支払先</th>
			<th class="text-right">入金額</th>
			<th class="text-right">源泉徴収</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th colspan="3">&nbsp;</th>
			<th class="text-right">{{total | monetize}}</th>
			<th class="text-right">{{tax | monetize}}</th>
		</tr>
		</tfoot>
		<tbody>
		<tr v-for="record in records">
			<td class="text-center">{{record.fixed | moment('MM')}}</td>
			<td class="text-center">{{record.fixed | moment('DD')}}</td>
			<td>{{record.display_name}}</td>
			<td class="text-right">{{record.total | monetize}}</td>
			<td class="text-right">{{record.deducting | monetize}}</td>
		</tr>
		<tr v-if="!records.length">
			<td class="error text-center disabled" colspan="7">
				記録がありません。
			</td>
		</tr>
		</tbody>
	</table>
</div>

<hr />

<div class="payment-info">
	<h2><?php esc_html_e( '支払い調書の出力', 'hametuha' ); ?></h2>
	<p><?php esc_html_e( '確定申告に必要な支払い調書を以下のフォームから出力できます。前年度分を選択して印刷し、確定申告にお役立てください。', 'hametuha' ); ?></p>
	<form method="get" action="<?php echo home_url( '/accounting' ); ?>" target="_blank" rel="noopener noreferrer">
		<div class="form-group">
			<label for="accounting-year">
				<?php esc_html_e( '年度を選んでください', 'hametuha' ); ?>
			</label>
			<select id="accounting-year" name="accounting-year" class="form-control">
				<?php
				$this_year = (int) date_i18n( 'Y' );
				for ( $i = $this_year; $i >= 2015; $i-- ) {
					printf( '<option value="%d">%s</option>', $i, sprintf( esc_html__( '%d年度', 'hametuha' ), $i ) );
				}
				?>
			</select>
		</div>
		<div class="form-group">
			<button type="submit" class="btn btn-primary mb-2"><?php esc_html_e( '印刷する', 'hametuha' ); ?></button>
		</div>
	</form>
</div>
