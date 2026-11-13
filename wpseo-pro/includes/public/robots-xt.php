<?php
/**
 * robots.txt 管理器。
 *
 * 后台配置由插件设置保存，前台输出 robots.txt 文本。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 输出 robots.txt 内容。
 */
function wpseo_pro_output_robots_txt() {
	$robots = wpseo_pro_get_option( 'robots_txt', '' );

	// 基础规则。
	$rules = "User-agent: *\n";
	$rules .= "Disallow: /wp-admin/\n";
	$rules .= "Allow: /wp-admin/admin-ajax.php\n";
	$rules .= "Allow: /wp-admin/widgets\n";
	$rules .= "Allow: /wp-admin/revision.php\n";

	// 插件自定义规则。
	$custom = trim( $robots );

	if ( ! empty( $custom ) ) {
		$rules = $custom . "\n" . $rules;
	}

	// 默认允许站点。
	$rules .= "\n";
	$rules .= "Sitemap: " . home_url( '/sitemap.xml' ) . "\n";

	header( 'Content-Type: text/plain; charset=utf-8' );
	echo $rules;
	exit;
}

/**
 * 挂载 robots.txt 路由（假想文件访问）。
 */
function wpseo_pro_robots_rewrite_rules() {
	add_rewrite_rule( '^robots\.txt$', 'index.php?wpseo_robots=enabled', 'top' );
}
add_action( 'init', 'wpseo_pro_robots_rewrite_rules' );

/**
 * 处理 robots.txt 请求。
 */
function wpseo_pro_handle_robots_request() {
	if ( ! isset( $_GET['wpseo_robots'] ) ) {
		return;
	}

	wpseo_pro_output_robots_txt();
}
add_action( 'init', 'wpseo_pro_handle_robots_request' );

/**
 * 激活时刷新重写规则。
 */
function wpseo_pro_flush_robots_rewrite_on_activation() {
	wpseo_pro_robots_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpseo_pro_flush_robots_rewrite_on_activation' );
