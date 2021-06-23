<?php
/**
 * Colophon
 *
 * @package hametuha
 * @var array $args
 */

$id     = $args['id'] ?? 'footer-colophon';
$suffix = empty( $args['suffix'] ) ? '' : ' footer-colophon-' . $args['suffix'];
?>

<div id="<?php echo esc_attr( $id ) ?>" class="footer-colophon<?php echo esc_attr( $suffix ) ?>">
	<p class="footer-copy-right">
		&copy; <span itemprop="copyrightYear">2007</span> Hametuha
	</p>
</div><!-- footer-colophon -->
