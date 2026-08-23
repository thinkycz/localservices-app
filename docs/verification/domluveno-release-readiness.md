# Domluveno Release Readiness

**Assessment date:** 2026-08-23

**Decision:** Ready for staging and release handoff
**Application baseline:** Laravel 12.67.0, Vue 3.5.41, Inertia 2.3.27, Vite 7.3.6, Tailwind 3

## Outcome

The audited marketplace core is implemented and the current verification gates pass. The previous March 2026 “production-ready” report is superseded. Domluveno now has an evidence-backed Czech-first discovery, booking, review, provider-onboarding, and provider-management surface with English as the secondary interface locale.

This decision covers application readiness. Trademark/domain approval and production infrastructure configuration—TLS, secrets, mail/queue workers, persistent public storage, backups, monitoring, and rollback—remain deployment responsibilities.

## Release gates

| Gate | Fresh result |
| --- | --- |
| Pint | Pass (`vendor/bin/pint --test`) |
| PHP suite | Pass — 68 tests, 399 assertions |
| Vitest | Pass — 5 tests in 2 files |
| Playwright | Pass — 12 journeys across mobile, tablet, and desktop |
| Accessibility | Pass — zero Axe violations for WCAG 2 A/AA tags on primary rendered surfaces |
| Responsive interaction | Pass at 390×844, 768×1024, and 1440×900; no asserted horizontal overflow |
| Browser health | Pass — no application console errors or unexpected 5xx responses in successful journeys |
| Client build | Pass — Vite production build |
| SSR build | Pass — Vite SSR production build |
| Fresh database | Pass — 25 migrations, 19 tables, deterministic seed |
| Seed snapshot | 6 users, 5 shops, 15 services, 36 bookings, 10 approved reviews |
| Route/config/view caches | Pass; caches generated and cleared successfully |
| Route reconciliation | Pass — 78 routes, each recorded once in the matrix |
| Composer audit | Pass — no advisories |
| npm audit | Pass — 0 vulnerabilities for full and production dependency sets |
| Diff whitespace | Pass (`git diff --check`) |

## Security and domain evidence

- Booking fields that affect authorization, price, time, or ownership are derived server-side from the selected service and shop.
- Booking transitions use one enum and reject illegal or terminal-state changes; completion is time-gated.
- Provider booking, customer, shop, service, and notification access is owner-scoped; cross-account tests return a non-disclosing denial.
- Availability honors active state, shop hours, duration, timezone, overlap, and shop ownership; booking creation rechecks inside a transaction and lock.
- Customer and guest cancellation behavior is tested before, exactly at, and after the 24-hour boundary.
- Guest management uses a random raw token delivered in the URL while only its SHA-256 hash is stored; claiming requires a verified matching email and invalidates matching guest tokens.
- Reviews are bound to the actual completed booking/shop and protected by a database unique constraint.
- Booking and contact notification work runs after commit; booking mail failures cannot make a committed booking appear to fail.
- Public booking, availability, guest-management, contact, registration, verification, password-reset, and confirmation-sensitive routes are throttled.
- Composer and npm report no known advisories, including zero high/critical findings.

## Product and UI evidence

- Domluveno tokens, self-hosted Manrope, Lucide iconography, shared controls, clear focus treatment, reduced-motion handling, accessible status/feedback, and 44px interaction targets are in place.
- Customer discovery uses one search contract and one pagination model; misleading totals, background-check claims, proximity sorting, duplicate loading, and mock reviews are absent.
- The booking journey is linear and exposes price, currency, timezone, cancellation deadline, and guest management before confirmation.
- Customer history separates upcoming and past bookings and exposes only valid cancel, review, and rebook actions.
- Provider desktop uses a sidebar; mobile uses a compact header and five-item bottom navigation. Tables convert to readable cards at narrow widths.
- Dashboard and customer totals never combine currencies; the calendar collision algorithm remains isolated under unit tests and mobile opens to the current day.
- Shop covers accept JPG/PNG/WebP up to 5 MB, normalize to 1600×900 WebP, and replace the old asset only after the new asset succeeds.
- Czech is the default interface; core public, booking, authentication, profile, legal, help, and onboarding surfaces retain English UI copy and locale-aware formatting.

## Browser journeys

The disposable Playwright environment migrates and seeds a dedicated SQLite database, launches the application on an isolated port, and runs:

1. Guest search, shop discovery, live availability, full booking review, secure management, cancellation, and contact.
2. Customer login, upcoming/history view, rebook, reviews, profile, and logout.
3. Verified provider dashboard, responsive calendar, bookings, customers, and shops.
4. FAQ, privacy, terms, registration entry, password-reset entry, and Czech/English switching.

Each journey runs at all three acceptance viewport sizes. Focused PHP tests cover the destructive/mutation and denial variants that should not be replayed repeatedly in a shared browser data set.

## Intentional exclusions

Payments, messaging, bookmark functionality, geolocation sorting, and dark mode are not part of this release. The bookmark table remains dormant for migration safety; it has no active query or UI flow.

## Handoff checklist

- Approve the Domluveno name, trademark, domain, legal text, privacy contacts, and support mailbox.
- Supply production secrets and set `APP_ENV=production`, `APP_DEBUG=false`, the canonical URL, secure session/cookie settings, and trusted proxy/host configuration.
- Configure a durable queue worker and mail transport, persistent public storage/CDN, database backups, error monitoring, uptime checks, and alerting.
- Run the same release gates in CI against the deployment artifact and production database engine before traffic cutover.
- Perform a rollback rehearsal and a final smoke test using production-like mail, queue, storage, and HTTPS behavior.

See the [complete route and flow matrix](./route-flow-matrix.md) for per-route ownership, expected behavior, and evidence.
