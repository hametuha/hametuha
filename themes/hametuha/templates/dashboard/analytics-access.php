<?php
/**
 * Statistics access page template
 *
 * @param array $args Template arguments.
 */
$endpoint = $args['endpoint'] ?? '';
?>
<div id="access-container" data-endpoint="<?php echo esc_url( $endpoint ); ?>"></div>
