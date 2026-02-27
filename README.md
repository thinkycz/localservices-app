# Local Services App

Marketplace-style app for discovering local services, booking providers, messaging, reviews, and payments.

## Tech Stack

- Laravel 12
- Inertia + Vue 3
- Tailwind CSS
- Stripe (payments)
- Socialite (Google/Facebook login)
- Scout (search)

## Quick Start

1. Copy environment file and set an app key:
   - Copy `.env.example` to `.env`
   - Set `APP_KEY` (or run `php artisan key:generate`)
2. Install dependencies and build assets:
   - `composer run setup`
3. Run the dev stack:
   - `composer run dev`

## Configuration

Set these in `.env` as needed:

- Stripe
  - `STRIPE_KEY`, `STRIPE_SECRET`
  - `STRIPE_WEBHOOK_SECRET` (recommended for production)
- Social Login
  - `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`
  - `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, `FACEBOOK_REDIRECT_URI`
- Contact Form
  - `CONTACT_TO_EMAIL` (admin inbox for contact submissions)
- Search
  - `SCOUT_DRIVER=database` (default)

## Stripe Webhook

- Endpoint: `POST /api/stripe/webhook`
- Configure the webhook in Stripe and set `STRIPE_WEBHOOK_SECRET`.

## Notes

- Uploaded service images use the `public` disk. Ensure `php artisan storage:link` has been run (included in `composer run setup`).
