<?php
/**
 * Project page ACF field group.
 *
 * @package Built-On
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Overview table row sub fields.
 *
 * @return array<int, array<string, mixed>>
 */
function builton_project_overview_row_fields() {
	return [
		[
			'key'           => 'field_project_overview_row_type',
			'label'         => 'Row type',
			'name'          => 'row_type',
			'type'          => 'select',
			'choices'       => [
				'simple'   => __( 'Label + value', 'builton' ),
				'partners' => __( 'Partner highlights (two columns)', 'builton' ),
			],
			'default_value' => 'simple',
			'ui'            => 1,
		],
		[
			'key'   => 'field_project_overview_row_label',
			'label' => 'Label',
			'name'  => 'label',
			'type'  => 'text',
		],
		[
			'key'               => 'field_project_overview_row_value',
			'label'             => 'Value',
			'name'              => 'value',
			'type'              => 'textarea',
			'rows'              => 3,
			'new_lines'         => 'br',
			'conditional_logic' => [
				[
					[
						'field'    => 'field_project_overview_row_type',
						'operator' => '==',
						'value'    => 'simple',
					],
				],
			],
		],
		[
			'key'               => 'field_project_overview_row_partners',
			'label'             => 'Partners',
			'name'              => 'partners',
			'type'              => 'repeater',
			'layout'            => 'table',
			'button_label'      => __( 'Add partner', 'builton' ),
			'conditional_logic' => [
				[
					[
						'field'    => 'field_project_overview_row_type',
						'operator' => '==',
						'value'    => 'partners',
					],
				],
			],
			'sub_fields'        => [
				[
					'key'   => 'field_project_overview_partner_name',
					'label' => 'Name',
					'name'  => 'name',
					'type'  => 'text',
				],
				[
					'key'   => 'field_project_overview_partner_role',
					'label' => 'Role',
					'name'  => 'role',
					'type'  => 'text',
				],
			],
		],
	];
}

/**
 * Approach media item sub fields.
 *
 * @return array<int, array<string, mixed>>
 */
function builton_project_approach_media_fields() {
	return [
		[
			'key'           => 'field_project_approach_media_type',
			'label'         => 'Media type',
			'name'          => 'media_type',
			'type'          => 'select',
			'choices'       => [
				'image' => __( 'Image', 'builton' ),
				'video' => __( 'Video', 'builton' ),
			],
			'default_value' => 'image',
			'ui'            => 1,
		],
		[
			'key'               => 'field_project_approach_media_image',
			'label'             => 'Image',
			'name'              => 'image',
			'type'              => 'image',
			'return_format'     => 'array',
			'preview_size'      => 'medium',
			'conditional_logic' => [
				[
					[
						'field'    => 'field_project_approach_media_type',
						'operator' => '==',
						'value'    => 'image',
					],
				],
			],
		],
		[
			'key'               => 'field_project_approach_media_video_url',
			'label'             => 'Video URL',
			'name'              => 'video_url',
			'type'              => 'url',
			'instructions'      => __( 'MP4 or hosted video URL.', 'builton' ),
			'conditional_logic' => [
				[
					[
						'field'    => 'field_project_approach_media_type',
						'operator' => '==',
						'value'    => 'video',
					],
				],
			],
		],
		[
			'key'               => 'field_project_approach_media_video_poster',
			'label'             => 'Video poster',
			'name'              => 'video_poster',
			'type'              => 'image',
			'return_format'     => 'array',
			'preview_size'      => 'medium',
			'conditional_logic' => [
				[
					[
						'field'    => 'field_project_approach_media_type',
						'operator' => '==',
						'value'    => 'video',
					],
				],
			],
		],
		[
			'key'               => 'field_project_approach_media_video_label',
			'label'             => 'Video label',
			'name'              => 'video_label',
			'type'              => 'text',
			'instructions'      => __( 'Optional overlay label (e.g. “Top Off Event”).', 'builton' ),
			'conditional_logic' => [
				[
					[
						'field'    => 'field_project_approach_media_type',
						'operator' => '==',
						'value'    => 'video',
					],
				],
			],
		],
	];
}

/**
 * One repeated project’s sub fields.
 *
 * @return array<int, array<string, mixed>>
 */
