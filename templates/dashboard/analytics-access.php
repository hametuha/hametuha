<?php
/**
 * Home screen of hashboard/
 *
 * @parm array $args
 */
$endpoint = $args['endpoint'] ?? '';
?>
<div id="access-container" data-endpoint="<?php echo esc_url( $endpoint ); ?>" :class="{loading: loading, minHeight: true}">

	<div class="form-row align-items-end">
		<div class="form-group col">
			<label for="user-pv-from"><?php esc_html_e( '開始', 'hametuha' ); ?></label>
			<input id="user-pv-from" class="form-control" type="date" v-model="from" />
		</div>
		<div class="form-group col">
			<label id="user-pv-to"><?php esc_html_e( '終了', 'hametuha' ); ?></label>
			<input id="user-pv-to" class="form-control" type="date" v-model="to" />
		</div>
		<div class="form-group col">
			<button class="btn btn-primary" v-on:click="dateChangeHandler"><?php esc_html_e( '日付指定', 'hametuha' ); ?></button>
		</div>
	</div>

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
