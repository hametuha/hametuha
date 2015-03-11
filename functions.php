<?php
/**
 * テーマ用ブートストラップ
 */


/**
 * 現在のテーマのバージョンを返す
 *
 * @return bool|string
 */
function hametuha_version(){
    $theme = wp_get_theme();
    return $theme->get('Version');
}

require '/Users/guy/Documents/Vagrant/hamepub/www/HamePub/vendor/autoload.php';




/**
 * Version Number for Hametuha Theme
 *
 * @deprecated
 */
define('HAMETUHA_THEME_VERSION', hametuha_version());


/**
 * Name space for theme
 *
 */
define('WPAMETU_NAMESPACE_ROOT', 'Hametuha');

/**
 * Name space root directory
 */
define('WPAMETU_NAMESPACE_ROOT_DIR', __DIR__.'/app');


// Load WPametu
get_template_part('wpametu/bootstrap');




/**
 * 読み込むべきスクリプトのフラグ
 * @var array
 */
$script_flg = array();

get_template_part('functions/utility');
get_template_part('functions/display');
get_template_part('functions/ranking');
get_template_part('functions/meta');
get_template_part('functions/post_types');
get_template_part('functions/post_list');
get_template_part('functions/post_list_admin');
get_template_part('functions/series');
get_template_part('functions/dashboard');
get_template_part('functions/assets');
get_template_part('functions/assets', 'ssl');
get_template_part('functions/analytics');
get_template_part('functions/override');
get_template_part('functions/social');
get_template_part('functions/user');
get_template_part('functions/user_content');
get_template_part('functions/user_change_login');
get_template_part('functions/user_profile_picture');
get_template_part('functions/widget');
get_template_part('functions/menu');
get_template_part('functions/error');
get_template_part('functions/tinyMCE');
get_template_part('functions/hamazon');
get_template_part('functions/eyecatch');
get_template_part('functions/bulletin-board');
get_template_part('functions/device');
get_template_part('functions/lwp');
