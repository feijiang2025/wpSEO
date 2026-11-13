<?php
/**
 * SEO 审计面板渲染（后台页面）。
 */

if ( ! defined( 'ABSPATH' ) ) {
\texit;
}

/**
 * 是否允许访问审计。
 */
function wpseo_pro_can_check_audit() {
	return current_user_can( 'manage_options' );
}
