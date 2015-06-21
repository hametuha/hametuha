<li class="notification__item">
	<a class="notification__link<? if(!$i) echo ' notification__link--new' ?> clearfix" href="#">
		<?= get_avatar(3, 98) ?>
		<p class="notification__text">
			<strong>〇〇さん</strong>があなたの事を殺すといっています。<br />
			<small class="notification__time"><?= $i ?>日前</small>
		</p>
	</a>
</li>