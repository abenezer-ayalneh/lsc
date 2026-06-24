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

Also, if you added new images/media:

```bash
# They're already in wordpress/wp-content/uploads/ (bind mount)
# Nothing to do — they'll sync when staging deploys
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
- Check Docker logs: `cd /opt/lsc-wordpress && docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f wordpress`
- Restart if needed: `docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d`

**Staging database out of sync:**
- The import may have failed. Manually re-import:
  ```bash
  cd /opt/lsc-wordpress
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
cd /opt/lsc-wordpress
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
cd /opt/lsc-wordpress
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

## CI/CD Setup (Phase 2 - Planning)

The workflows below are **pending implementation** via GitHub Actions. Once set up, they will automate all deploys.

### Staging deploy workflow

Triggers: every push to `main`

```yaml
# .github/workflows/deploy-staging.yml
on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to staging
        env:
          DEPLOY_KEY: ${{ secrets.STAGING_DEPLOY_KEY }}
          STAGING_HOST: lsc.abenezer-ayalneh.dev
        run: |
          mkdir -p ~/.ssh
          echo "$DEPLOY_KEY" > ~/.ssh/id_ed25519
          chmod 600 ~/.ssh/id_ed25519
          ssh-keyscan -H $STAGING_HOST >> ~/.ssh/known_hosts
          ssh user@$STAGING_HOST 'cd /opt/lsc-wordpress && git pull && docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d && docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint sh wpcli /var/www/html/_build/import-db.sh'
```

### Production deploy workflow

Triggers: every push to `release/prod`

```yaml
# .github/workflows/deploy-production.yml
on:
  push:
    branches: [release/prod]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to production
        env:
          DEPLOY_KEY: ${{ secrets.PRODUCTION_DEPLOY_KEY }}
          PRODUCTION_HOST: your-production-server.com
        run: |
          mkdir -p ~/.ssh
          echo "$DEPLOY_KEY" > ~/.ssh/id_ed25519
          chmod 600 ~/.ssh/id_ed25519
          ssh-keyscan -H $PRODUCTION_HOST >> ~/.ssh/known_hosts
          ssh user@$PRODUCTION_HOST 'cd /opt/lsc-wordpress && git pull && docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d && docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint sh wpcli /var/www/html/_build/import-db.sh'
```

**Setup tasks:**
- [ ] Generate SSH deploy keys for staging/production
- [ ] Add deploy keys as GitHub Secrets (`STAGING_DEPLOY_KEY`, `PRODUCTION_DEPLOY_KEY`)
- [ ] Create `.github/workflows/deploy-staging.yml` and `deploy-production.yml`
- [ ] Test by pushing to main and watching GitHub Actions logs

---

## Troubleshooting Matrix

| Symptom | Likely Cause | Fix |
|---------|--------------|-----|
| Staging not updating after push | DB export not committed | Run `docker compose run --rm --entrypoint wp wpcli db export - > _build/db/lsc-db.sql`, `git add _build/db/lsc-db.sql`, `git commit`, `git push` |
| GitHub Actions workflow failing | Deploy key not installed or SSH permissions wrong | Check Actions logs; verify deploy key is in GitHub Secrets and authorized on server |
| Staging old content but code new | Import-db.sh failed silently | SSH to staging, manually run: `docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm --entrypoint sh wpcli /var/www/html/_build/import-db.sh` |
| Production won't start after deploy | Docker Compose config mismatch | SSH to production, run: `docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f` to see errors |
| Media files missing on staging/prod | Not all uploads synced | Check that `wordpress/wp-content/uploads/` is bind-mounted in docker-compose.prod.yml; files should sync automatically |

---

## Related

- [ADR 0004: Git-driven three-stage deployment](../adr/0004-git-driven-three-stage-deployment.md) — why this workflow
- [Deploy to Ubuntu Server runbook](deploy-ubuntu-server.md) — initial server setup
- [CONTEXT.md](../../CONTEXT.md) — deployment glossary (main, release/prod, staging, production)
