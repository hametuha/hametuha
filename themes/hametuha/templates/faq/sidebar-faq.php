<?php
/**
 * FAQ用サイドバー
 *
 * @feature-group faq
 */
?>
<div class="col-12 col-md-3" id="sidebar" role="navigation">

	<?php
	$sidebar = get_transient( 'faq-sidebar' );
	if ( false === $sidebar ) {
		ob_start();
		dynamic_sidebar( 'faq-sidebar' );
		$sidebar = ob_get_contents();
		ob_end_clean();
		set_transient( 'faq-sidebar', $sidebar, 60 * 10 );
	}
	echo $sidebar;
	?>

</div><!-- //#sidebar -->
