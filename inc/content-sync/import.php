<?php
/**
 * Import front-page ACF values from bundled or uploaded JSON.
 *
 * @package Built-On
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current content JSON schema version.
 */
if ( ! defined( 'BUILTON_CONTENT_JSON_SCHEMA_VERSION' ) ) {
	define( 'BUILTON_CONTENT_JSON_SCHEMA_VERSION', 1 );
}

/**
 * Path to bundled front-page content JSON (relative to stylesheet directory).
 */
if ( ! defined( 'BUILTON_CONTENT_JSON_FRONT_PAGE_REL' ) ) {
	define( 'BUILTON_CONTENT_JSON_FRONT_PAGE_REL', '/content-json/front-page.json' );
}

/**
 * Path to bundled What we do content JSON (relative to stylesheet directory).
 */
if ( ! defined( 'BUILTON_CONTENT_JSON_WHAT_WE_DO_REL' ) ) {
	define( 'BUILTON_CONTENT_JSON_WHAT_WE_DO_REL', '/content-json/what-we-do.json' );
}

/**
 * Resolve static front page post ID.
 *
 * @return int 0 if not set.
 */
function builton_content_sync_get_front_page_id() {
	$id = (int) get_option( 'page_on_front', 0 );
	return $id > 0 ? $id : 0;
}

/**
 * Absolute paths to check for bundled JSON (parent theme first, then active stylesheet).
 *
 * When a child theme is active, `get_stylesheet_directory()` points at the child folder while
 * `content-json/front-page.json` normally lives in the parent (Builton) theme — checking the
 * template directory first fixes “file missing” and a permanently disabled import button.
 *
 * @return string[]
 */
function builton_content_sync_bundled_json_candidate_paths() {
	return builton_content_sync_bundled_json_candidate_paths_for_rel( BUILTON_CONTENT_JSON_FRONT_PAGE_REL );
}

/**
 * Candidate paths for a bundled JSON file (parent theme first, then child).
 *
 * @param string $rel Path relative to theme root, e.g. '/content-json/foo.json'.
 * @return string[]
 */
function builton_content_sync_bundled_json_candidate_paths_for_rel( $rel ) {
	return array_values(
		array_unique(
			[
				get_template_directory() . $rel,
				get_stylesheet_directory() . $rel,
			]
		)
	);
}

/**
 * Full path to bundled front-page JSON (first readable candidate).
 *
 * @return string Path used for reading; if none exist yet, returns the primary (template) path.
 */
function builton_content_sync_bundled_json_path() {
	foreach ( builton_content_sync_bundled_json_candidate_paths_for_rel( BUILTON_CONTENT_JSON_FRONT_PAGE_REL ) as $path ) {
		if ( is_readable( $path ) ) {
			return $path;
		}
	}
	$candidates = builton_content_sync_bundled_json_candidate_paths_for_rel( BUILTON_CONTENT_JSON_FRONT_PAGE_REL );

	return isset( $candidates[0] ) ? $candidates[0] : get_template_directory() . BUILTON_CONTENT_JSON_FRONT_PAGE_REL;
}

/**
 * Resolve the What we do page post ID (slug what-we-do).
 *
 * @return int 0 if not found.
 */
function builton_content_sync_get_what_we_do_page_id() {
	$page = get_page_by_path( 'what-we-do', OBJECT, 'page' );
	if ( $page instanceof \WP_Post ) {
		return (int) $page->ID;
	}
	return 0;
}

/**
 * First readable path for bundled what-we-do.json.
 *
 * @return string Primary path if none readable.
 */
function builton_content_sync_bundled_what_we_do_json_path() {
	$rel = BUILTON_CONTENT_JSON_WHAT_WE_DO_REL;
	foreach ( builton_content_sync_bundled_json_candidate_paths_for_rel( $rel ) as $path ) {
		if ( is_readable( $path ) ) {
			return $path;
		}
	}
	$candidates = builton_content_sync_bundled_json_candidate_paths_for_rel( $rel );

	return isset( $candidates[0] ) ? $candidates[0] : get_template_directory() . $rel;
}

