<?php
/**
 * Front page ACF field group.
 *
 * @package Built-On
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register front-page section fields.
 *
 * @return void
 */
function builton_register_front_page_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		[
			'key'                   => 'group_builton_front_page_sections',
			'title'                 => 'Front Page Sections',
			'fields'                => [
				[
					'key'   => 'field_front_page_tab_hero',
					'label' => 'Hero',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_hero',
					'label'      => 'Hero Section',
					'name'       => 'hero',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[ 'key' => 'field_front_page_hero_subheading', 'label' => 'Subheading', 'name' => 'subheading', 'type' => 'text' ],
						[ 'key' => 'field_front_page_hero_aria', 'label' => 'Headline Aria Label', 'name' => 'headline_aria_label', 'type' => 'text' ],
						[
							'key'          => 'field_front_page_hero_headline_lines',
							'label'        => 'Headline Lines',
							'name'         => 'headline_lines',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => 'Add Line',
							'sub_fields'   => [
								[
									'key'          => 'field_front_page_hero_headline_words',
									'label'        => 'Words',
									'name'         => 'words',
									'type'         => 'repeater',
									'layout'       => 'table',
									'button_label' => 'Add Word',
									'sub_fields'   => [
										[ 'key' => 'field_front_page_hero_headline_word_text', 'label' => 'Word', 'name' => 'word', 'type' => 'text' ],
										[ 'key' => 'field_front_page_hero_headline_word_accent', 'label' => 'Accent', 'name' => 'accent', 'type' => 'true_false', 'ui' => 1, 'default_value' => 0 ],
									],
								],
							],
						],
						[
							'key'           => 'field_front_page_hero_marquee_gallery',
							'label'         => 'Marquee Images',
							'name'          => 'marquee_images',
							'type'          => 'gallery',
							'instructions'  => 'Images for the bottom hero carousel; order is preserved.',
							'return_format' => 'array',
							'preview_size'  => 'medium',
							'insert'        => 'append',
							'library'       => 'all',
						],
					],
				],
				[
					'key'   => 'field_front_page_tab_headline_reveal_1',
					'label' => 'Headline Reveal 1',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_headline_reveal_1',
					'label'      => 'Headline Reveal 1',
					'name'       => 'headline_reveal_1',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => builton_front_page_headline_reveal_fields( 'one' ),
				],
				[
					'key'   => 'field_front_page_tab_text_block_1',
					'label' => 'Text Block 1',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_text_block_1',
					'label'      => 'Text Block 1',
					'name'       => 'text_block_1',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[ 'key' => 'field_front_page_text_block_1_content', 'label' => 'Content', 'name' => 'content', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'full', 'media_upload' => 1 ],
						[
							'key'           => 'field_front_page_text_block_1_regular_weight',
							'label'         => 'Typography',
							'name'          => 'regular_weight',
							'type'          => 'true_false',
							'instructions'  => 'Use regular (400) font weight instead of the default medium weight.',
							'default_value' => 0,
							'ui'            => 1,
							'ui_on_text'    => __( 'Regular', 'builton' ),
							'ui_off_text'   => __( 'Default', 'builton' ),
						],
					],
				],
				[
					'key'   => 'field_front_page_tab_video_block',
					'label' => 'Video Block',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_video_block',
					'label'      => 'Video Block',
					'name'       => 'video_block',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[ 'key' => 'field_front_page_video_src', 'label' => 'Video URL', 'name' => 'video_src', 'type' => 'url' ],
						[ 'key' => 'field_front_page_video_poster', 'label' => 'Poster Image', 'name' => 'poster_src', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium' ],
						[ 'key' => 'field_front_page_video_aria_label', 'label' => 'Aria Label', 'name' => 'aria_label', 'type' => 'text' ],
					],
				],
				[
					'key'   => 'field_front_page_tab_reveal_grid',
					'label' => 'Reveal Grid',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_reveal_grid',
					'label'      => 'Reveal Grid',
					'name'       => 'reveal_grid',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[
							'key'          => 'field_front_page_reveal_grid_items',
							'label'        => 'Items',
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => 'Add Item',
							'sub_fields'   => [
								[ 'key' => 'field_front_page_reveal_grid_type', 'label' => 'Type', 'name' => 'type', 'type' => 'select', 'choices' => [ 'text' => 'Text', 'image' => 'Image' ], 'default_value' => 'text', 'ui' => 1 ],
								[ 'key' => 'field_front_page_reveal_grid_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ],
								[ 'key' => 'field_front_page_reveal_grid_content', 'label' => 'Content', 'name' => 'content', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'full', 'media_upload' => 1 ],
								[ 'key' => 'field_front_page_reveal_grid_image', 'label' => 'Image', 'name' => 'image_src', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium' ],
								[ 'key' => 'field_front_page_reveal_grid_image_alt', 'label' => 'Image Alt', 'name' => 'alt', 'type' => 'text' ],
							],
						],
					],
				],
				[
					'key'   => 'field_front_page_tab_headline_reveal_2',
					'label' => 'Headline Reveal 2',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_headline_reveal_2',
					'label'      => 'Headline Reveal 2',
					'name'       => 'headline_reveal_2',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => builton_front_page_headline_reveal_fields( 'two' ),
				],
				[
					'key'   => 'field_front_page_tab_text_block_2',
					'label' => 'Text Block 2',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_text_block_2',
					'label'      => 'Text Block 2',
					'name'       => 'text_block_2',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[ 'key' => 'field_front_page_text_block_2_content', 'label' => 'Content', 'name' => 'content', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'full', 'media_upload' => 1 ],
						[
							'key'           => 'field_front_page_text_block_2_regular_weight',
							'label'         => 'Typography',
							'name'          => 'regular_weight',
							'type'          => 'true_false',
							'instructions'  => 'Use regular (400) font weight instead of the default medium weight.',
							'default_value' => 0,
							'ui'            => 1,
							'ui_on_text'    => __( 'Regular', 'builton' ),
							'ui_off_text'   => __( 'Default', 'builton' ),
						],
					],
				],
				[
					'key'   => 'field_front_page_tab_outcome_cards',
					'label' => 'Outcome Cards',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_outcome_cards',
					'label'      => 'Outcome Cards',
					'name'       => 'outcome_cards',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[
							'key'          => 'field_front_page_outcome_cards_items',
							'label'        => 'Cards',
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => 'Add Card',
							'sub_fields'   => [
								[ 'key' => 'field_front_page_outcome_card_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ],
								[ 'key' => 'field_front_page_outcome_card_body', 'label' => 'Body', 'name' => 'body', 'type' => 'textarea', 'rows' => 4 ],
								[ 'key' => 'field_front_page_outcome_card_number', 'label' => 'Number', 'name' => 'number', 'type' => 'text' ],
								[ 'key' => 'field_front_page_outcome_card_image', 'label' => 'Image', 'name' => 'image_src', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium' ],
								[ 'key' => 'field_front_page_outcome_card_alt', 'label' => 'Image Alt', 'name' => 'image_alt', 'type' => 'text' ],
							],
						],
					],
				],
				[
					'key'   => 'field_front_page_tab_text_block_3',
					'label' => 'Text Block (after outcomes)',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_text_block_3',
					'label'      => 'Text Block (after outcome cards)',
					'name'       => 'text_block_3',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[ 'key' => 'field_front_page_text_block_3_content', 'label' => 'Content', 'name' => 'content', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'full', 'media_upload' => 1 ],
						[
							'key'           => 'field_front_page_text_block_3_regular_weight',
							'label'         => 'Typography',
							'name'          => 'regular_weight',
							'type'          => 'true_false',
							'instructions'  => 'Use regular (400) font weight instead of the default medium weight.',
							'default_value' => 0,
							'ui'            => 1,
							'ui_on_text'    => __( 'Regular', 'builton' ),
							'ui_off_text'   => __( 'Default', 'builton' ),
						],
					],
				],
				[
					'key'   => 'field_front_page_tab_featured_project_1',
					'label' => 'Featured Project 1',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_featured_project_1',
					'label'      => 'Featured Project 1',
					'name'       => 'featured_project_1',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => builton_front_page_featured_project_fields( 'one' ),
				],
				[
					'key'   => 'field_front_page_tab_headline_reveal_3',
					'label' => 'Headline Reveal 3',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_headline_reveal_3',
					'label'      => 'Headline Reveal 3',
					'name'       => 'headline_reveal_3',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => builton_front_page_headline_reveal_fields( 'three' ),
				],
				[
					'key'   => 'field_front_page_tab_tandem_hub',
					'label' => 'Tandem Hub',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_tandem_hub',
					'label'      => 'Tandem Hub',
					'name'       => 'tandem_hub',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[ 'key' => 'field_front_page_tandem_hub_heading', 'label' => 'Heading', 'name' => 'heading', 'type' => 'text' ],
						[ 'key' => 'field_front_page_tandem_hub_lead', 'label' => 'Lead', 'name' => 'lead', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'full', 'media_upload' => 0 ],
						[ 'key' => 'field_front_page_tandem_hub_bottom_text', 'label' => 'Bottom Text', 'name' => 'bottom_text', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'full', 'media_upload' => 0 ],
						[ 'key' => 'field_front_page_tandem_hub_image', 'label' => 'Image', 'name' => 'image_src', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium' ],
						[ 'key' => 'field_front_page_tandem_hub_spin', 'label' => 'Spin Degrees', 'name' => 'spin_degrees', 'type' => 'number', 'default_value' => 720, 'min' => 0, 'step' => 1 ],
					],
				],
				[
					'key'   => 'field_front_page_tab_featured_project_2',
					'label' => 'Featured Project 2',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_featured_project_2',
					'label'      => 'Featured Project 2',
					'name'       => 'featured_project_2',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => builton_front_page_featured_project_fields( 'two' ),
				],
				[
					'key'   => 'field_front_page_tab_headline_reveal_timeline',
					'label' => 'Headline Reveal (Timeline)',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_headline_reveal_timeline',
					'label'      => 'Headline Reveal (Timeline)',
					'name'       => 'headline_reveal_timeline',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => builton_front_page_headline_reveal_fields( 'timeline' ),
				],
				[
					'key'   => 'field_front_page_tab_timeline',
					'label' => 'Timeline Accordion',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_timeline_accordion',
					'label'      => 'Timeline Accordion',
					'name'       => 'timeline_accordion',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[ 'key' => 'field_front_page_timeline_label', 'label' => 'Section Label', 'name' => 'section_label', 'type' => 'text' ],
						[
							'key'          => 'field_front_page_timeline_items',
							'label'        => 'Items',
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => 'Add Timeline Item',
							'sub_fields'   => [
								[ 'key' => 'field_front_page_timeline_year', 'label' => 'Year', 'name' => 'year', 'type' => 'text' ],
								[ 'key' => 'field_front_page_timeline_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ],
								[ 'key' => 'field_front_page_timeline_body', 'label' => 'Body', 'name' => 'body', 'type' => 'textarea', 'rows' => 4 ],
								[ 'key' => 'field_front_page_timeline_image', 'label' => 'Image', 'name' => 'image_src', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium' ],
								[ 'key' => 'field_front_page_timeline_image_alt', 'label' => 'Image Alt', 'name' => 'image_alt', 'type' => 'text' ],
								[ 'key' => 'field_front_page_timeline_caption', 'label' => 'Image Caption', 'name' => 'image_caption', 'type' => 'text' ],
							],
						],
					],
				],
				[
					'key'   => 'field_front_page_tab_explore_dual',
					'label' => 'Explore Dual',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_front_page_explore_dual',
					'label'      => 'Explore Dual',
					'name'       => 'explore_dual',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[ 'key' => 'field_front_page_explore_subheading', 'label' => 'Subheading', 'name' => 'subheading', 'type' => 'text' ],
						[ 'key' => 'field_front_page_explore_heading', 'label' => 'Heading', 'name' => 'heading', 'type' => 'text' ],
						[
							'key'        => 'field_front_page_explore_light_card',
							'label'      => 'Light Card',
							'name'       => 'light_card',
							'type'       => 'group',
							'layout'     => 'block',
							'sub_fields' => [
								[ 'key' => 'field_front_page_explore_light_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ],
								[ 'key' => 'field_front_page_explore_light_href', 'label' => 'Link URL', 'name' => 'href', 'type' => 'url' ],
								[
									'key'          => 'field_front_page_explore_light_lines',
									'label'        => 'Lines',
									'name'         => 'lines',
									'type'         => 'repeater',
									'layout'       => 'table',
									'button_label' => 'Add Line',
									'sub_fields'   => [
										[ 'key' => 'field_front_page_explore_light_line_text', 'label' => 'Text', 'name' => 'text', 'type' => 'text' ],
									],
								],
								[
									'key'          => 'field_front_page_explore_light_thumbs',
									'label'        => 'Thumbs',
									'name'         => 'thumbs',
									'type'         => 'repeater',
									'layout'       => 'block',
									'button_label' => 'Add Thumb',
									'sub_fields'   => [
										[ 'key' => 'field_front_page_explore_light_thumb_image', 'label' => 'Image', 'name' => 'src', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium' ],
										[ 'key' => 'field_front_page_explore_light_thumb_alt', 'label' => 'Alt', 'name' => 'alt', 'type' => 'text' ],
									],
								],
							],
						],
						[
							'key'        => 'field_front_page_explore_leadership',
							'label'      => 'Leadership Card',
							'name'       => 'leadership',
							'type'       => 'group',
							'layout'     => 'block',
							'sub_fields' => [
								[ 'key' => 'field_front_page_explore_leadership_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ],
								[ 'key' => 'field_front_page_explore_leadership_href', 'label' => 'Link URL', 'name' => 'href', 'type' => 'url' ],
								[
									'key'          => 'field_front_page_explore_leadership_portraits',
									'label'        => 'Portraits',
									'name'         => 'portraits',
									'type'         => 'repeater',
									'layout'       => 'block',
									'button_label' => 'Add Portrait',
									'sub_fields'   => [
										[ 'key' => 'field_front_page_explore_portrait_src', 'label' => 'Image', 'name' => 'src', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium' ],
										[ 'key' => 'field_front_page_explore_portrait_alt', 'label' => 'Alt', 'name' => 'alt', 'type' => 'text' ],
										[ 'key' => 'field_front_page_explore_portrait_top', 'label' => 'Top', 'name' => 'top', 'type' => 'text', 'instructions' => 'Examples: 5%, 20%' ],
										[ 'key' => 'field_front_page_explore_portrait_left', 'label' => 'Left', 'name' => 'left', 'type' => 'text', 'instructions' => 'Examples: 10%, 45%' ],
										[ 'key' => 'field_front_page_explore_portrait_width', 'label' => 'Width', 'name' => 'width', 'type' => 'text', 'instructions' => 'Examples: 25%, 32%' ],
										[ 'key' => 'field_front_page_explore_portrait_z', 'label' => 'Z-Index', 'name' => 'z', 'type' => 'number', 'default_value' => 1, 'step' => 1 ],
										[ 'key' => 'field_front_page_explore_portrait_dim', 'label' => 'Dim', 'name' => 'dim', 'type' => 'true_false', 'ui' => 1, 'default_value' => 0 ],
										[ 'key' => 'field_front_page_explore_portrait_hidden', 'label' => 'Hidden Back Layer', 'name' => 'hidden', 'type' => 'true_false', 'ui' => 1, 'default_value' => 0 ],
										[ 'key' => 'field_front_page_explore_portrait_hx', 'label' => 'Hover X Offset', 'name' => 'hx', 'type' => 'number', 'default_value' => 0, 'step' => 1 ],
										[ 'key' => 'field_front_page_explore_portrait_hy', 'label' => 'Hover Y Offset', 'name' => 'hy', 'type' => 'number', 'default_value' => 0, 'step' => 1 ],
									],
								],
							],
						],
					],
				],
			],
			'location'              => [
				[
					[
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					],
				],
			],
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
		]
	);
}

