<?php
/**
 * Upsert the Notice Board page and its initial gallery image.
 *
 * Run with:
 *   wp --path=/var/www/html eval-file /var/www/html/_build/scripts/upsert-notice-board.php
 */

$source = '/var/www/html/_build/media/notice-board/catering-volunteer.jpeg';
$title = 'Catering volunteer notice';

$attachments = get_posts(
	array(
		'post_type'      => 'attachment',
		'title'          => $title,
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	)
);

if ( $attachments ) {
	$attachment_id = (int) $attachments[0];
} else {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$upload = wp_upload_bits( basename( $source ), null, file_get_contents( $source ) );
	if ( ! empty( $upload['error'] ) ) {
		WP_CLI::error( $upload['error'] );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attachment_id ) ) {
		WP_CLI::error( $attachment_id->get_error_message() );
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
	wp_update_attachment_metadata( $attachment_id, $metadata );

	update_post_meta(
		$attachment_id,
		'_wp_attachment_image_alt',
		'Catering volunteer notice for Lewisham Sports Consortium'
	);
}

$image_url = wp_get_attachment_url( $attachment_id );
$alt       = esc_attr( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );

$content = sprintf(
	'<!-- wp:group {"align":"wide","className":"lsc-notice-board","layout":{"type":"constrained","justifyContent":"center"}} -->
<div class="wp-block-group alignwide lsc-notice-board"><!-- wp:gallery {"columns":1,"linkTo":"media","sizeSlug":"large","align":"center"} -->
<figure class="wp-block-gallery aligncenter has-nested-images columns-1"><!-- wp:image {"id":%1$d,"sizeSlug":"large","linkDestination":"media","align":"center"} -->
<figure class="wp-block-image aligncenter size-large"><a href="%2$s"><img src="%2$s" alt="%3$s" class="wp-image-%1$d"/></a></figure>
<!-- /wp:image --></figure>
<!-- /wp:gallery --></div>
<!-- /wp:group -->',
	$attachment_id,
	esc_url( $image_url ),
	$alt
);

$existing = get_page_by_path( 'notice-board', OBJECT, 'page' );

$post_data = array(
	'post_title'   => 'Notice Board',
	'post_name'    => 'notice-board',
	'post_type'    => 'page',
	'post_status'  => 'publish',
	'post_content' => $content,
);

if ( $existing ) {
	$post_data['ID'] = $existing->ID;
	$page_id         = wp_update_post( $post_data, true );
} else {
	$page_id = wp_insert_post( $post_data, true );
}

if ( is_wp_error( $page_id ) ) {
	WP_CLI::error( $page_id->get_error_message() );
}

WP_CLI::success( sprintf( 'Notice Board page #%d uses attachment #%d.', $page_id, $attachment_id ) );
