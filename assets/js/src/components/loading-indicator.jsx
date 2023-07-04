/*!
 * Hametuha Loading Indicator
 *
 * @handle hametuha-loading-indicator
 * @deps wp-element, wp-i18n, hametuha-components
 */

const { __ } = wp.i18n;

wp.hametuha.LoadingIndicator = ( prop ) => {
	const label = prop.label || __( '読み込み中...', 'hametuha' );
	return prop.loading ? (
		<div className="hb-loading-indicator">
			<img src={ HametuhaComponents.indicator } width="100" height="100" alt={ label } />
			<span class="hb-loading-indicator-title">{ label }</span>
		</div>
	) : null;
};
