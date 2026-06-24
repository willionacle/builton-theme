<?php
/**
 * Builton theme content JSON sync (front page + What we do) — admin tools.
 *
 * @package Built-On
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/import.php';
require_once __DIR__ . '/export.php';

/**
 * Admin menu slug.
 */
if ( ! defined( 'BUILTON_CONTENT_SYNC_MENU_SLUG' ) ) {
	define( 'BUILTON_CONTENT_SYNC_MENU_SLUG', 'builton-content' );
}

add_action( 'admin_menu', 'builton_content_sync_register_menu' );
add_action( 'admin_post_builton_content_import', 'builton_content_sync_handle_import' );
add_action( 'admin_post_builton_content_export', 'builton_content_sync_handle_export' );
add_action( 'admin_notices', 'builton_content_sync_admin_notices' );

/**
 * Register Tools submenu.
 *
 * @return void
 */
function builton_content_sync_register_menu() {
	add_management_page(
		__( 'Builton Content', 'builton' ),
		__( 'Builton Content', 'builton' ),
		'manage_options',
		BUILTON_CONTENT_SYNC_MENU_SLUG,
		'builton_content_sync_render_page'
	);
}

/**
 * Show redirect notices on the tools page.
 *
 * @return void
 */
function builton_content_sync_admin_notices() {
	if ( ! isset( $_GET['page'] ) || BUILTON_CONTENT_SYNC_MENU_SLUG !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( empty( $_GET['builton_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$code = sanitize_key( wp_unslash( $_GET['builton_notice'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( 'import_ok' === $code ) {
		$updated = isset( $_GET['updated'] ) ? (int) $_GET['updated'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %d: number of top-level fields processed */
					_n(
						'Import finished. %d top-level field processed.',
						'Import finished. %d top-level fields processed.',
						$updated,
						'builton'
					),
					$updated
				)
			)
		);
		return;
	}

	if ( 'import_err' === $code && ! empty( $_GET['builton_err'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$msg = sanitize_text_field( wp_unslash( rawurldecode( (string) $_GET['builton_err'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
			esc_html( $msg )
		);
	}
}

/**
 * Redirect back to tools page with query args.
 *
 * @param string               $notice Notice code.
 * @param array<string, mixed> $extra  Extra query args.
 * @return void
 */
function builton_content_sync_redirect( $notice, array $extra = [] ) {
	$url = add_query_arg(
		array_merge(
			[
				'page'          => BUILTON_CONTENT_SYNC_MENU_SLUG,
				'builton_notice' => $notice,
			],
			$extra
		),
		admin_url( 'tools.php' )
	);
	wp_safe_redirect( $url );
	exit;
}

/**
 * Handle import POST.
 *
 * @return void
 */
function builton_content_sync_handle_import() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to import content.', 'builton' ) );
	}

	check_admin_referer( 'builton_content_import' );

	$skip_media = ! empty( $_POST['builton_skip_media'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$source     = isset( $_POST['builton_import_source'] ) ? sanitize_text_field( wp_unslash( $_POST['builton_import_source'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	$json = '';

	if ( 'bundled' === $source ) {
		$bundle = isset( $_POST['builton_content_bundle'] ) ? sanitize_key( wp_unslash( (string) $_POST['builton_content_bundle'] ) ) : 'front_page'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( 'what_we_do_page' === $bundle ) {
			$path = builton_content_sync_bundled_what_we_do_json_path();
			$err  = __( 'Bundled JSON file is missing or not readable. Deploy content-json/what-we-do.json with the theme.', 'builton' );
		} else {
			$bundle = 'front_page';
			$path   = builton_content_sync_bundled_json_path();
			$err    = __( 'Bundled JSON file is missing or not readable. Deploy content-json/front-page.json with the theme.', 'builton' );
		}
		if ( ! is_readable( $path ) ) {
			builton_content_sync_redirect(
				'import_err',
				[
					'builton_err' => rawurlencode( $err ),
				]
			);
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json = (string) file_get_contents( $path );
		$pre  = builton_content_sync_validate_payload( $json );
		if ( ! $pre['ok'] ) {
			builton_content_sync_redirect(
				'import_err',
				[
					'builton_err' => rawurlencode( (string) ( $pre['error'] ?? __( 'Invalid JSON.', 'builton' ) ) ),
				]
			);
		}
		$data      = $pre['data'];
		$target    = isset( $data['target'] ) && is_array( $data['target'] ) ? $data['target'] : [];
		$json_type = isset( $target['type'] ) ? (string) $target['type'] : '';
		if ( $json_type !== $bundle ) {
			builton_content_sync_redirect(
				'import_err',
				[
					'builton_err' => rawurlencode( __( 'The JSON file target does not match the selected bundle (front page vs What we do).', 'builton' ) ),
				]
			);
		}
	} elseif ( 'upload' === $source ) {
		if ( empty( $_FILES['builton_json_file']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			builton_content_sync_redirect(
				'import_err',
				[
					'builton_err' => rawurlencode( __( 'No file uploaded.', 'builton' ) ),
				]
			);
		}
		$file = $_FILES['builton_json_file']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $file['error'] ) ) {
			builton_content_sync_redirect(
				'import_err',
				[
					'builton_err' => rawurlencode( __( 'Upload failed.', 'builton' ) ),
				]
			);
		}
		$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		if ( 'json' !== $ext ) {
			builton_content_sync_redirect(
				'import_err',
				[
					'builton_err' => rawurlencode( __( 'Please upload a .json file.', 'builton' ) ),
				]
			);
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json = (string) file_get_contents( $file['tmp_name'] );
	} else {
		builton_content_sync_redirect(
			'import_err',
			[
				'builton_err' => rawurlencode( __( 'Invalid import source.', 'builton' ) ),
			]
		);
	}

	$result = builton_content_sync_import_json_string( $json, $skip_media );

	if ( ! $result['ok'] ) {
		builton_content_sync_redirect(
			'import_err',
			[
				'builton_err' => rawurlencode( (string) ( $result['error'] ?? __( 'Import failed.', 'builton' ) ) ),
			]
		);
	}

	builton_content_sync_redirect(
		'import_ok',
		[
			'updated' => isset( $result['updated'] ) ? (int) $result['updated'] : 0,
		]
	);
}

/**
 * Handle export download.
 *
 * @return void
 */
function builton_content_sync_handle_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to export content.', 'builton' ) );
	}

	check_admin_referer( 'builton_content_export' );

	$export_target = isset( $_GET['builton_export_target'] ) ? sanitize_key( wp_unslash( (string) $_GET['builton_export_target'] ) ) : 'front_page'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! in_array( $export_target, [ 'front_page', 'what_we_do_page' ], true ) ) {
		$export_target = 'front_page';
	}

	$result = builton_content_sync_build_export_payload( $export_target );
	if ( ! $result['ok'] ) {
		builton_content_sync_redirect(
			'import_err',
			[
				'builton_err' => rawurlencode( (string) ( $result['error'] ?? __( 'Export failed.', 'builton' ) ) ),
			]
		);
	}

	$filename = 'what_we_do_page' === $export_target
		? 'what-we-do-export-' . gmdate( 'Ymd' ) . '.json'
		: 'front-page-export-' . gmdate( 'Ymd' ) . '.json';
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo builton_content_sync_json_encode( $result['payload'] );
	exit;
}

/**
 * Render admin page.
 *
 * @return void
 */
function builton_content_sync_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'builton' ) );
	}

	$bundled_path       = builton_content_sync_bundled_json_path();
	$bundled_ok         = is_readable( $bundled_path );
	$bundled_path_wwd   = builton_content_sync_bundled_what_we_do_json_path();
	$bundled_ok_wwd     = is_readable( $bundled_path_wwd );
	$front_id           = builton_content_sync_get_front_page_id();
	$wwd_id             = builton_content_sync_get_what_we_do_page_id();
	$import_blocked_fp  = ! $bundled_ok || $front_id <= 0;
	$import_blocked_wwd = ! $bundled_ok_wwd || $wwd_id <= 0;
	$candidates_fp      = builton_content_sync_bundled_json_candidate_paths();
	$candidates_wwd     = builton_content_sync_bundled_json_candidate_paths_for_rel( BUILTON_CONTENT_JSON_WHAT_WE_DO_REL );
	$theme_mismatch     = get_template_directory() !== get_stylesheet_directory();

	$export_url_fp = wp_nonce_url(
		admin_url( 'admin-post.php?action=builton_content_export&builton_export_target=front_page' ),
		'builton_content_export'
	);
	$export_url_wwd = wp_nonce_url(
		admin_url( 'admin-post.php?action=builton_content_export&builton_export_target=what_we_do_page' ),
		'builton_content_export'
	);

	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<p>
			<?php esc_html_e( 'Import bundled or uploaded JSON into ACF fields for the static front page or the What we do page. Use this after deploying the theme with updated JSON under content-json/. Editors usually change content under Pages; imports overwrite matching fields. Leave “Skip image and file fields” checked so attachment IDs from each environment stay valid.', 'builton' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Note:', 'builton' ); ?></strong>
			<?php esc_html_e( 'The live site reads ACF from the database. Editing JSON in the repo alone does not change the site until you run an import here (or edit fields in the editor).', 'builton' ); ?>
		</p>

		<?php if ( $front_id <= 0 ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'Set a static front page under Settings → Reading before importing bundled front-page JSON.', 'builton' ); ?></p></div>
		<?php else : ?>
			<p>
				<?php
				printf(
					/* translators: %d: WordPress post ID */
					esc_html__( 'Front page post ID: %d', 'builton' ),
					(int) $front_id
				);
				?>
			</p>
		<?php endif; ?>

		<?php if ( $wwd_id <= 0 ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'Create a Page with slug “what-we-do” and template “What we do” before importing bundled what-we-do JSON.', 'builton' ); ?></p></div>
		<?php else : ?>
			<p>
				<?php
				printf(
					/* translators: %d: WordPress post ID */
					esc_html__( 'What we do page post ID: %d', 'builton' ),
					(int) $wwd_id
				);
				?>
			</p>
		<?php endif; ?>

		<hr />

		<h2><?php esc_html_e( 'Import front page (bundled file)', 'builton' ); ?></h2>
		<?php if ( $theme_mismatch ) : ?>
			<p class="description">
				<?php esc_html_e( 'A child theme is active. Bundled files load from the parent theme first, then the child.', 'builton' ); ?>
			</p>
		<?php endif; ?>
		<p>
			<strong><?php esc_html_e( 'Resolved file:', 'builton' ); ?></strong>
			<code><?php echo esc_html( str_replace( ABSPATH, '', $bundled_path ) ); ?></code>
			<?php if ( $bundled_ok ) : ?>
				— <?php esc_html_e( 'readable', 'builton' ); ?>
			<?php else : ?>
				— <strong><?php esc_html_e( 'not found or not readable', 'builton' ); ?></strong>
			<?php endif; ?>
		</p>
		<?php if ( ! $bundled_ok && count( $candidates_fp ) > 0 ) : ?>
			<details style="margin-top:0.5em;">
				<summary><?php esc_html_e( 'Checked paths (debug)', 'builton' ); ?></summary>
				<ul style="list-style:disc;margin-left:1.5em;">
					<?php foreach ( $candidates_fp as $candidate ) : ?>
						<li>
							<code><?php echo esc_html( str_replace( ABSPATH, '', $candidate ) ); ?></code>
							<?php
							if ( is_readable( $candidate ) ) {
								echo ' — <span style="color:#007017;">' . esc_html__( 'readable', 'builton' ) . '</span>';
							} elseif ( file_exists( $candidate ) ) {
								echo ' — <span style="color:#b32d2e;">' . esc_html__( 'exists but not readable (permissions)', 'builton' ) . '</span>';
							} else {
								echo ' — ' . esc_html__( 'missing', 'builton' );
							}
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			</details>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'builton_content_import' ); ?>
			<input type="hidden" name="action" value="builton_content_import" />
			<input type="hidden" name="builton_import_source" value="bundled" />
			<input type="hidden" name="builton_content_bundle" value="front_page" />
			<p>
				<label>
					<input type="checkbox" name="builton_skip_media" value="1" checked="checked" />
					<?php esc_html_e( 'Skip image and file fields (preserve wp-admin media)', 'builton' ); ?>
				</label>
			</p>
			<?php
			$bundled_fp_attrs = [];
			if ( $import_blocked_fp ) {
				$bundled_fp_attrs['disabled'] = 'disabled';
				$bundled_fp_attrs['aria-disabled'] = 'true';
			}
			submit_button( __( 'Import bundled front page JSON', 'builton' ), 'primary', 'submit', false, $bundled_fp_attrs );
			?>
		</form>

		<hr />

		<h2><?php esc_html_e( 'Import What we do (bundled file)', 'builton' ); ?></h2>
		<p>
			<strong><?php esc_html_e( 'Resolved file:', 'builton' ); ?></strong>
			<code><?php echo esc_html( str_replace( ABSPATH, '', $bundled_path_wwd ) ); ?></code>
			<?php if ( $bundled_ok_wwd ) : ?>
				— <?php esc_html_e( 'readable', 'builton' ); ?>
			<?php else : ?>
				— <strong><?php esc_html_e( 'not found or not readable', 'builton' ); ?></strong>
			<?php endif; ?>
		</p>
		<?php if ( ! $bundled_ok_wwd && count( $candidates_wwd ) > 0 ) : ?>
			<details style="margin-top:0.5em;">
				<summary><?php esc_html_e( 'Checked paths (debug)', 'builton' ); ?></summary>
				<ul style="list-style:disc;margin-left:1.5em;">
					<?php foreach ( $candidates_wwd as $candidate ) : ?>
						<li>
							<code><?php echo esc_html( str_replace( ABSPATH, '', $candidate ) ); ?></code>
							<?php
							if ( is_readable( $candidate ) ) {
								echo ' — <span style="color:#007017;">' . esc_html__( 'readable', 'builton' ) . '</span>';
							} elseif ( file_exists( $candidate ) ) {
								echo ' — <span style="color:#b32d2e;">' . esc_html__( 'exists but not readable (permissions)', 'builton' ) . '</span>';
							} else {
								echo ' — ' . esc_html__( 'missing', 'builton' );
							}
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			</details>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'builton_content_import' ); ?>
			<input type="hidden" name="action" value="builton_content_import" />
			<input type="hidden" name="builton_import_source" value="bundled" />
			<input type="hidden" name="builton_content_bundle" value="what_we_do_page" />
			<p>
				<label>
					<input type="checkbox" name="builton_skip_media" value="1" checked="checked" />
					<?php esc_html_e( 'Skip image and file fields (preserve wp-admin media)', 'builton' ); ?>
				</label>
			</p>
			<?php
			$bundled_wwd_attrs = [];
			if ( $import_blocked_wwd ) {
				$bundled_wwd_attrs['disabled'] = 'disabled';
				$bundled_wwd_attrs['aria-disabled'] = 'true';
			}
			submit_button( __( 'Import bundled What we do JSON', 'builton' ), 'primary', 'submit', false, $bundled_wwd_attrs );
			?>
		</form>

		<hr />

		<h2><?php esc_html_e( 'Import from upload', 'builton' ); ?></h2>
		<p class="description"><?php esc_html_e( 'The file’s target.type must be front_page or what_we_do_page; the matching page must exist.', 'builton' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( 'builton_content_import' ); ?>
			<input type="hidden" name="action" value="builton_content_import" />
			<input type="hidden" name="builton_import_source" value="upload" />
			<p>
				<input type="file" name="builton_json_file" accept=".json,application/json" required />
			</p>
			<p>
				<label>
					<input type="checkbox" name="builton_skip_media" value="1" checked="checked" />
					<?php esc_html_e( 'Skip image and file fields (preserve wp-admin media)', 'builton' ); ?>
				</label>
			</p>
			<?php submit_button( __( 'Import uploaded JSON', 'builton' ), 'secondary', 'submit', false ); ?>
		</form>

		<hr />

		<h2><?php esc_html_e( 'Export', 'builton' ); ?></h2>
		<p><?php esc_html_e( 'Download ACF field values as JSON to merge back into the repo.', 'builton' ); ?></p>
		<p>
			<?php if ( $front_id > 0 ) : ?>
				<a class="button button-primary" href="<?php echo esc_url( $export_url_fp ); ?>"><?php esc_html_e( 'Download front page JSON', 'builton' ); ?></a>
			<?php else : ?>
				<span class="button button-disabled" aria-disabled="true"><?php esc_html_e( 'Download front page JSON', 'builton' ); ?></span>
			<?php endif; ?>
			<?php if ( $wwd_id > 0 ) : ?>
				<a class="button" style="margin-left:0.5em;" href="<?php echo esc_url( $export_url_wwd ); ?>"><?php esc_html_e( 'Download What we do JSON', 'builton' ); ?></a>
			<?php else : ?>
				<span class="button button-disabled" style="margin-left:0.5em;" aria-disabled="true"><?php esc_html_e( 'Download What we do JSON', 'builton' ); ?></span>
			<?php endif; ?>
		</p>
	</div>
	<?php
}
