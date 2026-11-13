<?php
/**
 * XML Sitemap 生成。
 *
 * 生成 /sitemap.xml 标准格式，支持文章、页面、附件、自定义链接。
 * 首次请求时生成，缓存到选项表。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 判断是否允许生成 Sitemap。
 */
function wpseo_pro_sitemap_enabled() {
	$gen = wpseo_pro_get_option( 'generate_sitemap', '1' );
	return '1' === $gen;
}

/**
 * 获取 Sitemap 的最后修改时间（用于 <lastmod>）。
 */
function wpseo_pro_sitemap_lastmod( $post ) {
	if ( $post->post_modified ) {
		return $post->post_modified;
	}
	if ( $post->post_date ) {
		return $post->post_date;
	}
	return current_time( 'c' );
}

/**
 * 生成 Sitemap Index（聚合）。
 */
function wpseo_pro_sitemap_index_xml() {
	$basename = wpseo_pro_get_option( 'sitemap_basename', 'sitemap' );
	$entries  = array();

	// 页面/文章。
	$post_types = wpseo_pro_get_option( 'post_types', 'post,page,attachment' );
	$post_types = explode( ',', $post_types );

	$statuses = wpseo_pro_get_option( 'post_statuses', 'publish,draft' );
	$statuses = explode( ',', $statuses );

	$args = array(
		'post_type'              => $post_types,
		'post_status'            => $statuses,
		'posts_per_page'         => -1,
		'fields'                 => 'ids',
		'update_post_meta_cache' => false,
	);

	$posts = new WP_Query( $args );

	if ( $posts->have_posts() ) {
		foreach ( $posts->posts as $post ) {
			$entries[] = array(
				'url'          => get_permalink( $post ),
				'lastmod'      => wpseo_pro_sitemap_lastmod( get_post( $post ) ),
				'priority'     => '0.6',
				'changefreq'   => 'weekly',
			);
		}
	}

	$now = current_time( 'c' );
	$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( $entries as $entry ) {
		$xml .= '  <sitemap>' . "\n";
		$xml .= '    <loc>' . esc_url( $entry['url'] ) . '</loc>' . "\n";
		$xml .= '    <lastmod>' . esc_xml( $entry['lastmod'] ) . '</lastmod>' . "\n";
		$xml .= '    <changefreq>' . esc_html( $entry['changefreq'] ) . '</changefreq>' . "\n";
		$xml .= '    <priority>' . esc_html( $entry['priority'] ) . '</priority>' . "\n";
		$xml .= '  </sitemap>' . "\n";
	}
	$xml .= '</sitemapindex>' . "\n";

	return $xml;
}

/**
 * 生成单个 Sitemap 内容。
 */
function wpseo_pro_sitemap_content_xml( $post_type ) {
	$statuses = wpseo_pro_get_option( 'post_statuses', 'publish,draft' );
	$statuses = explode( ',', $statuses );

	$args = array(
		'post_type'              => $post_type,
		'post_status'            => $statuses,
		'posts_per_page'         => -1,
		'fields'                 => 'ids',
		'update_post_meta_cache' => false,
	);

	$posts = new WP_Query( $args );

	$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

	if ( $posts->have_posts() ) {
		foreach ( $posts->posts as $post ) {
			$url   = get_permalink( $post );
			$last  = wpseo_pro_sitemap_lastmod( get_post( $post ) );
			$priority = '0.6';
			$changefreq = 'weekly';

			if ( 'attachment' === $post_type ) {
				$priority = '0.5';
				$changefreq = 'monthly';
			}

			$xml .= '  <url>' . "\n";
			$xml .= '    <loc>' . esc_url( $url ) . '</loc>' . "\n";
			$xml .= '    <lastmod>' . esc_xml( $last ) . '</lastmod>' . "\n";
			$xml .= '    <changefreq>' . esc_html( $changefreq ) . '</changefreq>' . "\n";
			$xml .= '    <priority>' . esc_html( $priority ) . '</priority>' . "\n";
			$xml .= '  </url>' . "\n";
		}
	}

	$xml .= '</urlset>' . "\n";
	return $xml;
}

/**
 * 生成 Sitemap 内容（直接输出）。
 */
function wpseo_pro_sitemap_generate( $post_type = 'post' ) {
	header( 'Content-Type: application/xml; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );

	if ( 'index' === $post_type ) {
		echo wpseo_pro_sitemap_index_xml();
	} else {
		echo wpseo_pro_sitemap_content_xml( $post_type );
	}
	exit;
}

/**
 * 插件加载时挂载重写规则，防止 404。
 */
function wpseo_pro_sitemap_rewrite_rules() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?wpseo_sitemap=index', 'top' );
	add_rewrite_rule( '^sitemap\.xml?([a-z0-9_-]+)?\.xml$', 'index.php?wpseo_sitemap=$matches[1]', 'top' );
}
add_action( 'init', 'wpseo_pro_sitemap_rewrite_rules' );

/**
 * 处理 Sitemap 请求。
 */
function wpseo_pro_handle_sitemap_request() {
	if ( ! wpseo_pro_sitemap_enabled() ) {
		return;
	}

	$post_type = isset( $_GET['wpseo_sitemap'] ) ? sanitize_text_field( $_GET['wpseo_sitemap'] ) : 'index';

	if ( 'index' === $post_type ) {
		wpseo_pro_sitemap_generate( 'index' );
		return;
	}

	// 只允许已注册的文章类型。
	$post_types = wpseo_pro_get_option( 'post_types', 'post,page,attachment' );
	$post_types = explode( ',', $post_types );

	if ( ! in_array( $post_type, $post_types, true ) ) {
		wp_die( __( '无效的站点地图请求。', 'wpseo-pro' ) );
	}

	wpseo_pro_sitemap_generate( $post_type );
}
add_action( 'init', 'wpseo_pro_handle_sitemap_request' );

/**
 * 插件激活时刷新重写规则。
 */
function wpseo_pro_flush_rewrite_on_activation() {
	add_rewrite_tag( '%wpseo_sitemap%', '([^&]+)' );
	wpseo_pro_sitemap_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpseo_pro_flush_rewrite_on_activation' );

/**
 * 转义 XML 实体。
 */
function wpseo_pro_xml_escape( $text ) {
	return htmlspecialchars( $text, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
}

/**
 * 简化的 esc_xml，WordPress 版本兼容。
 */
if ( ! function_exists( 'esc_xml' ) ) {
	function esc_xml( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}
