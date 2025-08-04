<?php
/**
 * Plugin Name: Hametuha Configuration
 * Description: Must-use plugin for Hametuha site configuration
 * Version: 1.0.0
 * Author: Hametuha
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// サイト固有の設定をここに記述
add_action('init', function() {
    // 例: デバッグ情報の表示
    if (WP_DEBUG) {
        error_log('Hametuha MU Plugin loaded');
    }
});

// 例: 管理バーのカスタマイズ
add_action('wp_before_admin_bar_render', function() {
    global $wp_admin_bar;
    $wp_admin_bar->add_menu([
        'id'    => 'hametuha-info',
        'title' => 'Hametuha Dev',
        'href'  => admin_url(),
    ]);
});