# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Local Docker dev environment for rebuilding the Lewisham Sports Consortium website (http://www.lsportsc.org) on WordPress. The project is a **like-for-like content rebuild — no new features** (see `PROJECT_PLAN.md` for full scope, sitemap, and milestones). The only code authored in this repo is the **Kadence child theme**; WordPress core, plugins, and the Kadence parent theme are pulled at container runtime, not committed.

## Commands

```bash
docker compose up -d                       # start the stack (first run auto-installs WP + Kadence)
docker compose down                        # stop, keep data
docker compose down -v && rm -rf wordpress # full reset (drops DB volume + WP files)
docker compose logs -f wpcli               # watch first-run install progress
docker compose logs -f wordpress           # tail site logs

# WP-CLI: the `wpcli` service's default entrypoint runs setup.sh (the installer)
# and ignores any command you append. Override it with `--entrypoint wp` to run
# ad-hoc commands. (The `wordpress` image itself does not include WP-CLI.)
docker compose run --rm --entrypoint wp wpcli <command> --path=/var/www/html
```

| Service    | URL                            | Login                       |
|------------|--------------------------------|-----------------------------|
| Site       | http://localhost:8080          | —                           |
| WP Admin   | http://localhost:8080/wp-admin | `admin` / `admin`           |
| phpMyAdmin | http://localhost:8081          | `lsc` / `lsc_pw`            |
| MailHog    | http://localhost:8025          | catches all outgoing mail   |

All ports/credentials live in `.env` (gitignored).

## Architecture

The stack is defined entirely in `docker-compose.yml` (5 services):

- **wordpress** — PHP 8.2 + Apache. Site files are bind-mounted to `./wordpress/` on the host, so edits to the child theme are live immediately. `WP_HOME`/`WP_SITEURL` are pinned via `WORDPRESS_CONFIG_EXTRA`.
- **db** — MariaDB 11; data persists in the `db_data` Docker volume.
- **wpcli** — a **run-once bootstrap**, not a long-running service. It runs `config/setup.sh`, then exits. The script is idempotent: it waits for `wp-config.php` and the DB, installs WP core (skips if already installed), sets pretty permalinks (`/%postname%/`), installs+activates Kadence, and deletes Hello Dolly / Akismet. Re-run it any time with `docker compose up wpcli`.
- **phpmyadmin** — DB GUI.
- **mailhog** — SMTP sink. WordPress is wired to it by the must-use plugin `config/mu-plugins/00-mailhog.php` (mounted into `wp-content/mu-plugins/`), which redirects all `phpmailer_init` mail to `mailhog:1025`. Nothing leaves the machine.

## Working with the child theme

- The child theme is the **only** versioned part of the WordPress install. `.gitignore` ignores all of `/wordpress/` **except** `wordpress/wp-content/themes/lsc-child/`. Build and commit the theme there; everything else is reproducible from the compose file.
- The child theme does not exist yet — it must be scaffolded under `wordpress/wp-content/themes/lsc-child/`.
- Build approach is block-editor-first (Gutenberg + reusable block patterns) on top of Kadence global styles, so a volunteer can edit content after handover. Avoid page builders.

## Scope guardrails

The redesign rebuilds **existing content only** as responsive pages. The visual design is a pixel-clone of marys.org.uk (mirrored at `reference/reference-site/`); Mary's palette/type supersedes the old LSC red/gold brand (see `docs/adr/0001` and `CONTEXT.md`). Page set (see `docs/adr/0002`): **Home, Who Are We, Get Involved, Events, Media, Get in Touch**. The pricing table (converted from the PDF) lives inside **Get Involved**; **Terms of Use** is a footer page. Booking forms, payments, donations, newsletter, blog, members area, and multilingual are explicitly **out of scope** (`PROJECT_PLAN.md` §8) — do not add them unless asked.
