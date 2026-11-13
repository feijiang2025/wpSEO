<?php
/**
 * robots.txt 管理器。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpseo_pro_output_robots_txt() {
	$robots = get_option( 'wpseo_pro_robots_txt', '' );

	$rules = "User-agent: *\n";
	$rules .= "Disallow: /wp-admin/\n";
	$rules .= "Allow: /wp-admin/admin-ajax.php\n";
	$rules .= "Allow: /wp-admin/widgets\n";
	$rules .= "Allow: /wp-admin/revision.php\n";

	$custom = trim( $robots );
	if ( ! empty( $custom ) ) {
		$rules = $custom . "\n" . $rules;
	}

	$rules .= "\n";
	$rules .= "Sitemap: " . home_url( '/sitemap.xml' ) . "\n";

	header( 'Content-Type: text/plain; charset=utf-8' );
	echo $rules;
	exit;
}

function wpseo_pro_robots_rewrite_rules() {
	add_rewrite_rule( '^robots\.txt$', 'index.php?wpseo_robots=enabled', 'top' );
}
add_action( 'init', 'wpseo_pro_robots_rewrite_rules' );

function wpseo_pro_handle_robots_request() {
	if ( ! isset( $_GET['wpseo_robots'] ) ) {
		return;
	}

	wpseo_pro_output_robots_txt();
}
add_action( 'init', 'wpseo_pro_handle_robots_request' );

function wpseo_pro_flush_robots_rewrite_on_activation() {
	wpseo_pro_robots_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpseo_pro_flush_robots_rewrite_on_activation' );
