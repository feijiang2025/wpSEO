<?php
/**
 * SEO 审计面板渲染（后台页面）。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 审计面板主入口。
 */
function wpseo_pro_render_audit_panel() {
	if ( ! wpseo_pro_can_check_audit() ) {
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
			<?php if ( $post_id ) : ?>
				<a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $post_id ) ); ?>" class="button"><?php esc_html_e( '编辑内容', 'wpseo-pro' ); ?></a>
			<?php endif; ?>
		</div>

		<?php if ( $post_id ) : ?>
			<div class="wpseo-pro-audit-body">
				<h2 style="margin-top:0;"><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>

				<div class="score-ring-wrap">
					<div class="score-ring" data-score="<?php echo esc_attr( $audit['score'] ?? 0 ); ?>">
						<span class="score-value"><?php echo esc_html( $audit['score'] ?? 0 ); ?></span>
						<span class="score-label"><?php esc_html_e( '总分', 'wpseo-pro' ); ?></span>
					</div>
				</div>

				<div class="audit-cards">
					<?php
					$rows = array(
						'summary' => array(
							'icon' => 'index-card',
							'label' => __( '概览', 'wpseo-pro' ),
							'items' => array(
								array(
									'label' => __( '标题长度', 'wpseo-pro' ),
									'value' => isset( $audit['title_len'] ) ? esc_html( $audit['title_len'] ) : '—',
									'note'  => __( '字符数', 'wpseo-pro' ),
								),
								array(
									'label' => __( '缩略图', 'wpseo-pro' ),
									'value' => isset( $audit['has_image'] ) ? ( $audit['has_image'] ? __( '已有', 'wpseo-pro' ) : __( '未找到', 'wpseo-pro' ) ) : '—',
									'note'  => '',
								),
								array(
									'label' => __( '内部链接', 'wpseo-pro' ),
									'value' => isset( $audit['internal_links'] ) ? esc_html( $audit['internal_links'] ) : '—',
									'note'  => __( '个', 'wpseo-pro' ),
								),
							),
						),
						'headings' => array(
							'icon' => 'list',
							'label' => __( '标题结构', 'wpseo-pro' ),
							'items' => array(
								array(
									'label' => __( 'H1', 'wpseo-pro' ),
									'value' => isset( $audit['h1_count'] ) ? esc_html( $audit['h1_count'] ) : '0',
									'note' => $audit['details']['missing_h1'] ? __( '建议至少一个 H1', 'wpseo-pro' ) : '',
								),
								array(
									'label' => __( 'H2', 'wpseo-pro' ),
									'value' => isset( $audit['h2_count'] ) ? esc_html( $audit['h2_count'] ) : '0',
									'note' => $audit['details']['missing_h2'] ? __( '建议至少一个 H2', 'wpseo-pro' ) : '',
								),
								array(
									'label' => __( 'H3', 'wpseo-pro' ),
									'value' => isset( $audit['h3_count'] ) ? esc_html( $audit['h3_count'] ) : '0',
									'note' => $audit['details']['too_many_h3'] ? __( '建议控制 H3 数量', 'wpseo-pro' ) : '',
								),
							),
						),
						'keywords' => array(
							'icon' => 'tag',
							'label' => __( '关键词密度', 'wpseo-pro' ),
							'items' => array(),
						),
						'common' => array(
							'icon' => 'admin-tools',
							'label' => __( '高频词', 'wpseo-pro' ),
							'items' => array(),
						),
					);

					if ( ! empty( $audit['keyword_density'] ) ) {
						foreach ( $audit['keyword_density'] as $kw => $rate ) {
							$rating = 'low';
							if ( $rate >= 3 ) {
								$rating = 'high';
							} elseif ( $rate >= 1 ) {
								$rating = 'medium';
							}
							$rows['keywords']['items'][] = array(
								'label' => esc_html( $kw ),
								'value' => esc_html( $rate ),
								'note'  => sprintf( __( '%s%%', 'wpseo-pro' ), $rate ),
								'badge' => $rating,
							);
						}
					}

					if ( ! empty( $audit['common_words'] ) ) {
						foreach ( $audit['common_words'] as $w ) {
							$rows['common']['items'][] = array(
								'label' => esc_html( $w ),
								'value' => '',
								'note'  => '',
							);
						}
					}

					foreach ( $rows as $section ) {
						if ( empty( $section['items'] ) && empty( $section['label'] ) ) {
							continue;
						}
						?>
						<div class="audit-card">
							<h3 class="audit-card-title">
								<span class="dashicons <?php echo esc_attr( 'dashicons-' . $section['icon'] ); ?>"></span>
								<?php echo esc_html( $section['label'] ); ?>
							</h3>
							<ul class="audit-card-list">
								<?php foreach ( $section['items'] as $item ) : ?>
									<li>
										<span class="audit-card-item-label"><?php echo esc_html( $item['label'] ); ?></span>
										<span class="audit-card-item-value"><?php echo esc_html( $item['value'] ); ?></span>
										<?php if ( ! empty( $item['note'] ) ) : ?>
											<span class="audit-card-item-note"><?php echo esc_html( $item['note'] ); ?></span>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<?php
					}
					?>
				</div>

				<?php if ( ! empty( $audit['id'] ) ) : ?>
					<div class="audit-meta">
						<?php printf( __( '文章：%s（%s）', 'wpseo-pro' ), esc_html( $audit['post_type'] ), esc_html( $audit['post_id'] ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="wpseo-pro-empty">
				<p><?php esc_html_e( '请输入文章/页面 ID 或在 SEO 编辑器中选择具体内容进行审计。', 'wpseo-pro' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * 是否允许访问审计。
 */
function wpseo_pro_can_check_audit() {
	return current_user_can( 'manage_options' );
}
