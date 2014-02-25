<?php
define("IS_LOGIN_PAGE", true);
get_header('meta');

?>
<body <?php body_class('login-page'); ?>>

	<div id="login-form">
		<?php if(have_posts()): while(have_posts()): the_post();?>
		<div id="login-body">
			<h1><?php the_title(); ?></h1>
			<a id="login-logo" href="<?php bloginfo('url'); ?>" rel="home" title="破滅派に戻る"><img src="<?php bloginfo('template_directory'); ?>/img/header-logo.png" alt="<?php bloginfo('name'); ?>" width="140" height="50" /></a>
			<?php the_content(); ?>
		</div>
		<?php endwhile; endif; ?>
		<div id="footer-login" class="footer-note">
			<p class="copy-right serif center">&copy; 2007-<?php echo date_i18n('Y'); ?> HAMETUHA</p>
		</div>
	</div>
<?php wp_footer(); ?>
</body>
</html>