<?php
/**
 * Deprecated: pitch-use ground rules now live on the standalone Book the grounds
 * page. This helper is intentionally a no-op so old runbooks cannot reinsert
 * the rules into Get Involved.
 *
 * Usage (production server, from /opt/lsc-wordpress):
 *
 *   docker compose -f docker-compose.yml -f docker-compose.prod.yml \
 *     run --rm --entrypoint wp wpcli \
 *     eval-file /var/www/html/_build/scripts/insert-ground-rules.php \
 *     --path=/var/www/html
 */

WP_CLI::success( 'No changes made: pitch-use ground rules live on /book-the-grounds/.' );
