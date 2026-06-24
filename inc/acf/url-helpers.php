<?php
/**
 * Shared ACF value → URL helpers for Timber templates.
 *
 * @package Built-On
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize an ACF image/file/url field into a URL string.
 *
 * @param mixed $value Field value.
 * @return string
 */
function builton_acf_resolve_url( $value ) {
	if ( is_string( $value ) ) {
		return $value;
	}

	if ( is_array( $value ) ) {
		if ( ! empty( $value['url'] ) ) {
			return (string) $value['url'];
		}

		if ( ! empty( $value['ID'] ) && function_exists( 'wp_get_attachment_url' ) ) {
			$url = wp_get_attachment_url( (int) $value['ID'] );
			return $url ? (string) $url : '';
		}
	}

	if ( is_int( $value ) && function_exists( 'wp_get_attachment_url' ) ) {
		$url = wp_get_attachment_url( $value );
		return $url ? (string) $url : '';
	}

	return '';
}

/**
 * Resolve hero marquee to a list of image URLs (gallery, attachment IDs, legacy repeater rows, or URL strings).
 *
 * @param mixed $raw ACF gallery/repeater value.
 * @return array<int, string>
 */
function builton_acf_hero_marquee_urls( $raw ) {
	$urls = [];
	foreach ( (array) $raw as $item ) {
		if ( is_array( $item ) && isset( $item['image'] ) && ! isset( $item['ID'] ) && ! isset( $item['id'] ) ) {
			$urls[] = builton_acf_resolve_url( $item['image'] );
			continue;
		}
		$urls[] = builton_acf_resolve_url( $item );
	}

	return array_values( array_filter( $urls ) );
}

/**
 * Resolve hero marquee to a list of { url, title } pairs (gallery, attachment IDs, legacy repeater rows, or URL strings).
 *
 * Title comes from the attachment's Media Library "Title" field (gallery items already carry it when
 * return_format is 'array'); falls back to get_the_title() for ID-only/legacy values.
 *
 * @param mixed $raw ACF gallery/repeater value.
 * @return array<int, array{url: string, title: string}>
 */
function builton_acf_hero_marquee_items( $raw ) {
	$items = [];
	foreach ( (array) $raw as $item ) {
		if ( is_array( $item ) && isset( $item['image'] ) && ! isset( $item['ID'] ) && ! isset( $item['id'] ) ) {
			$item = $item['image'];
		}

		$url = builton_acf_resolve_url( $item );
		if ( '' === $url ) {
			continue;
		}

		$title = is_array( $item ) ? (string) ( $item['title'] ?? '' ) : '';
		if ( '' === $title ) {
			$attachment_id = 0;
			if ( is_array( $item ) && ! empty( $item['ID'] ) ) {
				$attachment_id = (int) $item['ID'];
			} elseif ( is_int( $item ) ) {
				$attachment_id = $item;
			}
			if ( $attachment_id > 0 && function_exists( 'get_the_title' ) ) {
				$title = (string) get_the_title( $attachment_id );
			}
		}

		$items[] = [
			'url'   => $url,
			'title' => $title,
		];
	}

	return $items;
}

/**
 * Resolve the front page's Explore Dual field into Twig-ready context.
 *
 * Explore Dual is only editable on the front page (ACF location rule), but the section is reused
 * on other page templates with the same shared content — so every template reads from the front page post.
 *
 * @return array<string, mixed>
 */
