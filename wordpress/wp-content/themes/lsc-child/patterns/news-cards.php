<?php
/**
 * LSC "Latest" — Mary's-style "News & blogs" card grid. Each card has a
 * placeholder image cap, a title and a short standfirst. Content is illustrative
 * LSC news until the client supplies real updates. See docs/adr/0001.
 *
 * @package lsc-child
 */

$cards = array(
	array( 'brand-navy',    'Our public consultation', 'Aerial views, location plans and Phase 1 proposals for the future of Firhill Road Sports Ground.', '/media/' ),
	array( 'brand-teal',    'Saturday football is back', 'Adult, junior and small-sided fixtures across the season — find out how your team can take part.', '/events/' ),
	array( 'brand-yellow',  'Summer play scheme', 'Sport, games and activities for local young people over the school holidays.', '/events/' ),
);

$cols = '';
foreach ( $cards as $c ) {
	list( $color, $title, $body, $href ) = $c;
	$cols .= '
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"lsc-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group lsc-card"><!-- wp:group {"className":"lsc-card-media has-' . $color . '-background-color","backgroundColor":"' . $color . '","style":{"spacing":{"padding":{"top":"3rem","bottom":"3rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group lsc-card-media has-' . $color . '-background-color has-background" style="padding-top:3rem;padding-bottom:3rem"><!-- wp:paragraph {"align":"center","textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color">Photo</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
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
