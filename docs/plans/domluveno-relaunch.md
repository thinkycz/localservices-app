# Domluveno Relaunch Plan

**Started:** 2026-08-23

**Status:** Completed and verified
**Source:** User-approved implementation plan from the Codex task

## Goal

Turn the existing local-services prototype into Domluveno: a Czech-first, honest, accessible marketplace focused on discovery, booking, reviews, provider onboarding, and provider operations.

## Delivery order

1. Reconcile inherited changes and replace stale readiness claims with fresh evidence.
2. Harden booking, review, authorization, guest access, and provider onboarding.
3. Establish the Domluveno design system and responsive application shells.
4. Polish customer and provider journeys, content, images, and empty states.
5. Add feature, unit, browser, accessibility, and responsive verification.
6. Run release gates and publish an evidence-backed readiness assessment.

## Fixed product decisions

- Brand: Domluveno (working name pending external legal/domain validation).
- Locale: Czech first, English secondary; `Europe/Prague` application timezone.
- Currency: CZK primary and per-shop EUR support; mixed currencies are never summed.
- Core scope: discovery, booking, reviews, onboarding, and provider operations.
- Excluded scope: payments, messaging, bookmarks, and geolocation sorting.
- Guest booking: secure emailed management link backed by a hashed token.
- Verification: optional for customers, required for providers and guest-booking claims.
- Cancellation: customer/guest cancellation requires at least 24 hours' notice.
- Framework baseline: Laravel 12, Vue 3, Inertia 2, Tailwind 3, Vite 7.

## Completion standard

Completion requires fresh automated tests, production client and SSR builds, fresh migration and seed, dependency audits without known high/critical advisories, route/cache checks, responsive browser replay of primary journeys, accessibility checks, and current verification docs. Source changes without this evidence remain implemented but unverified.

## Inherited worktree

The following pre-existing edits must be integrated, not reverted: booking/service naming cleanup, vendor onboarding/service naming cleanup, vendor shop service naming cleanup, booking and shop UI edits, the calendar overlap layout rewrite, and generated Ziggy changes. `.minimax/` is unrelated untracked state and remains untouched.
