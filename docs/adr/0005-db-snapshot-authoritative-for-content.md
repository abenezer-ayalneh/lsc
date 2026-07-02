# 0005 — DB snapshot is authoritative for content after initial build

**Status:** Accepted (2026-07-02)

After the site has been seeded once, page content, menus, widgets, options, and forms are owned by the WordPress database snapshot at `wordpress/_build/db/lsc-db.sql`, not by the HTML seed templates. This protects WP Admin edits in the hybrid admin/AI workflow: AI agents may change theme code and helper scripts, but content changes must be made through local WordPress/WP-CLI and exported. The seed templates and `build.sh` remain available for deliberate greenfield/reset rebuilds only, guarded by an explicit force flag because re-running them can overwrite admin-edited content.
