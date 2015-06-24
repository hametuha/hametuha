<?php
/** @var string $message */
/** @var string $url */
/** @var bool $new */
/** @var string $url */
/** @var string $img */
?>
<li class="notification__item" data-time="<?= $time ?>">
	<a class="notification__link<? if($new) echo ' notification__link--new' ?> clearfix" href="<?= $url ?>">
		<?= $img ?>
		<p class="notification__text">
			<?= wp_kses($message, ['strong' => []]) ?><br />
			<small class="notification__time"><?= hametuha_passed_time($time, true) ?></small>
		</p>
	</a>
</li>