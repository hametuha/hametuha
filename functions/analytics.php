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
<?php if( is_singular() && !is_preview() ): ?>
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
<?php elseif( is_ranking() ): ?>
    ga('set', 'dimension4', 'ranking');
<?php endif; ?>
ga('send', 'pageview');
</script>
<?php
}
add_action('wp_head', '_hametuha_ga_code', 19);
add_action('admin_head', '_hametuha_ga_code', 19);


/**
 * Google Analytics用のCookieを設定する
 *
 * @param string $page
 */
function ga_record_cookie($page){
	if( session_id() || session_start() ){
		$_SESSION['wpga_page'] = $page;
	}
}

/**
 * セッションに書き込みがあったらGAに記録する
 */
add_action('admin_init', function(){
	if( (!defined('DOING_AJAX') || !DOING_AJAX) && (session_id() || session_start()) && isset($_SESSION['wpga_page']) ){
		$page = esc_js($_SESSION['wpga_page']);
		unset($_SESSION['wpga_page']);
		add_action('admin_notices', function() use ($page) {
			echo <<<HTML
<script>
try{
    ga('send', 'pageview', {
        page: '{$page}',
        title: '投稿完了'
    });
}catch(err){}
</script>
HTML;
		});
	}
});

/**
 * 投稿公開時に可能であればセッションに記録を付与
 *
 */
add_action('transition_post_status', function( $new, $old, WP_Post $post ){
	switch( $new ){
		case 'publish':
		case 'future':
			if( !defined('DOING_CRON') || !DOING_CRON ){
				ga_record_cookie(sprintf('/wp-admin/edit-done/%d', $post->ID));
			}
			break;
		default:
			// Do nothing
			break;
	}
}, 10, 3);


