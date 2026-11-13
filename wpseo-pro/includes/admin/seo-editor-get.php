<?php
/**
 * SEO 编辑器：文本数据读写与缓存。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpseo_pro_get_seo' ) ) :
	/**
	 * 获取/生成文章的 SEO 数据。
	 */
	function wpseo_pro_get_seo( $post_id ) {
		global $wpdb;

		$post_id = absint( $post_id );
		$cache_key = 'wpseo_pro_seo_' . $post_id;

		$cache = wp_cache_get( $cache_key, 'wpseo_pro' );
		if ( false !== $cache && is_array( $cache ) ) {
			return $cache;
		}

		$table = WPSEO_PRO_TABLE_POSTS;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE post_id = %d LIMIT 1", $post_id ) );

		$data = array(
			'post_id'         => $post_id,
			'seo_title'       => '',
			'metadescription' => '',
			'focus_keyword'   => '',
			'metatags'        => '',
			'schema_type'     => '',
			'schema_config'   => '',
			'custom_titles'   => '',
			'score'           => 0,
		);

		if ( $row ) {
			$data = array(
				'post_id'         => $row->post_id,
				'seo_title'       => $row->seo_title,
				'metadescription' => $row->metadescription,
				'focus_keyword'   => $row->focus_keyword,
				'metatags'        => $row->metatags,
				'schema_type'     => $row->schema_type,
				'schema_config'   => $row->schema_config,
				'custom_titles'   => $row->custom_titles,
				'score'           => 0,
			);
		}

		$cache = $data;
		wp_cache_set( $cache_key, $cache, 'wpseo_pro', 300 );

		return $cache;
	}
endif;

if ( ! function_exists( 'wpseo_pro_save_seo' ) ) :
	/**
	 * 保存文章 SEO 数据到数据库。
	 */
	function wpseo_pro_save_seo( $post_id, $data ) {
		global $wpdb;

		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return false;
		}

		$seo_title     = isset( $data['seo_title'] ) ? sanitize_text_field( $data['seo_title'] ) : '';
		$metadescription = isset( $data['metadescription'] ) ? wp_kses_post( $data['metadescription'] ) : '';
		$focus_keyword   = isset( $data['focus_keyword'] ) ? sanitize_text_field( $data['focus_keyword'] ) : '';
		$metatags      = isset( $data['metatags'] ) ? wp_kses_post( $data['metatags'] ) : '';
		$schema_type   = isset( $data['schema_type'] ) ? sanitize_text_field( $data['schema_type'] ) : '';
		$schema_config   = isset( $data['schema_config'] ) ? maybe_serialize( $data['schema_config'] ) : '';
		$custom_titles   = isset( $data['custom_titles'] ) ? maybe_serialize( $data['custom_titles'] ) : '';

		$table = WPSEO_PRO_TABLE_POSTS;

		$wpdb->replace(
			$table,
			array(
				'post_id'      => $post_id,
				'seo_title'    => $seo_title,
				'metadescription' => $metadescription,
				'focus_keyword'   => $focus_keyword,
				'metatags'      => $metatags,
				'schema_type'   => $schema_type,
				'schema_config' => $schema_config,
				'custom_titles' => $custom_titles,
			),
			array(
				'%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
			)
		);

		// 清除相关缓存。
		wp_cache_delete( 'wpseo_pro_seo_' . $post_id, 'wpseo_pro' );
		wp_cache_delete( wpseo_pro_audit_cache_key( $post_id ), 'wpseo_pro' );

		return true;
	}
endif;

if ( ! function_exists( 'wpseo_pro_delete_seo' ) ) :
	/**
	 * 删除文章的 SEO 数据。
	 */
	function wpseo_pro_delete_seo( $post_id ) {
		global $wpdb;

		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return false;
		}

		$table = WPSEO_PRO_TABLE_POSTS;
		$wpdb->delete( $table, array( 'post_id' => $post_id ), array( '%d' ) );

		wp_cache_delete( 'wpseo_pro_seo_' . $post_id, 'wpseo_pro' );
		wp_cache_delete( wpseo_pro_audit_cache_key( $post_id ), 'wpseo_pro' );

		return true;
	}
endif;
