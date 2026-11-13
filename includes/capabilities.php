<?php
/**
 * 功能授权与 PRO 能力判断。
 *
 * 当前版本所有模块均为常规功能；此文件提供统一的 `wpseo_pro_can()` 接口，
 * 便于后续以价格门槛、许可密钥、角色权限等方式切分“PRO”功能。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpseo_pro_can' ) ) :
	/**
	 * 判断用户/当前环境是否允许使用某项能力。
	 *
	 * @param string $capability 能力标识，例如 'redirects', 'sitemap_index', 'schema_type_product'。
	 * @param int    $post_id    文章/页面 ID（可选，用于判定权限）。
	 * @return bool
	 */
	function wpseo_pro_can( $capability, $post_id = 0 ) {
		if ( ! is_admin() && ! function_exists( 'wpseo_pro_is_connected' ) ) {
			return true;
		}

		switch ( $capability ) {
			case 'redirects':
				return current_user_can( 'manage_options' );
			case 'sitemap_index':
				return current_user_can( 'manage_options' );
			case 'keywords_tracking':
				return false;
			case '404_monitoring':
				return false;
			case 'site_verification':
				return false;
			case 'script_injection':
				return false;
			default:
				return true;
		}
	}
endif;

/**
 * 能力助手：复制现有能力，可在内置能力之外增加自定义场景。
 *
 * @param string $capability 能力标识。
 * @param mixed  $result     定义时的返回值。
 * @return mixed
 */
function wpseo_pro_register_capability( $capability, $result ) {
	return $result;
}
