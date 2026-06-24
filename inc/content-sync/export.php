<?php
/**
 * Export front-page ACF values as JSON download.
 *
 * @package Built-On
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Top-level ACF field names for the What we do template.
 *
 * @return array<int, string>
 */
function builton_content_sync_what_we_do_field_names() {
	return [
		'headline_reveal_hero',
		'section_1',
		'section_2',
		'section_3',
		'page_intro',
		'text_block_1',
		'text_block_2',
	];
}

/**
 * Build export payload for a supported JSON target.
 *
 * @param string $target_type front_page or what_we_do_page.
 * @return array{ok:bool,payload?:array,error?:string}
 */
function builton_content_sync_build_export_payload( $target_type = 'front_page' ) {
	if ( ! function_exists( 'get_fields' ) ) {
		return [ 'ok' => false, 'error' => __( 'ACF is not available.', 'builton' ) ];
	}

	$target_type = sanitize_key( (string) $target_type );
	if ( 'what_we_do_page' === $target_type ) {
		$post_id = builton_content_sync_get_what_we_do_page_id();
		if ( $post_id <= 0 ) {
			return [
				'ok'    => false,
				'error' => __( 'No page with slug “what-we-do” was found.', 'builton' ),
			];
		}
		$allowed = builton_content_sync_what_we_do_field_names();
	} elseif ( 'front_page' === $target_type ) {
		$post_id = builton_content_sync_get_front_page_id();
		if ( $post_id <= 0 ) {
			return [ 'ok' => false, 'error' => __( 'No static front page is set (Settings → Reading).', 'builton' ) ];
		}
		$allowed = builton_content_sync_front_page_field_names();
	} else {
		return [ 'ok' => false, 'error' => __( 'Invalid export target.', 'builton' ) ];
	}

	$fields = get_fields( $post_id );
	if ( ! is_array( $fields ) ) {
		$fields = [];
	}

	$subset = [];
	foreach ( $allowed as $key ) {
		if ( array_key_exists( $key, $fields ) ) {
			$subset[ $key ] = $fields[ $key ];
		}
	}

	if ( 'front_page' === $target_type && isset( $subset['hero'] ) && is_array( $subset['hero'] ) ) {
		$subset['hero'] = builton_content_sync_export_hero_for_json( $subset['hero'] );
	}

	$payload = [
		'schema_version' => BUILTON_CONTENT_JSON_SCHEMA_VERSION,
		'target'         => [
			'type'    => $target_type,
			'post_id' => $post_id,
		],
		'exported_at'    => gmdate( 'c' ),
		'fields'         => $subset,
	];

	return [ 'ok' => true, 'payload' => $payload ];
}

/**
 * Shape hero.marquee_images as an array of URLs for JSON (gallery stores attachment IDs / image arrays in DB).
 *
 * @param array<string,mixed> $hero Hero field group.
 * @return array<string,mixed>
 */
function builton_content_sync_export_hero_for_json( array $hero ) {
	if ( ! isset( $hero['marquee_images'] ) ) {
		return $hero;
	}

	$raw = $hero['marquee_images'];
	$urls  = [];

	foreach ( (array) $raw as $item ) {
		if ( is_array( $item ) && isset( $item['url'] ) ) {
			$urls[] = (string) $item['url'];
			continue;
		}
		if ( is_numeric( $item ) ) {
			$u = wp_get_attachment_image_url( (int) $item, 'full' );
			if ( $u ) {
				$urls[] = $u;
			}
			continue;
		}
		if ( is_string( $item ) && '' !== $item ) {
			$urls[] = $item;
		}
	}

	$hero['marquee_images'] = array_values( array_filter( $urls ) );

	return $hero;
}

/**
 * Top-level ACF field names used on the front page template.
 *
 * @return array<int, string>
 */
function builton_content_sync_front_page_field_names() {
	return [
		'hero',
		'headline_reveal_1',
		'text_block_1',
		'video_block',
		'reveal_grid',
		'headline_reveal_2',
		'text_block_2',
		'outcome_cards',
		'text_block_3',
		'featured_project_1',
		'headline_reveal_3',
		'tandem_hub',
		'featured_project_2',
		'headline_reveal_timeline',
		'timeline_accordion',
		'explore_dual',
	];
}

/**
 * Encode payload as pretty JSON.
 *
 * @param array<string,mixed> $payload Export data.
 * @return string
 */
function builton_content_sync_json_encode( array $payload ) {
	return wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n";
}
