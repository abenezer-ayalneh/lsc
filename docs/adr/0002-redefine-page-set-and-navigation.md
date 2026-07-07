# 0002 — Redefine the page set and navigation

**Status:** Accepted (2026-06-02); amended (2026-07-02, 2026-07-05, 2026-07-07)

## Amendment (2026-07-07) - Volunteer promoted to primary nav

The client asked to add the existing `/become-a-volunteer/` page to the
top-level navigation with the label **Volunteer**. It sits after **Get
Involved**, keeping the volunteering action close to the related participation
page while making the form directly accessible.

## Amendment (2026-07-05) — Media replaced by About

The client asked to replace the top-level Media page with an About page at
`/about/`, using trustee information supplied in `Trustees Information - draft
2026 (V3).pdf`. The old `/media/` route redirects to `/about/`; the old media
gallery and consultation-document content is no longer part of the public site
IA.

## Amendment (2026-07-02) — Pricing moved to Book the grounds

The pricing table remains outside the top-level navigation, but it no longer
lives on Get Involved. The client asked for a standalone Book the grounds page
at `/book-the-grounds/`, so pitch costs and pitch-use rules now live there.
Get Involved keeps only a short teaser that links to Book the grounds.

## Context

`PROJECT_PLAN.md` §4 and `CLAUDE.md` documented the rebuild sitemap as: Home, About, Facilities, Pricing, Terms of Use, Contact. The first build shipped those six pages.

The client has now specified a different nav — matching the *old* lsportsc.org structure rather than the documented rebuild plan. This was flagged as a contradiction and the client confirmed the new nav is authoritative.

## Decision

The authoritative **page set / navigation** is:

- **Home**
- **Who Are We**
- **Get Involved**
- **Volunteer**
- **Events**
- **About**
- **Get in Touch**

Pricing and Terms of Use are no longer top-level pages:

- **Pricing table** (from `LSC_Info_Prices.pdf`) lives on **Book the grounds**.
- **Terms of Use** is reachable from the **footer**.

`PROJECT_PLAN.md` and `CLAUDE.md` are updated to match.

## Consequences

- Content from the old site (`reference/old-site/`) maps onto these six pages; About/Facilities content folds into Who Are We / Get Involved.
- The "convert the PDF pricing to an on-page table" deliverable is preserved, relocated under Book the grounds.
- The first build's six pages are superseded; pages are rebuilt against the new set (compounds with ADR 0001's fresh theme rebuild).
- The IA now mirrors the old site's organisation, which may ease 301 redirect mapping from the old `.html` URLs.
