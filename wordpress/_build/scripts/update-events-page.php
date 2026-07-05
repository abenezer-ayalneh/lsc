<?php
/**
 * Updates the Events page from the old LSC events content.
 *
 * Run from the wpcli container:
 *   wp eval-file /var/www/html/_build/scripts/update-events-page.php --path=/var/www/html
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

function lsc_events_import_media( $relative_path, $title, $alt_text = '' ) {
	$file = ABSPATH . ltrim( $relative_path, '/' );

	if ( ! file_exists( $file ) ) {
		WP_CLI::error( sprintf( 'Missing media file: %s', $file ) );
	}

	$existing = get_posts( array(
		'post_type'      => 'attachment',
		'title'          => $title,
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );

	if ( $existing ) {
		$attachment_id = (int) $existing[0];
	} else {
		$attachment_id = (int) trim( WP_CLI::runcommand(
			sprintf(
				'media import %s --title=%s --porcelain',
				escapeshellarg( $file ),
				escapeshellarg( $title )
			),
			array( 'return' => true )
		) );
	}

	if ( $alt_text ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
	}

	return array(
		'id'  => $attachment_id,
		'url' => wp_get_attachment_url( $attachment_id ),
	);
}

$banner = lsc_events_import_media(
	'_build/media/events/img_main_events.jpg',
	'Events banner',
	'Lewisham Sports Consortium Events banner'
);
$flyer = lsc_events_import_media(
	'_build/media/events/SummerScheme2019.JPG',
	'Summer Scheme 2019 flyer',
	'Lewisham Sports Consortium Summer Scheme 2019 flyer'
);
$flyer_pdf = lsc_events_import_media(
	'_build/media/events/LSC-Summer-Scheme-2019.pdf',
	'LSC Summer Scheme 2019 PDF'
);
$motto = lsc_events_import_media(
	'_build/media/events/LSCmotto.jpg',
	'LSC motto',
	'Lewisham Sports Consortium motto'
);

$banner_url    = esc_url( $banner['url'] );
$flyer_url     = esc_url( $flyer['url'] );
$flyer_pdf_url = esc_url( $flyer_pdf['url'] );
$motto_url     = esc_url( $motto['url'] );
$banner_id     = (int) $banner['id'];

$content = <<<HTML
<!-- wp:image {"id":{$banner_id},"sizeSlug":"full","linkDestination":"none","align":"full","className":"lsc-page-banner lsc-events-banner"} -->
<figure class="wp-block-image alignfull size-full lsc-page-banner lsc-events-banner"><img src="{$banner_url}" alt="Lewisham Sports Consortium Events banner" class="wp-image-{$banner_id}"/></figure>
<!-- /wp:image -->

<!-- wp:html -->
<div class="lsc-tabs lsc-events-tabs" data-lsc-tabs>
	<div class="lsc-tablist" role="tablist" aria-label="Events sections">
		<button class="lsc-tab" id="lsc-tab-holiday-events" type="button" role="tab" aria-selected="true" aria-controls="lsc-panel-holiday-events">Holiday &amp; summer events</button>
		<button class="lsc-tab" id="lsc-tab-sport-events" type="button" role="tab" aria-selected="false" aria-controls="lsc-panel-sport-events" tabindex="-1">Sport events &amp; ground hire</button>
	</div>

	<section class="lsc-tab-panel" id="lsc-panel-holiday-events" role="tabpanel" aria-labelledby="lsc-tab-holiday-events">
		<h2>Holiday &amp; Summer Events</h2>
		<p>We host regular sports events throughout the summer and also run an annual family day. Below are some of the sports activities that we host during these events:</p>
		<div class="lsc-events-activity-grid">
			<ul>
				<li>Family Fun day events</li>
				<li>2k sponsored fun run</li>
				<li>Youth performance stage</li>
				<li>Band performance</li>
				<li>Bouncy castle</li>
				<li>Skittles</li>
				<li>Egg and spoon race</li>
				<li>Netball</li>
			</ul>
			<ul>
				<li>Junior football competition</li>
				<li>Steel pan</li>
				<li>Street dance</li>
				<li>Dance troupe</li>
				<li>Barbecue and refreshments</li>
				<li>Badminton</li>
				<li>Sack race</li>
				<li>Badminton</li>
			</ul>
			<ul>
				<li>100 metres</li>
				<li>Sponge javelin</li>
				<li>Space hopper</li>
				<li>Quick cricket</li>
				<li>Face painting</li>
				<li>Crazy golf</li>
				<li>Tennis</li>
				<li>Volley demo</li>
			</ul>
		</div>
		<div class="lsc-events-flyer">
			<p><strong>To download the flyer, click on the image.</strong></p>
			<a href="{$flyer_pdf_url}" target="_blank" rel="noreferrer noopener"><img src="{$flyer_url}" alt="Lewisham Sports Consortium Summer Scheme 2019 flyer"></a>
		</div>
		<p class="lsc-events-cta">To book a stall at one of our summer or half term events, please <a href="/get-in-touch/">contact us</a>.</p>
	</section>

	<section class="lsc-tab-panel" id="lsc-panel-sport-events" role="tabpanel" aria-labelledby="lsc-tab-sport-events" hidden>
		<h2>Sports Events</h2>
		<h3>Saturday Football Programme</h3>
		<p><strong>Challenge:</strong> To use football as a way to make a difference and have a positive impact on people's lives, particularly children and young people.</p>
		<p><strong>Objectives:</strong> To develop the individual as a person. The coaching of football is undertaken within a framework that encourages and promotes:</p>
		<figure class="lsc-events-motto"><img src="{$motto_url}" alt="Lewisham Sports Consortium motto"></figure>
		<p>There is provision for those who are participating just for fun, skill development and healthy recreation, and for those who also wish to join competitive teams. Where no LSportsC team currently exists, we refer on to other community teams, particularly those that use pitches.</p>

		<h2>Pitch and Ground Hire</h2>
		<h3>Firhill Road Sports Ground has:</h3>
		<ul>
			<li>3 full-sized adult pitches</li>
			<li>1 junior pitch</li>
			<li>2 small-sided pitches</li>
		</ul>
		<p>These facilities are available for hire per match, unless another time period is specified. For other activities, please contact us.</p>
		<p>To hire a pitch, please contact us by the Tuesday prior to the date needed. Please specify pitch number, time and date desired. Pitch reservations are made on a first come, first served basis.</p>
		<p class="lsc-events-cta">For pricing and booking details, visit <a href="/book-the-grounds/">Book the grounds</a>.</p>
	</section>
</div>
<!-- /wp:html -->
HTML;

$events_page = get_page_by_path( 'events' );

if ( ! $events_page ) {
	WP_CLI::error( 'Could not find /events/ page.' );
}

$result = wp_update_post( array(
	'ID'           => $events_page->ID,
	'post_title'   => 'Events',
	'post_content' => $content,
	'post_status'  => 'publish',
), true );

if ( is_wp_error( $result ) ) {
	WP_CLI::error( $result );
}

WP_CLI::success( sprintf( 'Updated Events page #%d.', $events_page->ID ) );
