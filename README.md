# Lewisham Sports Consortium — Local WordPress (Docker)

Local development environment for the LSC website rebuild. Brings up WordPress,
MariaDB, phpMyAdmin, WP-CLI and MailHog. WordPress and the Kadence theme are
installed automatically on first run.

## Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (includes `docker compose`)

## Start

```bash
docker compose up -d
```

First run pulls images and auto-installs WordPress + Kadence (watch progress with
`docker compose logs -f wpcli`). Give it ~1–2 minutes, then open the site.

## URLs & credentials

| Service     | URL                          | Login                        |
|-------------|------------------------------|------------------------------|
| Site        | http://localhost:8080        | —                            |
| WP Admin    | http://localhost:8080/wp-admin | `admin` / `admin`          |
| phpMyAdmin  | http://localhost:8081        | `lsc` / `lsc_pw` (or root)   |
| MailHog     | http://localhost:8025        | — (all outgoing mail lands here) |

All credentials and ports are defined in `.env` — change them there if needed.

## Everyday commands

```bash
docker compose up -d        # start
docker compose down         # stop (keeps data)
docker compose logs -f wordpress   # tail logs

# Run any WP-CLI command. The `wpcli` service's default entrypoint runs the
# installer, so override it with `--entrypoint wp` for ad-hoc commands:
docker compose run --rm --entrypoint wp wpcli plugin list --path=/var/www/html
```

## Reset everything

```bash
docker compose down -v   # also drops the database volume
rm -rf wordpress         # removes WordPress core/uploads
docker compose up -d     # fresh install
```

## How it fits together
- **wordpress** — PHP 8.2 + Apache; site files live on disk in `./wordpress/`.
- **db** — MariaDB 11; data persists in the `db_data` Docker volume.
- **wpcli** — runs `config/setup.sh` once to install WP + Kadence, then exits.
- **phpmyadmin** — database GUI.
- **mailhog** — catches all email so contact-form testing never sends real mail
  (configured via `config/mu-plugins/00-mailhog.php`).

## Child theme & Git
`.gitignore` excludes the WordPress install **except** the child theme at
`wordpress/wp-content/themes/lsc-child/`. Build the Kadence child theme there and
commit it; the rest of WP is reproducible from this compose file.

> Production note: these credentials and the `WORDPRESS_DEBUG` flag are for local
> dev only. LSC owns the real hosting/domain (see `PROJECT_PLAN.md` §7).
