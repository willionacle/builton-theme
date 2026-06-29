<?php
/**
 * Built-On theme functions and definitions.
 *
 * @package Built-On
 */

require_once __DIR__ . '/vendor/autoload.php';

Timber\Timber::init();
Timber::$dirname = [ 'views' ];

if ( file_exists( __DIR__ . '/inc/acf/bootstrap.php' ) ) {
	require_once __DIR__ . '/inc/acf/bootstrap.php';
}

if ( file_exists( __DIR__ . '/inc/content-sync/bootstrap.php' ) ) {
	require_once __DIR__ . '/inc/content-sync/bootstrap.php';
}

require_once __DIR__ . '/inc/acf/url-helpers.php';

/**
 * Theme setup.
 */
function builton_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ] );
	add_theme_support(
		'custom-logo',
		[
			'height'      => 120,
			'width'       => 400,
			'flex-height' => true,
			'flex-width'  => true,
		]
	);
	register_nav_menus( [ 'primary' => __( 'Primary Menu', 'builton' ) ] );
}
add_action( 'after_setup_theme', 'builton_setup' );

/**
 * Preconnect to Google Fonts for faster font loading.
 *
 * @param array<int, string|array<string, string>> $urls          URLs to print for resource hints.
 * @param string                                   $relation_type The relation type the URLs are printed for.
 * @return array<int, string|array<string, string>>
 */
function builton_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = [
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		];
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'builton_resource_hints', 10, 2 );

/**
 * Enqueue Inter + Fraunces (variable axes) from Google Fonts.
 */
function builton_enqueue_fonts() {
	wp_enqueue_style(
		'builton-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap',
		[],
		null
	);
}
add_action( 'wp_enqueue_scripts', 'builton_enqueue_fonts', 5 );

/**
 * Allow WebP uploads (helps when the host or security rules omit image/webp; requires WP 5.8+ and PHP GD/Imagick with WebP).
 *
 * @param array<string, string> $mimes MIME types keyed by extension.
 * @return array<string, string>
 */
function builton_allow_webp_upload_mimes( $mimes ) {
	$mimes['webp'] = 'image/webp';
	return $mimes;
}
add_filter( 'upload_mimes', 'builton_allow_webp_upload_mimes' );

/**
 * Disable WordPress admin bar (removes body margin so hero 100vh matches visible viewport).
 */
add_filter( 'show_admin_bar', '__return_false' );

/**
 * Enqueue scripts and styles (Vite: dev server or production build).
 */
function builton_enqueue_assets() {
	$theme_uri = get_stylesheet_directory_uri();
	$theme_dir = get_stylesheet_directory();
	$vite_dev  = 'http://localhost:5173';
	$entry     = 'src/main.js';

	$is_vite_dev = false;
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$response = @file_get_contents( $vite_dev . '/', false, stream_context_create( [ 'http' => [ 'timeout' => 0.5 ] ] ) );
		$is_vite_dev = $response !== false;
	}

	if ( $is_vite_dev ) {
		wp_enqueue_script(
			'builton-vite-client',
			$vite_dev . '/@vite/client',
			[],
			null,
			true
		);
		wp_script_add_data( 'builton-vite-client', 'type', 'module' );
		wp_enqueue_script(
			'builton-main',
			$vite_dev . '/' . $entry,
			[ 'builton-vite-client' ],
			null,
			true
		);
		wp_script_add_data( 'builton-main', 'type', 'module' );
	} else {
		$manifest_path = $theme_dir . '/dist/.vite/manifest.json';
		if ( ! file_exists( $manifest_path ) ) {
			return;
		}
		$manifest = json_decode( file_get_contents( $manifest_path ), true );
		if ( ! isset( $manifest[ $entry ] ) ) {
			return;
		}
		$asset = $manifest[ $entry ];
		$base  = $theme_uri . '/dist/';
		if ( ! empty( $asset['css'] ) ) {
			foreach ( $asset['css'] as $i => $css_file ) {
				wp_enqueue_style(
					'builton-main-' . $i,
					$base . $css_file,
					[ 'builton-fonts' ],
					null
				);
			}
		}
		wp_enqueue_script(
			'builton-main',
			$base . $asset['file'],
			[],
			null,
			true
		);
		wp_script_add_data( 'builton-main', 'type', 'module' );
	}
}
add_action( 'wp_enqueue_scripts', 'builton_enqueue_assets' );

add_filter( 'timber/twig', function( \Twig\Environment $twig ) {
    $twig->addFilter( new \Twig\TwigFilter( 'shortcode', 'do_shortcode' ) );
    return $twig;
} );

add_action( 'init', 'register_team_members_cpt' );
function register_team_members_cpt() {
    register_post_type( 'team_member', [
        'labels'      => [ 'name' => 'Team Members', 'singular_name' => 'Team Member' ],
        'public'      => true,
        'has_archive' => false,
        'supports'    => [ 'title' ],
        'menu_icon'   => 'dashicons-groups',
    ] );
}

/**
 * Add the footer's Portfolio links, and the header sub-nav's project links
 * (rendered only on the Projects page), to every page's Timber context,
 * since partials/footer.twig and partials/header.twig are both included
 * unconditionally/unscoped from base.twig.
 *
 * @param array<string, mixed> $context Timber context.
 * @return array<string, mixed>
 */
function builton_add_footer_context( $context ) {
	$context['footer_projects']   = builton_footer_portfolio_context( 5, 'footer_label' );
	$context['project_nav_items'] = builton_project_subnav_context( 5 );
	return $context;
}
add_filter( 'timber/context', 'builton_add_footer_context' );