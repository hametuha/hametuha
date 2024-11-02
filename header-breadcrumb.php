<?php if ( function_exists( 'bcn_display' ) ) : ?>
	<nav id="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
		<div class="container">
			<i class="icon-location5"></i>
			<?php
			ob_start();
			bcn_display();
			$bcn = ob_get_clean();
			foreach ( [
				'property="itemListElement"' => 'itemprop="itemListElement"',
				'typeof="ListItem"'          => 'itemscope itemtype="https://schema.org/ListItem"',
				' typeof="WebPage"'          => '',
				'property="item"'            => 'itemprop="item"',
				'property="name"'            => 'itemprop="name"',
				'property="position"'        => 'itemprop="position"',
			] as $search => $repl ) {
				$bcn = str_replace( $search, $repl, $bcn );
			}
			echo $bcn;
			?>
		</div>
	</nav>
<?php endif; ?>
