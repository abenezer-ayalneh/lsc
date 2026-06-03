<?php
/**
 * LSC "Latest" — Mary's-style "News & blogs" card grid. Each card has a
 * placeholder image cap, a title and a short standfirst. Content is illustrative
 * LSC news until the client supplies real updates. See docs/adr/0001.
 *
 * @package lsc-child
 */

$img_base = get_stylesheet_directory_uri() . '/assets/images/';

$cards = array(
	array( 'brand-navy',    'Our public consultation', 'Aerial views, location plans and Phase 1 proposals for the future of Firhill Road Sports Ground.', '/media/', 'partnership-meeting.jpg', 'Consultation meeting about the future of the ground' ),
	array( 'brand-teal',    'Saturday football is back', 'Adult, junior and small-sided fixtures across the season — find out how your team can take part.', '/events/', 'grounds-maintenance.jpg', 'The football pitches at Firhill Road Sports Ground' ),
	array( 'brand-yellow',  'Summer play scheme', 'Sport, games and activities for local young people over the school holidays.', '/events/', 'heritage-display.jpg', 'Local people at a community event' ),
);

$cols = '';
foreach ( $cards as $c ) {
	list( $color, $title, $body, $href, $image, $alt ) = $c;
	$img_url = esc_url( $img_base . $image );
	$cols .= '
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"lsc-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group lsc-card"><!-- wp:cover {"url":"' . $img_url . '","dimRatio":0,"minHeight":220,"className":"lsc-card-media"} -->
<div class="wp-block-cover lsc-card-media" style="min-height:220px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background" alt="' . esc_attr( $alt ) . '" src="' . $img_url . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"></div></div>
<!-- /wp:cover -->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"1.5rem","right":"1.5rem","bottom":"1.5rem","left":"1.5rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding:1.5rem"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size">' . $title . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . $body . '</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><a href="' . $href . '">Read more &rarr;</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->';
}

return '
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"4.5rem","bottom":"4.5rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:4.5rem;padding-bottom:4.5rem">
<!-- wp:heading {"textAlign":"center","fontSize":"x-large"} -->
<h2 class="wp-block-heading has-text-align-center has-x-large-font-size">Latest</h2>
<!-- /wp:heading -->
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">' . $cols . '</div>
<!-- /wp:columns --></div>
<!-- /wp:group -->';
