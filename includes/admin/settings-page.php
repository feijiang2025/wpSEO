<?php
/**
 * SEO PRO 基础设置页 — 使用 WordPress Settings API。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_settings_section( 'wpseo_pro_section_general', __( '基础设置', 'wpseo-pro' ), 'wpseo_pro_settings_section_general', 'wpseo_pro_settings' );
add_settings_section( 'wpseo_pro_section_sitemap', __( '站点地图', 'wpseo-pro' ), 'wpseo_pro_settings_section_sitemap', 'wpseo_pro_settings' );

add_settings_field( 'wpseo_pro_field_site_name', __( '站点名称', 'wpseo-pro' ), 'wpseo_pro_field_site_name', 'wpseo_pro_settings', 'wpseo_pro_section_general' );
add_settings_field( 'wpseo_pro_field_card_style', __( '审计卡片样式', 'wpseo-pro' ), 'wpseo_pro_field_card_style', 'wpseo_pro_settings', 'wpseo_pro_section_general' );
add_settings_field( 'wpseo_pro_field_analytics_id', __( '分析 ID（可选）', 'wpseo-pro' ), 'wpseo_pro_field_analytics_id', 'wpseo_pro_settings', 'wpseo_pro_section_general' );
add_settings_field( 'wpseo_pro_field_generate_sitemap', __( '生成站点地图', 'wpseo-pro' ), 'wpseo_pro_field_generate_sitemap', 'wpseo_pro_settings', 'wpseo_pro_section_sitemap' );
add_settings_field( 'wpseo_pro_field_sitemap_basename', __( '站点地图文件名', 'wpseo-pro' ), 'wpseo_pro_field_sitemap_basename', 'wpseo_pro_settings', 'wpseo_pro_section_sitemap' );
add_settings_field( 'wpseo_pro_field_post_types', __( '要纳入的文章类型', 'wpseo-pro' ), 'wpseo_pro_field_post_types', 'wpseo_pro_settings', 'wpseo_pro_section_sitemap' );
add_settings_field( 'wpseo_pro_field_post_statuses', __( '发布状况', 'wpseo-pro' ), 'wpseo_pro_field_post_statuses', 'wpseo_pro_settings', 'wpseo_pro_section_sitemap' );

function wpseo_pro_settings_section_general() {
	?><p><?php esc_html_e( '控制站点名称、卡片样式、分析追踪与默认行为。', 'wpseo-pro' ); ?></p><?php
}

function wpseo_pro_settings_section_sitemap() {
	?><p><?php esc_html_e( '生成与发布 XML Sitemap，确保搜索引擎能够抓取。', 'wpseo-pro' ); ?></p><?php
}

function wpseo_pro_field_site_name() {
	$current = get_option( 'wpseo_pro_site_name', '' );
	?><input name="wpseo_pro_site_name" id="wpseo_pro_field_site_name" type="text" value="<?php echo esc_attr( $current ); ?>" class="regular-text"><?php
}

function wpseo_pro_field_card_style() {
	$current = get_option( 'wpseo_pro_card_style', 'default' );
	?><select name="wpseo_pro_card_style" id="wpseo_pro_field_card_style"><option value="default" <?php selected( $current, 'default' ); ?>><?php esc_html_e( '默认深色', 'wpseo-pro' ); ?></option><option value="glass" <?php selected( $current, 'glass' ); ?>><?php esc_html_e( '玻璃拟态', 'wpseo-pro' ); ?></option></select><?php
}

function wpseo_pro_field_analytics_id() {
	$current = get_option( 'wpseo_pro_analytics_id', '' );
	?><input name="wpseo_pro_analytics_id" id="wpseo_pro_field_analytics_id" type="text" value="<?php echo esc_attr( $current ); ?>" class="regular-text" placeholder="e.g. G-XXXXXX"><?php
}

function wpseo_pro_field_generate_sitemap() {
	$current = get_option( 'wpseo_pro_generate_sitemap', '1' );
	?><input name="wpseo_pro_generate_sitemap" id="wpseo_pro_field_generate_sitemap" type="checkbox" value="1" <?php checked( $current, '1' ); ?>
	<label for="wpseo_pro_field_generate_sitemap" style="font-size:14px;cursor:pointer;"><?php esc_html_e( '开启后，访问 /sitemap.xml 即可访问站点地图。', 'wpseo-pro' ); ?></label><?php
}

function wpseo_pro_field_sitemap_basename() {
	$current = get_option( 'wpseo_pro_sitemap_basename', 'sitemap' );
	?><input name="wpseo_pro_sitemap_basename" id="wpseo_pro_field_sitemap_basename" type="text" value="<?php echo esc_attr( $current ); ?>" class="regular-text"><?php
}

function wpseo_pro_field_post_types() {
	$current = get_option( 'wpseo_pro_post_types', 'post,page,attachment' );
	?><input name="wpseo_pro_post_types" id="wpseo_pro_field_post_types" type="text" value="<?php echo esc_attr( $current ); ?>" class="regular-text"><?php
}

function wpseo_pro_field_post_statuses() {
	$current = get_option( 'wpseo_pro_post_statuses', 'publish,draft' );
	?><input name="wpseo_pro_post_statuses" id="wpseo_pro_field_post_statuses" type="text" value="<?php echo esc_attr( $current ); ?>" class="regular-text"><?php
}

function wpseo_pro_register_settings() {
	register_setting( 'wpseo_pro_general', 'wpseo_pro_site_name', 'sanitize_text_field' );
	register_setting( 'wpseo_pro_general', 'wpseo_pro_card_style', 'sanitize_text_field' );
	register_setting( 'wpseo_pro_general', 'wpseo_pro_analytics_id', 'sanitize_text_field' );
	register_setting( 'wpseo_pro_general', 'wpseo_pro_generate_sitemap', 'wpseo_pro_sanitize_bool' );
	register_setting( 'wpseo_pro_general', 'wpseo_pro_robots_txt', 'wp_kses_post' );
	register_setting( 'wpseo_pro_general', 'wpseo_pro_use_schema', 'wpseo_pro_sanitize_bool' );
	register_setting( 'wpseo_pro_general', 'wpseo_pro_sitemap_basename', 'sanitize_text_field' );
	register_setting( 'wpseo_pro_general', 'wpseo_pro_post_types', 'sanitize_text_field' );
	register_setting( 'wpseo_pro_general', 'wpseo_pro_post_statuses', 'sanitize_text_field' );
}
add_action( 'admin_init', 'wpseo_pro_register_settings' );

function wpseo_pro_sanitize_bool( $value ) {
	return in_array( $value, array( '1', 'on', 1, true ), true ) ? '1' : '0';
}

function wpseo_pro_default_settings() {
	return array(
		'site_name'        => '',
		'generate_sitemap' => '1',
		'robots_txt'       => '',
		'use_schema'       => '1',
		'card_style'       => 'default',
		'analytics_id'     => '',
		'sitemap_basename' => 'sitemap',
		'post_types'       => 'post,page,attachment',
		'post_statuses'    => 'publish,draft',
	);
}
