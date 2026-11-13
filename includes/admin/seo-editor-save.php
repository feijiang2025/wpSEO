<?php
/**
 * SEO 编辑器 AJAX 处理器：保存 SEO 数据。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpseo_pro_ajax_save_seo' ) ) :
	/**
	 * AJAX 保存 SEO 数据。
	 */
	function wpseo_pro_ajax_save_seo() {
		check_ajax_referer( 'wpseo_pro_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_die( 'Invalid post id' );
		}

		$allowed = array(
			'seo_title'       => array( 'sanitize_text_field' ),
			'metadescription' => array( 'wp_kses_post' ),
			'focus_keyword'   => array( 'sanitize_text_field' ),
			'metatags'        => array( 'wp_kses_post' ),
			'schema_type'     => array( 'sanitize_text_field' ),
			'schema_config'   => array( 'maybe_serialize' ),
			'custom_titles'   => array( 'maybe_serialize' ),
		);

		$data = array();
		foreach ( $allowed as $key => $methods ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = $_POST[ $key ];
				if ( is_array( $value ) ) {
					$value = implode( ',', $value );
				}
				$data[ $key ] = call_user_func_array( $methods[0], array( $value ) );
			}
		}

		// 计算评分。
		$score = wpseo_pro_calculate_score(
			isset( $data['seo_title'] ) ? $data['seo_title'] : '',
			! empty( $data['seo_title'] ),
			0,
			array()
		);

		$data['score'] = $score;

		$result = wpseo_pro_save_seo( $post_id, $data );

		if ( $result ) {
			$seo = wpseo_pro_get_seo( $post_id );
			wp_cache_set( wpseo_pro_audit_cache_key( $post_id ), $seo, 'wpseo_pro', 600 );

			wp_send_json_success( array(
				'score'     => $score,
				'seo_title' => $seo['seo_title'],
			) );
		} else {
			wp_send_json_error( array( 'message' => __( '保存失败', 'wpseo-pro' ) ) );
		}
	}
endif;

add_action( 'wp_ajax_wpseo_pro_save_seo', 'wpseo_pro_ajax_save_seo' );
add_action( 'wp_ajax_nopriv_wpseo_pro_save_seo', 'wpseo_pro_ajax_save_seo' );
