# 0004 — Git-driven three-stage deployment (local → staging → production)

**Status:** Accepted (2026-06-24)

## Context

The LSC rebuild has three deployment targets:
- **Local** — Developer's machine (Docker Compose with dev tools)
- **Staging** — Persistent VPS at `lsc.abenezer-ayalneh.dev` for client demos and testing
- **Production** — Client's hosting (TBD; awaiting confirmation)

Without a defined workflow, deployment is ad-hoc: manual SSH, copy-paste of SQL files, unclear handoff. We need a repeatable, automated process that gives fast feedback on staging while protecting production.

## Decision

**Three-stage promotion via git branches + GitHub Actions (CI/CD):**

1. **Local dev** — developers edit content/code locally; changes are committed and pushed to `main`.

2. **Staging** (`main` branch) — Every push to `main` automatically triggers a deploy to `lsc.abenezer-ayalneh.dev`:
   - Git pull latest
   - Rebuild Docker stack (`docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d`)
   - Import latest database snapshot from repo
   - Sync latest media files
   - **Result:** Staging reflects the absolute latest code, content, and media. No manual deploy needed.

3. **Production** (`release/prod` branch) — Separate branch used for production promotions:
   - To promote: merge `main` into `release/prod` (via PR or CLI)
   - Merge to `release/prod` automatically triggers production deploy
   - **Result:** Production is deliberately gated from staging; deploys are intentional, not automatic on every commit.

**Hotfixes:** All fixes (including hotfixes to production) are merged to `main` first, tested on staging, then promoted to `release/prod` via standard merge. No separate hotfix branch.

**Git protection:** Main is **not protected** initially (allows direct commits), but as the team grows, we'll add PR requirements and enforce them.

## Consequences

### Pros
- **Fast staging feedback:** Developers see code + content changes live on staging seconds after pushing, no manual steps.
- **Single source of truth per env:** `main` is always correct for staging, `release/prod` for production. Clear ownership.
- **Safe production:** Production requires deliberate merge to `release/prod`, lowering risk of accidental deploys.
- **Consistent hotfix flow:** Hotfixes go through the same path as normal changes (main → test on staging → promote to prod). Simpler than branching strategies.
- **Fully reproducible:** All promotions are git-based (no manual SSH); easy to audit, version, and replay.

### Cons
- **Database overwrites staging:** Staging is auto-imported on every push, so any manual edits in staging WP Admin are lost. Staging is not a writable persistent DB — it's a **transient test environment**. Only develop content on local.
- **Manual CI/CD setup needed:** Requires GitHub Actions workflows (not yet built). Need to provide deploy keys, secrets, SSH access from Actions to staging/prod.
- **Slower hotfixes:** Critical production bugs require fix → commit → push → 1–2 min staging test → merge to release/prod → 1–2 min prod deploy. Not immediate. Acceptable trade-off for stability.
- **All-or-nothing promotions:** Cannot cherry-pick individual commits from main to production. Either promote all of main or none. If needed later, we can add a release branch workflow; for now, we expect main is always deployable.

## Implementation tasks

- [ ] Set up GitHub Actions workflow to auto-deploy `main` to staging
- [ ] Set up GitHub Actions workflow to auto-deploy `release/prod` to production
- [ ] Add deploy SSH keys and environment secrets to GitHub
- [ ] Document the workflow for the team (runbook: `docs/runbooks/deployment-workflow.md`)
- [ ] Add main branch protection rules (require PR review, passing tests) once team grows

## Related decisions

- [[design-pivot-marys-clone]] — Content is built locally, exported as SQL, and committed to repo; this ADR defines how to promote that snapshot across envs.
