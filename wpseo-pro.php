<?php
/**
 * Plugin Name: WordPress SEO PRO
 * Plugin URI:  https://example.com/wpseo-pro
 * Description: 企业级 SEO 管理面板 — 实时 SEO 审计、单页编辑器、XML Sitemap、robots 管理、结构化数据。企业官网优先。
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: wpseo-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (c) 2026
 */

// Exit directly if accessed.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 插件版本。
 */
if ( ! defined( 'WPSEO_PRO_VERSION' ) ) {
	define( 'WPSEO_PRO_VERSION', '1.0.0' );
}

/**
 * 插件路径常量。
 */
if ( ! defined( 'WPSEO_PRO_PLUGIN_DIR' ) ) {
	define( 'WPSEO_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WPSEO_PRO_PLUGIN_BASENAME' ) ) {
	define( 'WPSEO_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'WPSEO_PRO_BASE_URL' ) ) {
	define( 'WPSEO_PRO_BASE_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'WPSEO_PRO_INCLUDES_DIR' ) ) {
	define( 'WPSEO_PRO_INCLUDES_DIR', WPSEO_PRO_PLUGIN_DIR . 'includes/' );
}
if ( ! defined( 'WPSEO_PRO_ASSETS_URL' ) ) {
	define( 'WPSEO_PRO_ASSETS_URL', WPSEO_PRO_BASE_URL . 'assets/' );
}
if ( ! defined( 'WPSEO_PRO_LANGUAGES_DIR' ) ) {
	define( 'WPSEO_PRO_LANGUAGES_DIR', WPSEO_PRO_PLUGIN_DIR . 'languages/' );
}

/**
 * 加载插件文本域。
 */
function wpseo_pro_load_textdomain() {
	load_plugin_textdomain( 'wpseo-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wpseo_pro_load_textdomain' );

/**
 * 加载所有核心模块。
 */
function wpseo_pro_load_includes() {
	require WPSEO_PRO_INCLUDES_DIR . 'database.php';
	require WPSEO_PRO_INCLUDES_DIR . 'capabilities.php';
	require WPSEO_PRO_INCLUDES_DIR . 'admin.php';
	require WPSEO_PRO_INCLUDES_DIR . 'admin/assets.php';
	require WPSEO_PRO_INCLUDES_DIR . 'admin/settings-page.php';
	require WPSEO_PRO_INCLUDES_DIR . 'admin/audit-panel.php';
	require WPSEO_PRO_INCLUDES_DIR . 'admin/seo-editor.php';
	require WPSEO_PRO_INCLUDES_DIR . 'admin/seo-editor-get.php';
	require WPSEO_PRO_INCLUDES_DIR . 'admin/seo-editor-save.php';
	require WPSEO_PRO_INCLUDES_DIR . 'analytics/seo-audit-cache.php';
	require WPSEO_PRO_INCLUDES_DIR . 'analytics/seo-audit-scan.php';
	require WPSEO_PRO_INCLUDES_DIR . 'analytics/seo-audit-score.php';
	require WPSEO_PRO_INCLUDES_DIR . 'analytics/seo-audit-ajax.php';
	require WPSEO_PRO_INCLUDES_DIR . 'schema/schema.php';
	require WPSEO_PRO_INCLUDES_DIR . 'metatags/meta-tags.php';
	require WPSEO_PRO_INCLUDES_DIR . 'public/sitemap/sitemap.php';
	require WPSEO_PRO_INCLUDES_DIR . 'public/sitemap-index.php';
	require WPSEO_PRO_INCLUDES_DIR . 'public/robots-xt.php';
}
add_action( 'plugins_loaded', 'wpseo_pro_load_includes', 20 );

/**
 * 插件激活钩子。
 */
register_activation_hook( __FILE__, 'wpseo_pro_install' );

/**
 * 插件卸载钩子。
 */
register_uninstall_hook( __FILE__, 'wpseo_pro_uninstall' );
