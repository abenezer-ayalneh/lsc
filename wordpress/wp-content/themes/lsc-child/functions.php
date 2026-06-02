<?php
/**
 * LSC Child theme functions.
 *
 * @package lsc-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the Kadence parent stylesheet, then the child stylesheet.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'kadence-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( get_template() )->get( 'Version' )
	);
	wp_enqueue_style(
		'lsc-child-style',
		get_stylesheet_uri(),
		array( 'kadence-parent-style' ),
		wp_get_theme()->get( 'Version' )
	);
} );

/**
 * Register navigation menu locations (in case the parent's are not used).
 */
add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'lsc-child' ),
		'footer'  => __( 'Footer Menu', 'lsc-child' ),
	) );
} );

/**
 * Register LSC block patterns and a dedicated pattern category so they are
 * easy to find in the editor inserter.
 */
add_action( 'init', function () {
	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category( 'lsc', array( 'label' => __( 'LSC', 'lsc-child' ) ) );
	}

	$patterns_dir = get_stylesheet_directory() . '/patterns';
	if ( ! is_dir( $patterns_dir ) || ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	foreach ( glob( $patterns_dir . '/*.php' ) as $file ) {
		$slug    = 'lsc/' . basename( $file, '.php' );
		$content = require $file; // each pattern file returns its block markup string.
		if ( is_string( $content ) ) {
			register_block_pattern( $slug, array(
				'title'      => ucwords( str_replace( '-', ' ', basename( $file, '.php' ) ) ),
				'categories' => array( 'lsc' ),
				'content'    => $content,
			) );
		}
	}
} );

/**
 * Expose the theme's logo + brand colours as a quick helper for templates.
 */
function lsc_logo_url() {
	return get_stylesheet_directory_uri() . '/assets/images/lsc-logo.png';
}

/**
 * Render a Mary's-style multi-column footer. Kadence's own footer is builder-
 * driven (and shows a "Kadence WP" credit), so we hide it via CSS and output
 * our own here: a navy brand column, an explore-links column, contact details,
 * social links and a legal bar with the charity number + Terms of Use.
 * See docs/adr/0001 (Mary's visual design) and docs/adr/0002 (Terms in footer).
 */
add_action( 'wp_footer', function () {
	$logo = lsc_logo_url();
	?>
	<footer class="lsc-footer" role="contentinfo">
		<div class="lsc-footer-inner">
			<div class="lsc-footer-col lsc-footer-brand">
				<img src="<?php echo esc_url( $logo ); ?>" alt="Lewisham Sports Consortium" />
				<p>Sport, recreation and opportunity for the Lewisham community.</p>
			</div>
			<div class="lsc-footer-col">
				<h4>Explore</h4>
				<ul>
					<li><a href="/who-are-we/">Who Are We</a></li>
					<li><a href="/get-involved/">Get Involved</a></li>
					<li><a href="/events/">Events</a></li>
					<li><a href="/media/">Media</a></li>
				</ul>
			</div>
			<div class="lsc-footer-col">
				<h4>Get in touch</h4>
				<ul>
					<li>Firhill Road Sports Ground</li>
					<li>140A Firhill Road, Bellingham</li>
					<li>London SE6 3SQ</li>
					<li><a href="tel:02086988273">0208 698 8273</a></li>
					<li><a href="mailto:info@lsportsc.org">info@lsportsc.org</a></li>
				</ul>
			</div>
			<div class="lsc-footer-col">
				<h4>Follow us</h4>
				<ul>
					<li><a href="https://www.facebook.com/lsportsc">Facebook</a></li>
					<li><a href="https://twitter.com/LSportsC">Twitter / X</a></li>
				</ul>
			</div>
		</div>
		<div class="lsc-footer-bar">
			<span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> Lewisham Sports Consortium &middot; Registered charity no. 1109468</span>
			<nav aria-label="Legal"><a href="/terms/">Terms of Use</a> &middot; <a href="/get-in-touch/">Contact</a></nav>
		</div>
	</footer>
	<?php
}, 5 );
