<div id="sales-container" data-endpoint="<?= esc_url( $endpoint ) ?>" data-slug="payments">
    <div class="form-group">
        <select class="form-control" v-on:change="getPayments" v-model="currentYear">
            <?php $curYear = date_i18n( 'Y' ); for ( $i = $curYear, $l = 2015; $i >= $l; $i-- ) : ?>
                <option value="<?= $i ?>"<?php selected( $i, $curYear ) ?>><?= $i ?>年</option>
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
            <td class="text-center">{{record.fixed|moment('MM')}}</td>
            <td class="text-center">{{record.fixed|moment('DD')}}</td>
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
