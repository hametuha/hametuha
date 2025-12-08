<?php
/**
 * Sales history graph page.
 *
 * React component will render inside #sales-container.
 *
 * @var array $args
 */
$endpoint = $args['endpoint'] ?? '';
?>
<div
	id="sales-container"
	data-endpoint="<?php echo esc_url( $endpoint ); ?>"
	data-slug="history"
></div>
