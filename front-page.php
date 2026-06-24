<?php
/**
 * Front page template.
 *
 * @package Built-On
 */

use Timber\Timber;

require_once get_template_directory() . '/inc/acf/url-helpers.php';

$context          = Timber::context();
$context['post']  = Timber::get_post();
$context['hero']  = [];

/**
 * Get ACF field or fallback empty array.
 *
 * @param string $field_name Field key.
 * @return mixed
 */
function builton_front_page_field( $field_name ) {
	if ( ! function_exists( 'get_field' ) ) {
		return [];
	}

	$value = get_field( $field_name );
	return is_null( $value ) ? [] : $value;
}

/**
 * Normalize words repeater.
 *
 * @param mixed $rows Repeater rows.
 * @return array<int, string>
 */
function builton_front_page_words( $rows ) {
	$words = [];
	foreach ( (array) $rows as $row ) {
		$words[] = isset( $row['text'] ) ? (string) $row['text'] : '';
	}
	return $words;
}

$hero = (array) builton_front_page_field( 'hero' );
$hero_lines = [];
foreach ( (array) ( $hero['headline_lines'] ?? [] ) as $line ) {
	$words = [];
	foreach ( (array) ( $line['words'] ?? [] ) as $word ) {
		$words[] = [
			'word'   => (string) ( $word['word'] ?? '' ),
			'accent' => ! empty( $word['accent'] ),
		];
	}
	$hero_lines[] = $words;
}

$context['hero'] = [
	'subheading'          => (string) ( $hero['subheading'] ?? '' ),
	'headline_aria_label' => (string) ( $hero['headline_aria_label'] ?? '' ),
	'headline_lines'      => $hero_lines,
	'marquee_images'      => builton_acf_hero_marquee_items( $hero['marquee_images'] ?? [] ),
];

$headline_reveal_1 = (array) builton_front_page_field( 'headline_reveal_1' );
$headline_reveal_2 = (array) builton_front_page_field( 'headline_reveal_2' );
$headline_reveal_3 = (array) builton_front_page_field( 'headline_reveal_3' );

$context['headline_reveal_1'] = [
	'words'      => builton_front_page_words( $headline_reveal_1['words'] ?? [] ),
	'aria_label' => (string) ( $headline_reveal_1['aria_label'] ?? '' ),
	'tag'        => (string) ( $headline_reveal_1['tag'] ?? 'h2' ),
];
$context['headline_reveal_2'] = [
	'words'      => builton_front_page_words( $headline_reveal_2['words'] ?? [] ),
	'aria_label' => (string) ( $headline_reveal_2['aria_label'] ?? '' ),
	'tag'        => (string) ( $headline_reveal_2['tag'] ?? 'h2' ),
];
$context['headline_reveal_3'] = [
	'words'      => builton_front_page_words( $headline_reveal_3['words'] ?? [] ),
	'aria_label' => (string) ( $headline_reveal_3['aria_label'] ?? '' ),
	'tag'        => (string) ( $headline_reveal_3['tag'] ?? 'h2' ),
];

$text_block_1 = (array) builton_front_page_field( 'text_block_1' );
$text_block_2 = (array) builton_front_page_field( 'text_block_2' );
$context['text_block_1'] = [
	'content'         => (string) ( $text_block_1['content'] ?? '' ),
	'regular_weight'  => ! empty( $text_block_1['regular_weight'] ),
];
$context['text_block_2'] = [
	'content'         => (string) ( $text_block_2['content'] ?? '' ),
	'regular_weight'  => ! empty( $text_block_2['regular_weight'] ),
];

$text_block_3 = (array) builton_front_page_field( 'text_block_3' );
$context['text_block_3'] = [
	'content'         => (string) ( $text_block_3['content'] ?? '' ),
	'regular_weight'  => ! empty( $text_block_3['regular_weight'] ),
	'modifier'        => 'supporting-copy',
];

$video_block = (array) builton_front_page_field( 'video_block' );
$context['video_block'] = [
	'video_src'  => builton_acf_resolve_url( $video_block['video_src'] ?? '' ),
	'poster_src' => builton_acf_resolve_url( $video_block['poster_src'] ?? '' ),
	'aria_label' => (string) ( $video_block['aria_label'] ?? '' ),
];

