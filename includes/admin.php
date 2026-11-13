<?php
/**
 * 后台入口：菜单、路由、设置页、审计面板、SEO 编辑器入口。
 *
 * 页面：
 *   - 仪表盘：SEO PRO 概览
 *   - 插件设置：通用设置（地图、robots、Schema、卡片风格）
 *   - 审计面板：站点/单篇 SEO 扫描与评分
 *   - 编辑器：单篇/单页 SEO 编辑器（与审计共用渲染）
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 注册后台菜单。
 */
function wpseo_pro_register_menu() {
	add_menu_page(
		__( 'SEO PRO', 'wpseo-pro' ),
		__( 'SEO PRO', 'wpseo-pro' ),
		'manage_options',
		'wpseo-pro',
		'wpseo_pro_render_dashboard',
		'dashicons-welcome-write-blog',
		26
	);

	add_submenu_page(
		'wpseo-pro',
		__( '概览', 'wpseo-pro' ),
		__( '概览', 'wpseo-pro' ),
		'manage_options',
		'wpseo-pro',
		'wpseo_pro_render_dashboard'
	);

	add_submenu_page(
		'wpseo-pro',
		__( '设置', 'wpseo-pro' ),
		__( '设置', 'wpseo-pro' ),
		'manage_options',
		'wpseo-pro-settings',
		'wpseo_pro_render_settings_page'
	);

	add_submenu_page(
		'wpseo-pro',
		__( 'SEO 审计', 'wpseo-pro' ),
		__( 'SEO 审计', 'wpseo-pro' ),
		'manage_options',
		'wpseo-pro-audit',
		'wpseo_pro_render_audit_panel'
	);

	add_submenu_page(
		'wpseo-pro',
		__( 'SEO 编辑器', 'wpseo-pro' ),
		__( 'SEO 编辑器', 'wpseo-pro' ),
		'manage_options',
		'wpseo-pro-editor',
		'wpseo_pro_render_seo_editor'
	);

	// 内容类型标签：SEO 编辑器跳转入口。
	add_submenu_page(
		'wpseo-pro',
		__( '编辑内容', 'wpseo-pro' ),
		__( '编辑内容', 'wpseo-pro' ),
		'manage_options',
		'wpseo-pro-editor-post',
		'wpseo_pro_render_seo_editor'
	);
}
add_action( 'admin_menu', 'wpseo_pro_register_menu' );

/**
 * 渲染仪表盘。
 */
function wpseo_pro_render_dashboard() {
	?>
	<div class="wrap wpseo-pro-dashboard">
		<h1><span class="dashicons dashicons-wordpress" style="color:#6366f1;"></span> WordPress SEO PRO</h1>
		<p class="description"><?php esc_html_e( '智能 SEO 审计与单页编辑器。', 'wpseo-pro' ); ?></p>

		<?php if ( wpseo_pro_can_check_audit() ) : ?>
			<div class="wpseo-pro-card">
				<h2><?php esc_html_e( '快速开始', 'wpseo-pro' ); ?></h2>
				<p><?php esc_html_e( '选择一篇文章或页面，进入“SEO 编辑器”实时编辑后，系统会自动扫描并给出标题、描述、图片、内部链接、关键词密度等维度的评分。', 'wpseo-pro' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpseo-pro-editor&post_type=post' ) ); ?>" class="button button-primary"><?php esc_html_e( '打开 SEO 编辑器', 'wpseo-pro' ); ?></a>
			</div>
		<?php endif; ?>

		<?php if ( wpseo_pro_can_generate_sitemap() ) : ?>
			<div class="wpseo-pro-card">
				<h2><?php esc_html_e( '站点地图', 'wpseo-pro' ); ?></h2>
				<p><?php esc_html_e( 'XML Sitemap 会自动生成并缓存，下次请求时生效。', 'wpseo-pro' ); ?></p>
				<a href="<?php echo esc_url( WPSEO_PRO_BASE_URL . 'sitemap.xml' ); ?>" class="button" target="_blank"><?php esc_html_e( '查看 Sitemap', 'wpseo-pro' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * 渲染设置页。
 */
function wpseo_pro_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( '对不起，您没有权限访问此页面。', 'wpseo-pro' ) );
	}

	$options = get_option( 'wpseo_pro_general', array() );

	?>
	<div class="wrap wpseo-pro-settings">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<?php settings_errors(); ?>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'wpseo_pro_general' );
			$current = get_option( 'wpseo_pro_general', array() );
			do_settings_sections( 'wpseo_pro_settings' );
			submit_button();
			?>
		</form>

		<div class="wpseo-pro-card" style="margin-top:24px;">
			<h3><?php esc_html_e( '快速帮助', 'wpseo-pro' ); ?></h3>
			<ul>
				<li><?php echo esc_html( wpseo_pro_help_link( 'editor' ) ); ?></li>
				<li><?php echo esc_html( wpseo_pro_help_link( 'audit' ) ); ?></li>
				<li><?php echo esc_html( wpseo_pro_help_link( 'sitemap' ) ); ?></li>
			</ul>
		</div>
	</div>
	<?php
}

/**
 * 渲染审计面板（后台快捷入口）。
 */
/**
 * 渲染 SEO 编辑器（需 JS 交互）。
 */

/**
 * 是否允许生成 Sitemap。
 */
function wpseo_pro_can_generate_sitemap() {
	return wpseo_pro_can( 'sitemap' );
}

/**
 * 帮助链接。
 */
function wpseo_pro_help_link( $key ) {
	$map = array(
		'editor' => '见“SEO 编辑器”标签页，实时更新标题、描述与评分。',
		'audit'  => '在“SEO 审计”页查看站点级扫描报告。',
		'sitemap' => '在“设置 > 站点地图”勾选后生成 XML Sitemap。',
	);
	return isset( $map[ $key ] ) ? esc_html( $map[ $key ] ) : '';
}
