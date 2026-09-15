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

- **2,000+ verified prompts** across Development, YouTube, Marketing, Design, Graphics, Business, Writing, Career, Education, plus Meta Manager, Content, Article/Blog Writer, Admin, Finances, SEO Pro, CTO, Math Learning, and English Learning
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
**Fast entry (recommended):** https://coderbiozed.github.io/bai/ — branded wake page, then opens the app  
**GitHub:** https://github.com/coderbiozed/bai

### Slow first load / “Application loading”?

Render’s **free** plan sleeps after idle and shows Render’s own loading screen until the container accepts traffic. We can’t remove that screen on the Render URL itself.

What we do instead:
1. **Keep-warm** GitHub Action pings `/up` every **5 minutes** (and on each push)
2. **Faster boot** — packaged DB + skip cache warming so the app opens the port sooner after wake
3. **Wake page** on GitHub Pages — share/bookmark that link so visitors see **bAI** branding while the server wakes, not Render’s spinner

For **zero** cold starts: upgrade the Render web service off the free plan (always on).

### Faster loads (what we optimize)

- Home + specialty lists are **cached ~3 minutes**
- Prompt previews are **truncated** (full text only on detail pages)
- Docker image ships **OPcache + config/route/view cache**
- Boot skips migrate when the DB schema already exists
- Keep-warm hits `/`, Instant Solutions, and journeys every 5 minutes

### Enable the wake page once

GitHub → **Settings → Pages → Source: GitHub Actions**. The `Deploy wake page` workflow publishes `docs/` to `https://coderbiozed.github.io/bai/`.

