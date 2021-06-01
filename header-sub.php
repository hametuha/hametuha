<?php if ( has_nav_menu( 'hametuha_sub_globals' ) ) : ?>
	<div class="submenu-wrapper">
		<?php
		wp_nav_menu(
			[
				'theme_location'  => 'hametuha_sub_globals',
				'container'       => 'nav',
				'container_class' => 'container',
				'menu_class'      => 'submenu',
				'depth'           => 1,
			]
		);
		?>
	</div>
<?php endif; ?>
