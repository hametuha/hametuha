<?php
/**
 * 作品用ループテンプレート
 *
 * タイトルと抜粋文は検閲すみ
 */
$title           = get_the_title();
$title_display   = hametuha_censor( $title );
$excerpt         = trim_long_sentence( get_the_excerpt(), 98 );
$excerpt_display = hametuha_censor( $excerpt );
$censored        = ! is_doujin_profile_page() && ( ( $title != $title_display ) || ( $excerpt != $excerpt_display ) );
?>
<li data-post-id="<?php the_ID(); ?>" <?php post_class( 'media loop-media' ); ?>>
	<a href="<?php the_permalink(); ?>" class="media__link media__link--nopad">

		<header class="media-header">
			<!-- Title -->
			<h2 class="media-body__title">
				<span><?php echo $title_display; ?></span>
				<?php
				echo implode( ' ', array_map( function ( $term ) {
					printf( '<small>%s</small>', esc_html( $term->name ) );
				}, get_the_category() ) );
				?>
			</h2>
		</header>

		<div class="media-series mb-2">
			<?php
			// 連載の場合はステータスを表示
			if ( $post->post_parent ) :
				?>
				<span class="media-series-title">
					<?php
					echo esc_html( sprintf(
						__( '『%s』収録', 'hametuha' ),
						hametuha_censor( get_the_title( $post->post_parent ) )
					) );
					if ( \Hametuha\Model\Series::get_instance()->is_finished( $post->post_parent ) ) {
						// 完結済み
						esc_html_e( '（完結済み）', 'hametuha' );
					} else {
						// 連載中
						esc_html_e( '（連載中）', 'hametuha' );
					}
					?>
				</span>
			<?php endif; ?>
			<?php
			// タグ
			foreach ( [ 'nouns', 'post_tag', 'campaign' ] as $taxonomy ) {
				$terms = get_the_terms( get_post(), $taxonomy );
				if ( $terms && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $tag ) {
						printf(
							'<span class="media-series-tag text-muted">#%s</span>',
							esc_html( $tag->name )
						);
					}
				}
			}
			?>
		</div>

		<div class="media-body">
			<!-- Post Data -->
			<ul class="list-inline">
				<li class="list-inline-item author-info">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
					<?php the_author(); ?>
				</li>
				<li class="list-inline-item date">
					<i class="icon-calendar2"></i> <?php echo hametuha_passed_time( $post->post_date ); ?>
					<?php if ( rand(0,1) < 0.5 ) : // is_recent_date( $post->post_date, 3 ) ) : ?>
						<span class="badge text-bg-danger"><?php esc_html_e( '新着', 'hametuha' ); ?></span>
					<?php endif; ?>
				</li>
				<li class="static list-inline-item">
					<i class="icon-reading"></i> <?php the_post_length( '', esc_html__( '文字', 'hametuha' ) ); ?>
				</li>
				<li class="static list-inline-item">
					<i class="icon-clock"></i> <?php printf( '読了%s分', hametuha_reading_time() ); ?>
				</li>
				<?php if ( in_array( $post->post_status, [ 'private', 'protected' ] ) ) : ?>
				<li class="list-inline-item">
					<span class="badge text-bg-secondary"><?php echo esc_html( get_post_status_object( get_post_status() )->label ); ?></span>
				</li>
				<?php endif; ?>
				<?php if ( $censored ) : ?>
				<li class="list-inline-item">
					<span class="badge text-bg-danger">検閲済み</span>
				</li>
				<?php endif; ?>
			</ul>

			<!-- Excerpt -->
			<div class="archive-excerpt list-inline-item">
				<p class="mb-0"><?php echo is_doujin_profile_page() ? $excerpt : $excerpt_display; ?></p>
			</div>

		</div>
	</a>
</li>
