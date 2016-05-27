<?php
$title = get_the_title();
$title_display = hametuha_censor( $title );
$excerpt = trim_long_sentence( get_the_excerpt(), 98 );
$excerpt_display = hametuha_censor( $excerpt );
$censored = ! is_doujin_profile_page() && ( ( $title != $title_display ) || ( $excerpt != $excerpt_display ) );
?>
<li data-post-id="<?php the_ID() ?>" <?php post_class( 'media' ) ?>>
	<a href="<?php the_permalink() ?>" class="media__link<?= has_post_thumbnail() ? '' : ' media__link--nopad' ?>">

		<?php
		if ( has_post_thumbnail() ) {
			$style = sprintf("background-image: url('%s')", wp_get_attachment_image_src(get_post_thumbnail_id(), 'medium')[0]);
			echo <<<HTML
				<div class="pseudo-thumbnail" style="{$style}"></div>
HTML;
		}
		?>

		<div class="media-body">

			<!-- Title -->
			<h2 class="media-body__title">
				<?= is_doujin_profile_page() ? $title : $title_display ?>
				<?php switch ( get_post_type() ) {
					case 'post':
						if ( $post->post_parent ) {
							printf( '<small class="media-title-label">%s</small> / ', get_the_title( $post->post_parent ) );
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
					case 'announcement':
						printf( '<small>%s</small>', get_post_type_object(get_post_type())->label );
						break;
					default:
						// Do nothing
						break;
				} ?>
			</h2>

			<!-- Post Data -->
			<ul class="list-inline">
				<?php switch ( get_post_type() ): case 'faq': ?>
					<li>
						<i class="icon-tags"></i>
						<?php if ( ( $terms = get_the_terms( get_the_ID(), 'faq_cat' ) ) && ! is_wp_error( $terms ) ): ?>
							<?= implode( ', ', array_map( function ( $term ) {
								return esc_html( $term->name );
							}, $terms ) ); ?>
						<?php else: ?>
							<span class="text-muted">分類なし</span>
						<?php endif; ?>
					</li>
					<?php break;
					case 'info': ?>
						<?php break;
					default: ?>
						<li class="author-info">
							<?= get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
							<?php the_author(); ?>
						</li>
						<?php break; endswitch; ?>
				<li class="date">
					<i class="icon-calendar2"></i> <?= hametuha_passed_time( $post->post_date ) ?>
					<?php if ( is_recent_date( $post->post_date, 3 ) ): ?>
						<span class="label label-danger">New!</span>
					<?php elseif ( is_recent_date( $post->post_modified, 7 ) ): ?>
						<span class="label label-info">更新</span>
					<?php endif; ?>
				</li>
				<li class="static"><i class="icon-reading"></i> <?= number_format( get_post_length() ) ?>文字</li>
				<?php if ( in_array( $post->post_status, ['private', 'protected'] ) ) : ?>
				<li>
					<span class="label label-default"><?= esc_html( get_post_status_object(get_post_status())->label ) ?></span>
				</li>
				<?php endif; ?>
				<?php if ( $censored ) : ?>
				<li>
					<span class="label label-danger">検閲済み</span>
				</li>
				<?php endif; ?>
			</ul>

			<!-- Excerpt -->
			<div class="archive-excerpt">
				<p class="text-muted"><?= is_doujin_profile_page() ? $excerpt : $excerpt_display ?></p>
			</div>


		</div>
	</a>
</li>
