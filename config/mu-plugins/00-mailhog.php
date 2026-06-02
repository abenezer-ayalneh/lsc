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
