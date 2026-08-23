# Domluveno Route and Flow Verification Matrix

**Captured:** 2026-08-23

**Live route count:** 78 (`php artisan route:list --json`; `GET|HEAD` counts as one route)

**Release evidence:** 68 PHPUnit tests, 5 Vitest tests, 12 Playwright journeys, three responsive sizes, WCAG A/AA Axe scans, manual browser inspection, fresh migration/seed, client/SSR builds, and dependency audits.

This matrix supersedes the historical March 2026 route and release claims. Every currently registered route appears exactly once below. A route marked `Framework` is owned by Laravel/server configuration and is not presented as an application product flow.

## Evidence and access legend

- `F` — direct or flow-level Laravel feature-test evidence.
- `U` — focused Vitest/unit evidence.
- `E` — exercised by Playwright against a fresh disposable database.
- `A` — automated WCAG 2 A/AA scan on the rendered surface.
- `M` — manual browser/visual inspection.
- `C` — route/config/view cache and registration check.
- `PUB` — public; `GUEST` — signed-out only; `AUTH` — authenticated customer; `VER` — authenticated and email-verified; `VENDOR` — verified provider; `API-AUTH` — authenticated JSON endpoint; `SYS` — framework/server surface.

Expected denial is consistent across the matrix: guests are redirected to login, unverified providers to verification, non-providers away from the provider workspace, and cross-owner resources return `403` or a non-disclosing `404`.

## Complete route matrix

