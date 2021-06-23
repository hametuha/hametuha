<footer id="footer" class="footer-wrapper footer-wrapper-general">
	<div class="footer-links-wrapper">
		<p class="footer-logo text-center">
			<img class="footer-logo-img" src="<?php echo get_template_directory_uri(); ?>/dist/img/brand/hametuha.svg"
				width="380" height="147" loading="lazy" alt="<?php esc_attr_e( '破滅派', 'hametuha' ); ?>" />
			<span class="footer-logo-desc">
				<?php esc_html_e( 'オンライン文芸誌', 'hametuha' ); ?>
			</span>
		</p>
		<div class="container">

			<div class="row footer-links">

				<?php
				// About links.
				if ( has_nav_menu( 'hametuha_global_about' ) ) {
					Kunoichi\SetMenu::nav_menu( [
						'theme_location'  => 'hametuha_global_about',
						'container'       => 'nav',
						'container_class' => 'col-12 col-md-6',
						'menu_class'      => 'footer-links-about',
						'depth'           => 1,
					] );
				}
				// Social links.
				if ( has_nav_menu( 'hametuha_socials' ) ) {
					Kunoichi\SetMenu::nav_menu( [
						'theme_location'  => 'hametuha_socials',
						'container_class' => 'col-12 col-md-6',
						'container'       => 'nav',
						'menu_class'      => 'footer-links-social',
						'depth'           => 1,
					] );
				}
				?>

			</div><!-- //.row -->
		</div><!-- //.container -->

	</div>

	<?php
	get_template_part( 'templates/footer', 'colophon', [
		'suffix' => 'general',
	] );
	?>

</footer>

<?php
// TODO: Remove action menu from footer.
if ( ! is_hamenew() ) :
	?>
	<div id="write-panel" class="write-panel">
	<div class="write-panel__inner">
		<p class="text-right">
			<button class="write-panel__close write-panel-btn btn btn-link"><i class="icon-cancel-circle"></i></button>
		</p>
		<ul class="write-panel__actions">
			<?php foreach ( hametuha_user_write_actions() as $icon => list( $url, $label, $desc, $class_name, $data ) ) : ?>
				<li class="write-panel__action">
					<a class="write-panel__link <?php echo esc_attr( $class_name ); ?>" href="<?php echo $url; ?>" <?php echo $data; ?>>
						<span class="write-panel__label">
							<i class="icon-<?php echo $icon; ?>"></i>
							<?php echo esc_html( $label ); ?>
						</span>
						<?php if ( $desc ) : ?>
							<p class="write-panel__desc"><?php echo esc_html( $desc ); ?></p>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php endif; ?>
<?php do_action( 'hametuha_after_whole_body' ); ?>
</div><!-- //#whole-body -->


<?php
get_template_part( 'parts/modal' );
wp_footer();
?>
</body>
</html>
