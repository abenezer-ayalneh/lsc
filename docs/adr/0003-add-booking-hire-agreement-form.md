# 0003 — Add a web Booking Hire Agreement form

**Status:** Accepted (2026-06-19); amended (2026-06-24, 2026-07-02)

## Amendment (2026-07-02) — Standalone Book the grounds page

The Booking Hire Agreement page is now a standalone page at
`/book-the-grounds/`, not a child of Get Involved. Pitch costs and pitch-use
ground rules live on the same page. Get Involved keeps a short teaser linking to
Book the grounds, and the primary navigation remains the six-page set from ADR
0002.

## Amendment (2026-06-24) — Forminator, not Contact Form 7

The form is now built with **Forminator**, not Contact Form 7. CF7 6.x threw under
WP-CLI when scripting the form, and its date field could not express the
"weekends in Jun–Aug only" hire-availability rule; Forminator handles both in its
admin UI and is equally volunteer-editable. The form is authored in wp-admin and
shipped via the DB snapshot (`export-db.sh` / `import-db.sh`), not bootstrapped by
a script. CF7 has been uninstalled. Two delivery details were fixed at the same
time: Forminator's default sender was `noreply@localhost` (an invalid address
PHPMailer rejects before send), so the option `forminator_sender_email_address`
is pinned to `noreply@lsportsc.org`; and the admin notification recipient is set
to `lewishamsportsconsortium@gmail.com`. The body below is the original
CF7-based decision, kept for the record.

## Context

`CLAUDE.md` §"Scope guardrails" explicitly lists **booking forms** as out of scope: *"Booking forms, payments, donations, newsletter, blog, members area, and multilingual are explicitly out of scope — do not add them unless asked."*

The client has now asked for a fillable web equivalent of the `LSC-000 Booking Hire Agreement Form.pdf` (pages 1–2 of which are the intake form, pages 3–5 the Conditions of Hire). This contradicts the documented scope.

## Decision

Scope is expanded to include a single **Booking Hire Agreement** page. Specifically:

- **URL / placement.** `/book-the-grounds/` — a standalone page linked from Home and Get Involved, where the pricing table and pitch-use ground rules also sit.
- **Submission mechanism.** Contact Form 7 (free, WP-native, volunteer-editable), delivering via `wp_mail` to `lewishamsportsconsortium@gmail.com`. In dev this routes through MailHog like the rest of the stack.
- **Signature.** Typed full name + an "I have read, understood, and agree to the Conditions of Hire" checkbox. No wet-signature or signature-pad plugin.
- **Conditions of Hire (PDF pp. 3–5).** Rendered inline below the form inside a collapsed `<details>` accordion. Distinct from the footer "Terms of Use" (general ground rules).
- **Verbatim content kept.** The page reproduces the PDF's intro paragraph, "In a nutshell" 10-point list, "Made the …day of…" legal recital, and "For office use only" table as static content — wording is **not** rewritten. Office-use fields are display-only, not user-editable.
- **Insurance certificate.** Required upload (PDF/JPG/PNG). CF7's file-attachment feature carries it on the email to LSC.
- **Plugin footprint.** Contact Form 7 only. No payments, no booking calendar, no scheduling/availability check — the form is intake, not transaction.

## Alternatives considered

1. **Stay strictly in scope and provide only a PDF download.** Cheapest, preserves the static-content build. Rejected: client explicitly asked for a *fillable* form.
2. **Heavier form plugin (Fluent Forms / WPForms / Gravity).** Nicer admin UI, conditional logic, native uploads. Rejected: CF7 is sufficient for a single intake form and aligns with the volunteer-editable handover principle.
3. **Hand-rolled HTML + child-theme `wp_mail` handler.** No plugin dependency. Rejected: re-implements CF7's spam guards, validation, and file-attachment plumbing without benefit.

## Consequences

- `CLAUDE.md` "Scope guardrails" paragraph must be updated: "booking forms" moves out of the explicitly-out-of-scope list, with a pointer to this ADR.
- Contact Form 7 becomes a runtime dependency. The `wpcli` bootstrap (`config/setup.sh`) installs/activates it alongside Kadence.
- The MailHog wiring (`config/mu-plugins/00-mailhog.php`) already covers local-dev mail for the booking inbox — no extra dev config needed.
- A new term **Conditions of Hire** enters `CONTEXT.md`, distinct from **Terms of Use**, to keep the footer link and the booking-page accordion unambiguous.
- Future scope creep (payments, calendar availability, deposit collection) remains explicitly out of scope unless re-opened by a further ADR.
