<?php



/**
 * Google Analyticsのトラッキングコードを登録する
 *
 * @action wp_head
 * @ignore
 */
function _hametuha_ga_code(){
    ?>
<script>
// Adsense
window.google_analytics_uacct = "UA-1766751-2";
// analytics.js
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-1766751-2', 'auto');
ga('require', 'displayfeatures');
ga('require', 'linkid', 'linkid.js');
<?php if( is_user_logged_in() ): ?>
    ga('set', '&uid', <?= get_current_user_id() ?>);
<?php endif; ?>
<?php if( is_singular() ): ?>
    ga('set', 'dimension1', '<?= get_post_type() ?>');
    ga('set', 'dimension2', '<?= get_the_author_meta('ID') ?>');
    <?php
        $cat = false;
        foreach( get_the_category(get_the_ID()) as $c ){
            $cat = $c->term_id;
        }
        if( $cat ):
    ?>
    ga('set', 'dimension3', '<?= $cat ?>');
    <?php endif; ?>
<?php endif; ?>
<?php if( is_404() ): ?>
    ga('set', 'dimension4', '404');
<?php elseif( is_admin() ): ?>
    ga('set', 'dimension4', 'admin');
<?php endif; ?>
ga('send', 'pageview');
</script>
<?php
}
add_action('wp_head', '_hametuha_ga_code', 19);
add_action('admin_head', '_hametuha_ga_code', 19);
