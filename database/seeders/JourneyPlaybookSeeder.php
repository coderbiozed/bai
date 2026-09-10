<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Journey;
use App\Models\JourneyStep;
use App\Models\Prompt;
use App\Models\Subcategory;
use App\Support\AiRecommendation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class JourneyPlaybookSeeder extends Seeder
{
    private const TIP = 'Verified step: complete this prompt, save the output, then move to the next step. Do not skip ahead.';

    public function run(): void
    {
        foreach ($this->playbooks() as $index => $playbook) {
            $category = Category::query()->where('slug', $playbook['category_slug'])->first();

            $journey = Journey::query()->updateOrCreate(
                ['slug' => $playbook['slug']],
                [
                    'category_id' => $category?->id,
                    'title' => $playbook['title'],
                    'tagline' => $playbook['tagline'],
                    'description' => $playbook['description'],
                    'outcome' => $playbook['outcome'],
                    'is_featured' => $playbook['is_featured'] ?? false,
                    'is_verified' => true,
                    'is_free' => true,
                    'sort_order' => $index + 1,
                ]
            );

            JourneyStep::query()->where('journey_id', $journey->id)->delete();

            foreach ($playbook['steps'] as $stepNumber => $step) {
                $promptId = $this->syncLibraryPrompt($playbook, $step, $stepNumber + 1);
                $ai = AiRecommendation::for(
                    $playbook['category_slug'] ?? null,
                    $playbook['library_subcategory_slug'] ?? null
                );

                JourneyStep::query()->create([
                    'journey_id' => $journey->id,
                    'prompt_id' => $promptId,
                    'step_number' => $stepNumber + 1,
                    'title' => $step['title'],
                    'goal' => $step['goal'],
                    'instructions' => $step['instructions'],
                    'prompt_body' => $step['prompt_body'],
                    'tip_note' => self::TIP,
                    'recommended_platform' => $ai['platform'],
                    'recommended_model' => $ai['model'],
                    'is_verified' => true,
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $playbook
     * @param  array<string, mixed>  $step
     */
    private function syncLibraryPrompt(array $playbook, array $step, int $stepNumber): ?int
    {
        if (empty($playbook['library_subcategory_slug']) || empty($playbook['category_slug'])) {
            return null;
        }

        $category = Category::query()->where('slug', $playbook['category_slug'])->first();
        if (! $category) {
            return null;
        }

        $subcategory = Subcategory::query()->firstOrCreate(
            [
                'category_id' => $category->id,
                'slug' => $playbook['library_subcategory_slug'],
            ],
            [
                'name' => $playbook['library_subcategory_name'] ?? 'Journey Steps',
                'description' => 'Step prompts synced from verified playbooks.',
                'sort_order' => 99,
            ]
        );

        $title = $playbook['title'].' — Step '.$stepNumber.': '.$step['title'];
        $slug = Str::slug($title);
        $ai = AiRecommendation::for(
            $playbook['category_slug'] ?? null,
            $playbook['library_subcategory_slug'] ?? null
        );

        $prompt = Prompt::query()->updateOrCreate(
            [
                'subcategory_id' => $subcategory->id,
                'slug' => $slug,
            ],
            [
                'title' => $title,
                'body' => $step['prompt_body'],
                'tip_note' => self::TIP,
                'recommended_platform' => $ai['platform'],
                'recommended_model' => $ai['model'],
                'is_best' => $stepNumber === 1,
                'is_verified' => true,
                'verified_by' => 'bAI Verified Curriculum',
                'verified_at' => Carbon::now(),
                'is_public' => true,
                'is_free' => true,
                'status' => 'published',
            ]
        );

        if ($prompt->versions()->count() === 0) {
            $prompt->recordVersion('Journey step sync');
        }

        return $prompt->id;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function playbooks(): array
    {
        return [
            [
                'title' => 'Become a Great YouTuber',
                'slug' => 'become-a-great-youtuber',
                'category_slug' => 'youtube',
                'library_subcategory_slug' => 'youtuber-playbook',
                'library_subcategory_name' => 'YouTuber Playbook',
                'tagline' => 'Click once. Follow every step. Build a real channel system.',
                'description' => 'A verified 15-step playbook from niche to publishing system. Each step gives you a ready prompt — copy, run, save the output, continue.',
                'outcome' => 'A positioned channel, content pillars, scripts, packaging, production checklist, and 90-day plan.',
                'is_featured' => true,
                'steps' => [
                    [
                        'title' => 'Clarify your niche',
                        'goal' => 'One clear audience + promise',
                        'instructions' => 'Answer the prompt with your interests and skills. Keep the niche specific enough to recognize, broad enough to publish weekly.',
                        'prompt_body' => "Act as a YouTube Channel Strategist with 12+ years advising creator businesses.\n\nHelp me choose a durable niche.\n\nAsk me for: skills, interests, audience I can help, and formats I enjoy filming.\nThen deliver:\n1) 3 niche options\n2) who each is for\n3) promise in one sentence\n4) risks of each niche\n5) your recommended pick with reasons\n\nStandards: specific, honest, no “get rich quick” framing.",
                    ],
                    [
                        'title' => 'Build your viewer persona',
                        'goal' => 'Know who you film for',
                        'instructions' => 'Use your chosen niche. Make the persona concrete enough to write hooks.',
                        'prompt_body' => "Act as a Creator Economy Coach with 10+ years helping creators monetize sustainably.\n\nUsing my niche, create one primary viewer persona.\nInclude: goals, frustrations, language they use, where they discover videos, what “success” means after watching, and content they already binge.\n\nDeliver a one-page persona and 10 phrases they would actually say.",
                    ],
                    [
                        'title' => 'Define content pillars',
                        'goal' => '3–5 repeatable topic pillars',
                        'instructions' => 'Pillars keep you consistent without repeating yourself.',
                        'prompt_body' => "Act as a Content Series Architect with 9+ years packaging educational content.\n\nDesign 4 content pillars for my niche and persona.\nFor each pillar give: name, viewer job-to-be-done, example video titles (5), and how often to publish it.\n\nAlso show a simple weekly mix so the channel feels coherent.",
                    ],
                    [
                        'title' => 'Write your channel positioning',
                        'goal' => 'About section + brand voice',
                        'instructions' => 'This becomes your About text and creative north star.',
                        'prompt_body' => "Act as a YouTube Channel Strategist specializing in positioning.\n\nWrite:\n1) Channel one-liner\n2) About section (short + long)\n3) Brand voice rules (do / don’t)\n4) 5 signature phrases I can reuse on camera\n\nKeep it human, specific, and free of empty superlatives.",
                    ],
                    [
                        'title' => 'Plan your first 12 video ideas',
                        'goal' => 'A starter content slate',
                        'instructions' => 'Balance search demand, binge potential, and filming feasibility.',
                        'prompt_body' => "Act as a YouTube packaging and growth strategist.\n\nGenerate 12 video ideas for my niche.\nFor each idea include: working title, pillar, viewer intent, difficulty to film (1–5), and why it earns a click honestly.\n\nRank the top 5 to film first for a new channel.",
                    ],
                    [
                        'title' => 'Craft irresistible (honest) hooks',
                        'goal' => 'First 15 seconds that earn attention',
                        'instructions' => 'Pick one video idea and generate hooks you can test.',
                        'prompt_body' => "Act as a YouTube Scriptwriter with 11+ years writing educational video scripts.\n\nFor this video idea, write 12 cold-open hooks under 12 seconds of speech.\nRules: create curiosity without lying, preview the payoff, sound natural on camera.\n\nMark your top 3 and explain why.",
                    ],
                    [
                        'title' => 'Write a full video script',
                        'goal' => 'A speakable 8–12 minute script',
                        'instructions' => 'Use your best hook. Keep sections clear.',
                        'prompt_body' => "Act as a YouTube Scriptwriter for educational creators.\n\nWrite a full script (8–12 minutes spoken) with:\n- Hook\n- Context\n- 3–5 teaching sections\n- Examples\n- Common mistakes\n- Recap\n- Soft CTA\n\nAdd brief stage directions (B-roll / on-screen text). Write for speaking, not essays.",
                    ],
                    [
                        'title' => 'Package titles that match the video',
                        'goal' => '20 title options, ranked',
                        'instructions' => 'Choose titles that match the real content.',
                        'prompt_body' => "Act as a YouTube Packaging Specialist with 10+ years optimizing titles with integrity.\n\nCreate 20 title options for my scripted video.\nSplit into: search-intent titles and browse/curiosity titles.\nRank the safest high-performing 5 and warn about any clickbait risk.",
                    ],
                    [
                        'title' => 'Brief your thumbnail',
                        'goal' => 'A clear thumbnail creative brief',
                        'instructions' => 'Even if you design it yourself, write the brief first.',
                        'prompt_body' => "Act as a Thumbnail Art Director with 9+ years in attention design for video.\n\nWrite a thumbnail brief including: emotion, subject, composition, text (3 words max), color contrast, mobile readability, and 3 concept variations.\n\nAlso list what NOT to do for this niche.",
                    ],
                    [
                        'title' => 'Build your filming checklist',
                        'goal' => 'A repeatable shoot setup',
                        'instructions' => 'Optimize for consistency over cinema gear.',
                        'prompt_body' => "Act as a YouTube Producer with 12+ years producing online video.\n\nCreate a one-camera filming checklist for solo creators:\ngear minimum, framing, lighting, audio, backup takes, file naming, and a 90-minute batch-shoot schedule.\n\nKeep it practical for a bedroom or small office.",
                    ],
                    [
                        'title' => 'Edit for retention',
                        'goal' => 'An editing pacing guide',
                        'instructions' => 'Use this while editing your first video.',
                        'prompt_body' => "Act as a Video Editor Mentor with 9+ years editing educational YouTube.\n\nCreate an editing pacing guide for tutorial/explainer videos:\ncut rhythm, pattern interrupts, zoom/emphasis rules, B-roll placement, text-on-screen rules, and a rough cut → final cut checklist.\n\nFocus on average view duration, not flashy effects.",
                    ],
                    [
                        'title' => 'Write description, tags, chapters',
                        'goal' => 'Publish-ready metadata',
                        'instructions' => 'Complete this before uploading.',
                        'prompt_body' => "Act as an SEO-informed YouTube strategist.\n\nFrom my title + script, produce:\n1) Description (with chapters)\n2) 15 tags\n3) Pinned comment\n4) End-screen idea\n5) Cards placement suggestions\n\nMatch search intent and keep claims honest.",
                    ],
                    [
                        'title' => 'Design your community loop',
                        'goal' => 'Comments that create next videos',
                        'instructions' => 'Turn audience replies into content fuel.',
                        'prompt_body' => "Act as a Creator Economy Coach specializing in community flywheels.\n\nDesign a weekly community loop:\nhow to ask for comments, how to reply in the first 2 hours, how to turn comments into future videos, and 10 question prompts that invite useful replies.\n\nNo engagement bait spam.",
                    ],
                    [
                        'title' => 'Create your analytics ritual',
                        'goal' => 'A weekly decision system',
                        'instructions' => 'Review data the same way every week.',
                        'prompt_body' => "Act as a YouTube Channel Strategist focused on analytics decision-making.\n\nCreate a 30-minute weekly analytics ritual:\nwhich metrics matter at my stage, what to ignore, how to diagnose CTR vs retention problems, and a decision tree for what to film next.\n\nInclude a simple scorecard template.",
                    ],
                    [
                        'title' => 'Build your 90-day roadmap',
                        'goal' => 'Cadence + milestones for 3 months',
                        'instructions' => 'This is your operating plan. Revisit monthly.',
                        'prompt_body' => "Act as a YouTube Channel Strategist and producer.\n\nBuild a realistic 90-day roadmap for a serious beginner-to-intermediate creator:\npublishing cadence, learning milestones, packaging experiments, community habits, and monetization readiness criteria.\n\nDeliver week-by-week themes and a “do not do yet” list.",
                    ],
                ],
            ],
            [
                'title' => 'Become a Freelance Laravel Developer',
                'slug' => 'become-freelance-laravel-developer',
                'category_slug' => 'business',
                'library_subcategory_slug' => 'laravel-freelance-playbook',
                'library_subcategory_name' => 'Laravel Freelance Playbook',
                'tagline' => 'From skills to paid retainers — step by step.',
                'description' => 'A verified playbook to package Laravel skills into offers, proposals, delivery, and retainers.',
                'outcome' => 'Offer, pricing, proposal system, delivery checklist, and first retainer plan.',
                'is_featured' => true,
                'steps' => [
                    [
                        'title' => 'Choose a paid specialty',
                        'goal' => 'A niche clients understand',
                        'instructions' => 'Pick a Laravel specialty you can deliver confidently.',
                        'prompt_body' => "Act as a Freelance Business Coach with 14+ years helping specialists productize services.\n\nHelp me choose a freelance Laravel specialty.\nAsk for my strongest skills, preferred project types, and income goal.\nThen propose 3 specialty options with target clients, example offers, and positioning lines.",
                    ],
                    [
                        'title' => 'Design your offer',
                        'goal' => 'Outcome-based packaging',
                        'instructions' => 'Sell outcomes, not hours.',
                        'prompt_body' => "Act as a Senior Independent Consultant with 12+ years selling expertise B2B.\n\nDesign one clear Laravel freelance offer:\nwho it’s for, problem solved, deliverables, timeline, price anchors, and what’s out of scope.\n\nOutput an offer one-pager.",
                    ],
                    [
                        'title' => 'Write discovery questions',
                        'goal' => 'Diagnose before you pitch',
                        'instructions' => 'Use on every sales call.',
                        'prompt_body' => "Act as a consultative sales coach for technical freelancers.\n\nWrite a discovery-call script for Laravel projects:\nopening, diagnostic questions, qualification criteria, and how to close for a paid audit or proposal next step.",
                    ],
                    [
                        'title' => 'Build a proposal template',
                        'goal' => 'Reusable proposal structure',
                        'instructions' => 'Fill this for each opportunity.',
                        'prompt_body' => "Act as a Freelance Business Coach.\n\nCreate a Laravel project proposal template with sections for problem, approach, milestones, assumptions, exclusions, pricing options, and next steps.\nInclude sample wording I can adapt.",
                    ],
                    [
                        'title' => 'Create a delivery checklist',
                        'goal' => 'Reliable client delivery',
                        'instructions' => 'Protect quality and reputation.',
                        'prompt_body' => "Act as a Principal Laravel Architect with 15+ years of enterprise experience.\n\nCreate a freelance delivery checklist for Laravel apps: kickoff, environment, coding standards, testing, staging QA, handoff docs, and warranty window.\nKeep it practical for a solo developer.",
                    ],
                    [
                        'title' => 'Design a retainer',
                        'goal' => 'Monthly recurring revenue offer',
                        'instructions' => 'Turn good clients into ongoing work.',
                        'prompt_body' => "Act as a Freelance Business Coach specializing in retainers.\n\nDesign a Laravel care/retainer offer: what’s included each month, response times, fair boundaries, pricing tiers, and a simple onboarding checklist.",
                    ],
                ],
            ],
            [
                'title' => 'Master SEO Content Systems',
                'slug' => 'master-seo-content-systems',
                'category_slug' => 'marketing',
                'library_subcategory_slug' => 'seo-systems-playbook',
                'library_subcategory_name' => 'SEO Systems Playbook',
                'tagline' => 'From audit to topical authority — in order.',
                'description' => 'A verified SEO playbook for building a content system that ranks with integrity.',
                'outcome' => 'Audit, cluster map, briefs, internal links, and reporting ritual.',
                'is_featured' => true,
                'steps' => [
                    [
                        'title' => 'Run a technical baseline',
                        'goal' => 'Know what blocks ranking',
                        'instructions' => 'Start with crawl/index health before more content.',
                        'prompt_body' => "Act as an SEO consultant with 15 years of experience in technical SEO.\n\nGuide me through a prioritized technical SEO baseline audit.\nCover crawlability, indexation, speed, structured data, and canonical issues.\nDeliver a ranked fix list with expected impact.",
                    ],
                    [
                        'title' => 'Build topic clusters',
                        'goal' => 'Intent-based content map',
                        'instructions' => 'Map topics before writing.',
                        'prompt_body' => "Act as a Content SEO Strategist with 9+ years mapping topics to intent.\n\nHelp me build a topic cluster map for my niche.\nInclude pillar pages, supporting articles, intent labels, and internal-link targets.",
                    ],
                    [
                        'title' => 'Write a reusable content brief',
                        'goal' => 'Brief template for every article',
                        'instructions' => 'Use this for writers or yourself.',
                        'prompt_body' => "Act as an SEO consultant specializing in content strategy.\n\nCreate a reusable SEO content brief template and fill one example for a target keyword.\nInclude search intent, outline, entities, FAQs, internal links, and success criteria.",
                    ],
                    [
                        'title' => 'Plan internal links',
                        'goal' => 'Authority flow across the cluster',
                        'instructions' => 'Connect new and existing pages.',
                        'prompt_body' => "Act as a Technical SEO Specialist.\n\nCreate an internal linking plan for my topic cluster: hub rules, anchor text guidance, orphan prevention, and a weekly linking checklist.",
                    ],
                    [
                        'title' => 'Set a monthly SEO ritual',
                        'goal' => 'Decisions from data',
                        'instructions' => 'Keep the system improving.',
                        'prompt_body' => "Act as an SEO consultant.\n\nDesign a monthly SEO operating ritual: metrics to review, content refresh rules, technical checks, and how to choose the next brief.\nInclude a one-page scorecard.",
                    ],
                ],
            ],
            [
                'title' => 'Ship Production-Ready React Features',
                'slug' => 'ship-production-ready-react-features',
                'category_slug' => 'development',
                'library_subcategory_slug' => 'react-feature-playbook',
                'library_subcategory_name' => 'React Feature Playbook',
                'tagline' => 'From ticket to accessible UI — step by step.',
                'description' => 'A verified engineering playbook for shipping React features with quality.',
                'outcome' => 'Component plan, a11y checks, state strategy, tests, and handoff notes.',
                'is_featured' => false,
                'steps' => [
                    [
                        'title' => 'Clarify the feature contract',
                        'goal' => 'Acceptance criteria you can build against',
                        'instructions' => 'Start before opening the editor.',
                        'prompt_body' => "Act as a Senior React Engineer with deep expertise in modern React.\n\nHelp me turn a vague feature request into a build contract: user stories, edge cases, empty/loading/error states, accessibility requirements, and out-of-scope list.",
                    ],
                    [
                        'title' => 'Design component boundaries',
                        'goal' => 'Clear component and state ownership',
                        'instructions' => 'Avoid prop-drilling messes early.',
                        'prompt_body' => "Act as a React + TypeScript Lead.\n\nPropose a component architecture for this feature: folder structure, presentational vs container components, state ownership, and data-fetching boundaries.",
                    ],
                    [
                        'title' => 'Plan accessibility',
                        'goal' => 'Keyboard and screen-reader ready UI',
                        'instructions' => 'Bake a11y into the plan.',
                        'prompt_body' => "Act as a Senior React Engineer with accessibility expertise.\n\nCreate an accessibility plan for this feature: roles, focus order, labels, error announcements, and a QA checklist.",
                    ],
                    [
                        'title' => 'Define a test plan',
                        'goal' => 'Confidence before merge',
                        'instructions' => 'Write the test list first.',
                        'prompt_body' => "Act as a Frontend engineer who values maintainable tests.\n\nCreate a pragmatic test plan for this React feature: unit, component, and critical path checks. List the first 10 tests to write.",
                    ],
                    [
                        'title' => 'Write the PR summary',
                        'goal' => 'Reviewer-ready handoff',
                        'instructions' => 'Use after implementation.',
                        'prompt_body' => "Act as a Staff Software Engineer mentoring through code review.\n\nWrite a PR summary template filled for this feature: purpose, screenshots checklist, test evidence, risks, and follow-ups.",
                    ],
                ],
            ],
            [
                'title' => 'Generate On-Brand Product Photos',
                'slug' => 'generate-on-brand-product-photos',
                'category_slug' => 'graphics',
                'library_subcategory_slug' => 'product-photo-playbook',
                'library_subcategory_name' => 'Product Photo Playbook',
                'tagline' => 'A repeatable image-prompt system for products.',
                'description' => 'Verified steps to build a reusable photo style and generate consistent product images.',
                'outcome' => 'Style lock, prompt templates, variations, and quality checklist.',
                'is_featured' => false,
                'steps' => [
                    [
                        'title' => 'Lock your visual style',
                        'goal' => 'A reusable style paragraph',
                        'instructions' => 'This style block will be pasted into every prompt.',
                        'prompt_body' => "Act as a commercial photographer art director.\n\nInterview me about my product and brand, then write a reusable photoreal style lock covering lens feel, lighting, color grade, background, composition, and exclusions.",
                    ],
                    [
                        'title' => 'Write the hero product prompt',
                        'goal' => 'One flagship image prompt',
                        'instructions' => 'Use the style lock.',
                        'prompt_body' => "Act as an Editorial Image Prompt Engineer.\n\nUsing my style lock, write a hero product photo prompt with subject details, camera direction, lighting, and negative prompt. Provide 3 slight variations.",
                    ],
                    [
                        'title' => 'Create a lifestyle set',
                        'goal' => '3 lifestyle scene prompts',
                        'instructions' => 'Keep brand consistency.',
                        'prompt_body' => "Act as a commercial photographer art director.\n\nCreate 3 lifestyle scene prompts for my product that stay on-brand, with notes for text-safe space if used in ads.",
                    ],
                    [
                        'title' => 'Build a QA checklist',
                        'goal' => 'Accept/reject generated images fast',
                        'instructions' => 'Use after every generation batch.',
                        'prompt_body' => "Act as an Image Prompt Engineer and art director.\n\nCreate a QA checklist for AI product photos: anatomy/geometry issues, brand mismatch, lighting errors, and marketplace readiness. Include fix-prompt patterns for common failures.",
                    ],
                ],
            ],
            [
                'title' => 'Become a Graphic & Video Shorts Maker',
                'slug' => 'become-graphic-video-shorts-maker',
                'category_slug' => 'graphics',
                'library_subcategory_slug' => 'shorts-maker-playbook',
                'library_subcategory_name' => 'Shorts Maker Playbook',
                'tagline' => 'From brand look to scroll-stopping Shorts — graphics + vertical video in one system.',
                'description' => 'A verified 10-step playbook for makers who design graphics and ship TikTok / Reels / YouTube Shorts. Each step gives you a ready prompt — copy, run, save the output, continue.',
                'outcome' => 'Visual brand lock, hook scripts, on-screen graphics system, shot lists, cover frames, batch calendar, and publish checklist.',
                'is_featured' => true,
                'steps' => [
                    [
                        'title' => 'Choose your Shorts niche',
                        'goal' => 'One clear audience + content promise',
                        'instructions' => 'Answer with your skills, topics you can film or design weekly, and which platforms you will publish on.',
                        'prompt_body' => "Act as a Vertical Video Strategist with 10+ years advising Shorts, Reels, and TikTok creators who also design their own graphics.\n\nHelp me lock a durable Shorts niche.\n\nAsk me for: skills, topics I can teach or show, design style I enjoy, platforms (TikTok / Reels / YouTube Shorts), and how often I can publish.\nThen deliver:\n1) 3 niche options\n2) who each is for\n3) promise in one sentence\n4) content formats that fit (talking head, screen recording, motion graphics, product demo)\n5) risks of each niche\n6) your recommended pick with reasons\n\nStandards: specific, honest, no viral-guarantee claims.",
                    ],
                    [
                        'title' => 'Lock your visual brand system',
                        'goal' => 'Reusable look for graphics + video',
                        'instructions' => 'This style lock gets pasted into every graphic and cover-frame prompt.',
                        'prompt_body' => "Act as a Motion Graphics Art Director with 12+ years building brand systems for short-form video.\n\nInterview me about my niche, brand personality, and preferred vibe, then write a reusable visual brand lock covering:\n- color palette (hex + usage)\n- typography hierarchy for on-screen text\n- graphic motifs / shapes / textures\n- motion feel (cuts, transitions, pace)\n- safe margins for 9:16\n- what to never use\n\nDeliver as a paste-ready style paragraph plus a one-page brand kit summary.",
                    ],
                    [
                        'title' => 'Build your content pillars',
                        'goal' => '4 pillars you can film weekly',
                        'instructions' => 'Pillars stop random posting. Keep them tied to your niche promise.',
                        'prompt_body' => "Act as a Short-Form Content Architect with 9+ years packaging educational and product Shorts.\n\nDesign 4 content pillars for my niche and visual brand.\nFor each pillar give: name, viewer job-to-be-done, 5 Shorts title ideas, ideal length (15–45s), and whether it leans graphic-led, face-led, or product-led.\n\nAlso show a simple weekly mix (e.g. 3 teach / 2 proof / 1 promo) so the feed feels coherent.",
                    ],
                    [
                        'title' => 'Write scroll-stopping hooks',
                        'goal' => '10 honest hooks that earn the first 3 seconds',
                        'instructions' => 'Use your niche and pillars. Hooks must match what the video actually delivers.',
                        'prompt_body' => "Act as a Retention Copywriter specializing in vertical video with 8+ years writing hooks for Shorts and Reels.\n\nUsing my niche and pillars, write 10 opening hooks (spoken + on-screen text versions).\nFor each hook include:\n1) spoken first line (under 2 seconds)\n2) on-screen text (max 6 words)\n3) which pillar it serves\n4) why it earns attention without clickbait\n\nStandards: specific, truthful, no fake urgency.",
                    ],
                    [
                        'title' => 'Script a full Short',
                        'goal' => 'One complete 20–40s script',
                        'instructions' => 'Pick one pillar idea. Script must be shootable with phone + simple graphics.',
                        'prompt_body' => "Act as a Shorts Scriptwriter and Vertical Video Producer with 10+ years writing retention-first short scripts.\n\nWrite one complete Shorts script (20–40 seconds) for my niche.\nStructure:\n- Hook (0–3s)\n- Setup / problem\n- Payoff / steps (max 3)\n- Proof or example\n- Soft CTA\n\nAlso deliver: on-screen text cues, B-roll / graphic callouts, estimated duration per beat, and a 1-line caption for the post.\n\nKeep language conversational and filmable.",
                    ],
                    [
                        'title' => 'Design on-screen graphics system',
                        'goal' => 'Templates for text, lower-thirds, end cards',
                        'instructions' => 'Use your visual brand lock. Output should work in CapCut / Premiere / Canva.',
                        'prompt_body' => "Act as a Motion Graphics Designer specializing in 9:16 social video with 11+ years shipping template systems for creators.\n\nUsing my visual brand lock, design a reusable on-screen graphics system for Shorts:\n1) title card (first frame)\n2) lower-third name plate\n3) bullet / tip callout style\n4) number badge for steps\n5) end card with CTA\n6) caption/subtitle style notes\n\nFor each element specify: size relative to frame, safe zone, animation in/out, and a Canva or CapCut-friendly build note.\nAlso write 3 AI image prompts for background plates that match the brand.",
                    ],
                    [
                        'title' => 'Brief cover frames & thumbnails',
                        'goal' => 'Clickable stills that match the Short',
                        'instructions' => 'Covers must match the hook — no bait-and-switch.',
                        'prompt_body' => "Act as a Thumbnail & Cover Frame Designer for Shorts/Reels with 10+ years packaging vertical video.\n\nUsing my script and visual brand lock, create:\n1) 3 cover-frame concepts (composition, focal subject, text overlay under 5 words)\n2) exact on-image text hierarchy\n3) color contrast check for mobile\n4) 2 AI image generation prompts for the base still\n5) what to avoid (clutter, tiny text, misleading faces)\n\nDeliver as a brief I can hand to myself or a designer in under 10 minutes.",
                    ],
                    [
                        'title' => 'Build a shot list & edit map',
                        'goal' => 'Film and edit without guessing',
                        'instructions' => 'Turn the script into a phone-friendly shot list and cut plan.',
                        'prompt_body' => "Act as a Vertical Video Director and Editor with 12+ years shooting phone-first Shorts for brands and creators.\n\nTurn my Shorts script into:\n1) shot list (angle, duration, audio, graphic overlay)\n2) gear minimum (phone + light + mic assumptions)\n3) edit map with cut points for retention\n4) music / SFX guidance (mood only, no copyrighted track names required)\n5) export settings checklist for TikTok, Reels, and YouTube Shorts (9:16, captions burned-in or separate)\n\nKeep it practical for a solo maker.",
                    ],
                    [
                        'title' => 'Create a batch production calendar',
                        'goal' => 'One filming day → one week of Shorts',
                        'instructions' => 'Optimize for batching graphics and video in the same session.',
                        'prompt_body' => "Act as a Creator Operations Coach with 9+ years helping solo Shorts makers batch content without burnout.\n\nDesign a weekly batch system for a Graphic & Video Shorts Maker:\n1) Monday–Sunday calendar with roles (ideate, film, design graphics, edit, schedule)\n2) how to turn 1 long idea into 5 Shorts + matching cover graphics\n3) asset folder naming convention\n4) reuse rules (hooks, end cards, brand kit)\n5) a realistic 60-minute filming checklist\n\nAssume I am solo and publish 5 Shorts per week.",
                    ],
                    [
                        'title' => 'Publish & measure ritual',
                        'goal' => 'Caption, tags, and weekly review loop',
                        'instructions' => 'Use after every upload. Improve hooks from real retention data.',
                        'prompt_body' => "Act as a Short-Form Growth Analyst with 8+ years reading retention graphs for TikTok, Reels, and YouTube Shorts.\n\nCreate my publish + measure ritual:\n1) caption template with hook, value, CTA, and hashtag rules (platform-aware)\n2) posting checklist (cover, captions, alt text, pinned comment)\n3) weekly metrics to review (3-second hold, average watch %, saves, shares)\n4) decision rules: remake, remix, or retire a format\n5) a prompt I can reuse to diagnose a Short that died early\n\nStandards: actionable, no vanity-metric fluff.",
                    ],
                ],
            ],
        ];
    }
}
