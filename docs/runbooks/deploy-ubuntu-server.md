## Runbook: Deploy LSC WordPress on Ubuntu Server

**Owner:** LSC project team / deploy operator | **Frequency:** As Needed (go-live + updates)
**Last Updated:** 2026-06-24 | **Last Run:** —

### Purpose

Deploy the Lewisham Sports Consortium WordPress rebuild to an Ubuntu VPS using the repo's Docker Compose stack. Use this runbook for initial go-live and for subsequent theme/content updates. The site runs WordPress + MariaDB in containers; **your existing Caddy installation** on the host terminates TLS and reverse-proxies to WordPress on `127.0.0.1:8080`.

### Prerequisites

- [x] Ubuntu 22.04 or 24.04 LTS VPS with root or sudo SSH access
- [x] **Caddy already installed, running, and listening on ports 80/443** on this server
- [x] Domain DNS: `A` record for `www.lsportsc.org` (and optionally apex `lsportsc.org`) pointing to the server public IP
- [x] Git access to this repository
- [x] Docker Engine and Docker Compose plugin installed on the server
- [x] Strong passwords generated (`openssl rand -base64 32`) for DB root, DB user, and WP admin
- [x] (Optional, Flow B only) Local or staging environment with final content ready to export

### Production vs local checklist


| Setting              | Local dev                            | Production                                                              |
| -------------------- | ------------------------------------ | ----------------------------------------------------------------------- |
| Compose command      | `docker compose --profile dev up -d` | `docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d` |
| `WORDPRESS_DEBUG`    | `1`                                  | `0`                                                                     |
| MailHog mu-plugin    | mounted (`config/mu-plugins/`)       | **not mounted** (`config/mu-plugins-prod/`)                             |
| phpMyAdmin / MailHog | `--profile dev`                      | **not started**                                                         |
| WP port exposure     | `0.0.0.0:8080`                       | `127.0.0.1:8080` + Caddy TLS                                            |
| Credentials          | dev placeholders in `.env.example`   | strong, unique in server `.env`                                         |
| Email                | MailHog sink                         | real SMTP (configure post-go-live; see Step 9)                          |


---

### Procedure

#### Step 1: Prepare the Ubuntu server

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y git ufw
```

**Expected result:** Packages install without errors. Caddy is **not** installed here — it should already be on the server.
**If it fails:** Check `apt` output for disk space or mirror errors; retry after `sudo apt --fix-broken install`.

Confirm Caddy is running before continuing:

```bash
sudo systemctl status caddy
```

**Expected result:** Caddy service is `active (running)`.
**If it fails:** Install or start Caddy first, or fix your existing Caddy setup before deploying WordPress.

#### Step 2: Install Docker Engine

```bash
sudo apt install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
sudo usermod -aG docker "$USER"
```

Log out and back in so the `docker` group applies, then verify:

```bash
docker compose version
```

**Expected result:** Docker Compose v2 prints a version string.
**If it fails:** Confirm your user is in the `docker` group (`groups`) or prefix commands with `sudo`.

#### Step 3: Configure firewall

Skip this step if UFW is already configured for your server and Caddy.

```bash
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

**Expected result:** UFW is active; ports 22, 80, and 443 are allowed. Port 8080 is **not** exposed publicly.
**If it fails:** Ensure SSH (port 22) is allowed **before** enabling UFW to avoid locking yourself out.

#### Step 4: Clone the repository

```bash
sudo mkdir -p /home/lsc
sudo chown "$USER":"$USER" /home/lsc
git clone <REPO_URL> /home/lsc
cd /home/lsc
```

Replace `<REPO_URL>` with the actual Git remote URL.
**Expected result:** Repo files appear under `/home/lsc`, including `docker-compose.yml` and `docker-compose.prod.yml`.
**If it fails:** Verify Git credentials/SSH keys and network access to the remote.

#### Step 5: Create production environment file

```bash
cd /home/lsc
cp .env.production.example .env
nano .env   # or vim — set all change_me values and confirm WP_SITE_URL
```

