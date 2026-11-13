<?php
/**
 * 结构化数据（Schema / JSON-LD）生成器。
 *
 * 按需输出 JSON-LD 脚本，支持多种类型：
 * Article / WebPage / Organization / Product / LocalBusiness / FAQPage / BreadcrumbList
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 判断当前是否输出 Schema。
 */
function wpseo_pro_should_output_schema() {
	return '1' === wpseo_pro_get_option( 'use_schema', '1' );
}

/**
 * 输出单类型 Schema JSON-LD。
 */
function wpseo_pro_output_schema( $post_id = 0 ) {
	if ( ! wpseo_pro_should_output_schema() ) {
		return;
	}

	$post_id = absint( $post_id );
	$type    = wpseo_pro_get_seo( $post_id )['schema_type'];

	if ( $type ) {
		$json = wpseo_pro_build_schema( $type, $post_id );
		if ( $json ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			echo "\n</script>\n";
		}
	}
}

/**
 * 构建指定类型 Schema。
 */
function wpseo_pro_build_schema( $type, $post_id = 0 ) {
	$post_id = absint( $post_id );

	switch ( $type ) {
		case 'Article':
			return wpseo_pro_build_article_schema( $post_id );
		case 'WebPage':
			return wpseo_pro_build_webpage_schema( $post_id );
		case 'Organization':
			return wpseo_pro_build_organization_schema();
		case 'Product':
			return wpseo_pro_build_product_schema( $post_id );
		case 'LocalBusiness':
			return wpseo_pro_build_localbusiness_schema();
		case 'FAQPage':
			return wpseo_pro_build_faqpage_schema( $post_id );
		case 'BreadcrumbList':
			return wpseo_pro_build_breadcrumblist_schema( $post_id );
		default:
			return null;
	}
}

/**
 * Article Schema。
 */
function wpseo_pro_build_article_schema( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return null;
	}

	$author = get_userdata( $post->post_author );
	$date   = get_post_datetime( $post );

	$data = array(
		'@type' => 'Article',
		'headline' => get_the_title( $post_id ),
		'datePublished' => $date ? $date->format( 'c' ) : current_time( 'c' ),
		'dateModified'  => $date ? $date->format( 'c' ) : current_time( 'c' ),
		'author' => array(
			'@type' => 'Person',
			'name'  => $author ? $author->display_name : '',
		),
		'publisher' => array(
			'@type' => 'Organization',
			'name'  => wpseo_pro_get_option( 'site_name', get_bloginfo( 'name' ) ),
			'logo'  => array(
				'@type' => 'ImageObject',
				'url'   => $post_id ? get_the_post_thumbnail_url( $post_id, 'full' ) : '',
			),
		),
	);

	if ( has_post_thumbnail( $post_id ) ) {
		$data['image'] = array(
			'@type' => 'ImageObject',
			'url'   => get_the_post_thumbnail_url( $post_id, 'full' ),
		);
	}

	return $data;
}

/**
 * WebPage Schema。
 */
function wpseo_pro_build_webpage_schema( $post_id ) {
	$post = get_post( $post_id );

	$data = array(
		'@type' => 'WebPage',
		'url'   => $post_id ? get_permalink( $post_id ) : home_url( '/' ),
		'name'  => $post ? get_the_title( $post_id ) : '',
		'mainEntity' => array(
			'@type' => 'WebPageElement',
		),
	);

	return $data;
}

/**
 * Organization Schema。
 */
function wpseo_pro_build_organization_schema() {
	$site_name = wpseo_pro_get_option( 'site_name', get_bloginfo( 'name' ) );
	$description = wpseo_pro_get_option( 'description', get_bloginfo( 'description' ) );

	return array(
		'@type' => 'Organization',
		'name'        => $site_name,
		'description' => $description,
		'url'         => home_url( '/' ),
		'logo'        => home_url( '/favicon.ico' ),
	);
}

/**
 * Product Schema。
 */
function wpseo_pro_build_product_schema( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || 'product' !== $post->post_type ) {
		return null;
	}

	$data = array(
		'@type' => 'Product',
		'name' => get_the_title( $post_id ),
		'description' => wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ),
		'image' => get_the_post_thumbnail_url( $post_id, 'full' ),
	);

	$price = get_post_meta( $post_id, '_price', true );
	if ( $price ) {
		$data['offers'] = array(
			'@type' => 'Offer',
			'price' => $price,
			'priceCurrency' => get_bloginfo( 'currency' ),
			'availability' => 'InStock',
		);
	}

	return $data;
}

/**
 * LocalBusiness Schema。
 */
function wpseo_pro_build_localbusiness_schema() {
	$name = wpseo_pro_get_option( 'site_name', 'My Business' );
	$addr = wpseo_pro_get_option( 'address', '' );

	return array(
		'@type' => 'LocalBusiness',
		'name'        => $name,
		'address'     => $addr ? array( 'streetAddress' => $addr ) : null,
		'url'         => home_url( '/' ),
	);
}

/**
 * FAQPage Schema。
 */
function wpseo_pro_build_faqpage_schema( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return null;
	}

	$faqs = array();
	// 占位：可扩展为从FAQ插件/自定义字段读取。
	$faqs[] = array(
		'question' => __( '如何优化页面 SEO？', 'wpseo-pro' ),
		'answer'   => __( '可通过标题、描述、图片与内部链接共同优化。', 'wpseo-pro' ),
	);

	return array(
		'@type' => 'FAQPage',
		'mainEntity' => $faqs,
	);
}

/**
 * BreadcrumbList Schema。
 */
function wpseo_pro_build_breadcrumblist_schema( $post_id ) {
	$post = get_post( $post_id );

	$items = array(
		array(
			'@type' => 'ListItem',
			'position' => 1,
			'name'   => __( '首页', 'wpseo-pro' ),
			'item'   => home_url( '/' ),
		),
	);

	if ( $post ) {
		$post_type = get_post_type( $post_id );
		$post_type_obj = get_post_type_object( $post_type );
		$label = $post_type_obj ? $post_type_obj->label : $post_type;

		$items[] = array(
			'@type' => 'ListItem',
			'position' => 2,
			'name'   => $label,
			'item'   => get_permalink( $post_id ),
		);
	}

	return array(
		'@type' => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

/**
 * 获取插件全局选项。
 */
function wpseo_pro_get_option( $key, $default = '' ) {
	$options = get_option( 'wpseo_pro_general', array() );
	return isset( $options[ $key ] ) ? $options[ $key ] : $default;
}
