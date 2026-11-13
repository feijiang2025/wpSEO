<?php
/**
 * XML Sitemap Index。
 *
 * 暴露 /sitemap-index.xml 产物（可选），与 /sitemap.xml 互为便捷入口。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! wpseo_pro_sitemap_enabled() ) {
	exit;
}

if ( ! isset( $_GET['wpseo_sitemap'] ) || 'index' !== $_GET['wpseo_sitemap'] ) {
	wpseo_pro_handle_sitemap_request();
}

header( 'Content-Type: application/xml; charset=utf-8' );
echo wpseo_pro_sitemap_index_xml();
exit;