Set at minimum:

- `MYSQL_ROOT_PASSWORD`, `MYSQL_PASSWORD`, `WP_ADMIN_PASSWORD` — unique strong values
- `WP_SITE_URL=https://www.lsportsc.org` (your live HTTPS URL, no trailing slash — must match the Caddy site block)
- `WP_ADMIN_EMAIL=info@lsportsc.org` (or the LSC contact address)

**Expected result:** `.env` exists on the server with no `change_me` placeholders remaining.
**If it fails:** Never commit `.env`; it is gitignored. Regenerate passwords if accidentally exposed.

#### Step 6: Start the production stack

```bash
cd /home/lsc
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f wpcli
```

Wait until you see `[setup] ✅ Done.` (typically 1–2 minutes on first run). Press `Ctrl+C` to stop tailing logs.

**Expected result:** Containers `lsc_db` and `lsc_wordpress` are running; `lsc_wpcli` exits successfully. WordPress core and Kadence are installed; `lsc-child` theme is activated if present.
**If it fails:**

- `wp-config.php never appeared` — check `docker compose ... logs wordpress` for DB connection errors.
- Kadence install warning — ensure the server has outbound internet; re-run setup: `docker compose ... up wpcli`.
- Port conflict on 8080 — change `WP_PORT` in `.env` and update the Caddy `reverse_proxy` upstream to match.

Verify WordPress responds locally:

```bash
curl -sI http://127.0.0.1:8080 | head -n1
```

**Expected result:** `HTTP/1.1 200 OK` or `301`/`302` redirect.

#### Step 7: Build site content (greenfield — Flow A)

```bash
cd /home/lsc
docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint sh wpcli /var/www/html/_build/build.sh
```

**Expected result:** Script prints `[build] ✅ Done` and creates pages (Home, Who Are We, Get Involved, Events, Media, Get in Touch, Terms of Use), menus, and media imports.
**If it fails because LSC pages already exist:** Use `import-db.sh` instead, or set `LSC_ALLOW_SEED_REBUILD=1` only when deliberately overwriting page content from seed templates. For other failures, check that `wordpress/wp-content/themes/lsc-child/` exists in the clone.

Skip this step if you are promoting an existing database (Flow B — see Appendix A).

#### Step 8: Add the site to Caddy

Use the example site block in [`config/caddy/lsc.caddyfile.example`](../../config/caddy/lsc.caddyfile.example). Caddy obtains and renews TLS certificates automatically — no Certbot step.

**Option A — import snippet (recommended if you already use a modular Caddyfile):**

```bash
cd /home/lsc
sudo mkdir -p /etc/caddy/sites
sudo cp config/caddy/lsc.caddyfile.example /etc/caddy/sites/lsc.caddyfile
```

Edit `/etc/caddy/sites/lsc.caddyfile` — replace `www.lsportsc.org, lsportsc.org` with your domain and confirm `reverse_proxy 127.0.0.1:8080` matches `WP_PORT` in `.env`.

Ensure your main `/etc/caddy/Caddyfile` imports site snippets (add this line once if missing):

```
import /etc/caddy/sites/*.caddyfile
```

**Option B — append directly to your existing Caddyfile:**

```bash
cd /home/lsc
sudo tee -a /etc/caddy/Caddyfile < config/caddy/lsc.caddyfile.example
```

Edit `/etc/caddy/Caddyfile` to replace the domain names if needed.

Validate and reload Caddy:

```bash
sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

**Expected result:** `validate` reports config is valid; `https://www.lsportsc.org` loads with a valid certificate (Caddy provisions it on first request).
**If it fails:**

- `502 Bad Gateway` — WordPress container is not running or `WP_PORT` mismatch; check Step 6.
- Certificate errors — confirm DNS A records propagate (`dig www.lsportsc.org +short`) and ports 80/443 reach Caddy.
- Config conflict — another site block may already claim the same domain; merge or remove the duplicate.

#### Step 9: Post-go-live email (optional)