function builton_explore_dual_context() {
	$front_page_id = (int) get_option( 'page_on_front' );
	$explore_dual  = [];
	if ( $front_page_id > 0 && function_exists( 'get_field' ) ) {
		$value        = get_field( 'explore_dual', $front_page_id );
		$explore_dual = is_array( $value ) ? $value : [];
	}

	$rows_to_words = static function ( $rows ) {
		$words = [];
		foreach ( (array) $rows as $row ) {
			$words[] = isset( $row['text'] ) ? (string) $row['text'] : '';
		}
		return $words;
	};

	$light_card = (array) ( $explore_dual['light_card'] ?? [] );
	$leadership = (array) ( $explore_dual['leadership'] ?? [] );

	return [
		'subheading' => (string) ( $explore_dual['subheading'] ?? '' ),
		'heading'    => (string) ( $explore_dual['heading'] ?? '' ),
		'light_card' => [
			'title'  => (string) ( $light_card['title'] ?? '' ),
			'href'   => (string) ( $light_card['href'] ?? '' ),
			'lines'  => $rows_to_words( $light_card['lines'] ?? [] ),
			'thumbs' => array_map(
				static function ( $thumb ) {
					return [
						'src' => builton_acf_resolve_url( $thumb['src'] ?? '' ),
						'alt' => (string) ( $thumb['alt'] ?? '' ),
					];
				},
				(array) ( $light_card['thumbs'] ?? [] )
			),
		],
		'leadership' => [
			'title'     => (string) ( $leadership['title'] ?? '' ),
			'href'      => (string) ( $leadership['href'] ?? '' ),
			'portraits' => array_map(
				static function ( $portrait ) {
					return [
						'src'    => builton_acf_resolve_url( $portrait['src'] ?? '' ),
						'alt'    => (string) ( $portrait['alt'] ?? '' ),
						'top'    => (string) ( $portrait['top'] ?? '' ),
						'left'   => (string) ( $portrait['left'] ?? '' ),
						'width'  => (string) ( $portrait['width'] ?? '' ),
						'z'      => (int) ( $portrait['z'] ?? 1 ),
						'dim'    => ! empty( $portrait['dim'] ),
						'hidden' => ! empty( $portrait['hidden'] ),
						'hx'     => (int) ( $portrait['hx'] ?? 0 ),
						'hy'     => (int) ( $portrait['hy'] ?? 0 ),
					];
				},
				(array) ( $leadership['portraits'] ?? [] )
			),
		],
	];
}

/**
 * Resolve the first 5 rows of the Project page's "projects" repeater into
 * footer-ready {title, href} pairs, deep-linking to each project's existing
 * `project-N-title` heading anchor (see views/page-project.twig).
 *
 * @return array<int, array<string, string>>
 */
function builton_footer_portfolio_context() {
	$page = get_page_by_path( 'projects' );
	if ( ! $page || ! function_exists( 'get_field' ) ) {
		return [];
	}

	$rows      = (array) get_field( 'projects', $page->ID );
	$href_base = (string) get_permalink( $page->ID );
	$items     = [];

	foreach ( array_slice( $rows, 0, 5 ) as $i => $row ) {
		$items[] = [
			'title' => is_array( $row ) ? (string) ( $row['project_title'] ?? '' ) : '',
			'href'  => $href_base . '#project-' . ( $i + 1 ) . '-title',
		];
	}

	return $items;
}

/**
 * Map an ACF image field value to a small shape for Twig templates.
 *
 * Accepts image arrays, attachment IDs, or URL strings.
 *
 * @param mixed $img ACF image field value.
 * @return array<string, int|string>|null
 */
function builton_acf_image_for_twig( $img ) {
	$url = builton_acf_resolve_url( $img );
	if ( '' === $url ) {
		return null;
	}

	$alt    = '';
	$width  = 0;
	$height = 0;
	$attachment_id = 0;

	if ( is_array( $img ) ) {
		$alt    = (string) ( $img['alt'] ?? '' );
		$width  = isset( $img['width'] ) ? (int) $img['width'] : 0;
		$height = isset( $img['height'] ) ? (int) $img['height'] : 0;
		if ( ! empty( $img['ID'] ) ) {
			$attachment_id = (int) $img['ID'];
		}
	} elseif ( is_int( $img ) || ( is_string( $img ) && ctype_digit( $img ) ) ) {
		$attachment_id = (int) $img;
	}

	if ( '' === $alt && $attachment_id > 0 ) {
		$alt = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	}

	return [
		'url'    => $url,
		'alt'    => $alt,
		'width'  => $width,
		'height' => $height,
	];
}
