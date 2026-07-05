# Context — Lewisham Sports Consortium rebuild

Glossary of the language we use on this project. Terms only — no implementation detail.

## Terms

**Reference site** — A full local mirror of https://www.marys.org.uk/ stored at `reference/reference-site/`. It is the *visual* source of truth: palette, typography, spacing, component shapes. It is **not** a content source.

**Old site** — The scraped previous LSC website at `reference/old-site/` (`www.lsportsc.org`). It is the *content* source of truth: copy, contact details, assets, page structure.

**Pixel-clone** — Reproducing the reference site's appearance as faithfully as practical (colours, spacing, imagery placement, type scale), template by template — as opposed to merely borrowing its layout.

**Mary's palette** — The colour and type system extracted from the reference site. On this project it supersedes the older LSC red/gold brand palette wherever the two conflict (see `docs/adr/0001`).

**LSC brand marks** — The LSC logo and motto image. These survive the reskin as identity marks even though the surrounding colour system becomes Mary's.

**Page set** — The authoritative site navigation: **Home, Who Are We, Get Involved, Events, About, Get in Touch**. This replaces the earlier documented set (About/Facilities/Pricing/Terms/Contact), and later replaced Media with About. See `docs/adr/0002`.

**Pricing table** — The on-page hire-rates table derived from `LSC_Info_Prices.pdf`. No longer a top-level page; it lives on the standalone **Book the grounds** page.

**Terms of Use** — General **ground rules** for everyone using Firhill Road Sports Ground (no dogs, no smoking, changing-room etiquette, etc.). Reachable from the **footer**. Distinct from **Conditions of Hire** below.

**Conditions of Hire** — The 22-clause legal terms a Hirer agrees to when booking the grounds (deposits, cancellations, indemnity, public liability). Sourced from PDF pages 3–5 of `LSC-000 Booking Hire Agreement Form.pdf`. Surfaced inline on the **Booking Hire Agreement** page, not as a standalone nav page. *Not* the same as Terms of Use.

**Pitch-use ground rules** — The football/pitch rules for teams and spectators using Firhill Road Sports Ground (arrival, warm-up areas, no dogs, parking, filming, etc.). Surfaced on **Book the grounds** alongside pitch costs. Distinct from footer **Terms of Use**.

**Booking Hire Agreement** — The web equivalent of the LSC-000 PDF: a fillable form where a prospective Hirer submits booking details (dates, times, party details, equipment, insurer) and accepts the Conditions of Hire. Lives at `/book-the-grounds/`. See `docs/adr/0003`.

**Hirer** — The person, club, or organisation booking Firhill Road Sports Ground for an event. Single-source term — do not use "renter", "customer", "client", or "booker".

**Volunteer Application** — The online form where someone offers their time and skills for LSC volunteer roles such as events, grounds maintenance, catering, coaching support, administration, fundraising, media, social media, reception, or general volunteering. Lives at `/become-a-volunteer/`. See `docs/adr/0006`.

**Volunteer Applicant** — A person who submits a Volunteer Application. Distinct from a Hirer: they are applying to support LSC rather than booking the grounds.

**Contact Form** — The general enquiry form on **Get in Touch** where website visitors send a message to LSC by email. Distinct from the Booking Hire Agreement and Volunteer Application.

**Image slot** — A position in a Mary's template that holds a photo. In the LSC build these are filled with **neutral placeholder blocks**, not Mary's photography, until real LSC imagery is supplied.

**Content gutter** — The horizontal breathing space kept between page content and the screen edge on narrow screens, so text and images never run flush to (or look cropped at) the left/right edges on mobile. Full-width colour **bands** keep their backgrounds edge-to-edge; only the content inside them is inset by the gutter.

**DB snapshot** — The exported WordPress database state that is canonical for page content, menus, widgets, options, forms, and other admin-managed content after the initial build. Do not use the seed templates as the content source of truth once a DB snapshot exists.
_Avoid_: SQL dump, template content

**Seed templates** — The HTML files used to create the first version of the pages on a greenfield WordPress install. They are reset/bootstrap material only; they are not the canonical source for ongoing content changes.
_Avoid_: Page source, canonical templates

**Snapshot-first workflow** — The rule that local WP Admin edits are exported to the DB snapshot before AI or code work continues, so admin-managed content is preserved in git before other changes are made.
_Avoid_: Rebuild-first workflow, template sync

## Deployment

**Main branch** — The canonical source branch for **staging**. Every commit to `main` triggers an automatic deploy to the staging server (`lsc.abenezer-ayalneh.dev`). Main is the single source of truth for testing; it is not protected (direct commits allowed, but prefer PRs once you need code review).

**Release/prod branch** — The canonical source branch for **production**. Code is promoted to production by merging `main` into `release/prod`, which then auto-deploys. Kept separate to allow staging to move fast while production is more conservative. All hotfixes are merged to `main` first, then promoted to `release/prod` via standard merge.

**Staging** — The persistent staging server at `lsc.abenezer-ayalneh.dev` (Ubuntu VPS, Docker Compose, same stack as production). Used for client demos, testing, and validation. Auto-updates on every `main` push (code + database snapshot + media files). Staging database can be overwritten by auto-imports; the local machine is the source of truth for content.

**Production** — The live server (TBD — awaiting client hosting confirmation). Once online, promoted to via merges from `main` to `release/prod`. Treated as read-only in terms of content — all edits are made locally, exported, and imported to prod. Database backups are mandatory before any import.

**Local dev** — Developer's machine running `docker compose --profile dev up`. Includes WordPress, MariaDB, phpMyAdmin, MailHog. Only place where content edits happen directly in WP Admin; changes are then exported via `export-db.sh` and committed.
