<?php
/**
 * ニュースのループ
 *
 * @var array $args
 */
$args = wp_parse_args( $args, [
	'type' => 'normal',
] );
$class_names = [ 'news-list__item' ];
switch ( $args['type'] ) {
	case 'card':
		$class_names[] = 'news-card col-6 col-lg-4';
		break;
	case 'widget':
		$class_names[] = 'news-card news-widget col-12';
		break;
	default:
		$class_names[] = 'col-12';
		break;
}
?>
<li class="<?php echo esc_attr( implode( ' ', $class_names ) ); ?>">
	<a href="<?php the_permalink(); ?>" class="news-list__link">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="news-list__image">
				<?php the_post_thumbnail( 'thumbnail', [ 'class' => 'news-list__thumbnail' ] ); ?>
			</div>
		<?php endif ?>

		<div class="news-list__body">
			<h4 class="news-list__title">
				<?php if ( hamenew_is_pr() ) : ?>
					<small class="news-list__pr">【PR】</small>
				<?php endif; ?>
				<?php the_title(); ?>
			</h4>

			<p class="news-list__meta">

				<span class="news-list__time">
					<i class="icon-clock"></i> <?php echo hametuha_passed_time( $post->post_date ); ?>
				</span>

				<?php if ( ( $terms = get_the_terms( get_post(), 'genre' ) ) && ! is_wp_error( $terms ) ) : ?>

					<span class="news-list__genre">
						<i class="icon-tag5"></i>
						<?php
						echo implode( ', ', array_map( function ( $term ) {
							return esc_html( $term->name );
						}, $terms ) );
						?>
					</span>

				<?php endif; ?>

			</p>
		</div><!-- //.news-list__body -->
	</a>
</li>
