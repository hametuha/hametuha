<?php
/**
 * Template Name: 1カラムページ
 */
get_header();
the_post();
?>
<main <?php post_class( [ 'post-content', 'post-blocks' ] ) ?>
      itemscope
      itemtype="http://schema.org/BlogPosting"
    >
    <?php the_content(); ?>
</main>
<?php get_footer();
