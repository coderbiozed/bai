<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Journey;
use App\Models\JourneyStep;
use App\Models\Prompt;
use App\Models\Subcategory;
use App\Models\Tag;
use App\Support\AiRecommendation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NicheVerticalsSeeder extends Seeder
{
    private const VERIFIER = 'bAI Verified Curriculum';

    private const TIP = 'Verified niche prompt: specify product constraints, audience, compliance tone, and deliverable format. Avoid empty superlatives.';

    public function run(): void
    {
        $now = Carbon::now();
        $tagCache = [];
        $count = 0;

        foreach ($this->catalog() as $categoryIndex => $categoryData) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'accent' => $categoryData['accent'],
                    'sort_order' => 40 + $categoryIndex,
                ]
            );

            foreach ($categoryData['subcategories'] as $subIndex => $subData) {
                $subcategory = Subcategory::query()->updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'slug' => $subData['slug'],
                    ],
                    [
                        'name' => $subData['name'],
                        'description' => $subData['description'],
                        'sort_order' => $subIndex + 1,
                    ]
                );

                foreach ($subData['prompts'] as $promptData) {
                    $slug = Str::slug($promptData['title']);
                    $ai = AiRecommendation::for($categoryData['slug'], $subData['slug']);

                    $prompt = Prompt::query()->updateOrCreate(
                        [
                            'subcategory_id' => $subcategory->id,
                            'slug' => $slug,
                        ],
                        [
                            'title' => $promptData['title'],
                            'body' => $promptData['body'],
                            'tip_note' => self::TIP,
                            'recommended_platform' => $ai['platform'],
                            'recommended_model' => $ai['model'],
                            'is_best' => (bool) ($promptData['is_best'] ?? false),
                            'is_verified' => true,
                            'verified_by' => self::VERIFIER,
                            'verified_at' => $now,
                            'is_public' => true,
                            'is_free' => true,
                            'status' => 'published',
                        ]
                    );

                    if ($prompt->versions()->count() === 0) {
                        $prompt->recordVersion('Niche vertical seed');
                    }

                    $tagIds = [];
                    foreach ($promptData['tags'] ?? ['verified'] as $tagName) {
                        $tagSlug = Str::slug($tagName);
                        if (! isset($tagCache[$tagSlug])) {
                            $tagCache[$tagSlug] = Tag::query()->firstOrCreate(
                                ['slug' => $tagSlug],
                                ['name' => $tagName]
                            );
                        }
                        $tagIds[] = $tagCache[$tagSlug]->id;
                    }
                    $prompt->tags()->sync($tagIds);
                    $count++;
                }
            }
        }

        $this->seedJourneys();
        $this->command?->info("Niche vertical prompts upserted: {$count}");
    }

    private function seedJourneys(): void
    {
        $playbooks = [
            [
                'title' => 'Become a Fintech Social Media Designer',
                'slug' => 'become-fintech-social-media-designer',
                'category_slug' => 'fintech',
                'tagline' => 'Trust-first visuals for fintech brands — step by step.',
                'description' => 'Build a verified system for fintech product UI cues and social creatives that feel premium, compliant, and clear.',
                'outcome' => 'Style lock, post templates, story system, and a weekly content calendar.',
                'is_featured' => true,
                'steps' => [
                    [
                        'title' => 'Define trust-first visual rules',
                        'goal' => 'A fintech visual style lock',
                        'instructions' => 'Capture brand constraints before designing posts.',
                        'prompt_body' => "Act as a Senior Fintech Product Designer with 12+ years designing regulated financial products.\n\nCreate a trust-first visual style lock for a fintech brand covering: color psychology for finance, typography hierarchy, icon style, spacing, do/don’t examples, and compliance-safe imagery rules (no misleading wealth claims).\n\nDeliver a one-page style lock I can reuse for social and product surfaces.",
                    ],
                    [
                        'title' => 'Design social post templates',
                        'goal' => '6 reusable social templates',
                        'instructions' => 'Make templates for feed posts that stay on-brand.',
                        'prompt_body' => "Act as a Fintech Social Media Image Designer with 10+ years creating creatives for banks, wallets, and investment apps.\n\nDesign 6 social post templates (concept + layout notes + copy zones):\n1) educational tip\n2) product feature\n3) security/trust\n4) customer story (ethical)\n5) market insight\n6) CTA to app/waitlist\n\nFor each, specify dimensions, safe margins, and text hierarchy.",
                    ],
                    [
                        'title' => 'Build a stories/reels visual system',
                        'goal' => 'Vertical video/cover system',
                        'instructions' => 'Focus on mobile readability.',
                        'prompt_body' => "Act as a Fintech Social Media Image Designer specializing in Stories and Reels covers.\n\nCreate a vertical visual system: cover layouts, sticker/text rules, pacing for 15–30s explainers, and a checklist for readability on small screens.\nInclude 5 cover concepts for a wallet or investing app.",
                    ],
                    [
                        'title' => 'Weekly fintech content calendar',
                        'goal' => 'A 4-week posting plan',
                        'instructions' => 'Balance education, trust, and product.',
                        'prompt_body' => "Act as a Media Manager for fintech brands with 11+ years running social calendars in regulated categories.\n\nBuild a 4-week content calendar with themes, post types, design needs, approval notes, and risk flags (claims that need legal review).\nKeep it practical for a 2-person team.",
                    ],
                ],
            ],
            [
                'title' => 'Become a Travel Content Creator',
                'slug' => 'become-travel-content-creator',
                'category_slug' => 'travel',
                'tagline' => 'From niche to publishable travel content system.',
                'description' => 'A verified playbook for travel storytelling across writing, images, and social.',
                'outcome' => 'Niche, content pillars, shot list, captions, and a trip publishing workflow.',
                'is_featured' => true,
                'steps' => [
                    [
                        'title' => 'Pick a travel niche',
                        'goal' => 'Audience + promise',
                        'instructions' => 'Be specific: budget, luxury, family, adventure, etc.',
                        'prompt_body' => "Act as a Travel Content Creator coach with 12+ years building travel media brands.\n\nHelp me choose a durable travel niche. Propose 3 options with audience, promise, content pillars, monetization paths, and filming/writing difficulty.\nRecommend one with reasons.",
                    ],
                    [
                        'title' => 'Plan a destination content pack',
                        'goal' => 'One destination, multi-format pack',
                        'instructions' => 'Use for your next trip or city guide.',
                        'prompt_body' => "Act as a Travel Content Creator and media manager.\n\nFor one destination, create a content pack: 10 article/video angles, shot list, caption hooks, map of must-capture moments, and a 7-day publish schedule after the trip.",
                    ],
                    [
                        'title' => 'Write cinematic image prompts',
                        'goal' => 'Travel photo generation set',
                        'instructions' => 'Useful for covers, thumbnails, and moodboards.',
                        'prompt_body' => "Act as a travel image creator / commercial photographer art director.\n\nWrite 8 photoreal travel image prompts for my niche (hero cover, street food, hotel detail, landscape, night city, transport, local craft, candid traveler).\nInclude lens, light, composition, and exclusions for each.",
                    ],
                    [
                        'title' => 'Build a post-trip media workflow',
                        'goal' => 'From cards to published posts',
                        'instructions' => 'Make this your standard operating process.',
                        'prompt_body' => "Act as a Media Manager for travel creators.\n\nDesign a post-trip media workflow: file naming, selects, editing order, caption drafting, scheduling, UGC/rights checks, and a weekly analytics review.\nDeliver a checklist I can reuse every trip.",
                    ],
                ],
            ],
        ];

        foreach ($playbooks as $index => $playbook) {
            $category = Category::query()->where('slug', $playbook['category_slug'])->first();

            $journey = Journey::query()->updateOrCreate(
                ['slug' => $playbook['slug']],
                [
                    'category_id' => $category?->id,
                    'title' => $playbook['title'],
                    'tagline' => $playbook['tagline'],
                    'description' => $playbook['description'],
                    'outcome' => $playbook['outcome'],
                    'is_featured' => $playbook['is_featured'],
                    'is_verified' => true,
                    'is_free' => true,
                    'sort_order' => 20 + $index,
                ]
            );

            JourneyStep::query()->where('journey_id', $journey->id)->delete();

            foreach ($playbook['steps'] as $stepNumber => $step) {
                $ai = AiRecommendation::for($playbook['category_slug'] ?? null);

                JourneyStep::query()->create([
                    'journey_id' => $journey->id,
                    'prompt_id' => null,
                    'step_number' => $stepNumber + 1,
                    'title' => $step['title'],
                    'goal' => $step['goal'],
                    'instructions' => $step['instructions'],
                    'prompt_body' => $step['prompt_body'],
                    'tip_note' => 'Verified step: complete this prompt, save the output, then continue.',
                    'recommended_platform' => $ai['platform'],
                    'recommended_model' => $ai['model'],
                    'is_verified' => true,
                ]);
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function catalog(): array
    {
        return [
            [
                'name' => 'Fintech',
                'slug' => 'fintech',
                'description' => 'Verified prompts for fintech product design and social creatives.',
                'accent' => 'teal',
                'subcategories' => [
                    [
                        'name' => 'Fintech Designer',
                        'slug' => 'fintech-designer',
                        'description' => 'Product UI/UX for wallets, banking, and investing apps.',
                        'prompts' => $this->fintechDesignerPrompts(),
                    ],
                    [
                        'name' => 'Fintech Social Media Image Designer',
                        'slug' => 'fintech-social-media-image-designer',
                        'description' => 'Trust-first social creatives for fintech brands.',
                        'prompts' => $this->fintechSocialPrompts(),
                    ],
                ],
            ],
            [
                'name' => 'Forex',
                'slug' => 'forex',
                'description' => 'Verified prompts for forex education and market content (non-advisory).',
                'accent' => 'amber',
                'subcategories' => [
                    [
                        'name' => 'Forex Content Writer Pro',
                        'slug' => 'forex-content-writer-pro',
                        'description' => 'Educational forex writing with clear risk framing.',
                        'prompts' => $this->forexWriterPrompts(),
                    ],
                ],
            ],
            [
                'name' => 'Travel',
                'slug' => 'travel',
                'description' => 'Verified prompts for travel creators and destination storytelling.',
                'accent' => 'rose',
                'subcategories' => [
                    [
                        'name' => 'Travel Content Creator',
                        'slug' => 'travel-content-creator',
                        'description' => 'Guides, vlogs, captions, and trip content systems.',
                        'prompts' => $this->travelCreatorPrompts(),
                    ],
                ],
            ],
            [
                'name' => 'Graphics',
                'slug' => 'graphics',
                'description' => 'Verified image-generation and visual prompt craft.',
                'accent' => 'rose',
                'subcategories' => [
                    [
                        'name' => 'Image Creator',
                        'slug' => 'image-creator',
                        'description' => 'General image generation for brands, ads, and content.',
                        'prompts' => $this->imageCreatorPrompts(),
                    ],
                ],
            ],
            [
                'name' => 'Media',
                'slug' => 'media',
                'description' => 'Verified prompts for media ops, calendars, and channel management.',
                'accent' => 'ink',
                'subcategories' => [
                    [
                        'name' => 'Media Manager',
                        'slug' => 'media-manager',
                        'description' => 'Calendars, approvals, distribution, and performance rituals.',
                        'prompts' => $this->mediaManagerPrompts(),
                    ],
                ],
            ],
        ];
    }

    /** @return list<array{title:string,body:string,is_best?:bool,tags:list<string>}> */
    private function fintechDesignerPrompts(): array
    {
        $role = 'Act as a Senior Fintech Product Designer with 12+ years designing regulated financial products (banking, wallets, investing). Prioritize clarity, trust, accessibility, and compliance-safe UX.';

        return $this->expand([
            ['Onboarding KYC flow', "$role\n\nRedesign a KYC/onboarding flow for a fintech app. Reduce drop-off while keeping verification clear. Deliver wire notes, microcopy, error states, and accessibility checks.", true],
            ['Wallet home dashboard', "$role\n\nDesign a wallet home dashboard: balances, recent activity, quick actions, and trust cues. Provide information hierarchy and empty/loading/error states."],
            ['Card controls screen', "$role\n\nDesign card controls (freeze, limits, PIN, spend categories) with safe confirmations and undo patterns."],
            ['Investing risk disclosure UI', "$role\n\nDesign an investing risk-disclosure pattern that users actually read. Include progressive disclosure and confirmation UX without dark patterns."],
            ['Transfer confirmation', "$role\n\nDesign a transfer confirmation experience that prevents costly mistakes: amount emphasis, recipient verification, and review checklist."],
            ['Accessibility pass for finance UI', "$role\n\nAudit a fintech screen for accessibility: contrast for data tables, focus order, labels for amounts/currency, and screen-reader announcements."],
            ['Design system tokens for fintech', "$role\n\nPropose design tokens for a fintech system: color roles (trust/success/danger), type scale for money, spacing, and component states."],
            ['Fraud alert UX', "$role\n\nDesign a fraud/suspicious activity alert flow that is urgent but calm, with clear next actions and support paths."],
            ['Statements & documents hub', "$role\n\nDesign a statements/documents hub with search, filters, download states, and empty states."],
            ['Dark mode finance UI rules', "$role\n\nDefine dark-mode rules for financial data UI so amounts remain readable and status colors stay accessible."],
        ], ['fintech', 'design', 'verified']);
    }

    /** @return list<array{title:string,body:string,is_best?:bool,tags:list<string>}> */
    private function fintechSocialPrompts(): array
    {
        $role = 'Act as a Fintech Social Media Image Designer with 10+ years creating creatives for banks, neobanks, wallets, and investment apps. Favor trust, clarity, and compliance-safe claims.';

        return $this->expand([
            ['Feed post template system', "$role\n\nCreate a reusable feed-post template system (educational, feature, trust, CTA). Include layout grids, typography rules, and safe claim language.", true],
            ['Security awareness carousel', "$role\n\nDesign a 5-slide carousel teaching account security best practices without fearmongering. Provide slide-by-slide visual + copy notes."],
            ['App feature launch creatives', "$role\n\nCreate 4 launch creatives for a new fintech feature: hook visual, benefit visual, how-it-works, and CTA. Note platform sizes."],
            ['Stories covers for finance tips', "$role\n\nDesign 7 Story cover concepts for daily finance tips. Prioritize mobile readability and brand consistency."],
            ['Thumbnail style for fintech YouTube', "$role\n\nCreate a thumbnail style guide for a fintech education channel: expression, props, text limits, colors, and do/don’t examples."],
            ['Paid ads creative variants', "$role\n\nProduce 6 paid-ad creative concepts for a wallet app. Avoid guaranteed-return claims. Include primary text zones."],
            ['Infographic: fees explained', "$role\n\nDesign an infographic concept that explains fees simply. Structure sections, icons, and a plain-language hierarchy."],
            ['UGC-style authentic creative brief', "$role\n\nWrite a UGC-style creative brief for fintech social that still feels brand-safe and professional."],
            ['Crisis/comms visual kit', "$role\n\nCreate a visual kit for service-status/outage communications: calm colors, clear hierarchy, and template slots for status updates."],
            ['Brand vs performance creative split', "$role\n\nDefine when to use brand creatives vs performance creatives for fintech, with examples and a monthly mix recommendation."],
        ], ['fintech', 'social', 'image', 'verified']);
    }

    /** @return list<array{title:string,body:string,is_best?:bool,tags:list<string>}> */
    private function forexWriterPrompts(): array
    {
        $role = 'Act as a Forex Content Writer Pro with 12+ years writing educational FX content for brokers and media sites. Educate clearly, never give personalized trading advice, and always include risk framing.';

        return $this->expand([
            ['Beginner forex explainer', "$role\n\nWrite a beginner-friendly article explaining what forex is, how currency pairs work, and common beginner mistakes. Include a risk disclaimer and glossary.", true],
            ['Pair deep-dive outline', "$role\n\nCreate a research outline for a major pair deep-dive (e.g., EURUSD): drivers, sessions, volatility notes, and educational charts to include — no trade signals."],
            ['Session strategy explainer', "$role\n\nWrite an educational piece on London/New York sessions: characteristics, liquidity concepts, and what learners should observe. No entry/exit instructions."],
            ['Risk management primer', "$role\n\nWrite a practical risk-management primer: position sizing concepts, leverage risks, and emotional pitfalls. Emphasize capital preservation education."],
            ['News trading education', "$role\n\nExplain how economic news can affect FX volatility for learners. Include how to read a calendar responsibly — no predictions."],
            ['Broker comparison content ethics', "$role\n\nDraft an ethical framework for broker comparison content: disclosure, criteria, and language that avoids misleading guarantees."],
            ['YouTube forex script', "$role\n\nWrite an 8-minute educational YouTube script on a forex concept with hook, teaching sections, recap, and disclaimer CTA."],
            ['Social captions pack', "$role\n\nWrite 15 educational forex social captions that teach one micro-concept each without hype or signal-selling."],
            ['Glossary page set', "$role\n\nWrite 20 concise forex glossary entries (pip, lot, spread, swap, etc.) in plain language."],
            ['Compliance rewrite', "$role\n\nRewrite a hype-filled forex promo into compliant educational marketing copy with accurate claims and risk language."],
        ], ['forex', 'writing', 'verified']);
    }

    /** @return list<array{title:string,body:string,is_best?:bool,tags:list<string>}> */
    private function travelCreatorPrompts(): array
    {
        $role = 'Act as a Travel Content Creator with 12+ years producing guides, vlogs, and social travel media. Be specific, sensory, and useful — avoid cliché filler.';

        return $this->expand([
            ['Destination guide structure', "$role\n\nBuild a destination guide structure for a 3-day trip: angles, must-know logistics, budget bands, and photo/video shot list.", true],
            ['Vlog script for a city day', "$role\n\nWrite a speakable day-in-the-city vlog script with hooks, transitions, B-roll cues, and a soft CTA."],
            ['Caption set for a trip carousel', "$role\n\nWrite 10 Instagram/TikTok captions for a trip carousel: curiosity openers, useful tips, and location context."],
            ['Hotel review framework', "$role\n\nCreate a hotel/stay review framework: criteria, evidence notes, and a fair-tone template."],
            ['Food travel storytelling', "$role\n\nWrite a food-travel story outline with sensory detail and respectful cultural context."],
            ['Packing list content piece', "$role\n\nWrite a packing-list article for a specific trip type (adventure / family / business). Make it checklist-ready."],
            ['Shorts ideas from one trip', "$role\n\nTurn one trip into 12 Shorts ideas with hooks and on-screen text."],
            ['Itinerary SEO article', "$role\n\nWrite an SEO outline for “X days in [destination]” with intent match, FAQs, and internal link ideas."],
            ['Responsible travel notes', "$role\n\nAdd a responsible-travel section: local respect, tipping norms research prompts, and environmental considerations."],
            ['Travel newsletter edition', "$role\n\nDraft a travel newsletter edition: opener, 3 recommendations, one mistake to avoid, and a CTA."],
        ], ['travel', 'creator', 'verified']);
    }

    /** @return list<array{title:string,body:string,is_best?:bool,tags:list<string>}> */
    private function imageCreatorPrompts(): array
    {
        $role = 'Act as a professional Image Creator / prompt art director with 10+ years directing commercial and editorial imagery. Write precise prompts: subject, lens, light, composition, grade, exclusions.';

        return $this->expand([
            ['Brand hero image prompt', "$role\n\nWrite a hero image prompt for a brand homepage, plus 3 variations and a negative prompt.", true],
            ['Product-on-lifestyle set', "$role\n\nCreate 5 lifestyle product image prompts that keep consistent camera language across the set."],
            ['Ad creative base images', "$role\n\nWrite 4 image prompts intended as ad bases with space for headline text."],
            ['Editorial portrait set', "$role\n\nCreate 6 editorial portrait prompts with defined lighting patterns and mood."],
            ['Seasonal campaign pack', "$role\n\nBuild a seasonal campaign image prompt pack (8 prompts) with a reusable style lock paragraph."],
            ['App store screenshot scenes', "$role\n\nWrite 5 scene prompts that support app-store storytelling (context around a phone UI)."],
            ['Before/after transformation', "$role\n\nWrite matched before/after prompts with locked camera settings so the pair feels consistent."],
            ['Texture & material studies', "$role\n\nCreate 8 material/texture study prompts for design moodboards."],
            ['Social thumbnail bases', "$role\n\nWrite 6 thumbnail-base image prompts optimized for faces + text-safe space."],
            ['Quality repair prompts', "$role\n\nProvide fix-prompt patterns for common AI image failures (hands, typography, warped logos, bad reflections)."],
        ], ['image', 'graphics', 'verified']);
    }

    /** @return list<array{title:string,body:string,is_best?:bool,tags:list<string>}> */
    private function mediaManagerPrompts(): array
    {
        $role = 'Act as a Media Manager with 11+ years running multi-channel content operations for brands and creators. Prioritize calendars, approvals, distribution quality, and learning loops.';

        return $this->expand([
            ['Monthly content calendar', "$role\n\nBuild a monthly multi-channel content calendar template (LinkedIn, Instagram, YouTube, email) with owners, statuses, and asset needs.", true],
            ['Approval workflow', "$role\n\nDesign an approval workflow for regulated or brand-sensitive content: roles, SLAs, and checklist gates."],
            ['Asset naming system', "$role\n\nCreate a file/asset naming and folder system for campaigns that scales across designers and editors."],
            ['Channel mix strategy', "$role\n\nRecommend a channel mix for a given brand stage, with cadence and content types per channel."],
            ['Crisis response playbook', "$role\n\nWrite a social/media crisis response playbook: severity levels, response times, holding statements, and escalation paths."],
            ['UGC rights tracker', "$role\n\nDesign a UGC sourcing and rights-tracking process with consent language and renewal reminders."],
            ['Performance ritual', "$role\n\nCreate a weekly media performance ritual: metrics that matter, decisions to make, and a one-page scorecard."],
            ['Campaign retrospective', "$role\n\nWrite a campaign retrospective template: goals, results, creative learnings, and next experiments."],
            ['Creator briefing kit', "$role\n\nBuild a creator/influencer briefing kit: do/don’t, talking points, asset list, and disclosure requirements."],
            ['Repurposing pipeline', "$role\n\nDesign a repurposing pipeline from one long video/article into 10+ derivative assets with owners and deadlines."],
        ], ['media', 'operations', 'verified']);
    }

    /**
     * @param  list<array{0:string,1:string,2?:bool}>  $rows
     * @param  list<string>  $tags
     * @return list<array{title:string,body:string,is_best?:bool,tags:list<string>}>
     */
    private function expand(array $rows, array $tags): array
    {
        $audiences = [
            'Beginners' => 'Explain jargon and give examples.',
            'Practitioners' => 'Be concise and execution-focused.',
            'Teams' => 'Include ownership, handoffs, and review checkpoints.',
            'Agencies' => 'Include client communication and revision rounds.',
        ];

        $out = [];
        foreach ($rows as $row) {
            foreach ($audiences as $audience => $guidance) {
                $out[] = [
                    'title' => "{$row[0]} ({$audience})",
                    'body' => $row[1]."\n\nAudience: {$audience}. {$guidance}\n\nDeliver actionable output. Avoid empty superlatives.",
                    'is_best' => ($row[2] ?? false) && $audience === 'Practitioners',
                    'tags' => $tags,
                ];
            }
        }

        return $out;
    }
}
