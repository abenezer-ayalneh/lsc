<?php
/**
 * LSC "What we offer" — Mary's-style grid of vibrantly-coloured tiles, each a
 * different brand colour. See docs/adr/0001.
 *
 * @package lsc-child
 */

$tiles = array(
	array( 'brand-blue',    'Pitch hire',      'Six football pitches — 3 adult, 1 junior and 2 small-sided — for matches and training.', 'See pitch hire', '/get-involved/' ),
	array( 'brand-green',   'Youth programme', 'Our &ldquo;Making a Difference&rdquo; work combines sport with education, mentoring and life skills.', 'About our work', '/who-are-we/' ),
	array( 'brand-magenta', 'Events',          'Fun days, play schemes and community events — full day or half day hire of the ground.', 'See events', '/events/' ),
	array( 'brand-orange',  'Get in touch',    'Booking, enquiries and how to find us at Firhill Road Sports Ground in Bellingham.', 'Contact us', '/get-in-touch/' ),
);

$cols = '';
foreach ( $tiles as $t ) {
	list( $color, $title, $body, $cta, $href ) = $t;
	$cols .= '
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"lsc-tile has-' . $color . '-background-color","backgroundColor":"' . $color . '","style":{"spacing":{"padding":{"top":"1.9rem","right":"1.7rem","bottom":"1.9rem","left":"1.7rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group lsc-tile has-' . $color . '-background-color has-background" style="padding:1.9rem 1.7rem"><!-- wp:heading {"level":3,"textColor":"white","fontSize":"large"} -->
<h3 class="wp-block-heading has-white-color has-text-color has-large-font-size">' . $title . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"white"} -->
<p class="has-white-color has-text-color">' . $body . '</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><a href="' . $href . '">' . $cta . ' &rarr;</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->';
}

return '
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"4.5rem","bottom":"4.5rem"}}},"backgroundColor":"smoke","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-smoke-background-color has-background" style="padding-top:4.5rem;padding-bottom:4.5rem">
<!-- wp:heading {"textAlign":"center","fontSize":"x-large"} -->
<h2 class="wp-block-heading has-text-align-center has-x-large-font-size">What we offer</h2>
<!-- /wp:heading -->
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">' . $cols . '</div>
<!-- /wp:columns --></div>
<!-- /wp:group -->';