| # | Method and path | Name | Access | Expected outcome | Evidence | Status |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | `GET\|HEAD /` | `home` | PUB | Discovery landing page with one search and live categories | F/E/A/M/C | Verified |
| 2 | `POST /api/notifications/mark-all-read` | `notifications.markAllRead` | API-AUTH | Mark only the current user's notifications read | F/C | Verified |
| 3 | `GET\|HEAD /api/notifications/recent` | `notifications.recent` | API-AUTH | Return only the current user's recent notifications | F/E/C | Verified |
| 4 | `DELETE /api/notifications/{notification}` | `notifications.destroy` | API-AUTH | Delete an owned notification; reject another user's | F/C | Verified |
| 5 | `POST /api/notifications/{notification}/read` | `notifications.read` | API-AUTH | Mark an owned notification read; reject another user's | F/C | Verified |
| 6 | `GET\|HEAD /become-vendor` | `vendor.onboarding.index` | VER | Resume at the next valid onboarding step | F/C | Verified |
| 7 | `GET\|HEAD /become-vendor/step1` | `vendor.onboarding.step1` | VER | Render business/contact step; existing provider redirected | F/C | Verified |
| 8 | `POST /become-vendor/step1` | `vendor.onboarding.step1.store` | VER | Validate and persist resumable business/contact state | F/C | Verified |
| 9 | `GET\|HEAD /become-vendor/step2` | `vendor.onboarding.step2` | VER | Render shop step only after step 1 | F/C | Verified |
| 10 | `POST /become-vendor/step2` | `vendor.onboarding.step2.store` | VER | Validate Czech location, currency, and hours | F/C | Verified |
| 11 | `GET\|HEAD /become-vendor/step3` | `vendor.onboarding.step3` | VER | Render services/review step only after prior steps | F/C | Verified |
| 12 | `POST /become-vendor/step3` | `vendor.onboarding.step3.store` | VER | Atomically and idempotently create provider, shop, hours, services | F/C | Verified |
| 13 | `POST /bookings` | `bookings.store` | PUB | Throttled, server-derived booking; guest or account confirmation path | F/E/A/C | Verified |
| 14 | `GET\|HEAD /bookings` | `bookings.index` | AUTH | Owned upcoming/history list with legal actions only | F/E/A/M/C | Verified |
| 15 | `GET\|HEAD /bookings/confirmation/{id}` | `bookings.confirmation` | AUTH | Owned booking confirmation; cross-customer `404` | F/C | Verified |
| 16 | `POST /bookings/{id}/cancel` | `bookings.cancel` | AUTH | Cancel owned active booking at or before the 24-hour boundary | F/C | Verified |
| 17 | `GET\|HEAD /confirm-password` | `password.confirm` | AUTH | Render password confirmation | F/C | Verified |
| 18 | `POST /confirm-password` | unnamed | AUTH | Confirm correct password; rate-limit and reject invalid input | F/C | Verified |
| 19 | `GET\|HEAD /contact` | `pages.contact` | PUB | Honest Czech/English contact surface | F/E/A/C | Verified |
| 20 | `POST /contact` | `pages.contact.submit` | PUB | Validate, persist, and queue support email after commit | F/C | Verified |
| 21 | `GET\|HEAD /dashboard` | `dashboard` | AUTH | Route customer, onboarding user, or provider correctly | F/E/C | Verified |
| 22 | `POST /email/verification-notification` | `verification.send` | AUTH | Throttled verification email dispatch | F/C | Verified |
| 23 | `GET\|HEAD /faq` | `pages.faq` | PUB | Capability-accurate Czech/English FAQ | F/E/A/C | Verified |
| 24 | `GET\|HEAD /forgot-password` | `password.request` | GUEST | Render reset request | F/E/A/C | Verified |
| 25 | `POST /forgot-password` | `password.email` | GUEST | Send reset link without account disclosure | F/C | Verified |
| 26 | `GET\|HEAD /guest/bookings/{booking}/{token}` | `guest.bookings.show` | PUB+TOKEN | Show only with valid opaque token | F/E/A/C | Verified |
| 27 | `POST /guest/bookings/{booking}/{token}/cancel` | `guest.bookings.cancel` | PUB+TOKEN | Token-authorized cancellation with 24-hour rule | F/E/C | Verified |
| 28 | `POST /guest/bookings/{booking}/{token}/claim` | `guest.bookings.claim` | VER | Claim all matching guest bookings, then invalidate tokens | F/C | Verified |
| 29 | `GET\|HEAD /language/{locale}` | `language.switch` | PUB | Switch only `cs`/`en` and return safely | F/E/A/C | Verified |
| 30 | `GET\|HEAD /login` | `login` | GUEST | Render localized login | F/E/A/M/C | Verified |
| 31 | `POST /login` | unnamed | GUEST | Authenticate valid user; reject/rate-limit invalid attempts | F/E/C | Verified |
| 32 | `POST /logout` | `logout` | AUTH | Invalidate session and return home | F/E/C | Verified |
| 33 | `GET\|HEAD /my-reviews` | `reviews.user` | AUTH | Show only the current user's reviews | F/E/A/C | Verified |
| 34 | `PUT /password` | `password.update` | AUTH | Require current password and update securely | F/C | Verified |
| 35 | `GET\|HEAD /privacy` | `pages.privacy` | PUB | Capability-accurate Czech/English privacy notice | F/E/A/C | Verified |
| 36 | `GET\|HEAD /profile` | `profile.edit` | AUTH | Render current user's profile | F/E/A/M/C | Verified |
| 37 | `PATCH /profile` | `profile.update` | AUTH | Update owned profile and verification state correctly | F/C | Verified |
| 38 | `DELETE /profile` | `profile.destroy` | AUTH | Delete only current account after password confirmation | F/C | Verified |
| 39 | `GET\|HEAD /register` | `register` | GUEST | Render customer/provider account choice | F/E/A/C | Verified |
| 40 | `POST /register` | unnamed | GUEST | Create customer or verification-gated provider candidate | F/C | Verified |
| 41 | `POST /reset-password` | `password.store` | GUEST | Validate token and update password | F/C | Verified |
| 42 | `GET\|HEAD /reset-password/{token}` | `password.reset` | GUEST | Render reset form for valid token | F/C | Verified |
| 43 | `POST /reviews` | `reviews.store` | AUTH | Bind review to owned completed booking's real shop; unique per booking | F/C | Verified |
| 44 | `GET\|HEAD /reviews/create/{bookingId}` | `reviews.create` | AUTH | Render only for eligible booking owner | F/C | Verified |
| 45 | `GET\|HEAD /sanctum/csrf-cookie` | `sanctum.csrf-cookie` | SYS | Framework CSRF-cookie response | C | Framework |
| 46 | `GET\|HEAD /shops` | `shops.index` | PUB | Search/filter/sort with one pagination model | F/E/A/M/C | Verified |
| 47 | `GET\|HEAD /shops/{shop}/availability` | `shops.availability` | PUB | Throttled slots plus timezone/closed reason; reject foreign service | F/E/C | Verified |
| 48 | `GET\|HEAD /shops/{slug}` | `shops.show` | PUB | Active shop, cover/fallback, services, approved reviews, real contacts | F/E/A/M/C | Verified |
| 49 | `GET\|HEAD /shops/{slug}/book` | `shops.book` | PUB | Linear service/date/time/contact/review booking entry | F/E/A/M/C | Verified |
| 50 | `GET\|HEAD /storage/{path}` | `storage.local` | SYS | Laravel local public-disk serving behavior | C | Framework |
| 51 | `PUT /storage/{path}` | `storage.local.upload` | SYS | Laravel local temporary-upload behavior; environment-controlled | C | Framework |
| 52 | `GET\|HEAD /terms` | `pages.terms` | PUB | Capability-accurate Czech/English terms | F/E/A/C | Verified |
| 53 | `GET\|HEAD /up` | unnamed | SYS | Minimal health response | C | Framework |
| 54 | `GET\|HEAD /vendor/bookings` | `vendor.bookings.index` | VENDOR | Owned bookings, responsive table/cards, currency-safe totals | F/E/A/M/C | Verified |
| 55 | `GET\|HEAD /vendor/bookings/{id}` | `vendor.bookings.show` | VENDOR | Owned detail only; cross-provider `404` | F/M/C | Verified |
| 56 | `POST /vendor/bookings/{id}/cancel` | `vendor.bookings.cancel` | VENDOR | Cancel active owned booking with required persisted reason | F/C | Verified |
| 57 | `POST /vendor/bookings/{id}/complete` | `vendor.bookings.complete` | VENDOR | Complete confirmed owned booking only after start | F/C | Verified |
| 58 | `POST /vendor/bookings/{id}/confirm` | `vendor.bookings.confirm` | VENDOR | Confirm owned pending booking | F/C | Verified |
| 59 | `POST /vendor/bookings/{id}/notes` | `vendor.bookings.notes` | VENDOR | Add private note to owned booking | F/C | Verified |
| 60 | `POST /vendor/bookings/{id}/update` | `vendor.bookings.update` | VENDOR | Enforce enum transition graph and ownership | F/C | Verified |
| 61 | `GET\|HEAD /vendor/calendar` | `vendor.calendar` | VENDOR | Owned calendar; day on mobile, week on larger screens; collision-safe layout | F/U/E/A/M/C | Verified |
| 62 | `GET\|HEAD /vendor/customers` | `vendor.customers.index` | VENDOR | Registered customers from owned bookings, no guest collapse | F/E/A/M/C | Verified |
| 63 | `GET\|HEAD /vendor/customers/{customerId}` | `vendor.customers.show` | VENDOR | Customer detail only when related to provider | F/M/C | Verified |
| 64 | `GET\|HEAD /vendor/dashboard` | `vendor.dashboard` | VENDOR | Time-aware metrics and per-currency totals | F/E/A/M/C | Verified |
| 65 | `GET\|HEAD /vendor/shops` | `vendor.shops.index` | VENDOR | Owned shops in responsive cards/table | F/E/A/M/C | Verified |
| 66 | `POST /vendor/shops` | `vendor.shops.store` | VENDOR | Create owned shop, hours, and normalized optional cover image | F/C | Verified |
| 67 | `GET\|HEAD /vendor/shops/create` | `vendor.shops.create` | VENDOR | Render shared shop form | F/C | Verified |
| 68 | `GET\|HEAD /vendor/shops/{id}` | `vendor.shops.show` | VENDOR | Owned shop management detail; cross-provider `404` | F/M/C | Verified |
| 69 | `PUT /vendor/shops/{id}` | `vendor.shops.update` | VENDOR | Update owned shop/hours/image; retain old image on failed replacement | F/C | Verified |
| 70 | `DELETE /vendor/shops/{id}` | `vendor.shops.destroy` | VENDOR | Delete only owned shop after UI confirmation | F/C | Verified |
| 71 | `GET\|HEAD /vendor/shops/{id}/edit` | `vendor.shops.edit` | VENDOR | Render shared form for owned shop only | F/M/C | Verified |
| 72 | `POST /vendor/shops/{id}/toggle-availability` | `vendor.shops.toggle` | VENDOR | Toggle only owned shop with toast feedback | F/C | Verified |
| 73 | `POST /vendor/shops/{shopId}/business-hours` | `vendor.shops.business-hours.store` | VENDOR | Atomically replace owned shop hours; closed days stay unbookable | F/C | Verified |
| 74 | `POST /vendor/shops/{shopId}/services` | `vendor.shops.services.store` | VENDOR | Add validated service to owned shop | F/C | Verified |
| 75 | `PUT /vendor/shops/{shopId}/services/{serviceId}` | `vendor.shops.services.update` | VENDOR | Update service only inside owned shop | F/C | Verified |
| 76 | `DELETE /vendor/shops/{shopId}/services/{serviceId}` | `vendor.shops.services.destroy` | VENDOR | Delete service only inside owned shop | F/C | Verified |
| 77 | `GET\|HEAD /verify-email` | `verification.notice` | AUTH | Render verification prompt | F/C | Verified |
| 78 | `GET\|HEAD /verify-email/{id}/{hash}` | `verification.verify` | AUTH+SIGNED | Verify only valid signed subject; throttle invalid/repeated attempts | F/C | Verified |

## Flow-level acceptance result

| Flow | Evidence | Result |
| --- | --- | --- |
| Guest discovery → search/filter → shop → availability → booking → secure management → cancellation → contact | Feature tests plus Playwright/Axe at all three sizes | Pass |
| Customer registration/auth/reset/profile → booking history → review/rebook → logout | Auth/profile/review feature tests plus Playwright/Axe account/history/review/rebook/logout | Pass |
| Provider registration/verification → guarded onboarding → dashboard/calendar/bookings/customers/shops/services/hours/images/notifications | Feature tests cover mutations, authorization, onboarding, and images; Playwright/Axe covers the responsive operational shell | Pass |
| Unauthorized/cross-role/invalid token/terminal status/24-hour boundary/tampered payload/cross-currency behavior | Focused feature and unit suites | Pass |
| Responsive visual behavior at 390×844, 768×1024, and 1440×900 | Playwright overflow assertions, Axe, and manual browser inspection | Pass |

## Scope note

Payments, messaging, bookmarks, proximity sorting, and dark mode remain intentionally outside the product surface. The dormant bookmark table is retained to avoid a destructive migration; no bookmark query or UI is used by the marketplace flows.
