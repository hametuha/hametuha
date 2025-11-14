<?php
/**
 * 著者用テンプレート
 *
 * @var array $args
 */
$args = wp_parse_args( $args, [
	'responsibility' => true,
] );
?>
<div class="news-author row">
	<a class="news-author__link clearfix"
		href="<?php echo home_url( sprintf( '/doujin/detail/%s/', get_the_author_meta( 'user_nicename' ) ) ); ?>">
		<?php echo get_avatar( get_the_author_meta( 'ID' ), 48, '', get_the_author(), [ 'class' => ' img-circle news-author__img' ] ); ?>
		<?php if ( $args['responsibility'] ) : ?>
			文責:
		<?php endif; ?>
		<span class="news-author__name"><?php the_author(); ?></span>
		<small class="news-author__position">
			<?php echo hametuha_user_role( get_the_author_meta( 'ID' ) ); ?>
		</small>
		<span class="news-author__desc">
			<?php echo trim_long_sentence( get_the_author_meta( 'description' ) ); ?>
		</span>
	</a>
</div><!-- .news-author -->
