<?php
/**
 * Template Name: What we do
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
$wwd_field = static function ( $field_name ) use ( $post_id ) {
	if ( ! function_exists( 'get_field' ) || $post_id <= 0 ) {
		return [];
	}
	$value = get_field( $field_name, $post_id );
	// ACF often returns false (not null) when a group has never been saved.
	if ( null === $value || false === $value ) {
		return [];
	}
	return $value;
};

/**
 * Load a section group, with fallback for legacy seamless clone prefixed meta keys.
 *
 * @param string $field_name Section field name (section_1, section_2, …).
 * @return array<string, mixed>
 */
$wwd_get_section_field = static function ( $field_name ) use ( $post_id, $wwd_field ) {
	$raw = $wwd_field( $field_name );
	if ( ! is_array( $raw ) ) {
		$raw = [];
	}

	$sub_names = [
		'image_side',
		'panel_style',
		'parallax_image',
		'parallax_alt',
		'main_title',
		'main_subtitle',
		'feature_subhead',
		'feature_body',
		'feature_image',
		'feature_image_alt',
		'bottom_left_subhead',
		'bottom_left_body',
		'bottom_right_subhead',
		'bottom_right_body',
	];

	foreach ( $sub_names as $sub_name ) {
		if ( isset( $raw[ $sub_name ] ) && null !== $raw[ $sub_name ] && false !== $raw[ $sub_name ] ) {
			continue;
		}
		if ( ! function_exists( 'get_field' ) || $post_id <= 0 ) {
			continue;
		}
		$prefixed = get_field( $field_name . '_' . $sub_name, $post_id );
		if ( null !== $prefixed && false !== $prefixed ) {
			$raw[ $sub_name ] = $prefixed;
		}
	}

	return $raw;
};

/**
 * Map headline reveal repeater rows to word strings.
 *
 * @param mixed $rows Repeater rows.
 * @return array<int, string>
 */
$wwd_headline_words = static function ( $rows ) {
	$words = [];
	foreach ( (array) $rows as $row ) {
		$t = isset( $row['text'] ) ? trim( (string) $row['text'] ) : '';
		if ( '' !== $t ) {
			$words[] = $t;
		}
	}
	return $words;
};

$hr_hero = (array) $wwd_field( 'headline_reveal_hero' );
$words   = $wwd_headline_words( $hr_hero['words'] ?? [] );
if ( count( $words ) === 0 && $post ) {
	$title = trim( (string) $post->title );
	if ( '' !== $title ) {
		$split = preg_split( '/\s+/u', $title, -1, PREG_SPLIT_NO_EMPTY );
		$words = is_array( $split ) ? $split : [];
	}
}

$tag = (string) ( $hr_hero['tag'] ?? 'h1' );
if ( ! in_array( $tag, [ 'h1', 'h2', 'h3' ], true ) ) {
	$tag = 'h1';
}

$context['headline_reveal_hero'] = [
	'words'       => $words,
	'aria_label'  => (string) ( $hr_hero['aria_label'] ?? '' ),
	'tag'         => $tag,
	'modifier'    => 'what-we-do-hero',
	'fit_base_vw' => 14,
	'heading_id'  => 'wwd-hero-heading',
];

$wwd_section_defaults = static function ( $variant = 'property_development' ) {
	if ( 'construction_design' === $variant ) {
		return [
			'image_side'           => 'left',
			'panel_style'          => 'transparent',
			'parallax_alt'         => '',
			'main_title'           => __( 'CONSTRUCTION & DESIGN', 'builton' ),
			'main_subtitle'        => __( 'Hands-on Execution.', 'builton' ),
			'feature_subhead'      => __( 'Integrated Expertise from Day One', 'builton' ),
			'feature_body'         => '<p>' . esc_html__( 'From the first sketch to final construction, our integrated team brings deep technical insight and real-world experience. With design, development, and construction working as one, we uncover smarter solutions earlier—unlocking efficiency, reducing risk, speeding up timelines, and maximizing value at every step.', 'builton' ) . '</p>',
			'feature_image_alt'    => '',
			'bottom_left_subhead'  => __( 'Design Grounded in Reality', 'builton' ),
			'bottom_left_body'     => '<p>' . esc_html__( 'We don’t just design what looks good—we design what works by starting with the end in mind. Close collaboration between architects, engineers, and builders ensures every idea is constructible, cost-conscious, and aligned with the project’s goals. The result: better, more confident decision making that keeps the project on track.', 'builton' ) . '</p>',
			'bottom_right_subhead' => __( 'Trust Built on Transparency', 'builton' ),
			'bottom_right_body'    => '<p>' . esc_html__( 'No guesswork. No hidden costs. We lead with clarity—from early budgets to final closeout—so our teams stay aligned, confident, and in control. Our commitment to accountability and radical transparency reduces uncertainty and builds trust that lasts long after the project is complete.', 'builton' ) . '</p>',
		];
	}

	return [
		'image_side'           => 'right',
		'panel_style'          => 'muted',
		'parallax_alt'         => '',
		'main_title'           => __( 'PROPERTY DEVELOPMENT', 'builton' ),
		'main_subtitle'        => __( 'Building value, together.', 'builton' ),
		'feature_subhead'      => __( 'Strategic & Aligned Growth', 'builton' ),
		'feature_body'         => '<p>' . esc_html__( 'We focus on disciplined growth through aligned capital, clear execution, and partnerships built on transparency—from early vision through delivery.', 'builton' ) . '</p>',
		'feature_image_alt'    => '',
		'bottom_left_subhead'  => __( 'Finding Smart Opportunities', 'builton' ),
		'bottom_left_body'     => '<p>' . esc_html__( 'We target urban infill and emerging neighborhoods where thoughtful density creates lasting value for residents and stakeholders.', 'builton' ) . '</p>',
		'bottom_right_subhead' => __( 'Partnerships That Last', 'builton' ),
		'bottom_right_body'    => '<p>' . esc_html__( 'Our work is collaborative by design: we invest alongside partners who share long-term goals and a commitment to quality places.', 'builton' ) . '</p>',
	];
};

