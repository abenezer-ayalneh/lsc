# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## What this is

Local Docker dev environment for rebuilding the Lewisham Sports Consortium website (http://www.lsportsc.org) on WordPress. The project is a **like-for-like content rebuild — no new features** (see `PROJECT_PLAN.md` for full scope, sitemap, and milestones). Authored assets in this repo are the **Kadence child theme**, reproducibility scripts/seed templates, the canonical DB snapshot, and tracked media uploads; WordPress core, plugins, and the Kadence parent theme are pulled at container runtime, not committed.

## Commands

```bash
docker compose up -d                       # start the stack (first run auto-installs WP + Kadence)
docker compose down                        # stop, keep data
docker compose down -v && git clean -fdX wordpress # full reset of DB + ignored WP runtime files
docker compose logs -f wpcli               # watch first-run install progress
docker compose logs -f wordpress           # tail site logs
scripts/snapshot-admin-content.sh          # export WP Admin content before AI/code work
scripts/check-content-safety.sh            # guard against accidental seed-template overwrites

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

- The child theme is the only authored theme code. `.gitignore` ignores WordPress runtime files except the child theme, build reproducibility assets, the DB snapshot, and tracked media uploads.
- Build approach is block-editor-first (Gutenberg + reusable block patterns) on top of Kadence global styles, so a volunteer can edit content after handover. Avoid page builders.

## DB-snapshot-first content workflow

- After the initial build, `wordpress/_build/db/lsc-db.sql` is the canonical source for page content, menus, widgets, options, and forms. WP Admin edits win over seed templates.
- Before making AI/code changes, run `scripts/snapshot-admin-content.sh` unless the user explicitly says there were no WP Admin edits. Commit the DB snapshot and any changed `wordpress/wp-content/uploads/` files together.
- AI content changes must be made through local WordPress/WP-CLI and then exported. Do **not** edit `wordpress/_build/db/lsc-db.sql` directly.
- `wordpress/_build/pages/*.html`, `wordpress/_build/footer.html`, and the page-upsert path in `wordpress/_build/build.sh` are **seed templates** only. Do not edit them for normal content changes. Intentional seed changes require `[seed-content]` in the commit message or `LSC_ALLOW_SEED_CONTENT=1` for the safety check.
- `wordpress/_build/build.sh` refuses to overwrite existing pages unless `LSC_ALLOW_SEED_REBUILD=1` is set. Prefer `wordpress/_build/import-db.sh` for restoring content.

## Scope guardrails

The redesign rebuilds **existing content only** as responsive pages, except for explicitly requested form additions documented in ADRs. The visual design is a pixel-clone of marys.org.uk (mirrored at `reference/reference-site/`); Mary's palette/type supersedes the old LSC red/gold brand (see `docs/adr/0001` and `CONTEXT.md`). Page set (see `docs/adr/0002`): **Home, Who Are We, Get Involved, Events, About, Get in Touch**. The pricing table (converted from the PDF), pitch-use ground rules, and the **Booking Hire Agreement** form (web equivalent of `LSC-000`) live on standalone **Book the grounds** at `/book-the-grounds/` (see `docs/adr/0003`); this page is linked from content, not primary nav. The **Volunteer Application** form lives on standalone **Become a Volunteer** at `/become-a-volunteer/` (see `docs/adr/0006`); it is linked from Get Involved, not primary nav. **Terms of Use** is a footer page. Payments, donations, newsletter, blog, members area, and multilingual are explicitly **out of scope** (`PROJECT_PLAN.md` §8) — do not add them unless asked.
