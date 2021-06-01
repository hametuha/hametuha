<?php
/** @var \Hametuha\Rest\Doujin $this */
/** @var bool $breadcrumb */
/** @var bool $current */
?>
<?php get_header(); ?>

	<div id="breadcrumb" itemprop="breadcrumb">
		<div class="container">
			<i class="icon-location5"></i>
			<a href="<?php echo home_url( '' ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			&gt;
			<a href="<?php echo home_url( '/authors/' ); ?>">執筆者</a>
			&gt;
			<?php if ( $breadcrumb ) : ?>
				<a href="<?php echo home_url( '/doujin/' . $this->doujin->user_nicename . '/' ); ?>">
					<?php echo esc_html( $this->doujin->display_name ); ?>
				</a>
				&gt; <?php echo $breadcrumb; ?>
			<?php else : ?>
				<?php echo esc_html( $this->doujin->display_name ); ?>
			<?php endif; ?>
		</div>
	</div>

	<?php $this->load_template( 'templates/doujin/profile', $template ); ?>

<?php
get_footer();
