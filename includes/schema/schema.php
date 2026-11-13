<?php
/**
 * 结构化数据（Schema / JSON-LD）生成器。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpseo_pro_should_output_schema() {
	return '1' === get_option( 'wpseo_pro_use_schema', '1' );
}

function wpseo_pro_output_schema( $post_id = 0 ) {
	if ( ! wpseo_pro_should_output_schema() ) {
		return;
	}

	$post_id = absint( $post_id );
	$seo     = wpseo_pro_get_seo( $post_id );
	$type    = $seo['schema_type'];

	if ( $type ) {
		$json = wpseo_pro_build_schema( $type, $post_id );
		if ( $json ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			echo "\n</script>\n";
		}
	}
}

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

function wpseo_pro_build_article_schema( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return null;
	}

	$author = get_userdata( $post->post_author );
	$date   = get_post_datetime( $post );

	$data = array(
		'@type'        => 'Article',
		'headline'     => get_the_title( $post_id ),
		'datePublished' => $date ? $date->format( 'c' ) : current_time( 'c' ),
		'dateModified'  => $date ? $date->format( 'c' ) : current_time( 'c' ),
		'author'        => array(
			'@type' => 'Person',
			'name'  => $author ? $author->display_name : '',
		),
		'publisher'    => array(
			'@type' => 'Organization',
			'name'  => get_option( 'wpseo_pro_site_name', get_bloginfo( 'name' ) ),
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

function wpseo_pro_build_webpage_schema( $post_id ) {
	$post = get_post( $post_id );

	return array(
		'@type'   => 'WebPage',
		'url'     => $post_id ? get_permalink( $post_id ) : home_url( '/' ),
		'name'    => $post ? get_the_title( $post_id ) : '',
	);
}

function wpseo_pro_build_organization_schema() {
	return array(
		'@type'        => 'Organization',
		'name'         => get_option( 'wpseo_pro_site_name', get_bloginfo( 'name' ) ),
		'description'  => get_bloginfo( 'description' ),
		'url'          => home_url( '/' ),
		'logo'         => home_url( '/favicon.ico' ),
	);
}

function wpseo_pro_build_product_schema( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || 'product' !== $post->post_type ) {
		return null;
	}

	$data = array(
		'@type'       => 'Product',
		'name'        => get_the_title( $post_id ),
		'description' => wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ),
		'image'       => get_the_post_thumbnail_url( $post_id, 'full' ),
	);

	$price = get_post_meta( $post_id, '_price', true );
	if ( $price ) {
		$data['offers'] = array(
			'@type'           => 'Offer',
			'price'           => $price,
			'priceCurrency'   => get_bloginfo( 'currency' ),
			'availability'    => 'InStock',
		);
	}

	return $data;
}

function wpseo_pro_build_localbusiness_schema() {
	return array(
		'@type'   => 'LocalBusiness',
		'name'    => get_option( 'wpseo_pro_site_name', 'My Business' ),
		'address' => array( 'streetAddress' => '' ),
		'url'     => home_url( '/' ),
	);
}

function wpseo_pro_build_faqpage_schema( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return null;
	}

	return array(
		'@type'       => 'FAQPage',
		'mainEntity'  => array(
			array(
				'@type' => 'Question',
				'name'  => __( '如何优化页面 SEO？', 'wpseo-pro' ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => __( '可通过标题、描述、图片与内部链接共同优化。', 'wpseo-pro' ),
				),
			),
		),
	);
}

function wpseo_pro_build_breadcrumblist_schema( $post_id ) {
	$post = get_post( $post_id );

	$items = array(
		array(
			'@type'   => 'ListItem',
			'position' => 1,
			'name'    => __( '首页', 'wpseo-pro' ),
			'item'    => home_url( '/' ),
		),
	);

	if ( $post ) {
		$post_type_obj = get_post_type_object( $post->post_type );
		$label         = $post_type_obj ? $post_type_obj->label : $post->post_type;

		$items[] = array(
			'@type'   => 'ListItem',
			'position' => 2,
			'name'    => $label,
			'item'    => get_permalink( $post_id ),
		);
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

function wpseo_pro_get_option( $key, $default = '' ) {
	return get_option( 'wpseo_pro_' . $key, $default );
}