/**
 * Allowed JSON target.type values.
 *
 * @return array<int, string>
 */
function builton_content_sync_allowed_import_target_types() {
	return [ 'front_page', 'what_we_do_page' ];
}

/**
 * Resolve post ID for a JSON target type.
 *
 * @param string $type target.type from payload.
 * @return int 0 if not resolvable.
 */
function builton_content_sync_resolve_post_id_for_target_type( $type ) {
	if ( 'front_page' === $type ) {
		return builton_content_sync_get_front_page_id();
	}
	if ( 'what_we_do_page' === $type ) {
		return builton_content_sync_get_what_we_do_page_id();
	}
	return 0;
}

/**
 * Decode and validate payload.
 *
 * @param string $json Raw JSON string.
 * @return array{ok:bool,data?:array,error?:string}
 */
function builton_content_sync_validate_payload( $json ) {
	if ( '' === $json ) {
		return [ 'ok' => false, 'error' => __( 'Empty file.', 'builton' ) ];
	}

	$data = json_decode( $json, true );
	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) {
		return [ 'ok' => false, 'error' => __( 'Invalid JSON.', 'builton' ) ];
	}

	$version = isset( $data['schema_version'] ) ? (int) $data['schema_version'] : 0;
	if ( $version !== BUILTON_CONTENT_JSON_SCHEMA_VERSION ) {
		return [
			'ok'    => false,
			'error' => sprintf(
				/* translators: %d: expected schema version */
				__( 'Unsupported schema_version (expected %d).', 'builton' ),
				BUILTON_CONTENT_JSON_SCHEMA_VERSION
			),
		];
	}

	$target = isset( $data['target'] ) && is_array( $data['target'] ) ? $data['target'] : [];
	$type   = isset( $target['type'] ) ? (string) $target['type'] : '';
	if ( ! in_array( $type, builton_content_sync_allowed_import_target_types(), true ) ) {
		return [
			'ok'    => false,
			'error' => __( 'Invalid target.type (expected front_page or what_we_do_page).', 'builton' ),
		];
	}

	if ( ! isset( $data['fields'] ) || ! is_array( $data['fields'] ) ) {
		return [ 'ok' => false, 'error' => __( 'Missing or invalid fields object.', 'builton' ) ];
	}

	return [ 'ok' => true, 'data' => $data ];
}

/**
 * Merge imported value with current, preserving image/file subfields from DB when skip_media is on.
 *
 * @param mixed  $imported      Value from JSON.
 * @param mixed  $current       Current DB value from get_field.
 * @param array  $field_object  ACF field array (acf_get_field_object shape).
 * @return mixed
 */