$wwd_build_section_context = static function ( $raw, $defaults ) {
	$raw = is_array( $raw ) ? $raw : [];
	foreach ( $raw as $k => $v ) {
		if ( null === $v || false === $v ) {
			unset( $raw[ $k ] );
		}
	}
	$raw = array_replace( $defaults, $raw );

	$parallax_img = builton_acf_image_for_twig( $raw['parallax_image'] ?? null );
	$parallax_alt = trim( (string) ( $raw['parallax_alt'] ?? '' ) );
	if ( $parallax_img && '' !== $parallax_alt ) {
		$parallax_img['alt'] = $parallax_alt;
	}

	$feature_img = builton_acf_image_for_twig( $raw['feature_image'] ?? null );
	$feature_alt = trim( (string) ( $raw['feature_image_alt'] ?? '' ) );
	if ( $feature_img && '' !== $feature_alt ) {
		$feature_img['alt'] = $feature_alt;
	}

	return [
		'image_side'          => (string) ( $raw['image_side'] ?? 'right' ),
		'panel_style'         => (string) ( $raw['panel_style'] ?? 'muted' ),
		'parallax_image'      => $parallax_img,
		'main_title'          => (string) ( $raw['main_title'] ?? '' ),
		'main_subtitle'       => (string) ( $raw['main_subtitle'] ?? '' ),
		'feature_subhead'     => (string) ( $raw['feature_subhead'] ?? '' ),
		'feature_body'        => (string) ( $raw['feature_body'] ?? '' ),
		'feature_image'       => $feature_img,
		'bottom_left_subhead' => (string) ( $raw['bottom_left_subhead'] ?? '' ),
		'bottom_left_body'    => (string) ( $raw['bottom_left_body'] ?? '' ),
		'bottom_right_subhead'=> (string) ( $raw['bottom_right_subhead'] ?? '' ),
		'bottom_right_body'   => (string) ( $raw['bottom_right_body'] ?? '' ),
	];
};

$context['section_1'] = $wwd_build_section_context(
	$wwd_get_section_field( 'section_1' ),
	$wwd_section_defaults( 'property_development' )
);
$context['section_2'] = $wwd_build_section_context(
	$wwd_get_section_field( 'section_2' ),
	$wwd_section_defaults( 'construction_design' )
);
$context['section_3'] = $wwd_build_section_context(
	$wwd_get_section_field( 'section_3' ),
	$wwd_section_defaults( 'property_development' )
);

$page_intro = (array) $wwd_field( 'page_intro' );
$context['page_intro'] = [
	'eyebrow' => (string) ( $page_intro['eyebrow'] ?? '' ),
	'intro'   => (string) ( $page_intro['intro'] ?? '' ),
];

$text_block_1 = (array) $wwd_field( 'text_block_1' );
$text_block_2 = (array) $wwd_field( 'text_block_2' );
$context['text_block_1'] = [
	'content'        => (string) ( $text_block_1['content'] ?? '' ),
	'regular_weight' => ! empty( $text_block_1['regular_weight'] ),
];
$context['text_block_2'] = [
	'content'        => (string) ( $text_block_2['content'] ?? '' ),
	'regular_weight' => ! empty( $text_block_2['regular_weight'] ),
];

$context['explore_dual'] = builton_explore_dual_context();

Timber::render( 'page-what-we-do.twig', $context );
