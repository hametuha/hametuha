<?php
/**
 * スレッドのサイドバー
 *
 * @feature-group thread
 */
?>
<div class="col-12 col-md-3" id="sidebar" role="navigation">

	<?php
	$sidebar = get_transient( 'thread-sidebar' );
	if ( false === $sidebar ) {
		ob_start();
		dynamic_sidebar( 'thread-sidebar' );
		$sidebar = ob_get_contents();
		ob_end_clean();
		set_transient( 'thread-sidebar', $sidebar, 60 * 10 );
	}
	echo $sidebar;
	?>

</div><!-- //#sidebar -->
