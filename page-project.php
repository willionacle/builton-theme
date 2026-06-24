<?php
/**
 * Template Name: Project
 *
 * @package Built-On
 */

use Timber\Timber;

require_once get_template_directory() . '/inc/acf/url-helpers.php';

$context         = Timber::context();
$context['post'] = Timber::get_post();
$post            = $context['post'];
$post_id         = $post ? (int) ( $post->ID ?? $post->id ) : 0;

/**
 * Get ACF field for this page or empty array.
 *
 * @param string $field_name Field name.
 * @return mixed
 */
$project_page_field = static function ( $field_name ) use ( $post_id ) {
	if ( ! function_exists( 'get_field' ) || $post_id <= 0 ) {
		return [];
	}
	$value = get_field( $field_name, $post_id );
	if ( null === $value || false === $value ) {
		return [];
	}
	return $value;
};

/**
 * Build one project layout context from a repeater row.
 *
 * @param array<string,mixed> $row Project repeater row.
 * @return array<string,mixed>
 */
$build_project = static function ( array $row ) {
	$parallax_img = builton_acf_image_for_twig( $row['parallax_image'] ?? null );
	$parallax_alt = trim( (string) ( $row['parallax_alt'] ?? '' ) );
	if ( $parallax_img && '' !== $parallax_alt ) {
		$parallax_img['alt'] = $parallax_alt;
	}

	$overview_rows = [];
	foreach ( (array) ( $row['overview_rows'] ?? [] ) as $overview_row ) {
		if ( ! is_array( $overview_row ) ) {
			continue;
		}
		$type = (string) ( $overview_row['row_type'] ?? 'simple' );
		$item = [
			'type'     => $type,
			'label'    => (string) ( $overview_row['label'] ?? '' ),
			'value'    => (string) ( $overview_row['value'] ?? '' ),
			'partners' => [],
		];
		if ( 'partners' === $type ) {
			foreach ( (array) ( $overview_row['partners'] ?? [] ) as $partner ) {
				if ( ! is_array( $partner ) ) {
					continue;
				}
				$name = trim( (string) ( $partner['name'] ?? '' ) );
				$role = trim( (string) ( $partner['role'] ?? '' ) );
				if ( '' === $name && '' === $role ) {
					continue;
				}
				$item['partners'][] = [
					'name' => $name,
					'role' => $role,
				];
			}
		}
		if ( '' !== $item['label'] || '' !== $item['value'] || [] !== $item['partners'] ) {
			$overview_rows[] = $item;
		}
	}

	$approach_media = [];
	foreach ( (array) ( $row['approach_media'] ?? [] ) as $media_row ) {
		if ( ! is_array( $media_row ) ) {
			continue;
		}
		$type = (string) ( $media_row['media_type'] ?? 'image' );
		$item = [
			'type'         => $type,
			'image'        => null,
			'video_url'    => '',
			'video_poster' => '',
			'video_label'  => (string) ( $media_row['video_label'] ?? '' ),
		];
		if ( 'video' === $type ) {
			$item['video_url'] = (string) ( $media_row['video_url'] ?? '' );
			$poster = builton_acf_image_for_twig( $media_row['video_poster'] ?? null );
			$item['video_poster'] = $poster ? $poster['url'] : '';
			if ( '' === $item['video_url'] && '' === $item['video_poster'] ) {
				continue;
			}
		} else {
			$item['image'] = builton_acf_image_for_twig( $media_row['image'] ?? null );
			if ( ! $item['image'] ) {
				continue;
			}
		}
		$approach_media[] = $item;
	}

	$gallery_slides = [];
	foreach ( (array) ( $row['gallery_images'] ?? [] ) as $img ) {
		$mapped = builton_acf_image_for_twig( $img );
		if ( $mapped ) {
			$gallery_slides[] = $mapped;
		}
	}

	$faq_items = [];
	foreach ( (array) ( $row['faq_items'] ?? [] ) as $faq_row ) {
		if ( ! is_array( $faq_row ) ) {
			continue;
		}
		$q = trim( (string) ( $faq_row['question'] ?? '' ) );
		$a = (string) ( $faq_row['answer'] ?? '' );
		if ( '' === $q && '' === trim( wp_strip_all_tags( $a ) ) ) {
			continue;
		}
		$faq_items[] = [
			'question' => $q,
			'answer'   => $a,
		];
	}

	return [
		'hero'     => [ 'parallax_image' => $parallax_img ],
		'header'   => [
			'title'    => (string) ( $row['project_title'] ?? '' ),
			'subtitle' => (string) ( $row['subtitle'] ?? '' ),
		],
		'overview' => [
			'rows' => $overview_rows,
			'body' => (string) ( $row['overview_body'] ?? '' ),
		],
		'approach' => [
			'heading' => (string) ( $row['approach_heading'] ?? '' ),
			'body'    => (string) ( $row['approach_body'] ?? '' ),
			'media'   => $approach_media,
		],
		'gallery'  => [ 'slides' => $gallery_slides ],
		'faq'      => [
			'heading' => (string) ( $row['faq_heading'] ?? '' ),
			'items'   => $faq_items,
		],
	];
};

$page_heading_raw = $project_page_field( 'page_heading' );
$page_heading     = is_string( $page_heading_raw ) ? trim( $page_heading_raw ) : '';
if ( '' === $page_heading && $post ) {
	$page_heading = trim( (string) $post->title );
}
$heading_words = '' !== $page_heading
	? preg_split( '/\s+/u', $page_heading, -1, PREG_SPLIT_NO_EMPTY )
	: [];
$context['page_heading'] = [
	'words'      => is_array( $heading_words ) ? $heading_words : [],
	'aria_label' => $page_heading,
];

$projects = [];
foreach ( (array) $project_page_field( 'projects' ) as $project_row ) {
	if ( is_array( $project_row ) ) {
		$projects[] = $build_project( $project_row );
	}
}

$context['projects'] = $projects;

$context['explore_dual'] = builton_explore_dual_context();

Timber::render( 'page-project.twig', $context );
