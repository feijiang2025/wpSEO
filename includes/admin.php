<?php
/**
 * 后台入口：菜单注册与页面渲染。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpseo_pro_register_menu() {
	add_menu_page( __( 'SEO PRO', 'wpseo-pro' ), __( 'SEO PRO', 'wpseo-pro' ), 'manage_options', 'wpseo-pro', 'wpseo_pro_render_dashboard', 'dashicons-welcome-write-blog', 26 );
	add_submenu_page( 'wpseo-pro', __( '概览', 'wpseo-pro' ), __( '概览', 'wpseo-pro' ), 'manage_options', 'wpseo-pro', 'wpseo_pro_render_dashboard' );
	add_submenu_page( 'wpseo-pro', __( '设置', 'wpseo-pro' ), __( '设置', 'wpseo-pro' ), 'manage_options', 'wpseo-pro-settings', 'wpseo_pro_render_settings_page' );
	add_submenu_page( 'wpseo-pro', __( 'SEO 审计', 'wpseo-pro' ), __( 'SEO 审计', 'wpseo-pro' ), 'manage_options', 'wpseo-pro-audit', 'wpseo_pro_render_audit_panel' );
	add_submenu_page( 'wpseo-pro', __( 'SEO 编辑器', 'wpseo-pro' ), __( 'SEO 编辑器', 'wpseo-pro' ), 'manage_options', 'wpseo-pro-editor', 'wpseo_pro_render_seo_editor' );
	add_submenu_page( 'wpseo-pro', __( '编辑内容', 'wpseo-pro' ), __( '编辑内容', 'wpseo-pro' ), 'manage_options', 'wpseo-pro-editor-post', 'wpseo_pro_render_seo_editor' );
}
add_action( 'admin_menu', 'wpseo_pro_register_menu' );

function wpseo_pro_render_dashboard() {
	?>
	<div class="wrap wpseo-pro-dashboard">
		<h1><span class="dashicons dashicons-wordpress" style="color:#6366f1;"></span> WordPress SEO PRO</h1>
		<p class="description"><?php esc_html_e( '智能 SEO 审计与单页编辑器。', 'wpseo-pro' ); ?></p>
		<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<div class="wpseo-pro-card">
				<h2><?php esc_html_e( '快速开始', 'wpseo-pro' ); ?></h2>
				<p><?php esc_html_e( '选择一篇文章或页面，进入"SEO 编辑器"实时编辑后，系统会自动扫描并给出标题、描述、图片、内部链接、关键词密度等维度的评分。', 'wpseo-pro' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpseo-pro-editor&post_type=post' ) ); ?>" class="button button-primary"><?php esc_html_e( '打开 SEO 编辑器', 'wpseo-pro' ); ?></a>
			</div>
		<?php endif; ?>
		<?php if ( wpseo_pro_can( 'sitemap_index' ) ) : ?>
			<div class="wpseo-pro-card">
				<h2><?php esc_html_e( '站点地图', 'wpseo-pro' ); ?></h2>
				<p><?php esc_html_e( 'XML Sitemap 会自动生成并缓存，下次请求时生效。', 'wpseo-pro' ); ?></p>
				<a href="<?php echo esc_url( WPSEO_PRO_BASE_URL . 'sitemap.xml' ); ?>" class="button" target="_blank"><?php esc_html_e( '查看 Sitemap', 'wpseo-pro' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

function wpseo_pro_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( '对不起，您没有权限访问此页面。', 'wpseo-pro' ) );
	}
	?>
	<div class="wrap wpseo-pro-settings">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php settings_errors(); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'wpseo_pro_general' ); do_settings_sections( 'wpseo_pro_settings' ); submit_button(); ?>
		</form>
	</div>
	<?php
}

function wpseo_pro_render_audit_panel() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( '对不起，您没有权限访问此页面。', 'wpseo-pro' ) );
	}
	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
	if ( ! $post_id ) {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	}
	$audit = array();
	if ( $post_id ) {
		$audit = wpseo_pro_get_audit( $post_id );
	}
	?>
	<div class="wrap wpseo-pro-audit">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<div class="wpseo-pro-audit-sidebar">
			<form method="get">
				<input type="hidden" name="page" value="wpseo-pro-audit">
				<label for="post_id"><?php esc_html_e( '审计对象', 'wpseo-pro' ); ?></label>
				<input id="post_id" name="post_id" type="number" value="<?php echo esc_attr( $post_id ); ?>" class="regular-text" placeholder="文章/页面 ID">
				<button type="submit" class="button"><?php esc_html_e( '扫描', 'wpseo-pro' ); ?></button>
			</form>
			<?php if ( $post_id ) : ?><a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $post_id ) ); ?>" class="button"><?php esc_html_e( '编辑内容', 'wpseo-pro' ); ?></a><?php endif; ?>
		</div>
		<?php if ( $post_id ) : ?>
			<div class="wpseo-pro-audit-body">
				<h2 style="margin-top:0;"><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
				<div class="score-ring-wrap"><div class="score-ring" data-score="<?php echo esc_attr( $audit['score'] ?? 0 ); ?>"><span class="score-value"><?php echo esc_html( $audit['score'] ?? 0 ); ?></span><span class="score-label"><?php esc_html_e( '总分', 'wpseo-pro' ); ?></span></div></div>
				<div class="audit-cards">
					<?php
					$rows = array(
						summary => array( 'icon' => 'index-card', 'label' => __( '概览', 'wpseo-pro' ), 'items' => array( array( 'label' => __( '标题长度', 'wpseo-pro' ), 'value' => isset( $audit['title_len'] ) ? esc_html( $audit['title_len'] ) : '—', 'note' => __( '字符数', 'wpseo-pro' ) ), array( 'label' => __( '缩略图', 'wpseo-pro' ), 'value' => isset( $audit['has_image'] ) ? ( $audit['has_image'] ? __( '已有', 'wpseo-pro' ) : __( '未找到', 'wpseo-pro' ) ) : '—', 'note' => '' ), array( 'label' => __( '内部链接', 'wpseo-pro' ), 'value' => isset( $audit['internal_links'] ) ? esc_html( $audit['internal_links'] ) : '—', 'note' => __( '个', 'wpseo-pro' ) ) ) ),
						headings => array( 'icon' => 'list', 'label' => __( '标题结构', 'wpseo-pro' ), 'items' => array( array( 'label' => 'H1', 'value' => isset( $audit['h1_count'] ) ? esc_html( $audit['h1_count'] ) : '0', 'note' => $audit['details']['missing_h1'] ? __( '建议至少一个 H1', 'wpseo-pro' ) : '' ), array( 'label' => 'H2', 'value' => isset( $audit['h2_count'] ) ? esc_html( $audit['h2_count'] ) : '0', 'note' => $audit['details']['missing_h2'] ? __( '建议至少一个 H2', 'wpseo-pro' ) : '' ), array( 'label' => 'H3', 'value' => isset( $audit['h3_count'] ) ? esc_html( $audit['h3_count'] ) : '0', 'note' => $audit['details']['too_many_h3'] ? __( '建议控制 H3 数量', 'wpseo-pro' ) : '' ) ) ),
						'keywords' => array( 'icon' => 'tag', 'label' => __( '关键词密度', 'wpseo-pro' ), 'items' => array() ),
						'common' => array( 'icon' => 'admin-tools', 'label' => __( '高频词', 'wpseo-pro' ), 'items' => array() )
					);
					if ( ! empty( $audit['keyword_density'] ) ) {
						foreach ( $audit['keyword_density'] as $kw => $rate ) {
							$rating = 'low';
							if ( $rate >= 3 ) { $rating = 'high'; } elseif ( $rate >= 1 ) { $rating = 'medium'; }
							$rows['keywords']['items'][] = array( 'label' => esc_html( $kw ), 'value' => esc_html( $rate ), 'note' => sprintf( __( '%s%%', 'wpseo-pro' ), $rate ), 'badge' => $rating );
						}
					}
					if ( ! empty( $audit['common_words'] ) ) {
						foreach ( $audit['common_words'] as $w ) { $rows['common']['items'][] = array( 'label' => esc_html( $w ), 'value' => '', 'note' => '' ); }
					}
					foreach ( $rows as $section ) {
						if ( empty( $section['items'] ) && empty( $section['label'] ) ) { continue; }
						?><div class="audit-card"><h3 class="audit-card-title"><span class="dashicons <?php echo esc_attr( 'dashicons-' . $section['icon'] ); ?>"></span><?php echo esc_html( $section['label'] ); ?></h3><ul class="audit-card-list"><?php foreach ( $section['items'] as $item ) : ?><li><span class="audit-card-item-label"><?php echo esc_html( $item['label'] ); ?></span><span class="audit-card-item-value"><?php echo esc_html( $item['value'] ); ?></span><?php if ( ! empty( $item['note'] ) ) : ?><span class="audit-card-item-note"><?php echo esc_html( $item['note'] ); ?></span><?php endif; ?></li><?php endforeach; ?></ul></div><?php }
					}?>
				</div>
			</div>
		<?php else : ?><div class="wpseo-pro-empty"><p><?php esc_html_e( '请输入文章/页面 ID 或在 SEO 编辑器中选择具体内容进行审计。', 'wpseo-pro' ); ?></p></div><?php endif; ?>
	</div>
	<?php
}

function wpseo_pro_render_seo_editor() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( '对不起，您没有权限访问此页面。', 'wpseo-pro' ) );
	}
	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
	if ( ! $post_id ) {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	}
	if ( ! $post_id ) { wp_die( __( '未指定文章 ID。', 'wpseo-pro' ) ); }
	if ( ! get_post( $post_id ) ) { wp_die( __( '文章不存在。', 'wpseo-pro' ) ); }
	$seo = wpseo_pro_get_seo( $post_id );
	?>
	<div class="wrap wpseo-pro-editor">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<div class="wpseo-pro-editor-header"><a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $post_id ) ); ?>" class="button"><?php esc_html_e( '← 返回文章编辑', 'wpseo-pro' ); ?></a><span class="editor-post-label"><?php echo esc_html( get_the_title( $post_id ) ); ?></span></div>
		<div class="wpseo-pro-editor-body">
			<form id="wpseo-editor-form" method="post">
				<?php wp_nonce_field( 'wpseo_pro_nonce', 'nonce' ); ?><input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
				<div class="editor-section"><h2><?php esc_html_e( '标题与描述', 'wpseo-pro' ); ?></h2>
					<div class="field-group"><label for="seo_title"><?php esc_html_e( 'SEO 标题', 'wpseo-pro' ); ?></label><input id="seo_title" name="seo_title" type="text" value="<?php echo esc_attr( $seo['seo_title'] ?? '' ); ?>" class="regular-text"><span class="length-indicator"><span class="current">0</span>/60</span></div>
					<div class="field-group"><label for="metadescription"><?php esc_html_e( '描述', 'wpseo-pro' ); ?></label><textarea id="metadescription" name="metadescription" rows="4" class="large-text"><?php echo esc_textarea( $seo['metadescription'] ?? '' ); ?></textarea><span class="length-indicator"><span class="current">0</span>/160</span></div>
				</div>
				<div class="editor-section"><h2><?php esc_html_e( '关键词与标签', 'wpseo-pro' ); ?></h2>
					<div class="field-group"><label for="focus_keyword"><?php esc_html_e( '焦点关键词', 'wpseo-pro' ); ?></label><input id="focus_keyword" name="focus_keyword" type="text" value="<?php echo esc_attr( $seo['focus_keyword'] ?? '' ); ?>" class="regular-text"></div>
					<div class="field-group"><label for="metatags"><?php esc_html_e( '元标签', 'wpseo-pro' ); ?></label><textarea id="metatags" name="metatags" rows="3" class="large-text"><?php echo esc_textarea( $seo['metatags'] ?? '' ); ?></textarea><p class="description"><?php esc_html_e( '每行一个，支持[name]=value 格式。', 'wpseo-pro' ); ?></p></div>
				</div>
				<div class="editor-section"><h2><?php esc_html_e( '结构化数据', 'wpseo-pro' ); ?></h2>
					<div class="field-group"><label for="schema_type"><?php esc_html_e( 'Schema 类型', 'wpseo-pro' ); ?></label><select id="schema_type" name="schema_type"><option value=""><?php esc_html_e( '默认/无', 'wpseo-pro' ); ?></option><option value="Article" <?php selected( $seo['schema_type'] ?? '', 'Article' ); ?>><?php esc_html_e( 'Article', 'wpseo-pro' ); ?></option><option value="WebPage" <?php selected( $seo['schema_type'] ?? '', 'WebPage' ); ?>><?php esc_html_e( 'WebPage', 'wpseo-pro' ); ?></option><option value="Organization" <?php selected( $seo['schema_type'] ?? '', 'Organization' ); ?>><?php esc_html_e( 'Organization', 'wpseo-pro' ); ?></option><option value="Product" <?php selected( $seo['schema_type'] ?? '', 'Product' ); ?>><?php esc_html_e( 'Product', 'wpseo-pro' ); ?></option><option value="LocalBusiness" <?php selected( $seo['schema_type'] ?? '', 'LocalBusiness' ); ?>><?php esc_html_e( 'LocalBusiness', 'wpseo-pro' ); ?></option><option value="FAQPage" <?php selected( $seo['schema_type'] ?? '', 'FAQPage' ); ?>><?php esc_html_e( 'FAQPage', 'wpseo-pro' ); ?></option><option value="BreadcrumbList" <?php selected( $seo['schema_type'] ?? '', 'BreadcrumbList' ); ?>><?php esc_html_e( 'BreadcrumbList', 'wpseo-pro' ); ?></option></select></div>
				</div>
				<div class="editor-section"><h2><?php esc_html_e( '评分', 'wpseo-pro' ); ?></h2>
					<div class="score-ring-wrap"><div class="score-ring" data-score="<?php echo esc_attr( $seo['score'] ?? 0 ); ?>"><span class="score-value"><?php echo esc_html( $seo['score'] ?? 0 ); ?></span><span class="score-label"><?php esc_html_e( '当前评分', 'wpseo-pro' ); ?></span></div></div><div class="score-breakdown" id="score-breakdown"></div></div>
				<div class="editor-actions"><button type="submit" class="button button-primary"><?php esc_html_e( '保存', 'wpseo-pro' ); ?></button><button type="button" class="button" id="wpseo-save-async"><?php esc_html_e( '异步保存', 'wpseo-pro' ); ?></button></div>
			</form>
		</div>
	</div>
	<?php
}
