<?php
/** @var string $message */
/** @var string $url */
/** @var bool $new */
/** @var string $url */
/** @var string $img */
?>
<div class="notification__item" data-time="<?php echo $time; ?>">
	<a class="notification__link<?php echo $new ? ' notification__link--new' : ''; ?> clearfix" href="<?php echo $url; ?>">
		<?php echo $img; ?>
		<p class="notification__text">
			<?php echo wp_kses( $message, [ 'strong' => [] ] ); ?><br/>
			<small class="notification__time"><?php echo hametuha_passed_time( $time, true ); ?></small>
		</p>
	</a>
</div>
