/*!
 * Chart colors component.
 *
 *
 * @license GPL-3.0-or-later
 * @handle hametuha-chart-colors
 * @deps hametuha-components
 * @package hametuha
 */

const { hametuha } = wp;

/**
 * Get chart color by index.
 *
 * @param {number} index Index of data.
 * @return {string} rgba string.
 */
const chartColors = ( index ) => {

	const colors = [
		'rgba(54, 162, 235, 1)',
		'rgba(255, 99, 132, 1)',
		'rgba(255, 206, 86, 1)',
		'rgba(75, 192, 192, 1)',
		'rgba(153, 102, 255, 1)',
		'rgba(255, 159, 64, 1)',
		'rgb(255,102,189, 1 )',
	]
	return colors[ index ] || 'rgba(169,169,169, 1)';
}
hametuha.chartColors = chartColors;