WordPress PHP `mail()` is unreliable on most VPS hosts. When LSC provides SMTP credentials, install and configure an SMTP plugin (e.g. WP Mail SMTP) via WP Admin. Do **not** mount `config/mu-plugins/00-mailhog.php` in production — MailHog is local-dev only.

**Expected result:** Test emails from contact forms reach real inboxes.
**If it fails:** Check MailHog is not running in production (`docker compose ... ps` should not list mailhog).

---

### Verification

- [ ] `https://www.lsportsc.org` loads over HTTPS with no mixed-content warnings
- [ ] Primary nav shows: Home, Who Are We, Get Involved, Events, Media, Get in Touch
- [ ] Footer link to Terms of Use works
- [ ] `https://www.lsportsc.org/wp-admin` login succeeds with production admin credentials
- [ ] Pretty permalinks work (visit a sub-page directly, not only via nav)
- [ ] `docker compose -f docker-compose.yml -f docker-compose.prod.yml ps` shows `lsc_wordpress` and `lsc_db` up; no `mailhog` or `phpmyadmin`
- [ ] Port 8080 is not publicly reachable: `curl -sI http://<SERVER_PUBLIC_IP>:8080` should fail or time out
- [ ] `sudo caddy validate --config /etc/caddy/Caddyfile` passes after any Caddy changes

---

### Updating the site (routine deploy)

Content lives in the committed DB snapshot after the initial build:

- **Theme code** (`lsc-child/` — `style.css`, `functions.php`, `assets/`) is bind-mounted, so it is **live the moment you pull** (a container restart at most). No rebuild needed.
- **Page content, menus, widgets, options, and the Forminator booking form** live in the **WordPress database**. Deploys restore the committed snapshot at `wordpress/_build/db/lsc-db.sql`; WP Admin edits must be made locally, exported, committed, and deployed.

**Always back up the production DB before importing** — `import-db.sh` overwrites the entire database (see Rollback):

```bash
cd /home/lsc
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
  run --rm --entrypoint wp wpcli db export - --path=/var/www/html > "backup-$(date +%F).sql"
```

Pull the latest code, seed templates, DB snapshot, and tracked uploads, then bring the stack up:

```bash
git pull
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

Restore the committed content snapshot (pages, menus, **and the Forminator booking form**). `import-db.sh` rewrites the `__LSC_SITE_URL__` token to your `WP_SITE_URL` automatically:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
  run --rm --entrypoint sh wpcli /var/www/html/_build/import-db.sh
```

> **Use `import-db.sh`, not `build.sh`, for content.** `build.sh` seeds pages from HTML seed templates and now refuses to overwrite existing pages unless `LSC_ALLOW_SEED_REBUILD=1` is set. The DB snapshot (`wordpress/_build/db/lsc-db.sql`) is canonical after the first build.

**Caveats:**

- **`import-db.sh` is a one-way push (local → prod).** It replaces the whole production database, so any content edited directly in production wp-admin (and any form submissions stored there) is wiped and replaced by the snapshot. The backup above is your safety net. This matches the intended workflow: author locally → `scripts/snapshot-admin-content.sh` or `export-db.sh` → commit → `import-db.sh` on prod.
- **Media files are not in the DB snapshot, but uploads are tracked in git.** Commit changed files under `wordpress/wp-content/uploads/` alongside the DB snapshot. Plugin runtime upload dirs remain ignored.

Theme-only CSS/PHP changes under `lsc-child/` are live immediately via the bind mount; no rebuild or import needed unless pages reference new assets or DB content.

---

### Troubleshooting