/**
 * Shared sub fields for headline reveal sections.
 *
 * @param string $suffix Key suffix.
 * @return array<int, mixed>
 */
function builton_front_page_headline_reveal_fields( $suffix ) {
	return [
		[ 'key' => 'field_front_page_headline_reveal_' . $suffix . '_aria', 'label' => 'Aria Label', 'name' => 'aria_label', 'type' => 'text' ],
		[ 'key' => 'field_front_page_headline_reveal_' . $suffix . '_tag', 'label' => 'Heading Tag', 'name' => 'tag', 'type' => 'select', 'choices' => [ 'h1' => 'h1', 'h2' => 'h2', 'h3' => 'h3' ], 'default_value' => 'h2', 'ui' => 1 ],
		[
			'key'          => 'field_front_page_headline_reveal_' . $suffix . '_words',
			'label'        => 'Words',
			'name'         => 'words',
			'type'         => 'repeater',
			'layout'       => 'table',
			'button_label' => 'Add Word',
			'sub_fields'   => [
				[ 'key' => 'field_front_page_headline_reveal_' . $suffix . '_word_text', 'label' => 'Word', 'name' => 'text', 'type' => 'text' ],
			],
		],
	];
}

/**
 * Shared sub fields for featured project sections.
 *
 * @param string $suffix Key suffix.
 * @return array<int, mixed>
 */
function builton_front_page_featured_project_fields( $suffix ) {
	return [
		[ 'key' => 'field_front_page_featured_project_' . $suffix . '_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ],
		[ 'key' => 'field_front_page_featured_project_' . $suffix . '_href', 'label' => 'Link URL', 'name' => 'href', 'type' => 'url' ],
		[ 'key' => 'field_front_page_featured_project_' . $suffix . '_cursor_label', 'label' => 'Cursor Label', 'name' => 'cursor_label', 'type' => 'text' ],
		[ 'key' => 'field_front_page_featured_project_' . $suffix . '_image', 'label' => 'Image', 'name' => 'image_src', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium' ],
		[ 'key' => 'field_front_page_featured_project_' . $suffix . '_image_alt', 'label' => 'Image Alt', 'name' => 'image_alt', 'type' => 'text' ],
	];
}
