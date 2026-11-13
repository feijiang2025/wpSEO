<?php
/**
 * 卸载时清理的数据。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpseo_pro_uninstall' ) ) :
	/**
	 * 插件卸载钩子。
	 */
	function wpseo_pro_uninstall() {
		global $wpdb;

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		foreach ( array(
			WPSEO_PRO_TABLE_POSTS,
			WPSEO_PRO_TABLE_SETTINGS,
			WPSEO_PRO_TABLE_REDIRECTS,
			WPSEO_PRO_TABLE_SITEMAP_INDEX,
		) as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		}

		delete_option( 'wpseo_pro_general' );
	}
endif;