function builton_content_sync_merge_preserve_media( $imported, $current, $field_object ) {
	if ( ! is_array( $field_object ) ) {
		return $imported;
	}

	$type = isset( $field_object['type'] ) ? $field_object['type'] : '';

	if ( 'image' === $type || 'file' === $type || 'gallery' === $type ) {
		return $current;
	}

	if ( 'group' === $type || 'clone' === $type ) {
		$imported = is_array( $imported ) ? $imported : [];
		$current  = is_array( $current ) ? $current : [];
		$subs     = isset( $field_object['sub_fields'] ) && is_array( $field_object['sub_fields'] ) ? $field_object['sub_fields'] : [];
		$out      = [];
		foreach ( $subs as $sub ) {
			if ( ! is_array( $sub ) || empty( $sub['name'] ) ) {
				continue;
			}
			$n           = $sub['name'];
			$out[ $n ] = builton_content_sync_merge_preserve_media(
				$imported[ $n ] ?? null,
				$current[ $n ] ?? null,
				$sub
			);
		}
		return $out;
	}

	if ( 'repeater' === $type ) {
		$imported = is_array( $imported ) ? $imported : [];
		$current  = is_array( $current ) ? $current : [];
		$subs     = isset( $field_object['sub_fields'] ) && is_array( $field_object['sub_fields'] ) ? $field_object['sub_fields'] : [];
		$out      = [];
		foreach ( $imported as $i => $row ) {
			$row        = is_array( $row ) ? $row : [];
			$cur_row    = isset( $current[ $i ] ) && is_array( $current[ $i ] ) ? $current[ $i ] : [];
			$merged_row = [];
			foreach ( $subs as $sub ) {
				if ( ! is_array( $sub ) || empty( $sub['name'] ) ) {
					continue;
				}
				$n                = $sub['name'];
				$merged_row[ $n ] = builton_content_sync_merge_preserve_media(
					$row[ $n ] ?? null,
					$cur_row[ $n ] ?? null,
					$sub
				);
			}
			$out[] = $merged_row;
		}
		return $out;
	}

	if ( 'flexible_content' === $type ) {
		$imported = is_array( $imported ) ? $imported : [];
		$current  = is_array( $current ) ? $current : [];
		$layouts  = isset( $field_object['layouts'] ) && is_array( $field_object['layouts'] ) ? $field_object['layouts'] : [];
		$out      = [];
		foreach ( $imported as $i => $row ) {
			$row     = is_array( $row ) ? $row : [];
			$cur_row = isset( $current[ $i ] ) && is_array( $current[ $i ] ) ? $current[ $i ] : [];
			$layout  = isset( $row['acf_fc_layout'] ) ? (string) $row['acf_fc_layout'] : '';
			$layout_def = null;
			foreach ( $layouts as $layout_row ) {
				if ( is_array( $layout_row ) && isset( $layout_row['name'] ) && (string) $layout_row['name'] === $layout ) {
					$layout_def = $layout_row;
					break;
				}
			}
			if ( '' === $layout || null === $layout_def ) {
				$out[] = $row;
				continue;
			}
			$subs   = isset( $layout_def['sub_fields'] ) && is_array( $layout_def['sub_fields'] ) ? $layout_def['sub_fields'] : [];
			$merged = [ 'acf_fc_layout' => $layout ];
			foreach ( $subs as $sub ) {
				if ( ! is_array( $sub ) || empty( $sub['name'] ) ) {
					continue;
				}
				$n            = $sub['name'];
				$merged[ $n ] = builton_content_sync_merge_preserve_media(
					$row[ $n ] ?? null,
					$cur_row[ $n ] ?? null,
					$sub
				);
			}
			$out[] = $merged;
		}
		return $out;
	}

	return $imported;
}

/**
 * Apply optional media preservation for a top-level field.
 *
 * @param string $field_name Field name.
 * @param mixed  $value      Imported value.
 * @param int    $post_id    Post ID.
 * @param bool   $skip_media Whether to preserve image/file from DB.
 * @return mixed
 */
function builton_content_sync_prepare_field_value( $field_name, $value, $post_id, $skip_media ) {
	if ( ! $skip_media || ! function_exists( 'acf_get_field_object' ) || ! function_exists( 'get_field' ) ) {
		return $value;
	}

	$object = acf_get_field_object( $field_name, $post_id );
	if ( ! is_array( $object ) ) {
		return $value;
	}

	$current = get_field( $field_name, $post_id );
	return builton_content_sync_merge_preserve_media( $value, $current, $object );
}

/**
 * Run import from decoded payload.
 *
 * @param array<string,mixed> $data       Validated payload (schema_version, target, fields).
 * @param int                 $post_id    Target page ID.
 * @param bool                $skip_media Preserve image/file from DB.
 * @return array{ok:bool,error?:string,updated?:int}
 */
