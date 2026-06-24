<?php
/**
 * What we do page ACF field group (page template).
 *
 * @package Built-On
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register What we do template fields.
 *
 * @return void
 */
function builton_register_what_we_do_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		[
			'key'                   => 'group_builton_what_we_do',
			'title'                 => 'What we do',
			'fields'                => [
				[
					'key'   => 'field_wwd_tab_headline_reveal',
					'label' => 'Headline reveal',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_wwd_headline_reveal_hero',
					'label'      => 'Headline reveal (hero)',
					'name'       => 'headline_reveal_hero',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[
							'key'   => 'field_wwd_hr_hero_aria',
							'label' => 'Aria label',
							'name'  => 'aria_label',
							'type'  => 'text',
						],
						[
							'key'           => 'field_wwd_hr_hero_tag',
							'label'         => 'Heading tag',
							'name'          => 'tag',
							'type'          => 'select',
							'choices'       => [
								'h1' => 'h1',
								'h2' => 'h2',
								'h3' => 'h3',
							],
							'default_value' => 'h1',
							'ui'            => 1,
						],
						[
							'key'           => 'field_wwd_hr_hero_words',
							'label'         => 'Words',
							'name'          => 'words',
							'type'          => 'repeater',
							'layout'        => 'table',
							'button_label'  => __( 'Add word', 'builton' ),
							'instructions'  => __( 'One row per word for the reveal animation. If empty, the page title is split on spaces.', 'builton' ),
							'sub_fields'    => [
								[
									'key'   => 'field_wwd_hr_hero_word_text',
									'label' => 'Word',
									'name'  => 'text',
									'type'  => 'text',
								],
							],
						],
					],
				],
				[
					'key'   => 'field_wwd_tab_property_development',
					'label' => 'Section 1',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_wwd_property_development',
					'label'      => 'Section 1',
					'name'       => 'section_1',
					'type'       => 'group',
					'layout'     => 'block',
					'instructions' => __( 'Full-width parallax banner at the top of this block, then heading and two-row layout (see design).', 'builton' ),
					'sub_fields' => [
						[
							'key'           => 'field_wwd_s1_image_side',
							'label'         => 'Image side',
							'name'          => 'image_side',
							'type'          => 'select',
							'choices'       => [
								'right' => __( 'Right (default)', 'builton' ),
								'left'  => __( 'Left', 'builton' ),
							],
							'default_value' => 'right',
							'ui'            => 1,
						],
						[
							'key'           => 'field_wwd_s1_panel_style',
							'label'         => 'Background style',
							'name'          => 'panel_style',
							'type'          => 'select',
							'choices'       => [
								'muted'       => __( 'Muted (default)', 'builton' ),
								'transparent' => __( 'Transparent', 'builton' ),
							],
							'default_value' => 'muted',
							'ui'            => 1,
						],
						[
							'key'           => 'field_wwd_s1_parallax',
							'label'         => 'Parallax banner image',
							'name'          => 'parallax_image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'large',
							'instructions'  => __( 'Wide panoramic image; displayed edge-to-edge with a subtle scroll parallax effect.', 'builton' ),
						],
						[
							'key'   => 'field_wwd_s1_parallax_alt',
							'label' => 'Parallax image alt text',
							'name'  => 'parallax_alt',
							'type'  => 'text',
							'instructions' => __( 'Optional. Overrides the attachment alt text for accessibility.', 'builton' ),
						],
						[
							'key'   => 'field_wwd_s1_main_title',
							'label' => 'Main title',
							'name'  => 'main_title',
							'type'  => 'text',
							'default_value' => 'PROPERTY DEVELOPMENT',
						],
						[
							'key'   => 'field_wwd_s1_main_subtitle',
							'label' => 'Subtitle',
							'name'  => 'main_subtitle',
							'type'  => 'text',
							'default_value' => 'Building value, together.',
						],
						[
							'key'   => 'field_wwd_s1_feature_subhead',
							'label' => 'Feature row — subhead (left column)',
							'name'  => 'feature_subhead',
							'type'  => 'text',
							'default_value' => 'Strategic & Aligned Growth',
						],
						[
							'key'           => 'field_wwd_s1_feature_body',
							'label'         => 'Feature row — body (left column)',
							'name'          => 'feature_body',
							'type'          => 'wysiwyg',
							'tabs'          => 'all',
							'toolbar'       => 'basic',
							'media_upload'  => 0,
						],
						[
							'key'           => 'field_wwd_s1_feature_image',
							'label'         => 'Feature row — image (right column)',
							'name'          => 'feature_image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'large',
						],
						[
							'key'   => 'field_wwd_s1_feature_image_alt',
							'label' => 'Feature image alt text',
							'name'  => 'feature_image_alt',
							'type'  => 'text',
						],
						[
							'key'   => 'field_wwd_s1_bottom_left_subhead',
							'label' => 'Bottom row — left subhead',
							'name'  => 'bottom_left_subhead',
							'type'  => 'text',
							'default_value' => 'Finding Smart Opportunities',
						],
						[
							'key'           => 'field_wwd_s1_bottom_left_body',
							'label'         => 'Bottom row — left body',
							'name'          => 'bottom_left_body',
							'type'          => 'wysiwyg',
							'tabs'          => 'all',
							'toolbar'       => 'basic',
							'media_upload'  => 0,
						],
						[
							'key'   => 'field_wwd_s1_bottom_right_subhead',
							'label' => 'Bottom row — right subhead',
							'name'  => 'bottom_right_subhead',
							'type'  => 'text',
							'default_value' => 'Partnerships That Last',
						],
						[
							'key'           => 'field_wwd_s1_bottom_right_body',
							'label'         => 'Bottom row — right body',
							'name'          => 'bottom_right_body',
							'type'          => 'wysiwyg',
							'tabs'          => 'all',
							'toolbar'       => 'basic',
							'media_upload'  => 0,
						],
					],
				],
				[
					'key'   => 'field_wwd_tab_section_2',
					'label' => 'Section 2',
					'type'  => 'tab',
				],
				[
					'key'          => 'field_wwd_section_2',
					'label'        => 'Section 2',
					'name'         => 'section_2',
					'type'         => 'clone',
					'clone'        => [ 'field_wwd_property_development' ],
					'display'      => 'group',
					'layout'       => 'block',
					'prefix_name'  => 1,
					'prefix_label' => 1,
				],
				[
					'key'   => 'field_wwd_tab_section_3',
					'label' => 'Section 3',
					'type'  => 'tab',
				],
				[
					'key'          => 'field_wwd_section_3',
					'label'        => 'Section 3',
					'name'         => 'section_3',
					'type'         => 'clone',
					'clone'        => [ 'field_wwd_property_development' ],
					'display'      => 'group',
					'layout'       => 'block',
					'prefix_name'  => 1,
					'prefix_label' => 1,
				],
				[
					'key'   => 'field_wwd_tab_intro',
					'label' => 'Page intro',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_wwd_page_intro',
					'label'      => 'Page intro',
					'name'       => 'page_intro',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[
							'key'   => 'field_wwd_page_intro_eyebrow',
							'label' => 'Eyebrow',
							'name'  => 'eyebrow',
							'type'  => 'text',
						],
						[
							'key'   => 'field_wwd_page_intro_intro',
							'label' => 'Intro',
							'name'  => 'intro',
							'type'  => 'wysiwyg',
							'tabs'  => 'all',
							'media_upload' => 1,
							'toolbar' => 'full',
						],
					],
				],
				[
					'key'   => 'field_wwd_tab_text_1',
					'label' => 'Text block 1',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_wwd_text_block_1',
					'label'      => 'Text Block 1',
					'name'       => 'text_block_1',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[
							'key'   => 'field_wwd_text_block_1_content',
							'label' => 'Content',
							'name'  => 'content',
							'type'  => 'wysiwyg',
							'tabs'  => 'all',
							'toolbar' => 'full',
							'media_upload' => 1,
						],
						[
							'key'           => 'field_wwd_text_block_1_regular_weight',
							'label'         => 'Typography',
							'name'          => 'regular_weight',
							'type'          => 'true_false',
							'instructions'  => __( 'Use regular (400) font weight instead of the default medium weight.', 'builton' ),
							'default_value' => 0,
							'ui'            => 1,
							'ui_on_text'    => __( 'Regular', 'builton' ),
							'ui_off_text'   => __( 'Default', 'builton' ),
						],
					],
				],
				[
					'key'   => 'field_wwd_tab_text_2',
					'label' => 'Text block 2',
					'type'  => 'tab',
				],
				[
					'key'        => 'field_wwd_text_block_2',
					'label'      => 'Text Block 2',
					'name'       => 'text_block_2',
					'type'       => 'group',
					'layout'     => 'block',
					'sub_fields' => [
						[
							'key'   => 'field_wwd_text_block_2_content',
							'label' => 'Content',
							'name'  => 'content',
							'type'  => 'wysiwyg',
							'tabs'  => 'all',
							'toolbar' => 'full',
							'media_upload' => 1,
						],
						[
							'key'           => 'field_wwd_text_block_2_regular_weight',
							'label'         => 'Typography',
							'name'          => 'regular_weight',
							'type'          => 'true_false',
							'instructions'  => __( 'Use regular (400) font weight instead of the default medium weight.', 'builton' ),
							'default_value' => 0,
							'ui'            => 1,
							'ui_on_text'    => __( 'Regular', 'builton' ),
							'ui_off_text'   => __( 'Default', 'builton' ),
						],
					],
				],
			],
			'location'              => [
				[
					[
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-what-we-do.php',
					],
				],
			],
			'menu_order'            => 1,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
		]
	);
}