function builton_project_page_project_fields() {
	return [
		[
			'key'          => 'field_project_acc_hero',
			'label'        => __( '1 · Hero', 'builton' ),
			'type'         => 'accordion',
			'open'         => 1,
			'multi_expand' => 1,
			'endpoint'     => 0,
		],
		[
			'key'           => 'field_project_item_title',
			'label'         => 'Project title',
			'name'          => 'project_title',
			'type'          => 'text',
			'default_value' => 'AVENIR',
			'instructions'  => __( 'Shown large and centered under the hero image. Also used as this row’s label in the editor.', 'builton' ),
		],
		[
			'key'          => 'field_project_item_nav_label',
			'label'        => 'Short label (Projects page sub-nav)',
			'name'         => 'nav_label',
			'type'         => 'text',
			'instructions' => __( 'Optional shorter name shown in the Projects page sub-navigation (e.g. "Grand Hyatt" instead of "Grand Hyatt Residences"). Leave blank to reuse the Project title above.', 'builton' ),
		],
		[
			'key'          => 'field_project_item_footer_label',
			'label'        => 'Short label (footer Portfolio list)',
			'name'         => 'footer_label',
			'type'         => 'text',
			'instructions' => __( 'Optional shorter name shown in the footer "Portfolio" list. Leave blank to reuse the Project title above.', 'builton' ),
		],
		[
			'key'           => 'field_project_item_subtitle',
			'label'         => 'Subtitle',
			'name'          => 'subtitle',
			'type'          => 'text',
			'default_value' => 'Ambition, realized.',
			'instructions'  => __( 'Short italic tagline under the title.', 'builton' ),
		],
		[
			'key'           => 'field_project_item_parallax_image',
			'label'         => 'Hero image (full width)',
			'name'          => 'parallax_image',
			'type'          => 'image',
			'return_format' => 'array',
			'preview_size'  => 'large',
			'instructions'  => __( 'Wide banner image at the top of the project; scrolls with a subtle parallax effect.', 'builton' ),
		],
		[
			'key'          => 'field_project_item_parallax_alt',
			'label'        => 'Hero image alt text',
			'name'         => 'parallax_alt',
			'type'         => 'text',
			'instructions' => __( 'Optional. Describes the hero image for accessibility and SEO.', 'builton' ),
		],
		[
			'key'          => 'field_project_acc_overview',
			'label'        => __( '2 · Overview', 'builton' ),
			'type'         => 'accordion',
			'open'         => 0,
			'multi_expand' => 1,
			'endpoint'     => 0,
		],
		[
			'key'          => 'field_project_item_overview_rows',
			'label'        => 'Overview table rows',
			'name'         => 'overview_rows',
			'type'         => 'repeater',
			'layout'       => 'block',
			'collapsed'    => 'field_project_overview_row_label',
			'button_label' => __( 'Add row', 'builton' ),
			'instructions' => __( 'Left-column fact table (e.g. Location, Scope, Year). Pick “Partner highlights” for a two-column name / role list.', 'builton' ),
			'sub_fields'   => builton_project_overview_row_fields(),
		],
		[
			'key'          => 'field_project_item_overview_body',
			'label'        => 'Overview body (right column)',
			'name'         => 'overview_body',
			'type'         => 'wysiwyg',
			'tabs'         => 'all',
			'toolbar'      => 'basic',
			'media_upload' => 0,
			'instructions' => __( 'Narrative paragraph(s) shown to the right of the table.', 'builton' ),
		],
		[
			'key'          => 'field_project_acc_approach',
			'label'        => __( '3 · Approach & execution', 'builton' ),
			'type'         => 'accordion',
			'open'         => 0,
			'multi_expand' => 1,
			'endpoint'     => 0,
		],
		[
			'key'           => 'field_project_item_approach_heading',
			'label'         => 'Approach heading',
			'name'          => 'approach_heading',
			'type'          => 'text',
			'default_value' => 'APPROACH & EXECUTION',
			'instructions'  => __( 'Small uppercase label at the top-left of this section.', 'builton' ),
		],
		[
			'key'          => 'field_project_item_approach_body',
			'label'        => 'Approach body text',
			'name'         => 'approach_body',
			'type'         => 'wysiwyg',
			'tabs'         => 'all',
			'toolbar'      => 'basic',
			'media_upload' => 0,
			'instructions' => __( 'Body copy shown to the left of the media stack.', 'builton' ),
		],
		[
			'key'          => 'field_project_item_approach_media',
			'label'        => 'Approach media stack',
			'name'         => 'approach_media',
			'type'         => 'repeater',
			'layout'       => 'block',
			'collapsed'    => 'field_project_approach_media_type',
			'max'          => 3,
			'button_label' => __( 'Add media item', 'builton' ),
			'instructions' => __( 'Up to 3 stacked images or videos on the right. Each item can be an image or a video with a poster.', 'builton' ),
			'sub_fields'   => builton_project_approach_media_fields(),
		],
		[
			'key'          => 'field_project_acc_gallery',
			'label'        => __( '4 · Gallery', 'builton' ),
			'type'         => 'accordion',
			'open'         => 0,
			'multi_expand' => 1,
			'endpoint'     => 0,
		],
		[
			'key'           => 'field_project_item_gallery_images',
			'label'         => 'Gallery carousel images',
			'name'          => 'gallery_images',
			'type'          => 'gallery',
			'return_format' => 'array',
			'preview_size'  => 'medium',
			'insert'        => 'append',
			'library'       => 'all',
			'instructions'  => __( 'Horizontal carousel below the approach section. Add several images; drag to reorder.', 'builton' ),
		],
		[
			'key'          => 'field_project_acc_outcomes',
			'label'        => __( '5 · Outcomes / FAQ', 'builton' ),
			'type'         => 'accordion',
			'open'         => 0,
			'multi_expand' => 1,
			'endpoint'     => 0,
		],
		[
			'key'           => 'field_project_item_faq_heading',
			'label'         => 'Section heading',
			'name'          => 'faq_heading',
			'type'          => 'text',
			'default_value' => 'OUTCOMES + WHY IT MATTERS',
			'instructions'  => __( 'Heading above the expandable Q&A list.', 'builton' ),
		],
		[
			'key'          => 'field_project_item_faq_items',
			'label'        => 'Q&A items',
			'name'         => 'faq_items',
			'type'         => 'repeater',
			'layout'       => 'block',
			'collapsed'    => 'field_project_item_faq_question',
			'button_label' => __( 'Add question', 'builton' ),
			'instructions' => __( 'Accordion list. The first item is open by default on the front end.', 'builton' ),
			'sub_fields'   => [
				[
					'key'   => 'field_project_item_faq_question',
					'label' => 'Question',
					'name'  => 'question',
					'type'  => 'textarea',
					'rows'  => 2,
				],
				[
					'key'          => 'field_project_item_faq_answer',
					'label'        => 'Answer',
					'name'         => 'answer',
					'type'         => 'wysiwyg',
					'tabs'         => 'all',
					'toolbar'      => 'basic',
					'media_upload' => 0,
				],
			],
		],
		[
			'key'      => 'field_project_acc_end',
			'label'    => '',
			'type'     => 'accordion',
			'endpoint' => 1,
		],
	];
}

