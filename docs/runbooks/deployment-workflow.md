# Runbook: Git-driven deployment workflow

**Owner:** Project developer | **Frequency:** Every code change | **Status:** Planning (CI/CD setup pending)

## Overview

This runbook defines the three-stage deployment workflow:
- **Local dev** (your machine) → author code and content
- **Staging** (`lsc.abenezer-ayalneh.dev`) → auto-deploys on every push to `main`
- **Production** (client's hosting) → auto-deploys on merge to `release/prod`

All promotions are git-driven. No manual SSH or SQL copy-paste needed.

## Prerequisites

- Git repository hosted on GitHub (or similar)
- GitHub Actions enabled for the repo
- SSH deploy keys installed on staging and production servers
- Staging/production servers running Ubuntu with Docker Compose (as per `deploy-ubuntu-server.md`)
- Local `.env` configured for development

## Stage 1: Local Development

Develop everything locally on your machine. Content edits are made in WordPress Admin (`http://localhost:8080/wp-admin`).

### Starting a feature

```bash
git checkout main
git pull
git checkout -b feature/my-feature     # Create a feature branch
docker compose --profile dev up -d     # Start local stack if not running
```

### Making changes

**Code changes** (theme, configs):
```bash
# Edit files in wordpress/wp-content/themes/lsc-child/
# Changes are live immediately (bind mount)
```

**Content changes** (pages, menus, forms):
```bash
# Edit in WP Admin at http://localhost:8080/wp-admin
# Use the Forminator plugin for the Booking Hire Agreement form if needed
```

### Exporting content before committing

Once you've made content changes locally, **export the database** so they can be versioned and promoted to staging/prod:

```bash
cd /path/to/lewisham-sports-consortium
docker compose run --rm --entrypoint wp wpcli db export - --path=/var/www/html > _build/db/lsc-db.sql
```

This overwrites `_build/db/lsc-db.sql` with the latest state. **Commit this file.**

Also, if you added new images/media via the Media Library, commit the upload
files so they deploy with the next push:

```bash
git add wordpress/wp-content/uploads
# (plugin runtime dirs — forminator/, wpcf7_uploads/ — are gitignored automatically)
```

### Committing and pushing

```bash
git add -A
git commit -m "feat: update booking form copy and add Get Involved gallery"
git push origin feature/my-feature
```

### Merging to main (→ staging)

Once you're ready to test on staging, create a PR or merge directly to `main`:

```bash
# Via GitHub: open a PR, request review (once team grows), merge to main
# Or via CLI:
git checkout main && git pull
git merge feature/my-feature
git push origin main
```

**Result:** Push to `main` triggers an automatic deploy to staging. In ~2 minutes, `lsc.abenezer-ayalneh.dev` will reflect your latest code and content.

## Stage 2: Testing on Staging

Visit `https://lsc.abenezer-ayalneh.dev` and verify:

- [ ] Page layouts render correctly
- [ ] Content is up-to-date (not stale from a prior commit)
- [ ] Forms work (Booking Hire Agreement form submits, no errors)
- [ ] Navigation menus are correct
- [ ] Media loads (no broken images)
- [ ] Responsive on mobile (resize browser or test on phone)

### Troubleshooting staging deploy issues

**Staging is outdated (old content showing):**
- Check that you committed `_build/db/lsc-db.sql` and pushed to `main`
- Check GitHub Actions — is the workflow running? View logs at `github.com/your-repo/actions`

**Staging errors or blank pages:**
- SSH to staging: `ssh user@lsc.abenezer-ayalneh.dev`
- Check Docker logs: `cd /home/lsc && docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f wordpress`
- Restart if needed: `docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d`

**Staging database out of sync:**
- The import may have failed. Manually re-import:
  ```bash
  cd /home/lsc
  docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    run --rm --entrypoint sh wpcli /var/www/html/_build/import-db.sh
  ```

### Fixing bugs on staging

If you find a bug on staging:

1. **Do not edit WordPress directly on staging.** The database is auto-overwritten on every push.
2. Go back to local dev, fix the bug, export the DB, commit, and push.
3. Staging will auto-update in ~2 minutes.

## Stage 3: Promoting to Production

Once staging is tested and approved, promote to production by merging `main` into `release/prod`:

```bash
git fetch origin
git checkout release/prod && git pull origin release/prod
git merge main
git push origin release/prod
```

**Result:** Merge to `release/prod` triggers an automatic deploy to production. In ~2 minutes, the production server will run the latest code and content.

### Pre-production backup (mandatory)

**Always take a backup of production before deploying:**

```bash
# SSH to production server
ssh user@PROD_SERVER_IP

# Backup the database
cd /home/lsc
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
  run --rm --entrypoint wp wpcli db export - --path=/var/www/html > backup-$(date +%F-%H%M%S).sql
```

Keep backups on the server (or scp them locally) so you can roll back if needed.

### Verifying production deploy

After the merge and ~2-minute deploy:

- [ ] `https://www.lsportsc.org` loads
- [ ] Primary nav shows all pages
- [ ] Booking form works
- [ ] No console errors (browser dev tools)
- [ ] HTTPS certificate is valid (no warnings)

### Rolling back production

If production has a critical issue:

1. **Identify the bad commit** (e.g., via `git log release/prod`)
2. **Revert it:** `git revert <BAD_COMMIT_HASH>`
3. **Push the revert:** `git push origin release/prod`
4. Production will auto-deploy the reverted state in ~2 minutes.

**Example:**
```bash
git log release/prod --oneline | head -n 5    # Find the bad commit
git revert abc1234                             # Revert it (creates a new commit)
git push origin release/prod                   # Triggers auto-deploy
```

Alternatively, if you need to revert to a known-good database backup:

```bash
# SSH to production
cd /home/lsc
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
  run --rm --entrypoint sh wpcli sh -c 'wp db import - --path=/var/www/html' < backup-2026-06-20.sql
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
  run --rm --entrypoint wp wpcli rewrite flush --hard --path=/var/www/html
```

## Hotfixes

For critical production bugs:

1. **Fix on `main` (not `release/prod`):** Create a branch from `main`, fix, and push to `main`.
2. **Test on staging:** Main will auto-deploy to staging; verify the fix works.
3. **Promote to production:** Merge `main` into `release/prod` as normal.

This ensures all fixes are tested on staging before hitting production, and keeps the workflow consistent (no separate hotfix branches).

### Example hotfix workflow:

```bash
# On your machine
git checkout main && git pull
git checkout -b hotfix/critical-form-error
# ... make fix ...
git commit -am "fix: booking form not accepting dates"
git push origin hotfix/critical-form-error

# Test on staging (auto-deployed within 2 min)
# Then merge to main and promote to prod:
git checkout main && git pull
git merge hotfix/critical-form-error
git push origin main              # Stages the fix

# ... verify on staging ...

# Once confident, promote to production:
git checkout release/prod && git pull
git merge main
git push origin release/prod      # Deploys to production
```

## CI/CD Setup

The automation is **implemented** in three files:

- [`.github/workflows/deploy-staging.yml`](../../.github/workflows/deploy-staging.yml) — runs on every push to `main`
- [`.github/workflows/deploy-production.yml`](../../.github/workflows/deploy-production.yml) — runs on every push to `release/prod`
- [`wordpress/_build/deploy.sh`](../../wordpress/_build/deploy.sh) — the actual deploy logic, run **on the server** over SSH

Each workflow just SSHes into the right server and runs `deploy.sh`, which:
backs up the current DB → `git fetch` + `git reset --hard` to the tracked branch →
`docker compose up -d` → waits for WordPress → runs `import-db.sh`. Media rides
along in git (`wp-content/uploads/` is tracked), so no separate media sync is needed.

Until secrets are configured, each workflow **self-skips with a warning** (green run, not red) so an unconfigured push doesn't fail noisily.

### One-time setup (per server)

The workflows read secrets from two **GitHub Environments**: `staging` and `production`. Create both under **Settings → Environments**, then add these secrets to each:

| Secret            | Required | Value                         | Notes                                          |
| ----------------- | -------- | ----------------------------- | ---------------------------------------------- |
| `SSH_HOST`        | yes      | `lsc.abenezer-ayalneh.dev`    | Server hostname or IP                          |
| `SSH_USER`        | yes      | `deploy`                      | The existing `deploy` user (home `/home/deploy`) that owns `/home/lsc` |
| `SSH_PRIVATE_KEY` | yes      | `-----BEGIN OPENSSH...`       | Private half of the **CI** keypair (see below) |
| `SSH_PORT`        | no       | `22`                          | Defaults to `22` if unset                      |
| `REPO_DIR`        | no       | `/home/lsc`          | Defaults to `/home/lsc` if unset      |

> The `deploy` user already exists on the server with passwordless SSH and an
> existing keypair under `/home/deploy/.ssh`. We reuse that key for CI: paste its
> **private** half into `SSH_PRIVATE_KEY`. GitHub's runners are a separate client,
> so this only works if the key's **public** half is in
> `/home/deploy/.ssh/authorized_keys` on the *same* server (lets the runner log
> *in* as `deploy`) and the key has **no passphrase** (runners can't type one).
> A key the `deploy` user uses only to *pull from GitHub* won't meet the first
> condition until you add its public half to `authorized_keys` — verify below.
>
> **Trade-off:** reusing this key means it now also lives in GitHub secrets, so
> keep it deploy-only. If it's shared for other access (other hosts, GitHub
> pulls), prefer a dedicated CI key instead (`ssh-keygen -t ed25519 -f
> ~/.ssh/lsc_ci_deploy -N ""`, then `ssh-copy-id` its `.pub` to the deploy user).

**Reuse the existing `deploy` key** (run on the server as / for the `deploy` user):

```bash
# 1. Find the private key and confirm it has NO passphrase (must print a key, not prompt)
ls -la /home/deploy/.ssh/
ssh-keygen -y -P "" -f /home/deploy/.ssh/id_ed25519 >/dev/null && echo "no passphrase ✓"

# 2. Confirm its PUBLIC half is authorised to log in as deploy. If this prints
#    nothing, append it: cat id_ed25519.pub >> /home/deploy/.ssh/authorized_keys
grep -qf /home/deploy/.ssh/id_ed25519.pub /home/deploy/.ssh/authorized_keys \
  && echo "authorised ✓" || echo "NOT in authorized_keys — append it"

# 3. Print the PRIVATE key to paste into the SSH_PRIVATE_KEY secret (whole file)
cat /home/deploy/.ssh/id_ed25519
```

Adjust `id_ed25519` to the actual filename from step 1 (could be `id_rsa`, etc.).

Verify the key can log in as `deploy` before relying on it (from any client):

```bash
ssh -i /path/to/that/private_key deploy@lsc.abenezer-ayalneh.dev 'echo OK && whoami'
# Expect: OK / deploy
```

The server must already have the repo cloned at `REPO_DIR` (owned by `deploy`),
its branch tracking the right upstream (`main` on staging, `release/prod` on
production), `.env` configured, and Docker installed — see
[deploy-ubuntu-server.md](deploy-ubuntu-server.md).

### Setup checklist

- [ ] Confirm the existing `deploy` key has no passphrase (`ssh-keygen -y -P "" -f ...`)
- [ ] Confirm its public half is in `/home/deploy/.ssh/authorized_keys` (append if missing)
- [ ] Verify the key logs in as `deploy` from another client (`ssh -i ... deploy@host whoami`)
- [ ] Create `staging` and `production` GitHub Environments
- [ ] Add `SSH_HOST` / `SSH_USER=deploy` / `SSH_PRIVATE_KEY` (the existing deploy private key) (+ optional `SSH_PORT`, `REPO_DIR`) to each environment
- [ ] Confirm `/home/lsc` is cloned and owned by `deploy` on the staging server, clone on `main` (`git branch --show-current`)
- [ ] On the production server (when ready), ensure the clone is on `release/prod`
- [ ] Push a trivial change to `main` and confirm the staging deploy goes green
- [ ] (Optional) Add a required reviewer to the `production` environment for a manual approval gate

### Manual deploy

You can trigger either workflow by hand from the **Actions** tab (`workflow_dispatch`),
or run the script directly on the server:

```bash
ssh deploy@SERVER 'LSC_REPO_DIR=/home/lsc sh /home/lsc/wordpress/_build/deploy.sh'
```

---

## Troubleshooting Matrix

| Symptom | Likely Cause | Fix |
|---------|--------------|-----|
| Staging not updating after push | DB export not committed | Run `docker compose run --rm --entrypoint wp wpcli db export - > _build/db/lsc-db.sql`, `git add _build/db/lsc-db.sql`, `git commit`, `git push` |
| GitHub Actions workflow failing | Deploy key not installed or SSH permissions wrong | Check Actions logs; verify deploy key is in GitHub Secrets and authorized on server |
| Staging old content but code new | Import-db.sh failed silently | SSH to staging, manually run: `docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint sh wpcli /var/www/html/_build/import-db.sh` |
| Production won't start after deploy | Docker Compose config mismatch | SSH to production, run: `docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f` to see errors |
| Media files missing on staging/prod | New image not committed | `wp-content/uploads/` is tracked in git (except plugin runtime dirs). Commit new images: `git add wordpress/wp-content/uploads && git commit`. They deploy on the next push. |
| Broken image after deploy | Image edited in server wp-admin, not locally | Author media locally only; server uploads are overwritten by `git reset --hard`. Re-add the image locally, export the DB, commit, push. |

---

## Related

- [ADR 0004: Git-driven three-stage deployment](../adr/0004-git-driven-three-stage-deployment.md) — why this workflow
- [Deploy to Ubuntu Server runbook](deploy-ubuntu-server.md) — initial server setup
- [CONTEXT.md](../../CONTEXT.md) — deployment glossary (main, release/prod, staging, production)
