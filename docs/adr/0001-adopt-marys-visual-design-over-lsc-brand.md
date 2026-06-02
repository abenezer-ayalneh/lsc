# 0001 — Adopt Mary's visual design over the LSC brand palette

**Status:** Accepted (2026-06-02)

## Context

The LSC rebuild already had a brand palette baked into `theme.json` (LSC red `#e2231a`, gold `#f9a800`, ink, cream) and a first set of patterns built against it. The client now wants the site to *pixel-clone* https://www.marys.org.uk/ — a faithful reproduction of its look, template by template across the whole site.

A faithful clone and an existing brand palette cannot both win. Mary's uses its own colour and type system; keeping LSC red/gold would make the result a "layout borrow," not a clone.

## Decision

**Mary's palette wins.** We re-derive `theme.json` colours and typography from the reference site's CSS and reskin LSC onto that system. The LSC logo and motto image survive as identity marks, but the surrounding colour system is Mary's. Image slots are filled with neutral placeholder blocks (not Mary's photos) until LSC supplies real photography.

The existing `lsc-child` patterns (built for the red/gold system) are rebuilt fresh rather than retrofitted.

## Consequences

- `theme.json`, `style.css`, and all patterns are re-authored. The prior LSC-coloured build is discarded.
- The site will visibly resemble marys.org.uk; LSC's historic red/gold identity is largely set aside (logo aside).
- Reversing this means re-deriving the palette again — meaningful but not catastrophic rework.
- Mary's design is third-party; we reproduce structure/spacing/colour, not their content, copy, or photography.
