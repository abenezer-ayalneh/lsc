# 0002 — Redefine the page set and navigation

**Status:** Accepted (2026-06-02)

## Context

`PROJECT_PLAN.md` §4 and `CLAUDE.md` documented the rebuild sitemap as: Home, About, Facilities, Pricing, Terms of Use, Contact. The first build shipped those six pages.

The client has now specified a different nav — matching the *old* lsportsc.org structure rather than the documented rebuild plan. This was flagged as a contradiction and the client confirmed the new nav is authoritative.

## Decision

The authoritative **page set / navigation** is:

- **Home**
- **Who Are We**
- **Get Involved**
- **Events**
- **Media**
- **Get in Touch**

Pricing and Terms of Use are no longer top-level pages:

- **Pricing table** (from `LSC_Info_Prices.pdf`) lives **inside Get Involved**.
- **Terms of Use** is reachable from the **footer**.

`PROJECT_PLAN.md` and `CLAUDE.md` are updated to match.

## Consequences

- Content from the old site (`reference/old-site/`) maps onto these six pages; About/Facilities content folds into Who Are We / Get Involved.
- The "convert the PDF pricing to an on-page table" deliverable is preserved, relocated under Get Involved.
- The first build's six pages are superseded; pages are rebuilt against the new set (compounds with ADR 0001's fresh theme rebuild).
- The IA now mirrors the old site's organisation, which may ease 301 redirect mapping from the old `.html` URLs.