function builton_content_sync_run_import( array $data, $post_id, $skip_media ) {
	if ( ! function_exists( 'update_field' ) ) {
		return [ 'ok' => false, 'error' => __( 'ACF is not available.', 'builton' ) ];
	}

	$target  = isset( $data['target'] ) && is_array( $data['target'] ) ? $data['target'] : [];
	$ttype   = isset( $target['type'] ) ? (string) $target['type'] : '';
	$fields  = builton_content_sync_normalize_import_fields( $data['fields'], $ttype );
	$updated = 0;

	foreach ( $fields as $name => $value ) {
		if ( ! is_string( $name ) || '' === $name ) {
			continue;
		}
		$prepared = builton_content_sync_prepare_field_value( $name, $value, $post_id, $skip_media );
		update_field( $name, $prepared, $post_id );
		++$updated;
	}

	return [ 'ok' => true, 'updated' => $updated ];
}

/**
 * Normalize imported field values so ACF receives correct shapes (e.g. hero marquee gallery = attachment IDs).
 *
 * @param array<string,mixed> $fields       Top-level fields from JSON.
 * @param string              $target_type  Payload target.type.
 * @return array<string,mixed>
 */
function builton_content_sync_normalize_import_fields( array $fields, $target_type = 'front_page' ) {
	if ( 'front_page' === $target_type && isset( $fields['hero'] ) && is_array( $fields['hero'] ) && isset( $fields['hero']['marquee_images'] ) ) {
		$fields['hero']['marquee_images'] = builton_content_sync_normalize_hero_marquee_gallery_value( $fields['hero']['marquee_images'] );
	}

	return $fields;
}

/**
 * Convert JSON marquee data to an array of attachment IDs for the ACF gallery field.
 *
 * Accepts: array of URLs, array of numeric IDs, legacy repeater rows { "image": "url" }.
 *
 * @param mixed $value Raw value from JSON.
 * @return array<int, int>
 */
function builton_content_sync_normalize_hero_marquee_gallery_value( $value ) {
	if ( ! is_array( $value ) ) {
		return [];
	}

	$ids = [];
	foreach ( $value as $item ) {
		if ( is_int( $item ) || ( is_string( $item ) && ctype_digit( $item ) ) ) {
			$ids[] = (int) $item;
			continue;
		}

		if ( is_string( $item ) && '' !== $item ) {
			if ( function_exists( 'attachment_url_to_postid' ) ) {
				$found = attachment_url_to_postid( $item );
				if ( $found ) {
					$ids[] = $found;
				}
			}
			continue;
		}

		if ( is_array( $item ) && isset( $item['image'] ) ) {
			$url = (string) $item['image'];
			if ( '' !== $url && function_exists( 'attachment_url_to_postid' ) ) {
				$found = attachment_url_to_postid( $url );
				if ( $found ) {
					$ids[] = $found;
				}
			}
		}
	}

	return array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
}

/**
 * Import from a JSON string.
 *
 * @param string $json       Raw JSON.
 * @param bool   $skip_media Preserve media fields.
 * @return array{ok:bool,error?:string,updated?:int}
 */
function builton_content_sync_import_json_string( $json, $skip_media ) {
	$check = builton_content_sync_validate_payload( $json );
	if ( ! $check['ok'] ) {
		return [ 'ok' => false, 'error' => $check['error'] ?? __( 'Validation failed.', 'builton' ) ];
	}

	$data    = $check['data'];
	$target  = isset( $data['target'] ) && is_array( $data['target'] ) ? $data['target'] : [];
	$ttype   = isset( $target['type'] ) ? (string) $target['type'] : '';
	$post_id = builton_content_sync_resolve_post_id_for_target_type( $ttype );

	if ( $post_id <= 0 ) {
		if ( 'front_page' === $ttype ) {
			return [ 'ok' => false, 'error' => __( 'No static front page is set (Settings → Reading).', 'builton' ) ];
		}
		if ( 'what_we_do_page' === $ttype ) {
			return [
				'ok'    => false,
				'error' => __( 'No page with slug “what-we-do” was found. Create the page and assign the What we do template.', 'builton' ),
			];
		}
		return [ 'ok' => false, 'error' => __( 'Could not resolve target page for import.', 'builton' ) ];
	}

	return builton_content_sync_run_import( $data, $post_id, $skip_media );
}
