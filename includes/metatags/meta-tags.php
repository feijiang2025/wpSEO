<?php
/**
 * 元标签生成器。
 *
 * 输出/合并 SEO 标题、描述、OG、Twitter Card 等标签。
 * 优先级：Post 级 SEO 数据 > 插件全局设置 > 默认规则。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 获取文章 SEO 数据：先查自定义表，再降级取全局。
 */
function wpseo_pro_get_seo_post( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return array(
			'seo_title'       => '',
			'metadescription' => '',
			'focus_keyword'   => '',
			'metatags'        => '',
			'schema_type'     => '',
		);
	}

	$seo = wpseo_pro_get_seo( $post_id );

	return array(
		'seo_title'       => $seo['seo_title'],
		'metadescription' => $seo['metadescription'],
		'focus_keyword'   => $seo['focus_keyword'],
		'metatags'        => $seo['metatags'],
		'schema_type'     => $seo['schema_type'],
	);
}

/**
 * 生成完整 <title> 标签。
 */
function wpseo_pro_title( $post_id = 0 ) {
	$post_id = absint( $post_id );
	$seo     = wpseo_pro_get_seo_post( $post_id );

	$site_name = wpseo_pro_get_option( 'site_name', get_bloginfo( 'name' ) );

	if ( $seo['seo_title'] ) {
		return $seo['seo_title'];
	}

	$post = get_post( $post_id );
	if ( $post ) {
		$title = get_the_title( $post );
		if ( $title ) {
			return $title . ' | ' . $site_name;
		}
	}

	return $site_name;
}

/**
 * 打印 <title> 标签。
 */
function wpseo_pro_title_tag( $post_id = 0 ) {
	echo '<title>' . esc_html( wpseo_pro_title( $post_id ) ) . '</title>';
}

/**
 * 输出 meta description。
 */
function wpseo_pro_metadescription( $post_id = 0 ) {
	$post_id = absint( $post_id );
	$seo     = wpseo_pro_get_seo_post( $post_id );

	if ( $seo['metadescription'] ) {
		echo esc_html( $seo['metadescription'] );
		return;
	}

	$post = get_post( $post_id );
	if ( $post ) {
		$excerpt = get_the_excerpt( $post_id );
		if ( $excerpt ) {
			echo esc_html( $excerpt );
			return;
		}

		$content = get_post_field( 'post_content', $post_id );
		if ( $content ) {
			$text = wp_strip_all_tags( $content );
			$text = str_replace( array( "\n", "\r" ), ' ', $text );
			$text = trim( $text );
			echo esc_html( mb_substr( $text, 0, 160 ) );
		}
	}
}

/**
 * 输出 meta description 标签。
 */
function wpseo_pro_meta_description_tag( $post_id = 0 ) {
	echo '<meta name="description" content="' . esc_attr( wpseo_pro_metadescription( $post_id ) ) . '">';
}

/**
 * 输出 canonical 标签。
 */
function wpseo_pro_canonical_tag( $post_id = 0 ) {
	$post_id = absint( $post_id );
	$seo     = wpseo_pro_get_seo_post( $post_id );

	if ( $seo['seo_title'] ) {
		// 自定义 canonical 由插件全局配置。
	}

	if ( $post_id && $post_id !== 0 ) {
		$canonical = get_permalink( $post_id );
		if ( $canonical ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">';
		}
	}
}

/**
 * 输出 OG 标签。
 */
function wpseo_pro_og_tags( $post_id = 0 ) {
	$post_id = absint( $post_id );
	$seo     = wpseo_pro_get_seo_post( $post_id );

	$title = $seo['seo_title'];
	if ( ! $title ) {
		$title = get_the_title( $post_id );
	}

	if ( ! $title ) {
		$title = wpseo_pro_get_option( 'site_name', get_bloginfo( 'name' ) );
	}

	$image = '';
	if ( has_post_thumbnail( $post_id ) ) {
		$image = get_the_post_thumbnail_url( $post_id, 'full' );
	}

	$site_name = wpseo_pro_get_option( 'site_name', get_bloginfo( 'name' ) );
	$url       = home_url( '/' );

	if ( $post_id ) {
		$url = get_permalink( $post_id );
	}

	?>
	<meta property="og:type" content="website">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">

/**
 * 注册输出钩子。
 */
function wpseo_pro_register_output_hooks() {
	// 标题。
	add_action( 'wp_head', 'wpseo_pro_title_tag', 1 );
	// 描述。
	add_action( 'wp_head', 'wpseo_pro_meta_description_tag', 5 );
	// JSON-LD Schema。
	add_action( 'wp_head', 'wpseo_pro_output_schema', 10 );
	// OG 标签。
	add_action( 'wp_head', 'wpseo_pro_og_tags', 20 );
	// Twitter 标签。
	add_action( 'wp_head', 'wpseo_pro_twitter_tags', 20 );
	// 全局 meta 标签。
	add_action( 'wp_head', 'wpseo_pro_global_meta_tags', 100 );
}
add_action( 'init', 'wpseo_pro_register_output_hooks' );

	<meta property="og:description" content="<?php echo esc_attr( wpseo_pro_metadescription( $post_id ) ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
	<meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>">
	<?php if ( $image ) : ?>
		<meta property="og:image" content="<?php echo esc_url( $image ); ?>">
	<?php endif; ?>
	<?php
}

/**
 * 输出 Twitter Card 标签。
 */
function wpseo_pro_twitter_tags() {
	global $post;

	$title = get_the_title( $post );
	if ( ! $title ) {
		$title = wpseo_pro_get_option( 'site_name', get_bloginfo( 'name' ) );
	}

	$image = '';
	if ( has_post_thumbnail( $post ) ) {
		$image = get_the_post_thumbnail_url( $post, 'large' );
	}

	?>
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( wpseo_pro_metadescription( $post->ID ) ); ?>">
	<?php if ( $image ) : ?>
		<meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">
	<?php endif; ?>
	<?php
}

/**
 * 输出自定义元标签（来自 SEO 数据）。
 */
function wpseo_pro_custom_meta_tags( $post_id = 0 ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return;
	}

	$seo = wpseo_pro_get_seo( $post_id );
	$metatags = $seo['metatags'];

	if ( empty( $metatags ) ) {
		return;
	}

	$lines = preg_split( '/\r\n|\r|\n/', $metatags );
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( empty( $line ) || strpos( $line, '=' ) === false ) {
			continue;
		}

		$parts = explode( '=', $line, 2 );
		$name  = trim( $parts[0] );
		$value = trim( $parts[1] );

		if ( in_array( $name, array( 'robots', 'google', 'bing', 'facebook', 'twitter', 'viewport', 'theme-color' ), true ) ) {
			if ( strpos( $name, 'name' ) !== false || strpos( $name, 'property' ) !== false ) {
				echo '<meta ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '">' . "\n";
			} else {
				echo '<meta name="' . esc_attr( $name ) . '" content="' . esc_attr( $value ) . '">' . "\n";
			}
		}
	}
}

/**
 * 输出插件全局 meta 标签（如 theme-color）。
 */
function wpseo_pro_global_meta_tags() {
	$theme_color = wpseo_pro_get_option( 'theme_color', '#6366f1' );

	if ( is_admin() ) {
		return;
	}

	?>
	<meta name="theme-color" content="<?php echo esc_attr( $theme_color ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
}
