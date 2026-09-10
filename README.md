# bAI

A professional web app for storing and browsing **role prompts** by category → subcategory → prompt.

Built for a phased product path:

1. **Library (now)** — curated free prompts, search, copy, mark best
2. **Share free (next)** — public gallery via `is_public` / `is_free`
3. **Generate & review (later)** — AI draft + refine with version history

## Stack

- Laravel 13
- Livewire 4
- Tailwind CSS 4
- SQLite (local)

## Run locally

```bash
composer install
cp .env.example .env   # if needed
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build          # or: npm run dev
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

## Seeded library

- **2,000+ verified prompts** across Development, YouTube, Marketing, Design, Graphics, Business, Writing, Career, Education
- **Step-by-step journeys** including **Become a Great YouTuber** (15 steps), **Become a Graphic & Video Shorts Maker** (10 steps), Freelance Laravel, SEO Systems, React Features, Product Photos
- Writing guide: prefer experience, domain, standards, behavior — avoid “world’s best”

## Journeys

| Path | Purpose |
|------|---------|
| `/journeys` | All step-by-step playbooks |
| `/journeys/become-a-great-youtuber` | YouTuber roadmap overview |
| `/journeys/become-a-great-youtuber/step/1` | Start at step 1, continue in order |

## Key routes

| Path | Purpose |
|------|---------|
| `/` | Home + categories |
| `/c/{category}` | Specialties |
| `/c/{category}/{subcategory}` | Prompts in specialty |
| `/c/{category}/{subcategory}/{prompt}` | Prompt detail + copy |
| `/prompts/create` | Add a prompt |
| `/search` | Full-text search |
| `/guide` | How to write prompts |

## Auth

Laravel Breeze auth is enabled.

- **Public:** browse library, journeys, search, copy prompts
- **Logged in:** create / edit / delete prompts, profile
- **Demo admin** (after seed): `admin@bestteacher.test` / `password`

```bash
php artisan db:seed --class=AdminUserSeeder
```

Routes: `/login`, `/register`, `/profile`, `/logout`

## Deploy

**GitHub:** https://github.com/coderbiozed/bai

### Important: Netlify cannot run Laravel

Netlify is for static sites / serverless JS. This app needs **PHP + sessions + SQLite/MySQL**.

- `netlify.toml` + `netlify-site/` = **placeholder page only** (safe to publish on Netlify as a note/redirect)
- Real app: use **Render** (`render.yaml` + `Dockerfile`) or Railway with the same Docker image

### Render (recommended for the full app)

One-click Blueprint (free Web Service):

[Deploy to Render](https://dashboard.render.com/blueprint/new?repo=https://github.com/coderbiozed/bai)

Or manually:

1. Open the link above (or Render → New → Blueprint → connect `coderbiozed/bai`)
2. Approve the `bai` Docker service from `render.yaml`
3. Deploy — `APP_KEY` is generated; migrate/seed run on boot

### Railway

Railway CLI is supported (`railway up`), but needs an active paid/trial plan.