/**
 * Register Project page fields.
 *
 * @return void
 */
function builton_register_project_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		[
			'key'                   => 'group_builton_project_page',
			'title'                 => 'Project page',
			'fields'                => [
				[
					'key'           => 'field_project_page_heading',
					'label'         => 'Page heading',
					'name'          => 'page_heading',
					'type'          => 'text',
					'default_value' => 'PORTFOLIO',
					'placeholder'   => 'PORTFOLIO',
					'instructions'  => __( 'Large heading shown once at the top of the page (animated word by word). Leave blank to use the page title.', 'builton' ),
				],
				[
					'key'          => 'field_project_page_projects',
					'label'        => 'Projects',
					'name'         => 'projects',
					'type'         => 'repeater',
					'layout'       => 'block',
					'collapsed'    => 'field_project_item_title',
					'min'          => 0,
					'button_label' => __( 'Add project', 'builton' ),
					'instructions' => __( 'Each row is a full project section (hero, overview, approach, gallery, outcomes). Add as many projects as you need and drag to reorder; the collapsed title makes long lists easy to scan.', 'builton' ),
					'sub_fields'   => builton_project_page_project_fields(),
				],
			],
			'location'              => [
				[
					[
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-project.php',
					],
				],
			],
			'menu_order'            => 1,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		]
	);
}
