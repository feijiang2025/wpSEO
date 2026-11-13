<?php
/**
 * SEO PRO 基础设置页。
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

/**
 * 基础设置节。
 */
function wpseo_pro_settings_section_general() {
	?>
	<p><?php esc_html_e( '控制站点名称、卡片样式、分析追踪与默认行为。', 'wpseo-pro' ); ?></p>
	<?php
}

/**
 * 站点地图节。
 */
function wpseo_pro_settings_section_sitemap() {
	?>
	<p><?php esc_html_e( '生成与发布 XML Sitemap，确保搜索引擎能够抓取。', 'wpseo-pro' ); ?></p>
	<?php
}

/**
 * 字段：站点名称
 */
function wpseo_pro_field_site_name() {
	$current = get_option( 'wpseo_pro_general', array() );
	?>
	<input name="wpseo_pro_general[site_name]" id="wpseo_pro_field_site_name" type="text" value="<?php echo esc_attr( $current['site_name'] ?? '' ); ?>" class="regular-text">
	<?php
}

/**
 * 字段：审计卡片样式
 */
function wpseo_pro_field_card_style() {
	$current = get_option( 'wpseo_pro_general', array() );
	?>
	<select name="wpseo_pro_general[card_style]" id="wpseo_pro_field_card_style">
		<option value="default" <?php selected( $current['card_style'] ?? '', 'default' ); ?>><?php esc_html_e( '默认深色', 'wpseo-pro' ); ?></option>
		<option value="glass" <?php selected( $current['card_style'] ?? '', 'glass' ); ?>><?php esc_html_e( '玻璃拟态', 'wpseo-pro' ); ?></option>
	</select>
	<?php
}

/**
 * 字段：分析 ID
 */
function wpseo_pro_field_analytics_id() {
	$current = get_option( 'wpseo_pro_general', array() );
	?>
	<input name="wpseo_pro_general[analytics_id]" id="wpseo_pro_field_analytics_id" type="text" value="<?php echo esc_attr( $current['analytics_id'] ?? '' ); ?>" class="regular-text" placeholder="e.g. G-XXXXXX">
	<?php
}

/**
 * 字段：生成站点地图
 */
function wpseo_pro_field_generate_sitemap() {
	$current = get_option( 'wpseo_pro_general', array() );
	?>
	<input name="wpseo_pro_general[generate_sitemap]" id="wpseo_pro_field_generate_sitemap" type="checkbox" value="1" <?php checked( isset( $current['generate_sitemap'] ) && '1' === $current['generate_sitemap'], '1' ); ?>
	<label for="wpseo_pro_field_generate_sitemap" style="font-size:14px;cursor:pointer;"><?php esc_html_e( '开启后，访问 /sitemap.xml 即可访问站点地图。', 'wpseo-pro' ); ?></label>
	<?php
}

/**
 * 字段：站点地图文件名
 */
function wpseo_pro_field_sitemap_basename() {
	$current = get_option( 'wpseo_pro_general', array() );
	?>
	<input name="wpseo_pro_general[sitemap_basename]" id="wpseo_pro_field_sitemap_basename" type="text" value="<?php echo esc_attr( $current['sitemap_basename'] ?? '' ); ?>" class="regular-text">
	<?php
}

/**
 * 字段：要纳入的文章类型
 */
function wpseo_pro_field_post_types() {
	$current = get_option( 'wpseo_pro_general', array() );
	?>
	<input name="wpseo_pro_general[post_types]" id="wpseo_pro_field_post_types" type="text" value="<?php echo esc_attr( $current['post_types'] ?? 'post,page,attachment' ); ?>" class="regular-text">
	<?php
}

/**
 * 字段：发布状况
 */
function wpseo_pro_field_post_statuses() {
	$current = get_option( 'wpseo_pro_general', array() );
	?>
	<input name="wpseo_pro_general[post_statuses]" id="wpseo_pro_field_post_statuses" type="text" value="<?php echo esc_attr( $current['post_statuses'] ?? 'publish,draft' ); ?>" class="regular-text">
	<?php
}

/**
 * 注册设置。
 */
function wpseo_pro_register_settings() {
	register_setting( 'wpseo_pro_general', 'wpseo_pro_general', 'wpseo_pro_sanitize_settings' );
}
add_action( 'admin_init', 'wpseo_pro_register_settings' );

/**
 * 校验设置。
 */
function wpseo_pro_sanitize_settings( $input ) {
	$defaults = array(
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

	$clean = array();
	foreach ( $defaults as $key => $default ) {
		$val = isset( $input[ $key ] ) ? $input[ $key ] : '';
		if ( is_bool( $defaults[ $key ] ) ) {
			$clean[ $key ] = in_array( $val, array( '1', 'on', 1 ), true ) ? '1' : '0';
		} elseif ( is_string( $defaults[ $key ] ) ) {
			$clean[ $key ] = sanitize_text_field( $val );
		} else {
			$clean[ $key ] = $val;
		}
	}

	return $clean;
}

/**
 * 默认设置值。
 */
function wpseo_pro_default_settings() {
	$defaults = array(
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

	return $defaults;
}
