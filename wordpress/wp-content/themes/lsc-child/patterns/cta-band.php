<?php
/**
 * LSC call-to-action band — Mary's-style full-width orange strip with a bold
 * uppercase heading and a pill button. See docs/adr/0001.
 *
 * @package lsc-child
 */

return '
<!-- wp:group {"align":"full","backgroundColor":"brand-orange","style":{"spacing":{"padding":{"top":"3.5rem","bottom":"3.5rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-brand-orange-background-color has-background" style="padding-top:3.5rem;padding-bottom:3.5rem">
<!-- wp:heading {"textAlign":"center","textColor":"white","fontSize":"x-large"} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color has-x-large-font-size">Want to hire the ground?</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","textColor":"white","fontSize":"medium"} -->
<p class="has-text-align-center has-white-color has-text-color has-medium-font-size">Pitches, training areas and full-day event hire — affordable rates for the local community.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"white","textColor":"brand-orange"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-brand-orange-color has-white-background-color has-text-color has-background wp-element-button" href="/get-involved/">See hire rates</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->';
