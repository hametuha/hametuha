<?php
/**
 * コンパクトなループ用コンテナ
 *
 * @var array $args
 */
$args    = wp_parse_args( $args, [
	'no_title'      => false,
	'no_desc'       => is_front_page(),
	'should_censor' => true,
] );
$title   = get_the_title();
$excerpt = trim_long_sentence( get_the_excerpt(), 98 );
// 検閲を行う
$should_censor = $args['should_censor'];
if ( $should_censor ) {
	$title_display   = hametuha_censor( $title );
	$excerpt_display = hametuha_censor( $excerpt );
} else {
	$title_display   = $title;
	$excerpt_display = $excerpt;
}
$censored = ! is_doujin_profile_page() && ( ( $title != $title_display ) || ( $excerpt != $excerpt_display ) );
$no_desc  = $args['no_desc'];

// 表示するカテゴリー
$terms    = [];
$taxonomy = [
	'post'   => 'category',
	'thread' => 'topic',
	'anpi'   => 'anpi_cat',
];
if ( isset( $taxonomy[ get_post_type() ] ) ) {
	$terms = get_the_terms( get_post(), $taxonomy[ get_post_type() ] );
}

// タグ
$tag_taxonomy = [
	'post' => 'post_tag',
];
$tags         = [];
if ( isset( $tag_taxonomy[ get_post_type() ] ) ) {
	$tag_terms = get_the_terms( get_post(), $tag_taxonomy[ get_post_type() ] );
	if ( $tag_terms && ! is_wp_error( $tag_terms ) ) {
		foreach ( $tag_terms as $index => $term ) {
			if ( 2 < $index ) {
				break;
			}
			$tags [] = $term;
		}
	}
}
?>
<li>
	<a href="<?php the_permalink(); ?>" class="clearfix">

		<?php if ( ! $args['no_title'] ) : ?>
		<h3 class="list-heading">
			<?php echo is_doujin_profile_page() ? $title : $title_display; ?>
			<?php
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $cat ) :
					?>
					<small class="list-heading-category">
						<?php echo esc_html( $cat->name ); ?>
					</small>
					<?php
				endforeach;
			}
			if ( 'series' == get_post_type() ) :
				?>
				<small class="list-heading-category">
					<?php
					if ( isset( $post->children ) ) {
						printf( __( '全%d話', 'hametuha' ), $post->children );
					}
					?>
				</small>
			<?php endif; ?>
		</h3>
		<?php endif; ?>

		<div class="list-meta">
			<?php
			echo get_avatar( get_the_author_meta( 'ID' ), 60, null, '', [ 'class' => 'mr-1' ] );
			the_author();
			?>
			<?php if ( is_new_post( 3 ) ) : ?>
				<span class="badge text-bg-danger">New</span>
			<?php endif; ?>
			<?php if ( $censored ) : ?>
				<span class="badge text-bg-warning censored">検閲済み</span>
			<?php endif; ?>
			<p class="mt-2">
				<small>
					<time datetime="<?php the_time( DateTime::ATOM ); ?>"><?php the_time( get_option( 'date_format' ) ); ?></time>
					<?php
					$count = get_comment_count( $post->ID );
					if ( $count['approved'] > 0 ) :
						?>
						｜
						<span class="ml-2"><i class="icon-bubble"></i> <?php printf( __( '%d件', 'hametuha' ), $count['approved'] ); ?></span>
					<?php endif; ?>
					<?php
					if ( ! empty( $tags ) ) {
						foreach ( $tags as $tag ) {
							printf( '<span class="tag-link" style="margin-left: 0.5em;">#%s</span>', esc_html( $tag->name ) );
						}
					}
					?>
				</small>
			</p>
		</div>

		<?php
		if ( has_excerpt() && ! $no_desc ) :
			?>
			<div class="list-excerpt">
				<?php echo esc_html( is_doujin_profile_page() ? $excerpt : $excerpt_display ); ?>
			</div>
		<?php endif; ?>

	</a>
</li>
