<?php
/** @var \Hametuha\Rest\Sales $this */
/** @var array $records */
$total = 0;
$deducting = 0;
array_map( function( $reward ) use ( &$total, &$deducting ){
	$total += $reward->total;
	$deducting += $reward->deducting;
}, $records );
?>
<div id="kdp-reward" class="stat">

	<h3><i class="icon-calculate2"></i> <?= $breadcrumb ?></h3>

	<p class="text-muted">あなたがこれまで受けとった金額についての情報です。</p>

	<?php if ( ! $records ) : ?>
		<div class="alert alert-warning">
			表示すべき情報はありません。
		</div>
	<?php else : ?>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>#</th>
				<th>入金日</th>
				<th>源泉徴収税</th>
				<th>入金額</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&yen;<?= number_format_i18n( $deducting ) ?></th>
				<th>&yen;<?= number_format_i18n( $total ) ?></th>
			</tr>
			</tfoot>
			<tbody>
			<?php $i = 0; foreach ( $records as $record ) : $i++; ?>
				<tr>
					<th><?= $i ?></th>
					<td><?= preg_replace( '/(\d{4})(\d{2})/', '$1年$2月', $record->payed ) ?></td>
					<td>&yen;<?= number_format( $record->deducting ) ?></td>
					<td>&yen;<?= number_format( $record->total ) ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>



	<?php endif; ?>

</div><!-- .stat -->

<hr />
