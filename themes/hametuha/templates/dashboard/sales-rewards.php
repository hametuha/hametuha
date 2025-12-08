<?php
/**
 * Sales rewards page (deposit / rewards).
 *
 * React component will render inside #sales-container.
 *
 * @var array $args
 */
$endpoint = $args['endpoint'] ?? '';
$page     = $args['page'] ?? '';
?>
<div
	id="sales-container"
	data-endpoint="<?php echo esc_attr( $endpoint ); ?>"
	data-slug="<?php echo esc_attr( $page ); ?>"
></div>
