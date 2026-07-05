<?php
/**
 * Replace Media with the About trustee page, preserving PDF wording exactly.
 *
 * Run from the wpcli container:
 *   wp eval-file /var/www/html/_build/scripts/upsert-about-page.php --path=/var/www/html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/post.php';

function lsc_about_attachment_by_title( $title ) {
	$attachments = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'title'          => $title,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );

	return $attachments ? (int) $attachments[0] : 0;
}

function lsc_about_import_media( $relative_path, $title, $alt_text ) {
	$existing = lsc_about_attachment_by_title( $title );
	if ( $existing ) {
		update_post_meta( $existing, '_wp_attachment_image_alt', $alt_text );
		return $existing;
	}

	$source = ABSPATH . ltrim( $relative_path, '/' );
	if ( ! file_exists( $source ) ) {
		WP_CLI::error( sprintf( 'Missing media file: %s', $source ) );
	}

	$bits = wp_upload_bits( basename( $source ), null, file_get_contents( $source ) );
	if ( ! empty( $bits['error'] ) ) {
		WP_CLI::error( $bits['error'] );
	}

	$filetype = wp_check_filetype( $bits['file'] );
	$id       = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$bits['file']
	);

	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $bits['file'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt_text );

	return (int) $id;
}

function lsc_about_media_url( $id ) {
	return wp_get_attachment_url( $id );
}

$media = array(
	'HARRY'     => lsc_about_import_media( '_build/media/trustees/harry-powell.png', 'Harry Powell trustee portrait', 'Harry Powell' ),
	'ASHLEY'    => lsc_about_import_media( '_build/media/trustees/ashley-letts.png', 'Ashley Letts trustee portrait', 'Ashley Letts' ),
	'LASCELLES' => lsc_about_import_media( '_build/media/trustees/lascelles-dixon.png', 'Lascelles Dixon trustee portrait', 'Lascelles Dixon' ),
	'MIKE'      => lsc_about_import_media( '_build/media/trustees/mike-garrick.png', 'Mike Garrick trustee portrait', 'Mike Garrick' ),
);

$content = file_get_contents( ABSPATH . '_build/pages/about.html' );
foreach ( $media as $token => $id ) {
	$content = str_replace( "__{$token}_ID__", (string) $id, $content );
	$content = str_replace( "__{$token}_URL__", lsc_about_media_url( $id ), $content );
}

$about = get_page_by_path( 'about', OBJECT, 'page' );
$media_page = get_page_by_path( 'media', OBJECT, 'page' );
$page_id = $about ? (int) $about->ID : ( $media_page ? (int) $media_page->ID : 0 );

$page_args = array(
	'post_title'   => 'About',
	'post_name'    => 'about',
	'post_content' => $content,
	'post_status'  => 'publish',
	'post_type'    => 'page',
);

if ( $page_id ) {
	$page_args['ID'] = $page_id;
	wp_update_post( $page_args );
	WP_CLI::log( sprintf( 'Updated About page (#%d).', $page_id ) );
} else {
	$page_id = wp_insert_post( $page_args );
	WP_CLI::log( sprintf( 'Created About page (#%d).', $page_id ) );
}

if ( $media_page && (int) $media_page->ID !== $page_id ) {
	wp_delete_post( (int) $media_page->ID, true );
	WP_CLI::log( sprintf( 'Deleted old Media page (#%d).', $media_page->ID ) );
}

$home = get_page_by_path( 'home', OBJECT, 'page' );
if ( $home ) {
	$home_content = str_replace(
		array(
			'/media/',
			'Our public consultation',
			'Aerial views, location plans and Phase 1 proposals for the future of Firhill Road Sports Ground.',
			'Consultation meeting about the future of the ground',
			'partnership-meeting.jpg',
		),
		array(
			'/about/',
			'Lewisham Sports Consortium Management',
			'Lewisham Sports Consortium is a charity and company limited by guarantee.',
			'Committee meeting in the clubhouse',
			'committee-meeting.jpg',
		),
		$home->post_content
	);

	if ( $home_content !== $home->post_content ) {
		wp_update_post( array(
			'ID'           => $home->ID,
			'post_content' => $home_content,
		) );
		WP_CLI::log( 'Updated Home links from Media to About.' );
	}
}

$widgets = get_option( 'widget_block', array() );
if ( is_array( $widgets ) ) {
	$changed_widgets = false;
	foreach ( $widgets as $key => $widget ) {
		if ( ! is_array( $widget ) || empty( $widget['content'] ) ) {
			continue;
		}

		$updated = str_replace( '<a href="/media/">Media</a>', '<a href="/about/">About</a>', $widget['content'] );
		if ( $updated !== $widget['content'] ) {
			$widgets[ $key ]['content'] = $updated;
			$changed_widgets = true;
		}
	}

	if ( $changed_widgets ) {
		update_option( 'widget_block', $widgets );
		WP_CLI::log( 'Updated footer links from Media to About.' );
	}
}

$existing_menu = wp_get_nav_menu_object( 'Primary' );
if ( $existing_menu ) {
	wp_delete_nav_menu( $existing_menu->term_id );
}

$menu_id = wp_create_nav_menu( 'Primary' );
foreach ( array( 'home', 'who-are-we', 'get-involved', 'events', 'about', 'get-in-touch' ) as $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-object-id' => $page->ID,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
	}
}

$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::success( 'About page, trustee media, and primary menu are up to date.' );
