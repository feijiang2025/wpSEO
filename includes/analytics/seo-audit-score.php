<?php
/**
 * SEO 审计：字长、频率、总分计算。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpseo_pro_byte_length' ) ) :
	/**
	 * UTF-8 字符长度。
	 *
	 * @param string $text 文本。
	 * @return int
	 */
	function wpseo_pro_byte_length( $text ) {
		return mb_strlen( $text, 'UTF-8' );
	}
endif;

if ( ! function_exists( 'wpseo_pro_get_common_words' ) ) :
	/**
	 * 获取高频词。
	 *
	 * @param string $content 文章内容。
	 * @return array
	 */
	function wpseo_pro_get_common_words( $content ) {
		if ( empty( $content ) ) {
			return array();
		}

		$clean  = wp_strip_all_tags( $content );
		$clean  = mb_strtolower( $clean, 'UTF-8' );
		$words  = array_map( 'trim', explode( ' ', $clean ) );
		$words  = array_filter( $words );
		$counts = array();

		$stopwords = apply_filters( 'wpseo_pro_stopwords', array( 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from' ) );
		foreach ( $words as $w ) {
			$w = preg_replace( '/[^a-z0-9]/', '', $w );
			if ( $w === '' || in_array( $w, $stopwords, true ) ) {
				continue;
			}
			if ( isset( $counts[ $w ] ) ) {
				$counts[ $w ]++;
			} else {
				$counts[ $w ] = 1;
			}
		}

		arsort( $counts );

		return array_keys( $counts );
	}
endif;

if ( ! function_exists( 'wpseo_pro_calculate_score' ) ) :
	/**
	 * 计算总评分 0-100。
	 *
	 * @param string $title         文章标题。
	 * @param bool   $has_image     是否有缩略图。
	 * @param int    $internal_links 内部链接数。
	 * @param array  $headings      标题结构数组。
	 * @return int
	 */
	function wpseo_pro_calculate_score( $title, $has_image, $internal_links, $headings ) {
		$title_len = wpseo_pro_byte_length( $title );
		$score = 50;

		if ( $title_len >= 30 && $title_len <= 60 ) {
			$score += 15;
		} elseif ( $title_len >= 20 && $title_len < 30 ) {
			$score += 5;
		} else {
			$score += 5;
		}

		if ( $has_image ) {
			$score += 10;
		}

		if ( $internal_links >= 5 ) {
			$score += 10;
		} elseif ( $internal_links >= 2 ) {
			$score += 5;
		}

		if ( isset( $headings['h1'] ) && $headings['h1'] >= 1 ) {
			$score += 10;
		} elseif ( ! isset( $headings['h1'] ) || $headings['h1'] === 0 ) {
			$score -= 5;
		}

		if ( isset( $headings['h2'] ) && $headings['h2'] >= 1 ) {
			$score += 10;
		}

		return max( 0, min( 100, (int) $score ) );
	}
endif;
