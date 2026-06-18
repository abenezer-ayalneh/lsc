<?php
/**
 * Insert "Ground rules" section into the Get Involved page.
 * Idempotent — skips if the heading already exists.
 * Inserts before the CTA band pattern; falls back to appending at end.
 *
 * Usage (production server, from /opt/lsc-wordpress):
 *
 *   docker compose -f docker-compose.yml -f docker-compose.prod.yml \
 *     run --rm --entrypoint wp wpcli \
 *     eval-file /var/www/html/_build/scripts/insert-ground-rules.php \
 *     --path=/var/www/html
 */

$page = get_page_by_path( 'get-involved' );
if ( ! $page ) {
    WP_CLI::error( "Page 'get-involved' not found." );
}

$current = $page->post_content;

if ( strpos( $current, '>Ground rules</h2>' ) !== false ) {
    WP_CLI::success( 'Ground rules section already present — nothing to do.' );
    return;
}

$section = <<<'HTML'

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"3rem","bottom":"3.5rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:3rem;padding-bottom:3.5rem">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Ground rules</h2>
<!-- /wp:heading -->
<!-- wp:list {"ordered":true} -->
<ol class="wp-block-list">
<!-- wp:list-item -->
<li>Our gates open to the general public at 9.30 AM. If you arrive early, please do not block our gate.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>On arrival, Home and Opponent Team Managers need to sign in (from the car park, up the ramp to the White Cabin).</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Under no circumstances are footballs to be retrieved from over the fence of the railway line. We will be fined by Railtrack for unauthorised access. Please make sure that your team&#8217;s name is on your balls so that when they are thrown back over, you can reclaim them from lost property.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Please do not warm-up on the pitches &#8211; use the designated warm-up areas.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Dispose of all rubbish in the bins provided around grounds.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>No dogs are permitted on the site; please ensure that your spectators and opponents are made aware of this.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>There are toilets &#8211; we are overlooked by houses &#8211; no urinating outside. NB this is the most frequent complaint from our neighbours.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>We offer limited parking on site for users. Vehicles and contents are left at the owner&#8217;s or driver&#8217;s risk. Please be mindful of our neighbours if you have to street park. Do not block their driveways.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Skateboards, roller skates, scooters and e-scooters are not allowed to be ridden once through our gates.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Bicycle riders are to dismount on entering the gates and lock their bikes in the designated areas. Bicycles are not allowed beside our pitches.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Verbal and/or physical abuse will not be tolerated (i.e. no swearing, foul, abusive, or racist language) &#8211; the Police will be called if deemed necessary.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>When recording in public, you need to get permission from a member of staff. Individuals have the right to privacy and respect, and you must be aware of local laws and regulations regarding filming in public spaces. In general:
<!-- wp:list -->
<ul class="wp-block-list">
<!-- wp:list-item -->
<li><strong>Do:</strong> get permission from those around you before filming; keep footage for personal and non-commercial use only; keep recordings to a reasonable length.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>Don&#8217;t:</strong> use body cameras or other recording devices to intrude on people&#8217;s privacy; do not film in private areas such as restaurants, cinemas and gyms; do not film someone without their knowledge or consent.</li>
<!-- /wp:list-item -->
</ul>
<!-- /wp:list --></li>
<!-- /wp:list-item -->
</ol>
<!-- /wp:list -->
</div>
<!-- /wp:group -->
HTML;

$cta = '<!-- wp:pattern {"slug":"lsc/cta-band"} /-->';
if ( strpos( $current, $cta ) !== false ) {
    $updated = str_replace( $cta, $section . "\n\n" . $cta, $current );
} else {
    $updated = rtrim( $current ) . $section;
}

$result = wp_update_post( [
    'ID'           => $page->ID,
    'post_content' => $updated,
], true );

if ( is_wp_error( $result ) ) {
    WP_CLI::error( 'Update failed: ' . $result->get_error_message() );
} else {
    WP_CLI::success( "Done &#8212; ground rules added to 'get-involved' (post #{$page->ID})." );
}
