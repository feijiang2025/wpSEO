<?php
/**
 * SEO 审计 AJAX 处理器。
 *
 * 暴露 wpseo_pro_audit_save 动作，用于后台保存审计结果。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpseo_pro_ajax_audit_save' ) ) :
	/**
	 * AJAX 保存审计结果。
	 */
	function wpseo_pro_ajax_audit_save() {
		check_ajax_referer( 'wpseo_pro_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_die( 'Invalid post id' );
		}

		$result = wpseo_pro_audit_single_post( $post_id );
		wp_cache_set( wpseo_pro_audit_cache_key( $post_id ), $result, 'wpseo_pro', 600 );

		wp_send_json_success( $result );
	}
endif;

add_action( 'wp_ajax_wpseo_pro_audit_save', 'wpseo_pro_ajax_audit_save' );
add_action( 'wp_ajax_nopriv_wpseo_pro_audit_save', 'wpseo_pro_ajax_audit_save' );
