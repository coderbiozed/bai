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

**Live app:** https://bai-rwtf.onrender.com  
**GitHub:** https://github.com/coderbiozed/bai

### Slow first load?

Render’s **free** plan sleeps after ~15 minutes idle, so the first visit can wait 30–60s. A GitHub Action (`.github/workflows/keep-warm.yml`) pings `/up` every 10 minutes to keep it awake. For always-on hosting, upgrade the Render web service off the free plan.

### Important: Netlify / GitHub Pages cannot run Laravel

This app needs **PHP + sessions + SQLite**. Netlify and GitHub Pages are static hosts only.

- `netlify-site/` is a fast wake page that polls health then opens Render
- Real app runs on **Render** via Docker (`Dockerfile` + `render.yaml`)

### Render

Auto-deploys from `main`. Blueprint:

[Deploy to Render](https://dashboard.render.com/blueprint/new?repo=https://github.com/coderbiozed/bai)

### Netlify (wake page only)

```bash
npx netlify deploy --dir=netlify-site --prod
```

