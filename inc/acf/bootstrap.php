<?php
/**
 * ACF bootstrap and registration hooks.
 *
 * @package Built-On
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/groups/front-page.php';
require_once __DIR__ . '/groups/what-we-do.php';
require_once __DIR__ . '/groups/project.php';

/**
 * Register ACF JSON save path.
 *
 * @param string $path Default path.
 * @return string
 */
function builton_acf_json_save_point( $path ) {
	return get_stylesheet_directory() . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'builton_acf_json_save_point' );

/**
 * Register ACF JSON load path.
 *
 * @param array<int, string> $paths Existing load paths.
 * @return array<int, string>
 */
function builton_acf_json_load_point( $paths ) {
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return array_unique( $paths );
}
add_filter( 'acf/settings/load_json', 'builton_acf_json_load_point' );

add_action( 'acf/init', 'builton_register_front_page_fields' );
add_action( 'acf/init', 'builton_register_what_we_do_fields' );
add_action( 'acf/init', 'builton_register_project_fields' );
