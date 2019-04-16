<footer id="footer" class="footer-wrapper">
	<div class="footer-links-wrapper">
		<p class="footer-hametuha text-center">
			<i class="icon-hametuha"></i>
		</p>
		<div class="container">

			<div class="row footer-links">

				<div class="col-xs-12">
					<?php wp_nav_menu( array(
						'theme_location' => 'hametuha_global_about',
						'container'      => false,
						'menu_class'     => 'footer-links-nav',
						'depth'          => 1,
					) ); ?>

				</div>

				<div class="col-xs-12">
					<ul class="footer-links-nav">
						<?php
						foreach (
							[
								'ha'          => [ 'https://hametuha.co.jp', '株式会社破滅派', '' ],
								'image2'      => [ 'http://hametuha.pics', 'はめぴくっ！', '' ],
								'googleplus3' => [
									'https://plus.google.com/b/115001047459194790006/115001047459194790006/about/p/pub',
									'Google+',
									'rel="publisher"',
								],
								'youtube'     => [ 'https://www.youtube.com/user/hametuha', 'Youtube', '' ],
								'minicome'    => [ 'http://minico.me', 'ミニコme!', '' ],
								'github3'     => [ 'https://github.com/hametuha/', 'Github', '' ],
							] as $icon => list(
							$url, $label, $atts
							) ) :
							?>
							<li>
								<a href="<?= $url ?>" <?= $atts ?>><?= esc_html( $label ) ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

			</div><!-- //.row -->
		</div><!-- //.container -->

	</div>

	<div class="footer-copynote">
		<p class="copy-right text-center">
			&copy; <span itemprop="copyrightYear">2007</span> 破滅派
		</p>
	</div><!-- copynote -->

</footer>

<?php if ( ! is_hamenew() ) : ?>
<div id="write-panel" class="write-panel">
	<div class="write-panel__inner">
		<p class="text-right">
			<button class="write-panel__close write-panel-btn btn btn-link"><i class="icon-cancel-circle"></i></button>
		</p>
		<ul class="write-panel__actions">
			<?php foreach ( hametuha_user_write_actions() as $icon => list( $url, $label, $desc, $class_name, $data ) ) : ?>
				<li class="write-panel__action">
					<a class="write-panel__link <?= esc_attr( $class_name ) ?>" href="<?= $url ?>" <?= $data ?>>
						<span class="write-panel__label">
							<i class="icon-<?= $icon ?>"></i>
							<?= esc_html( $label ) ?>
						</span>
						<?php if ( $desc ) : ?>
							<p class="write-panel__desc"><?= esc_html( $desc ) ?></p>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php endif; ?>
<?php do_action( 'hametuha_after_whole_body' ) ?>
</div><!-- //#whole-body -->


<?php
get_template_part( 'parts/modal' );
wp_footer();
?>
</body>
</html>
