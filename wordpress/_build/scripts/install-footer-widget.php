<?php
/**
 * Seed the editable LSC footer widget area with the current footer markup.
 *
 * Run via WP-CLI:
 *   wp eval-file /var/www/html/_build/scripts/install-footer-widget.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sidebars = get_option( 'sidebars_widgets', array() );
if ( ! is_array( $sidebars ) ) {
	$sidebars = array();
}

if ( ! empty( $sidebars['lsc-footer'] ) ) {
	WP_CLI::log( '[footer] LSC Footer already has widgets; leaving admin edits untouched.' );
	return;
}

$footer_file = ABSPATH . '_build/footer.html';
$footer_html = file_get_contents( $footer_file );
if ( false === $footer_html ) {
	WP_CLI::error( '[footer] Could not read ' . $footer_file );
}

$widgets = get_option( 'widget_block', array() );
if ( ! is_array( $widgets ) ) {
	$widgets = array();
}

$next_id = 1;
foreach ( array_keys( $widgets ) as $key ) {
	if ( is_int( $key ) || ctype_digit( (string) $key ) ) {
		$next_id = max( $next_id, (int) $key + 1 );
	}
}

$widgets[ $next_id ] = array(
	'content' => "<!-- wp:html -->\n" . $footer_html . "\n<!-- /wp:html -->",
);
$widgets['_multiwidget'] = 1;

$sidebars['lsc-footer']    = array( 'block-' . $next_id );
$sidebars['array_version'] = 3;

update_option( 'widget_block', $widgets );
update_option( 'sidebars_widgets', $sidebars );

WP_CLI::success( '[footer] Seeded editable footer in Appearance > Widgets > LSC Footer.' );
