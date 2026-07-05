# 0006 - Add a Volunteer Application form

**Status:** Accepted (2026-07-05)

## Context

The rebuild was originally scoped as a like-for-like content refresh with no new
features. ADR 0003 already expanded that scope for the Booking Hire Agreement
form. The client has now asked for a second web form so people can apply to
become volunteers.

The form collects personal contact details, emergency contact details,
availability, role preferences, optional health/accessibility information, DBS
status, optional references, and declarations.

## Decision

Add a standalone **Volunteer Application** page at `/become-a-volunteer/`, linked
from the bottom of Get Involved but not added to the primary navigation. The page
uses Forminator, matching the Booking Hire Agreement implementation pattern:

- Admin notifications go to `lewishamsportsconsortium@gmail.com`.
- Submissions are stored in WordPress/Forminator admin.
- The public form uses typed full name plus date for the signature fields.
- Date of Birth, health/accessibility details, DBS status, and references remain
  optional unless the applicant chooses an answer that requires supporting text.
- Payments, membership accounts, onboarding workflows, and background-check
  processing remain out of scope.

## Consequences

- Forminator is now an explicit runtime dependency for multiple site forms, so
  setup/deployment documentation should describe it generically rather than as
  only the Booking Hire Agreement plugin.
- Volunteer form/page content lives in the DB snapshot, not the seed templates.
- Future direct edits to production WP Admin can still be overwritten by DB
  snapshot import, including stored form submissions, so production backups
  remain required before import.
