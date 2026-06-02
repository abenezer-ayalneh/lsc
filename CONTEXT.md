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

**Terms of Use** — Ground rules / conditions of hire. No longer a top-level page; reachable from the **footer**.

**Image slot** — A position in a Mary's template that holds a photo. In the LSC build these are filled with **neutral placeholder blocks**, not Mary's photography, until real LSC imagery is supplied.
