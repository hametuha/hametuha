<?php if ( is_category() ) : ?>

	<?php echo wpautop( category_description() ); ?>


<?php elseif ( is_tag() || is_tax() ) : ?>

	<?php echo wpautop( term_description() ); ?>

<?php elseif ( is_search() ) : ?>

	<?php echo wpautop( '「' . get_search_query() . '」で破滅派内を検索しました。' ); ?>

<?php elseif ( is_date() ) : ?>

	<p>投稿作品を日付別に表示しています。</p>

<?php elseif ( is_home() && 'latest' == get_query_var( 'pagename' ) ) : ?>

	<p>投稿を新着順に表示しています。</p>

<?php elseif ( is_singular( 'series' ) ) : ?>

	<?php get_template_part( 'parts/meta', 'single' ); ?>
	<?php the_excerpt(); ?>

<?php elseif ( is_post_type_archive( 'announcement' ) || is_post_type_archive( 'faq' ) || is_post_type_archive( 'info' ) || is_post_type_archive( 'news' ) || is_post_type_archive( 'thread' ) ) : ?>

	<?php echo wpautop( get_post_type_object( get_post_type() )->description ); ?>

<?php endif; ?>
