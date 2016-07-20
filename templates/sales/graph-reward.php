<?php
/** @var \Hametuha\Rest\Sales $this */
/** @var string $breadcrumb */
/** @var array $rewards */
$total     = 0;
$units     = 0;
$deducting = 0;
array_map( function ( $reward ) use ( &$total, &$units, &$deducting ) {
	$total += $reward->total;
	$units += $reward->unit;
	$deducting += $reward->deducting;
}, $rewards );
?>
<div id="kdp-reward" class="stat">

	<h3><i class="icon-coins"></i> <?= $breadcrumb ?></h3>

	<p class="text-muted">あなたが受け取ることのできる報酬です。</p>

	<?php if ( ! $rewards ) : ?>
		<div class="alert alert-warning">
			現在、確定している報酬はございません。
		</div>
	<?php else : ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>#</th>
			<th>適用</th>
			<th>数量</th>
			<th>源泉徴収税</th>
			<th>入金予定額</th>
			<th>登録日</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th><?= number_format_i18n( $units ) ?></th>
			<th>&yen;<?= number_format_i18n( $deducting ) ?></th>
			<th>&yen;<?= number_format_i18n( $total ) ?></th>
			<td>
				<?php if ( 3000 > $total ) : ?>
					<span class="text-muted"><i class="icon-close2"></i> 入金額不足</span>
				<?php elseif ( ! hametuha_bank_ready() || ! hametuha_billing_ready() ) : ?>
					<span class="text-muted"><i class="icon-close2"></i> 入金先未登録</span>
				<?php else : ?>
					<span class="text-success"><i class="icon-checkmark3"></i> 入金可</span>
				<?php endif; ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php $i = 0;
		foreach ( $rewards as $reward ) : $i ++; ?>
			<tr>
				<th><?= $i ?></th>
				<td>
					<?=
					sprintf(
						'<strong>[%s]</strong> %s',
						$this->user_sales->type_label( $reward->sales_type ),
						esc_html( $reward->description )
					) ?>
				</td>
				<td><?= number_format( $reward->unit ) ?></td>
				<td>&yen;<?= number_format( $reward->deducting ) ?></td>
				<td>&yen;<?= number_format( $reward->total ) ?></td>
				<td><?= mysql2date( get_option( 'date_format' ), $reward->created ) ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>

</div><!-- .stat -->

<hr/>
