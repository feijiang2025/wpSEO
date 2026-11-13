<?php
/**
 * SEO 审计扫描引擎：单篇扫描、关键词、内部链接、密度、标题结构。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpseo_pro_audit_single_post' ) ) :
	/**
	 * 单篇审计扫描。
	 *
	 * @param int $post_id 文章 ID。
	 * @return array
	 */
	function wpseo_pro_audit_single_post( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		$content   = get_post_field( 'post_content', $post_id );
		$title     = get_the_title( $post_id );
		$has_image = has_post_thumbnail( $post_id );

		$keywords       = wpseo_pro_collect_keywords( $post_id );
		$internal_links = wpseo_pro_count_internal_links( $content );
		$density        = wpseo_pro_calculate_keyword_density( $content, $keywords );
		$headings       = wpseo_pro_count_headings( $content );

		return array(
			'post_id'           => $post_id,
			'post_type'         => $post->post_type,
			'title'             => $title,
			'title_len'         => wpseo_pro_byte_length( $title ),
			'has_image'         => (bool) $has_image,
			'internal_links'    => $internal_links,
			'keyword_density'   => $density,
			'headings'          => $headings,
			'h1_count'          => isset( $headings['h1'] ) ? (int) $headings['h1'] : 0,
			'h2_count'          => isset( $headings['h2'] ) ? (int) $headings['h2'] : 0,
			'h3_count'          => isset( $headings['h3'] ) ? (int) $headings['h3'] : 0,
			'keywords'          => array_values( array_unique( $keywords ) ),
			'common_words'      => wpseo_pro_get_common_words( $content ),
		'score'             => wpseo_pro_calculate_score( $title, $has_image, $internal_links, $headings ),
		'details'           => array(
				'title_too_short'    => wpseo_pro_byte_length( $title ) < 40,
				'title_too_long'     => wpseo_pro_byte_length( $title ) > 60,
				'no_image'           => ! $has_image,
				'low_internal_links' => $internal_links < 3,
				'missing_h1'         => $headings['h1'] === 0,
				'missing_h2'         => $headings['h2'] === 0,
			'too_many_h3'        => isset( $headings['h3'] ) && $headings['h3'] > 5,
		),
	);
	}
endif;

if ( ! function_exists( 'wpseo_pro_collect_keywords' ) ) :
	/**
	 * 收集关键词（标签 + 目录）。
	 *
	 * @param int $post_id 文章 ID。
	 * @return array
	 */
	function wpseo_pro_collect_keywords( $post_id ) {
		$keywords = array();

		$tags = get_the_tags( $post_id );
		if ( $tags ) {
			foreach ( $tags as $tag ) {
				$keywords[] = $tag->name;
			}
		}

		$categories = get_the_terms( $post_id, 'category' );
		if ( $categories ) {
			foreach ( $categories as $cat ) {
				$keywords[] = $cat->name;
			}
		}

		return $keywords;
	}
endif;

if ( ! function_exists( 'wpseo_pro_count_internal_links' ) ) :
	/**
	 * 计算内部链接数。
	 *
	 * @param string $content 文章内容。
	 * @return int
	 */
	function wpseo_pro_count_internal_links( $content ) {
		if ( empty( $content ) ) {
			return 0;
		}

		$pattern = '#<a\s[^>]*href=["\']([^"\']+)["\']#i';
		preg_match_all( $pattern, $content, $matches );
		$links = array_filter( $matches[1] );

		$internal = 0;
		foreach ( $links as $link ) {
			if ( 0 === strpos( $link, '#' ) ) {
				continue;
			}
			$internal++;
		}

		return (int) $internal;
	}
endif;

if ( ! function_exists( 'wpseo_pro_calculate_keyword_density' ) ) :
	/**
	 * 计算关键词密度（百分比）。
	 *
	 * @param string  $content   文章内容。
	 * @param array   $keywords  关键词列表。
	 * @return array
	 */
	function wpseo_pro_calculate_keyword_density( $content, $keywords ) {
		if ( empty( $content ) || empty( $keywords ) ) {
			return array();
		}

		$clean  = wp_strip_all_tags( $content );
		$clean  = mb_strtolower( $clean, 'UTF-8' );
		$words  = array_map( 'trim', explode( ' ', $clean ) );
		$total  = count( $words );
		$result = array();

		if ( $total > 0 ) {
			foreach ( $keywords as $kw ) {
				$kw    = mb_strtolower( $kw, 'UTF-8' );
				$count = 0;
				foreach ( $words as $w ) {
					if ( $w === $kw ) {
						$count++;
					}
				}
				if ( $count > 0 ) {
					$result[ $kw ] = round( ( $count / $total ) * 100, 2 );
				}
			}
		}

		return $result;
	}
endif;

if ( ! function_exists( 'wpseo_pro_count_headings' ) ) :
	/**
	 * 计数 h1-h6。
	 *
	 * @param string $content 文章内容。
	 * @return array
	 */
	function wpseo_pro_count_headings( $content ) {
		$counts = array( 'h1' => 0, 'h2' => 0, 'h3' => 0, 'h4' => 0, 'h5' => 0, 'h6' => 0 );
		if ( empty( $content ) ) {
			return $counts;
		}

		preg_match_all( '#<h([1-6])[^>]*>(.*?)</h\1>#is', $content, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			$level = (int) $match[1];
			if ( isset( $counts[ 'h' . $level ] ) ) {
				$counts[ 'h' . $level ]++;
			}
		}

		return $counts;
	}
endif;
