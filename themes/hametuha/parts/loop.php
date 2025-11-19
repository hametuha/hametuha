<?php
/**
 * アーカイブページで使用される
 *
 * @var array $args;
 */
$args = wp_parse_args( $args, [
	'should_censor' => true,
] );
$should_censor = $args['should_censor'];
$title           = get_the_title();
$title_display   = $should_censor ? hametuha_censor( $title ) : $title;
$excerpt         = trim_long_sentence( get_the_excerpt(), 98 );
$excerpt_display = $should_censor ? hametuha_censor( $excerpt ) : $excerpt;
$censored        = ! is_doujin_profile_page() && ( ( $title != $title_display ) || ( $excerpt != $excerpt_display ) );
$announcement    = null;
if ( 'announcement' === get_post_type() ) {
	$announcement = $post->helper;
}
?>
<li data-post-id="<?php the_ID(); ?>" <?php post_class( 'media loop-media' ); ?>>
	<a href="<?php the_permalink(); ?>" class="media__link<?php echo has_post_thumbnail() ? '' : ' media__link--nopad'; ?>">

		<?php
		if ( has_post_thumbnail() ) {
			printf(
				'<div class="pseudo-thumbnail">%s</div>',
				get_the_post_thumbnail( null, 'medium' )
			);
		}
		?>

		<div class="media-body">

			<!-- Title -->
			<h2 class="media-body__title">
				<?php echo is_doujin_profile_page() ? $title : $title_display; ?>
				<?php
				switch ( get_post_type() ) {
					case 'post':
						if ( $post->post_parent ) {
							printf( '<small class="media-title-label">%s</small> / ', hametuha_censor( get_the_title( $post->post_parent ) ) );
						}
						echo implode( ' ', array_map( function ( $term ) {
							printf( '<small>%s</small>', esc_html( $term->name ) );
						}, get_the_category() ) );
						break;
					case 'anpi':
						if ( is_search() ) {
							echo '<small>安否情報</small>';
						} else {
							if ( $terms = get_the_terms( null, 'anpi_cat' ) ) {
								echo implode( ' ', array_map( function ( $term ) {
									printf( '<small>%s</small>', esc_html( $term->name ) );
								}, $terms ) );
							}
						}
						break;
					case 'newsletter':
						printf( '<small>%s</small>', get_post_type_object( get_post_type() )->label );
						break;
					case 'announcement':
						if ( $post->_event_title ) {
							printf( '<small>%s</small>', esc_html__( 'イベント', 'hametuha' ) );
						}
						break;
					default:
						// Do nothing
						break;
				}
				?>
			</h2>

			<!-- Post Data -->
			<ul class="list-inline">
				<?php
				switch ( get_post_type() ) :
					case 'faq':
						?>
					<li class="list-inline-item">
						<i class="icon-tags"></i>
						<?php if ( ( $terms = get_the_terms( get_the_ID(), 'faq_cat' ) ) && ! is_wp_error( $terms ) ) : ?>
							<?php
							echo implode( ', ', array_map( function ( $term ) {
								return esc_html( $term->name );
							}, $terms ) );
							?>
						<?php else : ?>
							<span class="text-muted">分類なし</span>
						<?php endif; ?>
					</li>
										<?php
						break;
					case 'info':
						?>
						<?php
						break;
					default:
						?>
						<li class="list-inline-item author-info">
							<?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
								<?php the_author(); ?>
						</li>
							<?php
						break;
endswitch;
				?>
				<li class="list-inline-item date">
					<i class="icon-calendar2"></i> <?php echo hametuha_passed_time( $post->post_date ); ?>
					<?php if ( is_recent_date( $post->post_date, 3 ) ) : ?>
						<span class="label label-danger">New!</span>
					<?php elseif ( is_recent_date( $post->post_modified, 7 ) ) : ?>
						<span class="label label-info">更新</span>
					<?php endif; ?>
				</li>
				<li class="static list-inline-item"><i class="icon-reading"></i> <?php echo number_format( get_post_length() ); ?>文字</li>
				<?php if ( in_array( $post->post_status, [ 'private', 'protected' ] ) ) : ?>
				<li>
					<span class="label label-default"><?php echo esc_html( get_post_status_object( get_post_status() )->label ); ?></span>
				</li>
				<?php endif; ?>
				<?php if ( $censored ) : ?>
				<li class="list-inline-item">
					<span class="label label-danger">検閲済み</span>
				</li>
				<?php endif; ?>
			</ul>

			<!-- Excerpt -->
			<div class="archive-excerpt list-inline-item">
				<p class="text-muted"><?php echo is_doujin_profile_page() ? $excerpt : $excerpt_display; ?></p>
			</div>


		</div>
	</a>
</li>
