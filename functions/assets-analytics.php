<?php

/**
 * Googleのランキングを取得する
 *
 * @param string $start
 * @param string $end
 * @param array $params
 * @param string $metrics
 *
 * @return WP_Error|array
 */
function hametuha_ga_ranking( $start, $end, $params = [], $metrics = 'ga:pageviews' ) {
	return \Hametuha\Hooks\Analytics::get_instance()->ranking( $start, $end, $params, $metrics );
}
