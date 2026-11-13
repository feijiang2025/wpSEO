<?php
/**
 * 数据库表格与协议。
 *
 * 使用自定义表，避免与 WooCommerce / Yoast 等插件冲突。
 * 通过 `wpseo_pro_install()` 安装时创建表，删除时删除表。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 插件数据库表前缀。
 *
 * 若多站点环境下不同站点需共享表，可在 wp-config.php 定义
 * define( 'WPSEO_PRO_DB_PREFIX', 'wpseo_pro_' );
 * 否则使用 $wpdb->prefix 。
 */
if ( ! defined( 'WPSEO_PRO_DB_PREFIX' ) ) {
	define( 'WPSEO_PRO_DB_PREFIX', $GLOBALS['wpdb']->prefix . 'wpseo_pro_' );
}

/**
 * 表名。
 */
defined( 'WPSEO_PRO_TABLE_POSTS' ) || define( 'WPSEO_PRO_TABLE_POSTS', WPSEO_PRO_DB_PREFIX . 'posts' );
defined( 'WPSEO_PRO_TABLE_SETTINGS' ) || define( 'WPSEO_PRO_TABLE_SETTINGS', WPSEO_PRO_DB_PREFIX . 'settings' );
defined( 'WPSEO_PRO_TABLE_REDIRECTS' ) || define( 'WPSEO_PRO_TABLE_REDIRECTS', WPSEO_PRO_DB_PREFIX . 'redirects' );
defined( 'WPSEO_PRO_TABLE_SITEMAP_INDEX' ) || define( 'WPSEO_PRO_TABLE_SITEMAP_INDEX', WPSEO_PRO_DB_PREFIX . 'sitemap_index' );

/**
 * 安装钩子：创建自定义表。
 */
function wpseo_pro_install() {
	global $wpdb;

	if ( ! function_exists( 'add_option' ) ) {
		return;
	}

	$charset_collate = $wpdb->get_charset_collate();

	$tables = array(
		WPSEO_PRO_TABLE_POSTS => "CREATE TABLE " . WPSEO_PRO_TABLE_POSTS . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) NOT NULL,
			seo_title varchar(200) DEFAULT '',
			metadescription text DEFAULT '',
			focus_keyword varchar(191) DEFAULT '',
			metatags longtext DEFAULT '',
			schema_type varchar(50) DEFAULT '',
			schema_config longtext DEFAULT '',
			custom_titles longtext DEFAULT '',
			modified timestamptz NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY focus_keyword (focus_keyword)
		) $charset_collate;",

		WPSEO_PRO_TABLE_SETTINGS => "CREATE TABLE " . WPSEO_PRO_TABLE_SETTINGS . " (
			option_name varchar(191) NOT NULL,
			option_value longtext DEFAULT '',
			PRIMARY KEY (option_name)
		) $charset_collate;",

		WPSEO_PRO_TABLE_REDIRECTS => "CREATE TABLE " . WPSEO_PRO_TABLE_REDIRECTS . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			source varchar(200) NOT NULL,
			target varchar(200) NOT NULL,
			status tinyint(1) NOT NULL DEFAULT '1',
			created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY source (source),
			KEY target (target)
		) $charset_collate;",

		WPSEO_PRO_TABLE_SITEMAP_INDEX => "CREATE TABLE " . WPSEO_PRO_TABLE_SITEMAP_INDEX . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) NOT NULL,
			sitemap_url varchar(255) NOT NULL,
			priority tinyint(2) NOT NULL DEFAULT '0.5',
			changefreq varchar(20) NOT NULL DEFAULT 'weekly',
			PRIMARY KEY (id),
			UNIQUE KEY unique_post (post_id)
		) $charset_collate;",
	);

	foreach ( $tables as $sql ) {
		$wpdb->query( $sql );
	}

	// 确保插件设置记录存在。
	$defaults = array(
		'wpseo_pro_general' => array(
			'site_name'        => get_option( 'blogname', '' ),
			'generate_sitemap' => '1',
			'robots_txt'       => '',
			'use_schema'       => '1',
			'card_style'       => 'default',
			'analytics_id'     => '',
		),
	);

	foreach ( $defaults as $option => $value ) {
		if ( ! get_option( $option ) ) {
			add_option( $option, $value );
		}
	}

	// 尝试生成第一条插件自身的 robots 记录（若启用）。
	wpseo_pro_schedule_index_sitemap();
}
register_activation_hook( __FILE__, 'wpseo_pro_install' );

/**
 * 停用钩子：清理任务与表（保持可选）。
 */
function wpseo_pro_uninstall() {
	global $wpdb;

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$tables = array(
		WPSEO_PRO_TABLE_POSTS,
		WPSEO_PRO_TABLE_SETTINGS,
		WPSEO_PRO_TABLE_REDIRECTS,
		WPSEO_PRO_TABLE_SITEMAP_INDEX,
	);

	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS $table" );
	}

	delete_option( 'wpseo_pro_general' );
}
