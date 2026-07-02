# Lewisham Sports Consortium — Website Redesign Plan (WordPress)

**Client:** Lewisham Sports Consortium (LSC) — registered charity (no. 1109468), Company no. 04318063
**Site:** http://www.lsportsc.org → rebuilt on WordPress
**Prepared:** June 2026
**Scope:** A like-for-like redesign — the **current content** rebuilt as a modern, responsive WordPress site. **No new features.**
**Build approach:** Premium/free theme customisation (child theme); branding & content mostly ready; single developer working with Claude Code

> **2026-06-02 design pivot:** The visual design is now a pixel-clone of [marys.org.uk](https://www.marys.org.uk/) (mirrored at `reference/reference-site/`). Mary's palette and typography supersede the original LSC red/gold brand, and the page set was redefined (Home / Who Are We / Get Involved / Events / Media / Get in Touch). This is a re-scope, not a tweak — the hour estimates in §5–6 predate it and should be re-confirmed with the client. See `CONTEXT.md`, `docs/adr/0001` and `docs/adr/0002`.

---

## 1. Project Overview

Lewisham Sports Consortium is a volunteer-run charity operating Firhill Road Sports Ground in Bellingham, London. This project takes the **existing website content** and rebuilds it as a clean, modern, mobile-friendly WordPress site. The goal is a visual and technical refresh — **not** new functionality.

The current website is effectively a single flat image (`indexOLD.jpg`) used as the homepage, with key information (such as pricing) only available as a downloadable PDF, no responsive layout, and no HTTPS. The rebuild keeps the same information but presents it as proper, responsive, indexable web pages.

### Goals
- Recreate the current site's content as a modern, fully responsive WordPress site.
- Convert the PDF-based information (pricing, terms) into real on-page content.
- Move to HTTPS and a maintainable, block-based setup.
- Keep it simple enough for a volunteer to make minor edits after handover.

### Explicitly out of scope (can be added later)
No booking enquiry form, online booking/payments, donations, newsletter, news/blog, members area, or multilingual support. See §8 for these as optional future add-ons.

---

## 2. Current State Assessment

| Area | Current state | Action in rebuild |
|---|---|---|
| Homepage | Single flat image (`indexOLD.jpg`) | Rebuild as a real, structured page |
| Content | Minimal; pricing & terms in a PDF | Re-author the same content as on-page text |
| Responsiveness | None | Mobile-first build |
| Security | HTTP only, no SSL | Enforce HTTPS at launch |
| SEO | Effectively none (image-based) | Basic on-page SEO + sitemap |
| Maintainability | Not editable by volunteers | Block-editor based, with light handover |

**Content to carry over (as-is):** organisation background/mission; facilities (6 football pitches — 3 adult, 1 junior, 2 small-sided — training areas, day/half-day event hire); hire charges; ground rules / terms of use; contact details (140A Firhill Road, Bellingham, London SE6 3SQ; phone; `info@lsportsc.org`); social links.

---

## 3. Recommended Tech Stack

Kept deliberately lean — only what a content-only rebuild needs.

- **CMS:** WordPress (self-hosted, latest stable).
- **Theme:** A fast, block-friendly theme — **Kadence**, **GeneratePress**, or **Blocksy** — customised via a **child theme** + theme global styles.
- **Editor:** Native Block Editor (Gutenberg) with reusable **block patterns**, so volunteers can make edits without a page builder.
- **Performance/caching:** LiteSpeed Cache or WP Super Cache + basic image optimisation.
- **SEO (basic):** Rank Math or Yoast (free tier) for titles/meta and a sitemap.
- **Security/backups:** Wordfence (or host equivalent) + UpdraftPlus scheduled backups.
- **Dev workflow:** LocalWP or Docker for local dev; Git for the child theme; a staging environment before go-live.

> **Hosting note:** A managed WordPress host is recommended for a volunteer team. LSC must create/own the hosting and domain accounts and handle any payments directly.

---

## 4. Information Architecture (Sitemap)

Mirrors the old lsportsc.org structure. Visual design is a pixel-clone of marys.org.uk (see `docs/adr/0001`); the page set was redefined from the original About/Facilities/Pricing/Terms/Contact list (see `docs/adr/0002`).

```
Home
├── Who Are We       (story, mission, the charity, facilities)
├── Get Involved     (youth programme + hire teaser linking to Book the grounds)
├── Events           (programmes, sports, summer scheme)
├── Media            (project docs, consultation, flyers)
└── Get in Touch     (address, phone, email, social links, map)

Linked page: Book the grounds (pitch costs, pitch-use rules, Booking Hire Agreement)
Footer: Terms of Use (general ground rules / conditions of use)
```

---

## 5. Milestones & Estimated Hours

Estimates assume one competent developer working with Claude Code, the chosen-theme route, and content that is "mostly ready." Ranges reflect normal uncertainty; client review/approval cycles add **calendar** time, not developer hours.

### Milestone 0 — Discovery & Setup
| Task | Hours |
|---|---|
| Confirm content inventory & sitemap from the existing site | 2 |
| Local dev environment (LocalWP/Docker) + Git repo | 2 |
| Hosting/domain assessment & HTTPS migration plan | 2 |
| WordPress install + base config (settings, permalinks, users) | 2 |
| **Subtotal** | **8 (7–10)** |

### Milestone 1 — Design & Theme Foundation
| Task | Hours |
|---|---|
| Install theme + create child theme | 2 |
| Apply the marys.org.uk design system (Mary's palette, League Spartan / Quicksand fonts, pill buttons) via global styles — see `docs/adr/0001` | 3 |
| Build reusable block patterns (hero, alternating colour programme strands, "Latest" news cards, programme tiles, CTA band, multi-column footer) | 6 |
| Homepage layout & build | 5 |
| Responsive pass + design QA across breakpoints | 4 |
| **Subtotal** | **20 (17–24)** |

### Milestone 2 — Page Build & Content Migration
| Task | Hours |
|---|---|
| Build pages: Who Are We, Get Involved, Events, Media, Get in Touch (+ footer Terms of Use) | 10 |
| Migrate & format existing copy; convert PDF pricing into the on-page table on Book the grounds | 5 |
| Image sourcing/optimisation from existing assets | 3 |
| Menus, navigation & internal linking | 2 |
| **Subtotal** | **20 (17–24)** |

### Milestone 3 — Essentials (no extra features)
| Task | Hours |
|---|---|
| Contact details, embedded map, social links (existing content) | 2 |
| Basic on-page SEO (titles/meta, sitemap, 301 redirects from old URLs) | 3 |
| Performance (caching, image optimisation) + backups/security baseline | 3 |
| **Subtotal** | **8 (6–10)** |

### Milestone 4 — QA, Launch & Handover
| Task | Hours |
|---|---|
| Cross-browser/device testing & bug fixes | 4 |
| Final content proofing & client review | 2 |
| Go-live: DNS, enforce HTTPS, redirects, smoke test | 3 |
| Light admin handover (short guide / walkthrough) | 3 |
| **Subtotal** | **12 (10–14)** |

---

## 6. Hours Summary

| Milestone | Hours |
|---|---|
| 0 — Discovery & Setup | 8 |
| 1 — Design & Theme Foundation | 20 |
| 2 — Page Build & Content Migration | 20 |
| 3 — Essentials | 8 |
| 4 — QA, Launch & Handover | 12 |
| **Total** | **≈ 68 hours (57–82)** |

---

## 7. Assumptions
- LSC creates/owns the hosting and domain accounts and handles any payments and account registrations directly.
- Branding (logo, colours, fonts) and the existing copy/photos are supplied and usable; budget covers formatting and light polish, not brand creation or a photoshoot.
- Scope is the existing content only — **no new features** (see §8).
- One round of consolidated feedback per milestone; extra revision rounds add hours.
- English-language, single site.

## 8. Optional Future Add-Ons (NOT included)
Priced/scoped separately if ever wanted: booking enquiry form; full online booking + availability calendar + card payments; online donations; newsletter signup; news/blog; cookie consent + privacy/accessibility policies; members/teams area; multilingual.

## 9. Open Decisions (before/at kickoff)
1. Hosting: stay with the current host or move to a managed WordPress host?
2. Theme choice: Kadence vs GeneratePress vs Blocksy (recommendation available).
3. Who on the LSC side is the content owner / point of contact for sign-off?

---

## 10. Suggested Claude Code Workflow
- Keep the **child theme under Git**; let Claude Code scaffold the theme, block patterns, custom CSS, and template files.
- Use Claude Code to **reformat the existing copy** (including converting the PDF pricing/terms into clean on-page content) and to generate the handover guide.
- Develop locally (LocalWP/Docker), push to **staging** for review, then promote to production at go-live.
- Keep this `PROJECT_PLAN.md` and a `CHANGELOG.md` in the repo to track progress against milestones.
