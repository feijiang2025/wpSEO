<?php
/**
 * SEO PRO 基础设置页。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_setting( 'wpseo_pro_general', 'site_name', array( 'type' => 'string', 'default' => '' ) );
add_setting( 'wpseo_pro_general', 'generate_sitemap', array( 'type' => 'boolean', 'default' => '1' ) );
add_setting( 'wpseo_pro_general', 'robots_txt', array( 'type' => 'string', 'default' => '' ) );
add_setting( 'wpseo_pro_general', 'use_schema', array( 'type' => 'boolean', 'default' => '1' ) );
add_setting( 'wpseo_pro_general', 'card_style', array( 'type' => 'string', 'default' => 'default' ) );
add_setting( 'wpseo_pro_general', 'analytics_id', array( 'type' => 'string', 'default' => '' ) );
add_setting( 'wpseo_pro_general', 'sitemap_basename', array( 'type' => 'string', 'default' => 'sitemap' ) );
add_setting( 'wpseo_pro_general', 'post_types', array( 'type' => 'string', 'default' => 'post,page,attachment' ) );
add_setting( 'wpseo_pro_general', 'post_statuses', array( 'type' => 'string', 'default' => 'publish,draft' ) );

add_settings_section( 'wpseo_pro_section_general', __( '基础设置', 'wpseo-pro' ), 'wpseo_pro_settings_section_general', 'wpseo_pro_settings' );
add_settings_section( 'wpseo_pro_section_sitemap', __( '站点地图', 'wpseo-pro' ), 'wpseo_pro_settings_section_sitemap', 'wpseo_pro_settings' );

/**
 * 基础设置节。
 */
function wpseo_pro_settings_section_general() {
	?>
	<p><?php esc_html_e( '控制站点名称、卡片样式、分析追踪与默认行为。', 'wpseo-pro' ); ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="site_name"><?php esc_html_e( '站点名称', 'wpseo-pro' ); ?></label></th>
			<td><input name="wpseo_pro_general[site_name]" id="site_name" type="text" value="" class="regular-text"></td>
		</tr>
		<tr>
			<th scope="row"><label for="card_style"><?php esc_html_e( '审计卡片样式', 'wpseo-pro' ); ?></label></th>
			<td>
				<select name="wpseo_pro_general[card_style]" id="card_style">
					<option value="default" <?php selected( esc_attr( $current['card_style'] ?? '' ), 'default' ); ?>><?php esc_html_e( '默认深色', 'wpseo-pro' ); ?></option>
					<option value="glass" <?php selected( esc_attr( $current['card_style'] ?? '' ), 'glass' ); ?>><?php esc_html_e( '玻璃拟态', 'wpseo-pro' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="analytics_id"><?php esc_html_e( '分析 ID（可选）', 'wpseo-pro' ); ?></label></th>
			<td><input name="wpseo_pro_general[analytics_id]" id="analytics_id" type="text" value="" class="regular-text" placeholder="e.g. G-XXXXXX"></td>
		</tr>
	</table>
	<?php
}

/**
 * 站点地图节。
 */
function wpseo_pro_settings_section_sitemap() {
	?>
	<p><?php esc_html_e( '生成与发布 XML Sitemap，确保搜索引擎能够抓取。', 'wpseo-pro' ); ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="generate_sitemap"><?php esc_html_e( '生成站点地图', 'wpseo-pro' ); ?></label></th>
			<td>
				<input name="wpseo_pro_general[generate_sitemap]" id="generate_sitemap" type="checkbox" value="1" <?php checked( isset( $current['generate_sitemap'] ) && '1' === $current['generate_sitemap'], '1' ); ?>>
				<label for="generate_sitemap" style="font-size:14px;cursor:pointer;"><?php esc_html_e( '开启后，访问 /sitemap.xml 即可访问站点地图。', 'wpseo-pro' ); ?></label>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sitemap_basename"><?php esc_html_e( '站点地图文件名', 'wpseo-pro' ); ?></label></th>
			<td><input name="wpseo_pro_general[sitemap_basename]" id="sitemap_basename" type="text" value="" class="regular-text"></td>
		</tr>
		<tr>
			<th scope="row"><label for="post_types"><?php esc_html_e( '要纳入的文章类型', 'wpseo-pro' ); ?></label></th>
			<td><input name="wpseo_pro_general[post_types]" id="post_types" type="text" value="" class="regular-text"></td>
		</tr>
		<tr>
			<th scope="row"><label for="post_statuses"><?php esc_html_e( '发布状况', 'wpseo-pro' ); ?></label></th>
			<td><input name="wpseo_pro_general[post_statuses]" id="post_statuses" type="text" value="" class="regular-text"></td>
		</tr>
	</table>
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
