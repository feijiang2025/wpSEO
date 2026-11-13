<?php
/**
 * 暗色主题资产：CSS 与 JS 注册、排队与输出。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 注册暗色主题样式表。
 */
function wpseo_pro_register_assets() {
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_register_style(
		'wpseo-pro-dashboard',
		WPSEO_PRO_ASSETS_URL . 'css/dark-theme.css',
		array(),
		WPSEO_PRO_VERSION
	);

	wp_register_script(
		'wpseo-pro-common',
		WPSEO_PRO_ASSETS_URL . 'js/common' . $min . '.js',
		array( 'jquery' ),
		WPSEO_PRO_VERSION,
		true
	);

	wp_register_script(
		'wpseo-pro-audit',
		WPSEO_PRO_ASSETS_URL . 'js/audit-panel' . $min . '.js',
		array( 'wpseo-pro-common' ),
		WPSEO_PRO_VERSION,
		true
	);

	wp_register_script(
		'wpseo-pro-editor',
		WPSEO_PRO_ASSETS_URL . 'js/seo-editor' . $min . '.js',
		array( 'wpseo-pro-common' ),
		WPSEO_PRO_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'wpseo_pro_register_assets' );

/**
 * 向后台页注入公共脚本与样式。
 */
function wpseo_pro_enqueue_admin_assets( $hook ) {
	// 所有后台页都加载主样式。
	wp_enqueue_style( 'wpseo-pro-dashboard' );

	// 编辑器页加载编辑器脚本。
	if ( 'toplevel_page_wpseo-pro-editor' === $hook || 'toplevel_page_wpseo-pro-editor-post' === $hook ) {
		wp_enqueue_script( 'wpseo-pro-editor' );
		wp_localize_script(
			'wpseo-pro-editor',
			'wpseoPro',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wpseo_pro_nonce' ),

				'i18n'     => array(
					'saved'       => __( '保存成功', 'wpseo-pro' ),
					'saveFailed'  => __( '保存失败', 'wpseo-pro' ),
					'titleLength' => __( '标题长度', 'wpseo-pro' ),
					'descriptionLength' => __( '描述长度', 'wpseo-pro' ),
				),

				'api'      => array(
					'get_audit'   => 'wpseo_pro_get_audit',
					'save_seo'    => 'wpseo_pro_save_seo',
					'validate'    => 'wpseo_pro_validate',
					'get_post'    => 'wpseo_pro_get_post',
					'bulk_save'   => 'wpseo_pro_bulk_save',
				),
			)
		);
	}

	// 审计页加载审计脚本。
	if ( 'toplevel_page_wpseo-pro-audit' === $hook ) {
		wp_enqueue_script( 'wpseo-pro-audit' );
		wp_localize_script(
			'wpseo-pro-audit',
			'wpseoPro',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wpseo_pro_nonce' ),

				'i18n'     => array(
					'scanning' => __( '正在扫描…', 'wpseo-pro' ),
					'score'    => __( '总分', 'wpseo-pro' ),
					'rating'   => __( '评分', 'wpseo-pro' ),
					'overview' => __( '概览', 'wpseo-pro' ),
					'title'    => __( '标题长度', 'wpseo-pro' ),
					'characters' => __( '字符数', 'wpseo-pro' ),
					'thumbnail' => __( '缩略图', 'wpseo-pro' ),
					'internalLinks' => __( '内部链接', 'wpseo-pro' ),
					'headings' => __( '标题结构', 'wpseo-pro' ),
					'keywordDensity' => __( '关键词密度', 'wpseo-pro' ),
					'commonWords' => __( '高频词', 'wpseo-pro' ),
					'failed'   => __( '请求失败', 'wpseo-pro' ),
				),

				'api'      => array(
					'get_audit' => 'wpseo_pro_get_audit',
				),
			)
		);
	}

	// 即使在设置页，也避免意外加载。
}
add_action( 'admin_enqueue_scripts', 'wpseo_pro_enqueue_admin_assets' );
