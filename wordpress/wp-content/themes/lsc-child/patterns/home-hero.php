<?php
/**
 * LSC home hero — Mary's-style full-width band: bold uppercase League Spartan
 * headline over a solid navy field (neutral placeholder, no photo yet) with
 * pill CTAs. See docs/adr/0001.
 *
 * @package lsc-child
 */

return '
<!-- wp:cover {"overlayColor":"brand-navy","minHeight":520,"className":"lsc-hero","align":"full","style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}}} -->
<div class="wp-block-cover alignfull lsc-hero" style="padding-top:5rem;padding-bottom:5rem;min-height:520px"><span aria-hidden="true" class="wp-block-cover__background has-brand-navy-background-color has-background-dim-100 has-background-dim"></span><div class="wp-block-cover__inner-container">
<!-- wp:paragraph {"align":"center","textColor":"brand-yellow","fontSize":"medium","style":{"typography":{"textTransform":"uppercase","letterSpacing":"2px","fontWeight":"700"}}} -->
<p class="has-text-align-center has-brand-yellow-color has-text-color has-medium-font-size" style="font-weight:700;letter-spacing:2px;text-transform:uppercase">Lewisham Sports Consortium</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"textAlign":"center","level":1,"textColor":"white","fontSize":"huge"} -->
<h1 class="wp-block-heading has-text-align-center has-white-color has-text-color has-huge-font-size">Sport, recreation and opportunity for the Lewisham community</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","textColor":"white","fontSize":"large"} -->
<p class="has-text-align-center has-white-color has-text-color has-large-font-size">A volunteer-run charity providing safe, accessible and affordable football pitches and youth activities at Firhill Road Sports Ground.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"brand-orange","textColor":"white"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-brand-orange-background-color has-text-color has-background wp-element-button" href="/get-involved/">Hire the ground</a></div>
<!-- /wp:button -->
<!-- wp:button {"backgroundColor":"white","textColor":"brand-navy"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-brand-navy-color has-white-background-color has-text-color has-background wp-element-button" href="/who-are-we/">Who we are</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div></div>
<!-- /wp:cover -->';