| Symptom                                       | Likely Cause                                        | Fix                                                                                                                               |
| --------------------------------------------- | --------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| `502 Bad Gateway` from Caddy                  | WordPress container down or wrong upstream port     | `docker compose ... ps`; restart stack; match `WP_PORT` in `.env` and Caddy `reverse_proxy`                                       |
| Mixed content / wrong URLs                    | `WP_SITE_URL` mismatch or HTTP in DB                | Set correct URL in `.env`; run `wp search-replace 'http://old' 'https://new' --all-tables` via wpcli                              |
| TLS certificate not issued                    | DNS not pointing at server, or port 80 blocked       | Check `dig` output and UFW; review `journalctl -u caddy`                                                                          |
| Caddy fails to reload                         | Syntax error or duplicate site block                | `sudo caddy validate --config /etc/caddy/Caddyfile`; fix the reported line                                                       |
| `[setup] ERROR: wp-config.php never appeared` | DB not healthy or wordpress container crashed       | Check `docker compose ... logs db wordpress`                                                                                      |
| Blank theme / no styles                       | Kadence or lsc-child not activated                  | Re-run `docker compose ... up wpcli` or activate theme via WP Admin                                                               |
| Kadence install warning                       | No outbound internet from container                 | Fix server networking; re-run wpcli setup                                                                                         |
| Admin login fails after promote               | Imported DB has different admin hash                | Reset password: `docker compose ... run --rm --entrypoint wp wpcli user update admin --user_pass='NEW_PASS' --path=/var/www/html` |
| Upload failures                               | Caddy body size limit                               | Confirm `request_body { max_size 64MB }` in the site block                                                                        |


---

### Rollback

**Before any major change**, take a backup:

```bash
cd /home/lsc
docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint wp wpcli db export - --path=/var/www/html > "backup-$(date +%F).sql"
tar czf "uploads-$(date +%F).tar.gz" wordpress/wp-content/uploads/
```

**To roll back code:**

```bash
cd /home/lsc
git checkout <PREVIOUS_TAG_OR_COMMIT>
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint sh wpcli /var/www/html/_build/import-db.sh
```

**To roll back database:**

```bash
cd /home/lsc
docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint sh wpcli sh -c 'wp db import - --path=/var/www/html' < backup-YYYY-MM-DD.sql
```

**To remove the Caddy site block:**

```bash
sudo rm /etc/caddy/sites/lsc.caddyfile   # if using Option A
sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

**Nuclear reset** (destroys all site data — use only on a fresh server):

```bash
cd /home/lsc
docker compose -f docker-compose.yml -f docker-compose.prod.yml down -v
git clean -fdX wordpress
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
# Re-run Steps 6–8
```

---

### Escalation


| Situation                        | Contact              | Method                                          |
| -------------------------------- | -------------------- | ----------------------------------------------- |
| Hosting/domain/DNS ownership     | LSC hosting owner    | Per PROJECT_PLAN.md — LSC owns hosting accounts |
| Content sign-off or copy changes | LSC content owner    | Project point of contact                        |
| Caddy or server access issues    | VPS provider support | Provider ticket/console                         |


---

### Appendix A: Promote from local/staging (Flow B)

Use when final content was built locally and should be promoted through the committed DB snapshot instead of re-running `build.sh`.

**On local machine:**

```bash
cd /path/to/lewisham-sports-consortium
scripts/snapshot-admin-content.sh
git add wordpress/_build/db/lsc-db.sql wordpress/wp-content/uploads
git commit -m "Snapshot wp-admin content"
git push
```

**On server** (after Steps 1–6 complete):

```bash
cd /home/lsc
git pull
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
  run --rm --entrypoint sh wpcli /var/www/html/_build/import-db.sh
```

**Expected result:** Production site mirrors local content and media.
**If it fails:** Check that `wordpress/_build/db/lsc-db.sql` and uploads were committed and that `WP_SITE_URL` is correct in `.env`. Skip Step 7 (`build.sh`) unless you are deliberately resetting seed content.

---

### Appendix B: WP-CLI reference (production)

Always use both compose files and override the wpcli entrypoint:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint wp wpcli <command> --path=/var/www/html
```

Examples:

```bash
# List plugins
docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint wp wpcli plugin list --path=/var/www/html

# Flush permalinks
docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint wp wpcli rewrite flush --hard --path=/var/www/html
```

---

### History


| Date | Run By | Notes |
| ---- | ------ | ----- |
| —    | —      | —     |