$reveal_grid = (array) builton_front_page_field( 'reveal_grid' );
$context['reveal_grid'] = [
	'items' => array_map(
		static function ( $item ) {
			$type = (string) ( $item['type'] ?? '' );
			if ( 'image' === $type ) {
				return [
					'type' => 'image',
					'src'  => builton_acf_resolve_url( $item['image_src'] ?? '' ),
					'alt'  => (string) ( $item['alt'] ?? '' ),
				];
			}

			return [
				'type'    => 'text',
				'title'   => (string) ( $item['title'] ?? '' ),
				'content' => (string) ( $item['content'] ?? '' ),
			];
		},
		(array) ( $reveal_grid['items'] ?? [] )
	),
];

$outcome_cards = (array) builton_front_page_field( 'outcome_cards' );
$context['outcome_cards'] = [
	'items' => array_map(
		static function ( $item ) {
			return [
				'title'     => (string) ( $item['title'] ?? '' ),
				'body'      => (string) ( $item['body'] ?? '' ),
				'number'    => (string) ( $item['number'] ?? '' ),
				'image_src' => builton_acf_resolve_url( $item['image_src'] ?? '' ),
				'image_alt' => (string) ( $item['image_alt'] ?? '' ),
			];
		},
		(array) ( $outcome_cards['items'] ?? [] )
	),
];

$featured_project_1 = (array) builton_front_page_field( 'featured_project_1' );
$featured_project_2 = (array) builton_front_page_field( 'featured_project_2' );
$context['featured_project_1'] = [
	'title'        => (string) ( $featured_project_1['title'] ?? '' ),
	'image_src'    => builton_acf_resolve_url( $featured_project_1['image_src'] ?? '' ),
	'image_alt'    => (string) ( $featured_project_1['image_alt'] ?? '' ),
	'href'         => (string) ( $featured_project_1['href'] ?? '' ),
	'cursor_label' => (string) ( $featured_project_1['cursor_label'] ?? '' ),
];
$context['featured_project_2'] = [
	'title'        => (string) ( $featured_project_2['title'] ?? '' ),
	'image_src'    => builton_acf_resolve_url( $featured_project_2['image_src'] ?? '' ),
	'image_alt'    => (string) ( $featured_project_2['image_alt'] ?? '' ),
	'href'         => (string) ( $featured_project_2['href'] ?? '' ),
	'cursor_label' => (string) ( $featured_project_2['cursor_label'] ?? '' ),
];

$tandem_hub = (array) builton_front_page_field( 'tandem_hub' );
$context['tandem_hub'] = [
	'heading'      => (string) ( $tandem_hub['heading'] ?? '' ),
	'lead'         => (string) ( $tandem_hub['lead'] ?? '' ),
	'bottom_text'  => (string) ( $tandem_hub['bottom_text'] ?? '' ),
	'image_src'    => builton_acf_resolve_url( $tandem_hub['image_src'] ?? '' ),
	'spin_degrees' => (int) ( $tandem_hub['spin_degrees'] ?? 720 ),
];

$headline_reveal_timeline = (array) builton_front_page_field( 'headline_reveal_timeline' );
$context['headline_reveal_timeline'] = [
	'words'      => builton_front_page_words( $headline_reveal_timeline['words'] ?? [] ),
	'aria_label' => (string) ( $headline_reveal_timeline['aria_label'] ?? '' ),
	'tag'        => (string) ( $headline_reveal_timeline['tag'] ?? 'h2' ),
];

$timeline = (array) builton_front_page_field( 'timeline_accordion' );
$context['timeline_accordion'] = [
	'section_label' => (string) ( $timeline['section_label'] ?? '' ),
	'items'         => array_map(
		static function ( $row ) {
			return [
				'year'          => (string) ( $row['year'] ?? '' ),
				'title'         => (string) ( $row['title'] ?? '' ),
				'body'          => (string) ( $row['body'] ?? '' ),
				'image_src'     => builton_acf_resolve_url( $row['image_src'] ?? '' ),
				'image_alt'     => (string) ( $row['image_alt'] ?? '' ),
				'image_caption' => (string) ( $row['image_caption'] ?? '' ),
			];
		},
		(array) ( $timeline['items'] ?? [] )
	),
];

$context['explore_dual'] = builton_explore_dual_context();

Timber::render( 'front-page.twig', $context );
