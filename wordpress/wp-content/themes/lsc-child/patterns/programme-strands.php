<?php
/**
 * LSC "Our Programme" — Mary's-style series of full-width alternating colour
 * strands. Each strand: a colour band with a placeholder image on one side and
 * a heading + intro + "What's included" list on the other; image side
 * alternates left/right down the page. See docs/adr/0001.
 *
 * @package lsc-child
 */

$img_base = get_stylesheet_directory_uri() . '/assets/images/';

$strands = array(
	array(
		'brand-blue', 'Football &amp; pitch hire',
		'Six pitches at Firhill Road Sports Ground — kept affordable so local teams of every age can play.',
		array( '3 full-sized adult pitches', '1 junior pitch', '2 small-sided pitches', 'Training areas for juniors and adults' ),
		'grounds-tractor.jpg', 'The pitches at Firhill Road Sports Ground',
	),
	array(
		'brand-green', 'Making a Difference — youth programme',
		'Sport is the way in; opportunity is the goal. We combine activity with education and mentoring for young people.',
		array( 'GCSE &amp; SAT prep — English, Maths, Science', 'Social and cultural education workshops', 'One-to-one mentorship', 'Encouragement into enterprise' ),
		'youth-activity.jpg', 'Young people taking part in a youth activity session',
	),
	array(
		'brand-magenta', 'Events &amp; play schemes',
		'The ground comes alive with community events and holiday activity for local young people.',
		array( 'Fun days and community events', 'Summer holiday play schemes', 'Full-day and half-day hire', 'Saturday football programme' ),
		'youth-team-trophies.jpg', 'A youth team celebrating with trophies',
	),
);

$bands = '';
$i = 0;
foreach ( $strands as $s ) {
	list( $color, $title, $intro, $items, $image, $alt ) = $s;
	$image_first = ( 0 === $i % 2 );
	$img_url     = esc_url( $img_base . $image );

	$li = '';
	foreach ( $items as $it ) {
		$li .= '<!-- wp:list-item --><li>' . $it . '</li><!-- /wp:list-item -->' . "\n";
	}

	$image_col = '
<!-- wp:column {"width":"42%"} -->
<div class="wp-block-column" style="flex-basis:42%"><!-- wp:cover {"url":"' . $img_url . '","dimRatio":0,"minHeight":360,"className":"lsc-strand-photo","style":{"border":{"radius":"18px"}}} -->
<div class="wp-block-cover lsc-strand-photo" style="border-radius:18px;min-height:360px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background" alt="' . esc_attr( $alt ) . '" src="' . $img_url . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->';

	$text_col = '
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:heading {"textColor":"white","fontSize":"x-large"} -->
<h2 class="wp-block-heading has-white-color has-text-color has-x-large-font-size">' . $title . '</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"white","fontSize":"medium"} -->
<p class="has-white-color has-text-color has-medium-font-size">' . $intro . '</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"textColor":"white","style":{"typography":{"textTransform":"uppercase","fontWeight":"700","letterSpacing":"1px"}}} -->
<p class="has-white-color has-text-color" style="font-weight:700;letter-spacing:1px;text-transform:uppercase">What&rsquo;s included</p>
<!-- /wp:paragraph -->
<!-- wp:list {"className":"lsc-strand-list"} -->
<ul class="wp-block-list lsc-strand-list">' . "\n" . $li . '</ul>
<!-- /wp:list --></div>
<!-- /wp:column -->';

	$cols = $image_first ? ( $image_col . $text_col ) : ( $text_col . $image_col );

	$bands .= '
<!-- wp:group {"align":"full","backgroundColor":"' . $color . '","style":{"spacing":{"padding":{"left":"2rem","right":"2rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-' . $color . '-background-color has-background" style="padding-left:2rem;padding-right:2rem"><!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">' . $cols . '</div>
<!-- /wp:columns --></div>
<!-- /wp:group -->';
	$i++;
}

return '
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"4rem","bottom":"1rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:4rem;padding-bottom:1rem">
<!-- wp:heading {"textAlign":"center","fontSize":"x-large"} -->
<h2 class="wp-block-heading has-text-align-center has-x-large-font-size">Our programme</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"medium"} -->
<p class="has-text-align-center has-medium-font-size">Affordable sport, youth opportunity and community events — all in one place in Bellingham.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->' . $bands;
