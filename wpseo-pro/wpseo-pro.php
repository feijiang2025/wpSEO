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

if ( defined( 'WPSEO_PRO_VERSION' ) ) {
	return;
}
define( 'WPSEO_PRO_VERSION', '1.0.0' );
define( 'WPSEO_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPSEO_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPSEO_PRO_BASE_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSEO_PRO_INCLUDES_DIR', WPSEO_PRO_PLUGIN_DIR . 'includes/' );
define( 'WPSEO_PRO_ASSETS_URL', WPSEO_PRO_BASE_URL . 'assets/' );
define( 'WPSEO_PRO_LANGUAGES_DIR', WPSEO_PRO_PLUGIN_DIR . 'languages/' );

/**
 * 激活/停用钩子。
 */
require WPSEO_PRO_INCLUDES_DIR . 'database.php';

/**
 * 功能授权（PRO 开关）。
 */
require WPSEO_PRO_INCLUDES_DIR . 'capabilities.php';

/**
 * 核心入口：加载所有模块。
 */
require WPSEO_PRO_INCLUDES_DIR . 'admin.php';
require WPSEO_PRO_INCLUDES_DIR . 'admin/assets.php';
require WPSEO_PRO_INCLUDES_DIR . 'admin/settings-page.php';
require WPSEO_PRO_INCLUDES_DIR . 'admin/seo-editor.php';
require WPSEO_PRO_INCLUDES_DIR . 'admin/seo-editor-get.php';

require WPSEO_PRO_INCLUDES_DIR . 'analytics/seo-audit-cache.php';
require WPSEO_PRO_INCLUDES_DIR . 'analytics/seo-audit-scan.php';
require WPSEO_PRO_INCLUDES_DIR . 'analytics/seo-audit-score.php';
require WPSEO_PRO_INCLUDES_DIR . 'admin/seo-editor-save.php';
require WPSEO_PRO_INCLUDES_DIR . 'analytics/seo-audit-ajax.php';

require WPSEO_PRO_INCLUDES_DIR . 'schema/schema.php';
require WPSEO_PRO_INCLUDES_DIR . 'metatags/meta-tags.php';
require WPSEO_PRO_INCLUDES_DIR . 'public/sitemap/sitemap.php';
require WPSEO_PRO_INCLUDES_DIR . 'public/sitemap-index.php';
require WPSEO_PRO_INCLUDES_DIR . 'public/robots-xt.php';

/**
 * 初始化前台帮助函数（示例：针对当前文章的 SEO 数据）
 */
function wpseo_pro_init_helpers() {
	// 可在此处注册辅助函数。
}
add_action( 'init', 'wpseo_pro_init_helpers' );

/**
 * 清理/卸载流程。
 */
register_uninstall_hook( __FILE__, 'wpseo_pro_uninstall' );
