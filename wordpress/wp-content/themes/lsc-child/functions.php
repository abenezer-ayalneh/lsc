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
 * Custom favicon from the child theme — overrides the WP Site Icon setting.
 */
add_action( 'wp_head', function () {
	$dir = get_stylesheet_directory_uri() . '/assets/images';
	echo '<link rel="icon" type="image/x-icon" href="' . esc_url( $dir . '/favicon.ico' ) . '">' . "\n";
	echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( $dir . '/favicon-32x32.png' ) . '">' . "\n";
	echo '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url( $dir . '/favicon-16x16.png' ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( $dir . '/apple-touch-icon.png' ) . '">' . "\n";
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
	?>
	<footer class="lsc-footer" role="contentinfo">
		<div class="lsc-footer-inner">
			<div class="lsc-footer-col lsc-footer-brand">
				<p class="lsc-footer-wordmark"><span>Lewisham</span>Sports Consortium</p>
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
				<ul class="lsc-social">
					<li><a href="https://www.facebook.com/lsportsc" aria-label="Facebook" rel="noopener" target="_blank">
						<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.52 1.49-3.91 3.78-3.91 1.1 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.78-1.63 1.57v1.88h2.78l-.44 2.91h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94z"/></svg>
					</a></li>
					<li><a href="https://x.com/lsportsc" aria-label="X (Twitter)" rel="noopener" target="_blank">
						<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.66l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
					</a></li>
					<li><a href="https://www.instagram.com/lewishamlsportsc" aria-label="Instagram" rel="noopener" target="_blank">
						<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.43.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.43.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.43-.36-1.06-.41-2.23-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.43-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zm0 3.68a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32zm0 10.16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.41-10.4a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/></svg>
					</a></li>
					<li><a href="https://www.tiktok.com/@lewishamsportscon" aria-label="TikTok" rel="noopener" target="_blank">
						<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16.6 5.82a4.28 4.28 0 0 1-1.06-2.82h-3.3v13.05a2.59 2.59 0 0 1-2.59 2.46 2.59 2.59 0 1 1 .72-5.07v-3.36a5.9 5.9 0 0 0-.72-.05A5.95 5.95 0 1 0 15.6 16v-6.7a7.56 7.56 0 0 0 4.4 1.4V7.4a4.28 4.28 0 0 1-3.4-1.58z"/></svg>
					</a></li>
					<li><a href="https://youtube.com/@lsportsc-c3e" aria-label="YouTube" rel="noopener" target="_blank">
						<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M23.5 6.5a3.02 3.02 0 0 0-2.12-2.14C19.5 3.85 12 3.85 12 3.85s-7.5 0-9.38.51A3.02 3.02 0 0 0 .5 6.5C0 8.39 0 12 0 12s0 3.61.5 5.5a3.02 3.02 0 0 0 2.12 2.14c1.88.51 9.38.51 9.38.51s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14C24 15.61 24 12 24 12s0-3.61-.5-5.5zM9.6 15.57V8.43L15.82 12z"/></svg>
					</a></li>
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
