<?php
/**
 * ニュースのヘッダー
 *
 * @feature-group news
 */
$description = get_post_type_object( 'news' )->description;
if ( is_hamenew( 'front' ) ) {
	$title = __( 'はめにゅー', 'hametuha' );
} elseif ( is_search() ) {
	$title = sprintf( __( '「%s」を含むニュース', 'hametuha' ), get_search_query() );
} elseif ( is_tax() ) {
	ob_start();
	single_term_title();
	$title     = ob_get_clean();
	$term_desc = term_description();
	if ( ! empty( $term_desc ) ) {
		$description = $term_desc;
	}
} else {
	$title = __( '文学ニュース', 'hametuha' );
}
// 件数
global $wp_query;
$count = $wp_query->found_posts;
?>
<div class="news-eyecatch container">
	<div class="news-eyecatch-text">
		<?php
		// LCP の画像。CSS の背景だとブラウザが見つけるのが遅れるので <img> で出す（#496 と同じ）
		printf(
			'<img class="news-eyecatch-bg" src="%s" alt="" width="1200" height="600" fetchpriority="high" decoding="async" />',
			esc_url( get_template_directory_uri() . '/assets/img/jumbotron/hamenew.jpg' )
		);
		?>
		<h1 class="news-eyecatch-title">
			<?php echo esc_html( $title ); ?>
		</h1>
		<div class="news-eyecatch-lead">
			<?php echo wp_kses_post( $description ); ?>
		</div>
		<div class="news-eyecatch-count mt-5">
			<?php echo sprintf( __( '%d件のニュース', 'hametuha' ), number_format( $count ) ); ?>
		</div>
	</div>
</div><!-- //.news-eyecatch -->
