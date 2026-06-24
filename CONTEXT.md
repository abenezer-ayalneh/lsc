# Context — Lewisham Sports Consortium rebuild

Glossary of the language we use on this project. Terms only — no implementation detail.

## Terms

**Reference site** — A full local mirror of https://www.marys.org.uk/ stored at `reference/reference-site/`. It is the *visual* source of truth: palette, typography, spacing, component shapes. It is **not** a content source.

**Old site** — The scraped previous LSC website at `reference/old-site/` (`www.lsportsc.org`). It is the *content* source of truth: copy, contact details, assets, page structure.

**Pixel-clone** — Reproducing the reference site's appearance as faithfully as practical (colours, spacing, imagery placement, type scale), template by template — as opposed to merely borrowing its layout.

**Mary's palette** — The colour and type system extracted from the reference site. On this project it supersedes the older LSC red/gold brand palette wherever the two conflict (see `docs/adr/0001`).

**LSC brand marks** — The LSC logo and motto image. These survive the reskin as identity marks even though the surrounding colour system becomes Mary's.

**Page set** — The authoritative site navigation: **Home, Who Are We, Get Involved, Events, Media, Get in Touch**. This replaces the earlier documented set (About/Facilities/Pricing/Terms/Contact). See `docs/adr/0002`.

**Pricing table** — The on-page hire-rates table derived from `LSC_Info_Prices.pdf`. No longer a top-level page; it lives **inside Get Involved**.

**Terms of Use** — General **ground rules** for everyone using Firhill Road Sports Ground (no dogs, no smoking, changing-room etiquette, etc.). Reachable from the **footer**. Distinct from **Conditions of Hire** below.

**Conditions of Hire** — The 22-clause legal terms a Hirer agrees to when booking the grounds (deposits, cancellations, indemnity, public liability). Sourced from PDF pages 3–5 of `LSC-000 Booking Hire Agreement Form.pdf`. Surfaced inline on the **Booking Hire Agreement** page, not as a standalone nav page. *Not* the same as Terms of Use.

**Booking Hire Agreement** — The web equivalent of the LSC-000 PDF: a fillable form where a prospective Hirer submits booking details (dates, times, party details, equipment, insurer) and accepts the Conditions of Hire. Lives at `/get-involved/book-the-grounds/`. See `docs/adr/0003`.

**Hirer** — The person, club, or organisation booking Firhill Road Sports Ground for an event. Single-source term — do not use "renter", "customer", "client", or "booker".

**Image slot** — A position in a Mary's template that holds a photo. In the LSC build these are filled with **neutral placeholder blocks**, not Mary's photography, until real LSC imagery is supplied.
