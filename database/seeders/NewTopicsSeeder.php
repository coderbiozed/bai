<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\Subcategory;
use App\Models\Tag;
use App\Support\AiRecommendation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewTopicsSeeder extends Seeder
{
    private const VERIFIER = 'bAI Verified Curriculum';

    private const TIP = 'Verified prompt craft: name the role, domain, audience, quality standards, and deliverable format. Avoid empty superlatives.';

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
                    'sort_order' => 60 + $categoryIndex,
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
                        $prompt->recordVersion('New topics seed');
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

        $this->command?->info("New topic prompts upserted: {$count}");
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function catalog(): array
    {
        return [
            [
                'name' => 'Meta Manager',
                'slug' => 'meta-manager',
                'description' => 'Facebook, Instagram, and Meta Ads ops — creative, targeting, and reporting.',
                'accent' => 'cyan',
                'subcategories' => [
                    [
                        'name' => 'Meta Ads Manager',
                        'slug' => 'meta-ads-manager',
                        'description' => 'Campaign structure, creatives, and performance rituals for Meta ads.',
                        'prompts' => $this->pack('a Meta Ads Manager with 10+ years running Facebook/Instagram ads for brands and agencies', [
                            ['Campaign architecture', 'Design a Meta Ads campaign structure for a product launch: objectives, ad sets, audiences, and budget split.', true],
                            ['Creative brief pack', 'Write 5 Meta ad creative briefs (hook, primary text, headline, CTA, visual direction) for one offer.'],
                            ['Audience map', 'Build a cold/warm/hot audience map with exclusions and lookalike ideas — no policy violations.'],
                            ['Retargeting funnel', 'Design a 14-day retargeting funnel with message sequencing and frequency caps.'],
                            ['A/B test plan', 'Create an A/B test plan for creatives and hooks with success metrics and kill rules.'],
                            ['Weekly reporting ritual', 'Write a weekly Meta Ads reporting ritual: KPIs, diagnosis questions, and next actions.'],
                            ['Policy-safe rewrite', 'Rewrite hype ad copy into policy-safer claims with clearer offers and disclaimers.'],
                            ['Budget pacing guide', 'Create a budget pacing and scaling checklist for when ads are winning vs struggling.'],
                            ['UGC ad script', 'Write 3 UGC-style Meta ad scripts (15–30s) with spoken lines and on-screen text.'],
                            ['Catalog / Advantage+ brief', 'Brief a catalog or Advantage+ shopping setup with feed requirements and creative overlays.'],
                        ], ['meta', 'ads', 'marketing', 'verified']),
                    ],
                    [
                        'name' => 'Meta Organic Manager',
                        'slug' => 'meta-organic-manager',
                        'description' => 'Organic Facebook & Instagram content systems and community ops.',
                        'prompts' => $this->pack('a Meta Organic Social Manager with 9+ years growing Facebook and Instagram pages without relying only on ads', [
                            ['30-day content calendar', 'Build a 30-day Instagram + Facebook calendar with pillars, formats, and posting cadence.', true],
                            ['Reel hooks pack', 'Write 20 Reel hooks for a niche page that earn the first 2 seconds honestly.'],
                            ['Carousel teaching post', 'Design a 7-slide educational carousel: outline, on-slide text, and caption.'],
                            ['Community reply playbook', 'Create a community reply playbook for comments, DMs, and conflict de-escalation.'],
                            ['Profile conversion audit', 'Audit a Meta profile for conversion: bio, highlights, CTAs, and link strategy.'],
                            ['Repurposing pipeline', 'Design a pipeline that turns 1 long post into Reels, carousels, Stories, and community prompts.'],
                            ['Hashtag & keyword system', 'Build a practical keyword/hashtag system for discovery without spam patterns.'],
                            ['Creator collab brief', 'Write a creator collaboration brief for Instagram: deliverables, talking points, and brand safety.'],
                        ], ['meta', 'social', 'organic', 'verified']),
                    ],
                ],
            ],
            [
                'name' => 'Content',
                'slug' => 'content',
                'description' => 'Content strategy, calendars, and multi-channel publishing systems.',
                'accent' => 'lime',
                'subcategories' => [
                    [
                        'name' => 'Content Strategist',
                        'slug' => 'content-strategist',
                        'description' => 'Pillars, messaging, and content systems that compound.',
                        'prompts' => $this->pack('a Content Strategist with 12+ years building content engines for SaaS and media brands', [
                            ['Content pillar system', 'Design 4 content pillars for a brand with audience jobs-to-be-done and example titles.', true],
                            ['Messaging house', 'Build a messaging house: promise, proof, differentiation, and banned phrases.'],
                            ['Editorial calendar', 'Create a 4-week editorial calendar mixing teach, prove, and promote pieces.'],
                            ['Content brief template', 'Write a reusable content brief template for writers and designers.'],
                            ['Distribution map', 'Map distribution for one flagship piece across blog, email, social, and community.'],
                            ['Repurpose matrix', 'Create a repurposing matrix from webinar → blog → social → email → short video.'],
                            ['Performance review ritual', 'Design a monthly content performance ritual with decisions: double-down, revise, or retire.'],
                            ['Voice & tone guide', 'Write a practical voice-and-tone guide with before/after examples.'],
                        ], ['content', 'strategy', 'verified']),
                    ],
                    [
                        'name' => 'Content Manager',
                        'slug' => 'content-manager',
                        'description' => 'Ops, workflows, and publishing checklists for content teams.',
                        'prompts' => $this->pack('a Content Manager with 10+ years running editorial ops for digital teams', [
                            ['Publishing workflow', 'Design an end-to-end content workflow from idea → draft → review → publish → promote.', true],
                            ['CMS checklist', 'Create a CMS publish checklist: SEO fields, images, internal links, CTA, and QA.'],
                            ['Contributor guidelines', 'Write contributor guidelines for freelancers: briefs, deadlines, revisions, and style.'],
                            ['Asset naming system', 'Propose a file/asset naming and folder system for a content team.'],
                            ['Status dashboard', 'Define a simple content status board (columns + WIP limits) for a small team.'],
                            ['Launch day runbook', 'Write a launch-day runbook for a major content release.'],
                            ['Update & prune policy', 'Create a policy for updating, consolidating, and pruning outdated content.'],
                            ['Stakeholder review loop', 'Design a stakeholder review loop that avoids endless revision cycles.'],
                        ], ['content', 'ops', 'verified']),
                    ],
                ],
            ],
            [
                'name' => 'Article Writer',
                'slug' => 'article-writer',
                'description' => 'Long-form articles, explainers, and editorial craft.',
                'accent' => 'sun',
                'subcategories' => [
                    [
                        'name' => 'Feature Article Writer',
                        'slug' => 'feature-article-writer',
                        'description' => 'In-depth articles with structure, evidence, and voice.',
                        'prompts' => $this->pack('a Feature Article Writer with 14+ years writing reported and explanatory long-form for magazines and professional publishers', [
                            ['Article outline from angle', 'Turn a rough topic into a sharp article angle + outline with section goals.', true],
                            ['Explainer article draft', 'Write a clear explainer article (1200–1600 words) with intro, sections, examples, and conclusion.'],
                            ['Interview-driven piece', 'Plan an interview-driven article: source questions, structure, and pull-quote strategy.'],
                            ['Op-ed with evidence', 'Draft an op-ed that argues one claim with evidence, counterpoints, and a concrete ask.'],
                            ['How-to article system', 'Write a how-to article with numbered steps, pitfalls, and a checklist box.'],
                            ['Lede options', 'Write 5 lede options for the same story and recommend the strongest.'],
                            ['Edit pass for clarity', 'Edit a muddy draft for clarity, pacing, and specificity — show tracked-change style notes.'],
                            ['Source & citation map', 'Create a source map and citation style for a research-backed article.'],
                        ], ['writing', 'article', 'verified']),
                    ],
                ],
            ],
            [
                'name' => 'Blog Writer',
                'slug' => 'blog-writer',
                'description' => 'SEO-friendly and brand blog posts that teach and convert.',
                'accent' => 'magenta',
                'subcategories' => [
                    [
                        'name' => 'Brand Blog Writer',
                        'slug' => 'brand-blog-writer',
                        'description' => 'Helpful blog posts for SaaS, creators, and service brands.',
                        'prompts' => $this->pack('a Brand Blog Writer with 11+ years writing SEO-aware blog content that teaches first and sells second', [
                            ['Blog post from keyword', 'Write a full blog post from a primary keyword: outline, H2s, intro, body, FAQ, and CTA.', true],
                            ['Listicle that earns trust', 'Write a useful listicle that avoids fluff ranking and includes concrete examples.'],
                            ['Comparison post', 'Write a fair comparison post (A vs B) with criteria table notes and a recommendation framework.'],
                            ['Case-study narrative', 'Turn rough results into a case-study blog post: problem, approach, outcome, lessons.'],
                            ['Evergreen update', 'Plan an evergreen refresh for an aging blog post: what to keep, cut, expand, and relink.'],
                            ['Internal linking plan', 'Add an internal linking plan for a new post across a topic cluster.'],
                            ['Meta title & description set', 'Write 5 meta titles and descriptions that match search intent without clickbait.'],
                            ['CTA variants', 'Write soft, medium, and strong CTA variants for the same blog post.'],
                        ], ['blog', 'writing', 'seo', 'verified']),
                    ],
                ],
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Executive admin, office ops, and business administration systems.',
                'accent' => 'coral',
                'subcategories' => [
                    [
                        'name' => 'Executive Admin',
                        'slug' => 'executive-admin',
                        'description' => 'Calendar, inbox, travel, and executive support playbooks.',
                        'prompts' => $this->pack('an Executive Assistant / Admin with 12+ years supporting founders and leadership teams', [
                            ['Weekly EA operating system', 'Design a weekly operating system for an executive assistant: priorities, rituals, and escalation rules.', true],
                            ['Inbox triage rules', 'Create inbox triage rules and reply templates for a busy founder.'],
                            ['Meeting prep brief', 'Write a meeting-prep brief template: goals, context, decisions needed, and follow-ups.'],
                            ['Travel itinerary system', 'Build a travel booking and itinerary checklist with contingencies.'],
                            ['Stakeholder update email', 'Draft a crisp stakeholder update email from messy notes.'],
                            ['Document control habits', 'Propose a lightweight document naming, versioning, and sharing policy.'],
                            ['Vendor coordination checklist', 'Create a vendor/onboarding coordination checklist for office or software vendors.'],
                            ['Confidentiality & discretion guide', 'Write practical discretion guidelines for handling sensitive executive information.'],
                        ], ['admin', 'ops', 'verified']),
                    ],
                    [
                        'name' => 'Office & Ops Admin',
                        'slug' => 'office-ops-admin',
                        'description' => 'Office administration, SOPs, and team support systems.',
                        'prompts' => $this->pack('an Office & Operations Admin with 10+ years running admin systems for growing teams', [
                            ['SOP starter kit', 'Write an SOP template and create 3 example SOPs for common office tasks.', true],
                            ['Onboarding admin checklist', 'Create a new-hire admin onboarding checklist (accounts, access, equipment, intros).'],
                            ['Inventory & supplies system', 'Design a simple inventory/supplies tracking system for a small office.'],
                            ['Event logistics runbook', 'Write a runbook for hosting a small company event or offsite.'],
                            ['Policy digest', 'Turn a long company policy into a one-page admin digest with FAQs.'],
                            ['Shared calendar rules', 'Define shared calendar rules that reduce meeting chaos.'],
                            ['Helpdesk macro set', 'Create internal helpdesk macros for common admin requests.'],
                            ['Quarterly admin audit', 'Design a quarterly admin audit: access, renewals, contracts, and risks.'],
                        ], ['admin', 'office', 'verified']),
                    ],
                ],
            ],
            [
                'name' => 'Finances',
                'slug' => 'finances',
                'description' => 'Business and personal finance systems — educational, not personalized advice.',
                'accent' => 'teal',
                'subcategories' => [
                    [
                        'name' => 'Business Finance',
                        'slug' => 'business-finance',
                        'description' => 'Cash flow, budgeting, and finance ops for small businesses.',
                        'prompts' => $this->pack('a Business Finance Coach with 13+ years helping small businesses build clear finance systems. Educate; do not give personalized investment advice.', [
                            ['Simple cash-flow system', 'Design a simple weekly cash-flow tracking system for a small business.', true],
                            ['Operating budget template', 'Create an operating budget framework with categories, assumptions, and review cadence.'],
                            ['Pricing & margin worksheet', 'Build a pricing/margin thinking worksheet for a service business.'],
                            ['Invoice & collections SOP', 'Write an invoicing and collections SOP that stays professional and firm.'],
                            ['Expense policy draft', 'Draft a lightweight employee expense policy with examples.'],
                            ['Finance dashboard metrics', 'Recommend a founder finance dashboard: 8 metrics and why each matters.'],
                            ['Month-end close checklist', 'Create a month-end close checklist for a non-accountant founder.'],
                            ['Fundraising data room list', 'List a practical data-room checklist for early fundraising conversations.'],
                        ], ['finance', 'business', 'verified']),
                    ],
                    [
                        'name' => 'Personal Finance Educator',
                        'slug' => 'personal-finance-educator',
                        'description' => 'Clear personal finance education without hype or guarantees.',
                        'prompts' => $this->pack('a Personal Finance Educator with 10+ years teaching money basics. Be practical and cautious; never promise returns or give personalized advice.', [
                            ['Budgeting starter plan', 'Teach a beginner budgeting method with categories, examples, and common pitfalls.', true],
                            ['Emergency fund explainer', 'Write an emergency-fund explainer with stages and decision questions.'],
                            ['Debt payoff education', 'Compare avalanche vs snowball payoff approaches educationally with a worksheet structure.'],
                            ['Paystub literacy', 'Explain how to read a paystub in plain language.'],
                            ['Savings automation setup', 'Create a savings automation setup guide for beginners.'],
                            ['Scam & fraud awareness', 'Write a scam/fraud awareness checklist for everyday banking and payments.'],
                            ['Money conversation script', 'Write respectful scripts for talking about shared expenses with a partner or roommate.'],
                            ['Financial goal planner', 'Build a goal planner template: goal, timeline, monthly amount, and review ritual.'],
                        ], ['finance', 'education', 'verified']),
                    ],
                ],
            ],
            [
                'name' => 'SEO Pro',
                'slug' => 'seo-pro',
                'description' => 'Technical SEO, content SEO, and search systems beyond basics.',
                'accent' => 'cyan',
                'subcategories' => [
                    [
                        'name' => 'SEO Specialist',
                        'slug' => 'seo-specialist',
                        'description' => 'Audits, clusters, on-page, and measurement.',
                        'prompts' => $this->pack('an SEO Specialist with 12+ years shipping sustainable organic growth for content and product sites', [
                            ['Technical SEO baseline', 'Run a technical SEO baseline checklist and turn findings into a prioritized fix list.', true],
                            ['Topic cluster map', 'Build a topic cluster map for a niche with pillar + supporting URLs.'],
                            ['On-page SEO brief', 'Write an on-page SEO brief for a target URL: intent, outline, entities, and internal links.'],
                            ['SERP intent analysis', 'Analyze SERP intent for a keyword and recommend content format + angle.'],
                            ['Programmatic SEO caution plan', 'Plan a careful programmatic SEO experiment with quality gates and indexation controls.'],
                            ['Core Web Vitals action list', 'Translate CWV issues into an engineering-friendly action list.'],
                            ['Link earning ideas', 'Propose ethical link-earning campaign ideas tied to real assets (not spam).'],
                            ['SEO reporting dashboard', 'Design a monthly SEO report: metrics, insights, and next bets.'],
                        ], ['seo', 'marketing', 'verified']),
                    ],
                ],
            ],
            [
                'name' => 'CTO',
                'slug' => 'cto',
                'description' => 'Technology leadership, architecture decisions, and eng org craft.',
                'accent' => 'lime',
                'subcategories' => [
                    [
                        'name' => 'Startup CTO',
                        'slug' => 'startup-cto',
                        'description' => 'CTO prompts for product-minded technical leadership.',
                        'prompts' => $this->pack('a Startup CTO with 15+ years leading engineering in early-stage and scale-up companies', [
                            ['Tech strategy one-pager', 'Write a tech strategy one-pager aligned to product goals for the next 2 quarters.', true],
                            ['Build vs buy decision', 'Facilitate a build-vs-buy decision with criteria, risks, and recommendation format.'],
                            ['Hiring plan for eng', 'Create an engineering hiring plan: roles, sequencing, scorecards, and interview loops.'],
                            ['Architecture decision record', 'Draft an ADR template and fill one example for a real system choice.'],
                            ['Incident leadership guide', 'Write an incident leadership guide for CTO/EM: roles, comms, and postmortems.'],
                            ['Engineering rituals', 'Design lightweight eng rituals (planning, demos, reviews) for a 8–20 person team.'],
                            ['Technical debt board', 'Create a technical debt prioritization board with scoring and capacity rules.'],
                            ['Vendor & security review', 'Build a vendor/security review checklist for SaaS tools touching customer data.'],
                            ['Roadmap negotiation', 'Help negotiate product vs platform roadmap with a transparent capacity model.'],
                            ['Board eng update', 'Draft a board-ready engineering update: progress, risks, and asks.'],
                        ], ['cto', 'leadership', 'engineering', 'verified']),
                    ],
                ],
            ],
            [
                'name' => 'Math Learning',
                'slug' => 'math-learning',
                'description' => 'Patient math tutoring prompts for students and self-learners.',
                'accent' => 'sun',
                'subcategories' => [
                    [
                        'name' => 'Math Tutor',
                        'slug' => 'math-tutor',
                        'description' => 'Step-by-step math teaching with checks for understanding.',
                        'prompts' => $this->pack('a Math Tutor with 12+ years teaching secondary and early college math. Teach stepwise, check understanding, and avoid doing all the work for the student.', [
                            ['Concept explainer', 'Explain a math concept with intuition first, then formal steps, then a practice problem.', true],
                            ['Worked example + try', 'Teach with one worked example, then give a similar problem and hint ladder.'],
                            ['Error diagnosis', 'Diagnose a student\'s wrong answer: likely misconception and a repair exercise.'],
                            ['Algebra practice set', 'Create a spaced algebra practice set with increasing difficulty and answer key notes.'],
                            ['Geometry proof coach', 'Coach a geometry proof: ask guiding questions before revealing the next step.'],
                            ['Word problem translator', 'Help translate a word problem into equations without jumping to the final answer.'],
                            ['Exam review plan', 'Build a 7-day exam review plan for a math unit with daily goals.'],
                            ['Parent help script', 'Write a script for parents to help with homework without giving answers away.'],
                        ], ['math', 'education', 'tutoring', 'verified']),
                    ],
                ],
            ],
            [
                'name' => 'English Learning',
                'slug' => 'english-learning',
                'description' => 'English learning for speaking, writing, grammar, and study plans.',
                'accent' => 'coral',
                'subcategories' => [
                    [
                        'name' => 'English Tutor',
                        'slug' => 'english-tutor',
                        'description' => 'ESL/EFL tutoring with clear levels and practice.',
                        'prompts' => $this->pack('an English Tutor (ESL/EFL) with 12+ years teaching learners from A2 to C1. Correct gently, explain simply, and always include practice.', [
                            ['Levelled lesson plan', 'Create a 45-minute English lesson plan for a stated CEFR level with warm-up, input, practice, and production.', true],
                            ['Conversation practice', 'Run a conversation practice session with prompts, useful phrases, and gentle corrections.'],
                            ['Grammar in context', 'Teach a grammar point through examples and mini-exercises (not only rules).'],
                            ['Writing correction', 'Correct a short learner paragraph: errors, better versions, and 3 practice sentences.'],
                            ['Pronunciation focus', 'Design a pronunciation mini-lesson for a common sound or stress pattern.'],
                            ['Vocabulary pack', 'Build a 20-word thematic vocabulary pack with example sentences and a short quiz.'],
                            ['IELTS/task writing coach', 'Coach a timed writing task: structure, band-focused tips, and a model outline.'],
                            ['Daily study plan', 'Create a 14-day English study plan mixing listening, speaking, reading, and writing.'],
                        ], ['english', 'education', 'esl', 'verified']),
                    ],
                    [
                        'name' => 'Business English Coach',
                        'slug' => 'business-english-coach',
                        'description' => 'Professional English for email, meetings, and presentations.',
                        'prompts' => $this->pack('a Business English Coach with 10+ years helping professionals communicate clearly at work', [
                            ['Email rewrite', 'Rewrite a workplace email for clarity, tone, and politeness — show before/after.', true],
                            ['Meeting phrases bank', 'Create a meeting phrases bank: opening, disagreeing politely, clarifying, and closing.'],
                            ['Presentation script', 'Write a short presentation script with signposting language and a strong close.'],
                            ['Interview English practice', 'Prepare interview answers with natural business English and follow-up questions.'],
                            ['Negotiation language', 'Teach negotiation language patterns with sample dialogues.'],
                            ['Report summary coach', 'Help summarize a report into a one-page executive brief in clear English.'],
                        ], ['english', 'business', 'verified']),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  list<array{0:string,1:string,2?:bool}>  $items
     * @param  list<string>  $tags
     * @return list<array{title:string,body:string,is_best?:bool,tags:list<string>}>
     */
    private function pack(string $roleLead, array $items, array $tags): array
    {
        $role = 'Act as '.$roleLead.'. Be specific, practical, and stepwise. Never use empty superlatives.';

        $out = [];
        foreach ($items as $item) {
            $out[] = [
                'title' => $item[0],
                'body' => $role."\n\n".$item[1],
                'is_best' => (bool) ($item[2] ?? false),
                'tags' => array_values(array_unique(array_merge(['role', 'verified'], $tags))),
            ];
        }

        return $out;
    }
}
