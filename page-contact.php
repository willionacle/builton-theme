<?php
/* Template Name: Contact Page Layout */

$context = Timber::context();
$timber_post = Timber::get_post();
$context['post'] = $timber_post;

// Header & Form
$context['subheader_heading'] = get_field('subheader_heading');
$context['subheader_text']    = get_field('subheader_text');
$context['form_shortcode']    = get_field('form_shortcode');

// Quick Info
$context['contact_email'] = get_field('contact_email');
$context['contact_phone'] = get_field('contact_phone');

// Offices
$context['offices_intro']     = get_field('offices_intro');

$context['office_1_image']    = get_field('office_1_image');
$context['office_1_name']     = get_field('office_1_name');
$context['office_1_timezone'] = get_field('office_1_timezone');

$context['office_2_link']     = get_field('office_2_link');
$context['office_2_image']    = get_field('office_2_image');
$context['office_2_name']     = get_field('office_2_name');
$context['office_2_timezone'] = get_field('office_2_timezone');
$context['office_2_address']  = get_field('office_2_address');

Timber::render( 'page-contact.twig', $context );