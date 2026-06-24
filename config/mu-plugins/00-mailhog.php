<?php
/**
 * Must-Use plugin: route all outgoing WordPress email to MailHog.
 * Local dev only — nothing leaves your machine. View mail at http://localhost:8025
 */
add_action( 'phpmailer_init', function ( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'mailhog';
    $phpmailer->Port       = 1025;
    $phpmailer->SMTPAuth   = false;
    $phpmailer->SMTPSecure = '';
    $phpmailer->SMTPAutoTLS = false;
} );

// WordPress's default From on this stack is wordpress@localhost — PHPMailer
// rejects it (no dotted domain), so wp_mail() throws *before* the reroute above
// ever runs and the message is silently dropped. Pin a valid default From so
// every generic WP mail (password resets, admin notices, plugin mail without an
// explicit From header) actually reaches MailHog. Plugins that set their own
// From header (e.g. Forminator) are unaffected and keep their address.
add_filter( 'wp_mail_from', function ( $from ) {
    return ( ! $from || str_ends_with( $from, '@localhost' ) ) ? 'no-reply@lsportsc.org' : $from;
} );
add_filter( 'wp_mail_from_name', function ( $name ) {
    return $name ?: 'Lewisham Sports Consortium';
} );
