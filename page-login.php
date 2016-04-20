<?php
/*
 * Template Name: ログインフォーム
 */
define("IS_LOGIN_PAGE", true);
get_header('login');

?>

        <p class="catch-copy text-center">
            <?php bloginfo( 'description' ) ?>
        </p>

		<?php if ( have_posts() ): while( have_posts() ): the_post();?>
            <div id="login-body">
                <?php the_content(); ?>
            </div><!--  -->
        <?php endwhile; endif; ?>


        <p class="text-center">
            <a href="<?= home_url() ?>">破滅派トップに戻る</a>
        </p>

<?php get_footer('login') ?>
