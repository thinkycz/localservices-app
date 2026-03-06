# Local Services App

Marketplace-style app for discovering local services, booking providers, messaging, reviews, and payments.

## Tech Stack

- Laravel 12
- Inertia + Vue 3
- Tailwind CSS

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

- Contact Form
  - `CONTACT_TO_EMAIL` (admin inbox for contact submissions)


## Notes

- Uploaded service images use the `public` disk. Ensure `php artisan storage:link` has been run (included in `composer run setup`).
