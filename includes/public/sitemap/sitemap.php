<?php
/**
 * XML Sitemap 生成。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpseo_pro_sitemap_enabled() {
	return '1' === get_option( 'wpseo_pro_generate_sitemap', '1' );
}

function wpseo_pro_sitemap_lastmod( $post ) {
	if ( $post->post_modified ) {
		return $post->post_modified;
	}
	if ( $post->post_date ) {
		return $post->post_date;
	}
	return current_time( 'c' );
}

function wpseo_pro_sitemap_index_xml() {
	$basename  = get_option( 'wpseo_pro_sitemap_basename', 'sitemap' );
	$post_types = explode( ',', get_option( 'wpseo_pro_post_types', 'post,page,attachment' ) );
	$statuses  = explode( ',', get_option( 'wpseo_pro_post_statuses', 'publish,draft' ) );

	$args = array(
		'post_type'              => $post_types,
		'post_status'            => $statuses,
		'posts_per_page'         => -1,
		'fields'                 => 'ids',
		'update_post_meta_cache' => false,
	);

	$posts = new WP_Query( $args );

	$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

	if ( $posts->have_posts() ) {
		foreach ( $posts->posts as $post_id ) {
			$post  = get_post( $post_id );
			$url   = get_permalink( $post_id );
			$last  = wpseo_pro_sitemap_lastmod( $post );
			$xml .= '  <sitemap>' . "\n";
			$xml .= '    <loc>' . esc_url( $url ) . '</loc>' . "\n";
			$xml .= '    <lastmod>' . esc_xml( $last ) . '</lastmod>' . "\n";
			$xml .= '    <changefreq>weekly</changefreq>' . "\n";
			$xml .= '    <priority>0.6</priority>' . "\n";
			$xml .= '  </sitemap>' . "\n";
		}
	}

	$xml .= '</sitemapindex>' . "\n";

	return $xml;
}

function wpseo_pro_sitemap_content_xml( $post_type ) {
	$statuses = explode( ',', get_option( 'wpseo_pro_post_statuses', 'publish,draft' ) );

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
		foreach ( $posts->posts as $post_id ) {
			$url    = get_permalink( $post_id );
			$last   = wpseo_pro_sitemap_lastmod( get_post( $post_id ) );
			$priority = '0.6';
			$changefreq = 'weekly';
			if ( 'attachment' === $post_type ) {
				$priority   = '0.5';
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

function wpseo_pro_sitemap_rewrite_rules() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?wpseo_sitemap=index', 'top' );
	add_rewrite_rule( '^sitemap\.xml?([a-z0-9_-]+)?\.xml$', 'index.php?wpseo_sitemap=$matches[1]', 'top' );
}
add_action( 'init', 'wpseo_pro_sitemap_rewrite_rules' );

function wpseo_pro_handle_sitemap_request() {
	if ( ! wpseo_pro_sitemap_enabled() ) {
		return;
	}

	$post_type = isset( $_GET['wpseo_sitemap'] ) ? sanitize_text_field( $_GET['wpseo_sitemap'] ) : 'index';

	if ( 'index' === $post_type ) {
		wpseo_pro_sitemap_generate( 'index' );
		return;
	}

	$post_types = explode( ',', get_option( 'wpseo_pro_post_types', 'post,page,attachment' ) );

	if ( ! in_array( $post_type, $post_types, true ) ) {
		wp_die( __( '无效的站点地图请求。', 'wpseo-pro' ) );
	}

	wpseo_pro_sitemap_generate( $post_type );
}
add_action( 'init', 'wpseo_pro_handle_sitemap_request' );

function wpseo_pro_flush_rewrite_on_activation() {
	add_rewrite_tag( '%wpseo_sitemap%', '([^&]+)' );
	wpseo_pro_sitemap_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpseo_pro_flush_rewrite_on_activation' );

if ( ! function_exists( 'esc_xml' ) ) {
	function esc_xml( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}
