<?php
/**
 * SEO 审计缓存与获取。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpseo_pro_audit_cache_key' ) ) :
	/**
	 * 审计结果缓存键。
	 */
	function wpseo_pro_audit_cache_key( $post_id ) {
		return 'wpseo_pro_audit_' . absint( $post_id );
	}
endif;

if ( ! function_exists( 'wpseo_pro_get_audit' ) ) :
	/**
	 * 获取审计结果，若无则生成并缓存。
	 */
	function wpseo_pro_get_audit( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || ! get_post( $post_id ) ) {
			return array();
		}

		$cache = wp_cache_get( wpseo_pro_audit_cache_key( $post_id ), 'wpseo_pro' );
		if ( false !== $cache && is_array( $cache ) ) {
			return $cache;
		}

		$result = wpseo_pro_audit_single_post( $post_id );
		wp_cache_set( wpseo_pro_audit_cache_key( $post_id ), $result, 'wpseo_pro', 600 );

		return $result;
	}
endif;
