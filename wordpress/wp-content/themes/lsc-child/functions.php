<?php
/**
 * LSC Child theme functions.
 *
 * @package lsc-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the Kadence parent stylesheet, then the child stylesheet.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'kadence-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( get_template() )->get( 'Version' )
	);
	wp_enqueue_style(
		'lsc-child-style',
		get_stylesheet_uri(),
		array( 'kadence-parent-style' ),
		wp_get_theme()->get( 'Version' )
	);
} );

/**
 * Register navigation menu locations (in case the parent's are not used).
 */
add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'lsc-child' ),
		'footer'  => __( 'Footer Menu', 'lsc-child' ),
	) );
} );

/**
 * Register LSC block patterns and a dedicated pattern category so they are
 * easy to find in the editor inserter.
 */
add_action( 'init', function () {
	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category( 'lsc', array( 'label' => __( 'LSC', 'lsc-child' ) ) );
	}

	$patterns_dir = get_stylesheet_directory() . '/patterns';
	if ( ! is_dir( $patterns_dir ) || ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	foreach ( glob( $patterns_dir . '/*.php' ) as $file ) {
		$slug    = 'lsc/' . basename( $file, '.php' );
		$content = require $file; // each pattern file returns its block markup string.
		if ( is_string( $content ) ) {
			register_block_pattern( $slug, array(
				'title'      => ucwords( str_replace( '-', ' ', basename( $file, '.php' ) ) ),
				'categories' => array( 'lsc' ),
				'content'    => $content,
			) );
		}
	}
} );

/**
 * Custom favicon from the child theme — overrides the WP Site Icon setting.
 */
add_action( 'wp_head', function () {
	$dir = get_stylesheet_directory_uri() . '/assets/images';
	echo '<link rel="icon" type="image/x-icon" href="' . esc_url( $dir . '/favicon.ico' ) . '">' . "\n";
	echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( $dir . '/favicon-32x32.png' ) . '">' . "\n";
	echo '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url( $dir . '/favicon-16x16.png' ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( $dir . '/apple-touch-icon.png' ) . '">' . "\n";
} );

/**
 * Book the grounds intentionally moved from a Get Involved child page to a
 * standalone URL. Do not let WordPress preserve the old nested path via its
 * automatic canonical redirect.
 */
add_filter( 'redirect_canonical', function ( $redirect_url ) {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? strtok( (string) $_SERVER['REQUEST_URI'], '?' ) : '';

	if ( '/get-involved/book-the-grounds/' === $path ) {
		return false;
	}

	return $redirect_url;
} );

/**
 * Expose the theme's logo + brand colours as a quick helper for templates.
 */
function lsc_logo_url() {
	return get_stylesheet_directory_uri() . '/assets/images/lsc-logo.png';
}

/**
 * Register the admin-editable footer widget area.
 */
add_action( 'widgets_init', function () {
	register_sidebar( array(
		'name'          => __( 'LSC Footer', 'lsc-child' ),
		'id'            => 'lsc-footer',
		'description'   => __( 'Edit the site footer content shown on every page.', 'lsc-child' ),
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => '',
	) );
} );

/**
 * Render the admin-editable footer widget area. Kadence's own footer is
 * builder-driven (and shows a "Kadence WP" credit), so CSS hides it and this
 * dedicated widget area supplies the visible footer instead.
 */
add_action( 'wp_footer', function () {
	if ( is_active_sidebar( 'lsc-footer' ) ) {
		dynamic_sidebar( 'lsc-footer' );
	}
}, 5 );
