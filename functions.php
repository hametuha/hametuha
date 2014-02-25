<?php

/**
 * Version Number for Hametuha Theme
 */
define('HAMETUHA_THEME_VERSION', "3.2.29");

/**
 * 読み込むべきスクリプトのフラグ
 * @var array
 */
$script_flg = array();

get_template_part('functions/utility');
get_template_part('functions/post_types');
get_template_part('functions/rewrite');
get_template_part('functions/adminbar');
get_template_part('functions/dashboard');
get_template_part('functions/assets');
get_template_part('functions/override');
get_template_part('functions/social');
get_template_part('functions/user');
get_template_part('functions/user_content');
get_template_part('functions/user_change_login');
get_template_part('functions/user_profile_picture');
get_template_part('functions/widget');
get_template_part('functions/menu');
get_template_part('functions/wp_die');
get_template_part('functions/user_tags');
get_template_part('functions/single-post-manager');
get_template_part('functions/tinyMCE');
get_template_part('functions/announcement');
get_template_part('functions/hamazon');
get_template_part('functions/bulletin-board');
get_template_part('functions/device');
get_template_part('functions/lwp');
