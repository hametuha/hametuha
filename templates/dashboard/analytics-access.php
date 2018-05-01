<div id="access-container" data-endpoint="<?= esc_url( $endpoint ) ?>" :class="{loading: loading, minHeight: true}">

    <hb-month-selector label="日付変更" @date-updated="dateChangeHandler"></hb-month-selector>

    <hb-bar-chart
            v-if="records.length"
            v-bind:chart-data="chartData"
            v-bind:options="options"></hb-bar-chart>

    <table :class="{table:true, 'table-striped': true, loading: loading, striped: true}">
        <thead>
        <tr>
            <th class="cell-2 text-right">#</th>
            <th class="text-left">タイトル</th>
            <th class="text-left">種別</th>
            <th class="text-right">PV</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(ranking, index) in rankings">
            <td class="text-right">{{ranking.rank}}</td>
            <td class="text-left"><a class="link" :href="ranking.url">{{ranking.title}}</a></td>
            <td class="text-left">{{ranking.type}}</td>
            <td class="text-right">{{ranking.pv}}</td>
        </tr>
        <tr v-if="!rankings.length">
            <td class="error text-center disabled" colspan="7">
                記録がありません。
            </td>
        </tr>
        </tbody>
    </table>

</div>