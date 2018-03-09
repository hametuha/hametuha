<div id="sales-container" data-endpoint="<?= esc_url( $endpoint ) ?>" data-slug="history">
    <hb-month-selector v-on:date-updated="getSales" label="日付" minYear="2015" curMonth="{{curMonth}}" curYear="{{curYear}}"></hb-month-selector>

    <hb-bar-chart
            v-if="records.length"
            class="hb-chart hb-chart-line"
            :chart-data="chartData"
            :options="options"></hb-bar-chart>

    <table :class="{table: true, 'table-striped': true, loading: loading, highlight: true}">
        <thead>
        <tr>
            <th class="cell-2">月</th>
            <th class="cell-2">日</th>
            <th class="text-left">商品</th>
            <th class="text-right">ロイヤリティ</th>
            <th class="text-right">数量</th>
            <th class="text-left">ストア</th>
            <th class="text-left">販売形態</th>
            <th class="text-left">種別</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th colspan="3">&nbsp;</th>
            <th class="text-right">{{total | monetize}}</th>
            <th colspan="4">&nbsp;</th>
        </tr>
        </tfoot>
        <tbody>
        <tr v-for="record in records">
            <td class="text-center">{{record.date|moment('MM')}}</td>
            <td class="text-center">{{record.date|moment('DD')}}</td>
            <td>{{record.post_title}}</td>
            <td class="text-right">{{record.royalty | currency(record.currency)}}</td>
            <td class="text-right">{{record.unit | addSuffix(record.store)}}</td>
            <td class="text-left">{{record.place}}</td>
            <td class="text-left">{{record.type}}</td>
            <td class="text-left">{{record.store | labeling}}</td>
        </tr>
        <tr v-if="!records.length">
            <td class="error text-center disabled" colspan="8">
                記録がありません。
            </td>
        </tr>
        </tbody>
    </table>
</div>
