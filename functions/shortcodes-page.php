<?php
/**
 * ショートコード関連をまとめたもの
 */

/**
 * 1/3になるブロックを出力
 */
add_shortcode( 'col', function( $atts, $content = '' ) {
	$atts = shortcode_atts( [
		'icon' => '',
		'href' => '',
	], $atts, 'block' );
	$icon    = $atts['icon'] ? sprintf( '<div class="row-block__icon"><i class="%s"></i></div>', esc_attr( $atts['icon'] ) ) : '';
	$content = $content ? sprintf( '<div class="row-block__desc">%s</div>', nl2br( esc_html( str_replace( '|', "\n", $content ) ) ) ) : '';
	$content = $icon.$content;
	if ( $atts['href'] ) {
		$content = sprintf( '<a href="%s" class="row-block__link">%s</a>', esc_url( $atts['href'] ), $content );
	}
	return sprintf( '<div class="col-xs-4">%s</div>', $content );
} );

