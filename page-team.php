<?php
/* Template Name: Team Page Layout */

require_once get_template_directory() . '/inc/acf/url-helpers.php';

$context = Timber::context();
$context['post'] = Timber::get_post();

// Query all Team Member posts
$context['team_members'] = Timber::get_posts([
    'post_type'      => 'team_member',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'ASC'
]);

$context['intro_text'] = get_field('intro_text');

// Shared "Explore Further" section (sections/explore-dual.twig) — same
// component as Home/What We Do/Projects, but the right card stays a
// portfolio link instead of the leadership cluster.
$context['explore_dual'] = [
    'subheading' => (string) get_field( 'cta_eyebrow' ),
    'heading'    => (string) get_field( 'cta_title' ),
    'light_card' => [
        'title'  => (string) get_field( 'card_1_title' ),
        'href'   => (string) get_field( 'card_1_link' ),
        'lines'  => [ 'Development', 'Construction & Design', 'Property Management' ],
        'thumbs' => array_map(
            static function ( $img ) {
                return [
                    'src' => builton_acf_resolve_url( $img ),
                    'alt' => is_array( $img ) ? (string) ( $img['alt'] ?? '' ) : '',
                ];
            },
            [ get_field( 'card_1_img_1' ), get_field( 'card_1_img_2' ), get_field( 'card_1_img_3' ) ]
        ),
    ],
    'portfolio_card' => [
        'title' => (string) get_field( 'card_2_title' ),
        'href'  => (string) get_field( 'card_2_link' ),
        'image' => builton_acf_image_for_twig( get_field( 'card_2_img' ) ),
    ],
];

Timber::render( 'page-team.twig', $context );