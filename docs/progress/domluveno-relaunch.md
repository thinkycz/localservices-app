# Domluveno Relaunch Progress

**Updated:** 2026-08-23

**Overall status:** Implementation and verification complete — ready for staging/release handoff

## Completed slices

- [x] Preserved and regression-tested inherited service naming, shop, booking UI, calendar collision, and Ziggy work.
- [x] Reconciled the live application surface and documented all 78 registered routes.
- [x] Replaced stale release claims with current audit, matrix, and readiness evidence.
- [x] Hardened booking ownership, authoritative field derivation, transitions, availability, locking, cancellation, review integrity, and post-commit notifications.
- [x] Added guest booking snapshots, hashed management tokens, secure management/cancellation, verified-email claiming, and nullable account ownership.
- [x] Added verified, resumable, guarded, atomic, and idempotent provider onboarding.
- [x] Built the Domluveno brand/design system, self-hosted Manrope, reusable controls, focus/reduced-motion treatment, and responsive customer/provider shells.
- [x] Simplified discovery, search, shop, booking, booking-history, review, rebook, profile, legal, FAQ, and contact flows with Czech-first/English-secondary interface copy.
- [x] Simplified provider dashboard, calendar, bookings, customers, shops, services, hours, image, notification, and empty-state experiences.
- [x] Removed unused direct Chart.js, Lodash, and Vue Draggable dependencies; upgraded the Vue Vite plugin for Vite 7; added consistent test/audit scripts.
- [x] Passed formatting, PHP, Vitest, Playwright/Axe, client/SSR build, fresh migration/seed, cache, audit, and whitespace gates.

## Blockers

None in application code. Trademark/domain approval and production infrastructure configuration remain external pre-launch responsibilities.

## Evidence policy

Old March 2026 reports are historical only. Current evidence is recorded in [the route/flow matrix](../verification/route-flow-matrix.md) and [the release-readiness report](../verification/domluveno-release-readiness.md).
