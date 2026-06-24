<?php
/**
 * Main template file. Fallback for the WordPress template hierarchy.
 *
 * @package Built-On
 */

use Timber\Timber;

$context = Timber::context();
Timber::render( 'index.twig', $context );
