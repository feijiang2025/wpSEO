<?php
/**
 * SEO 编辑器：单篇/单页 SEO 实时编辑器渲染。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 渲染 SEO 编辑器。
 */
function wpseo_pro_render_seo_editor() {
	if ( ! wpseo_pro_can_edit_seo() ) {
		wp_die( __( '对不起，您没有权限访问此页面。', 'wpseo-pro' ) );
	}

	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
	if ( ! $post_id ) {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	}

	if ( ! $post_id ) {
		wp_die( __( '未指定文章 ID。', 'wpseo-pro' ) );
	}

	if ( ! get_post( $post_id ) ) {
		wp_die( __( '文章不存在。', 'wpseo-pro' ) );
	}

	$seo = wpseo_pro_get_seo( $post_id );

	?>
	<div class="wrap wpseo-pro-editor">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="wpseo-pro-editor-header">
			<a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $post_id ) ); ?>" class="button"><?php esc_html_e( '← 返回文章编辑', 'wpseo-pro' ); ?></a>
			<span class="editor-post-label"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
		</div>

		<div class="wpseo-pro-editor-body">
			<form id="wpseo-editor-form" method="post">
				<?php wp_nonce_field( 'wpseo_pro_nonce', 'nonce' ); ?>
				<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">

				<div class="editor-section">
					<h2><?php esc_html_e( '标题与描述', 'wpseo-pro' ); ?></h2>
					<div class="field-group">
						<label for="seo_title"><?php esc_html_e( 'SEO 标题', 'wpseo-pro' ); ?></label>
						<input id="seo_title" name="seo_title" type="text" value="<?php echo esc_attr( $seo['seo_title'] ?? '' ); ?>" class="regular-text">
						<span class="length-indicator"><span class="current">0</span>/60</span>
					</div>
					<div class="field-group">
						<label for="metadescription"><?php esc_html_e( '描述', 'wpseo-pro' ); ?></label>
						<textarea id="metadescription" name="metadescription" rows="4" class="large-text"><?php echo esc_textarea( $seo['metadescription'] ?? '' ); ?></textarea>
						<span class="length-indicator"><span class="current">0</span>/160</span>
					</div>
				</div>

				<div class="editor-section">
					<h2><?php esc_html_e( '关键词与标签', 'wpseo-pro' ); ?></h2>
					<div class="field-group">
						<label for="focus_keyword"><?php esc_html_e( '焦点关键词', 'wpseo-pro' ); ?></label>
						<input id="focus_keyword" name="focus_keyword" type="text" value="<?php echo esc_attr( $seo['focus_keyword'] ?? '' ); ?>" class="regular-text">
					</div>
					<div class="field-group">
						<label for="metatags"><?php esc_html_e( '元标签', 'wpseo-pro' ); ?></label>
						<textarea id="metatags" name="metatags" rows="3" class="large-text"><?php echo esc_textarea( $seo['metatags'] ?? '' ); ?></textarea>
						<p class="description"><?php esc_html_e( '每行一个，支持[name]=value 格式。', 'wpseo-pro' ); ?></p>
					</div>
				</div>

				<div class="editor-section">
					<h2><?php esc_html_e( '结构化数据', 'wpseo-pro' ); ?></h2>
					<div class="field-group">
						<label for="schema_type"><?php esc_html_e( 'Schema 类型', 'wpseo-pro' ); ?></label>
						<select id="schema_type" name="schema_type">
							<option value=""><?php esc_html_e( '默认/无', 'wpseo-pro' ); ?></option>
							<option value="Article" <?php selected( $seo['schema_type'] ?? '', 'Article' ); ?>><?php esc_html_e( 'Article', 'wpseo-pro' ); ?></option>
							<option value="WebPage" <?php selected( $seo['schema_type'] ?? '', 'WebPage' ); ?>><?php esc_html_e( 'WebPage', 'wpseo-pro' ); ?></option>
							<option value="Organization" <?php selected( $seo['schema_type'] ?? '', 'Organization' ); ?>><?php esc_html_e( 'Organization', 'wpseo-pro' ); ?></option>
							<option value="Product" <?php selected( $seo['schema_type'] ?? '', 'Product' ); ?>><?php esc_html_e( 'Product', 'wpseo-pro' ); ?></option>
							<option value="LocalBusiness" <?php selected( $seo['schema_type'] ?? '', 'LocalBusiness' ); ?>><?php esc_html_e( 'LocalBusiness', 'wpseo-pro' ); ?></option>
							<option value="FAQPage" <?php selected( $seo['schema_type'] ?? '', 'FAQPage' ); ?>><?php esc_html_e( 'FAQPage', 'wpseo-pro' ); ?></option>
							<option value="BreadcrumbList" <?php selected( $seo['schema_type'] ?? '', 'BreadcrumbList' ); ?>><?php esc_html_e( 'BreadcrumbList', 'wpseo-pro' ); ?></option>
						</select>
					</div>
				</div>

				<div class="editor-section">
					<h2><?php esc_html_e( '评分', 'wpseo-pro' ); ?></h2>
					<div class="score-ring-wrap">
						<div class="score-ring" data-score="<?php echo esc_attr( $seo['score'] ?? 0 ); ?>">
							<span class="score-value"><?php echo esc_html( $seo['score'] ?? 0 ); ?></span>
							<span class="score-label"><?php esc_html_e( '当前评分', 'wpseo-pro' ); ?></span>
						</div>
					</div>
					<div class="score-breakdown" id="score-breakdown"></div>
				</div>

				<div class="editor-actions">
					<button type="submit" class="button button-primary"><?php esc_html_e( '保存', 'wpseo-pro' ); ?></button>
					<button type="button" class="button" id="wpseo-save-async"><?php esc_html_e( '异步保存', 'wpseo-pro' ); ?></button>
				</div>
			</form>
		</div>
	</div>
	<?php
}

/**
 * 是否允许编辑 SEO。
 */
function wpseo_pro_can_edit_seo() {
	return current_user_can( 'manage_options' );
}
