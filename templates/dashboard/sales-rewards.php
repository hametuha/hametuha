<div id="sales-container" data-endpoint="<?= esc_url( $endpoint ) ?>" data-slug="<?= esc_attr( $page ) ?>">
	<?php if ( 'rewards' === $page ) : ?>
        <hb-month-selector v-on:date-updated="getReward" label="日付"></hb-month-selector>
	<?php endif; ?>
    <table :class="{table: true, 'table-striped': true, loading: loading, highlight: true}">
        <thead>
        <tr>
            <th class="cell-2 text-right">#</th>
            <th class="text-left">適用</th>
			<th class="text-left">単価</th>
            <th class="cell-3 text-right">数量</th>
			<th class="text-right">消費税</th>
            <th class="text-right">源泉徴収</th>
            <th class="text-right">入金額</th>
            <th class="text-right">登録日</th>
			<?php if ( 'rewards' == $page ) : ?>
            	<th class="text-right">支払日</th>
			<?php endif; ?>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th colspan="5">&nbsp;</th>
			<th class="text-right">{{tax | monetize}}</th>
            <th class="text-right">{{total | monetize}}</th>
			<?php if ( 'rewards' == $page ) : ?>
                <th>&nbsp;</th>
			<?php else : ?>
                <th class="text-right">
                            <span v-if="available" class="text-success">
                                <i class="material-icons">check</i>
                                振込予定
                            </span>
                    <span v-else class="text-error">
                                <i class="material-icons">close</i>
                                金額不足
                            </span>
                </th>
			<?php endif; ?>
        </tr>
        </tfoot>
        <tbody>
        <tr v-for="record in records">
            <th>{{record.revenue_id}}</th>
			<td><strong>【{{record.label}}】</strong>{{record.description}}</td>
			<td class="text-right">{{record.price | monetize}}</td>
            <td class="text-right">{{record.unit}}</td>
			<td class="text-right">{{record.tax | monetize}}</td>
            <td class="text-right">{{record.deducting | monetize}}</td>
            <td class="text-right">{{record.total | monetize}}</td>
            <td class="text-right">{{record.created | moment('YYYY/MM/DD')}}</td>
			<?php if ( 'rewards' === $page ) : ?>
			<td class="text-right">
				<span v-if="'0000-00-00 00:00:00' == record.fixed">---</span>
				<span v-else>{{ record.fixed | moment('YYYY/MM/DD') }}</span>
			</td>
			<?php endif ?>
        </tr>
        <tr v-if="!records.length">
            <td class="error text-center disabled" colspan="<?php echo 'rewards' === $page ? '9' : '8' ?>">
                記録がありません。
            </td>
        </tr>
        </tbody>
    </table>
</div>
