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

class MassVerifiedPromptSeeder extends Seeder
{
    private const VERIFIER = 'bAI Verified Curriculum';

    private const TIP = 'Verified prompt craft: name the role, domain, responsibilities, quality standards, and desired output format. Avoid superlatives like “world’s best”.';

    public function run(): void
    {
        $now = Carbon::now();
        $tagCache = [];
        $created = 0;

        foreach ($this->catalog() as $categoryIndex => $categoryData) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'accent' => $categoryData['accent'],
                    'sort_order' => $categoryIndex + 1,
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

                foreach ($subData['prompts'] as $promptIndex => $promptData) {
                    $slug = Str::slug($promptData['title']);
                    if ($slug === '') {
                        $slug = 'prompt-'.$promptIndex;
                    }

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

                    if ($prompt->wasRecentlyCreated || $prompt->versions()->count() === 0) {
                        $prompt->recordVersion('Verified curriculum seed');
                    }

                    $tagIds = [];
                    foreach ($promptData['tags'] ?? ['verified', 'role'] as $tagName) {
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
                    $created++;
                }
            }
        }

        // Ensure existing library prompts are marked verified too.
        Prompt::query()->where('is_verified', false)->update([
            'is_verified' => true,
            'verified_by' => self::VERIFIER,
            'verified_at' => $now,
        ]);

        $this->command?->info("Verified prompts upserted in this catalog pass: {$created}");
    }

    /**
     * @return list<array{name:string,slug:string,description:string,accent:string,subcategories:list<array{name:string,slug:string,description:string,prompts:list<array{title:string,body:string,is_best?:bool,tags?:list<string>}>}>}>
     */
    private function catalog(): array
    {
        $categories = [];

        foreach ($this->blueprints() as $blueprint) {
            $subcategories = [];

            foreach ($blueprint['subs'] as $sub) {
                $prompts = [];

                foreach ($sub['roles'] as $role) {
                    foreach ($sub['focuses'] as $focus) {
                        foreach ($this->audiences() as $audience) {
                            $title = "{$role['short']} — {$focus['label']} ({$audience['label']})";
                            $body = $this->composeBody($role, $focus, $blueprint['defaults'], $audience);

                            $prompts[] = [
                                'title' => $title,
                                'body' => $body,
                                'is_best' => (bool) ($focus['is_best'] ?? false) && $audience['label'] === 'Practitioners',
                                'tags' => array_values(array_unique(array_merge(
                                    ['verified', 'role'],
                                    $blueprint['tags'] ?? [],
                                    $sub['tags'] ?? [],
                                    $role['tags'] ?? [],
                                    $focus['tags'] ?? [],
                                    $audience['tags']
                                ))),
                            ];
                        }
                    }
                }

                $subcategories[] = [
                    'name' => $sub['name'],
                    'slug' => $sub['slug'],
                    'description' => $sub['description'],
                    'prompts' => $prompts,
                ];
            }

            $categories[] = [
                'name' => $blueprint['name'],
                'slug' => $blueprint['slug'],
                'description' => $blueprint['description'],
                'accent' => $blueprint['accent'],
                'subcategories' => $subcategories,
            ];
        }

        return $categories;
    }

    /**
     * @param  array{title:string,short:string,experience:string,domain:string,tags?:list<string>}  $role
     * @param  array{label:string,task:string,standards:string,output:string,is_best?:bool,tags?:list<string>}  $focus
     * @param  array{behavior:string}  $defaults
     * @param  array{label:string,guidance:string,tags:list<string>}  $audience
     */
    private function composeBody(array $role, array $focus, array $defaults, array $audience): string
    {
        return implode("\n\n", [
            "Act as {$role['title']} with {$role['experience']} in {$role['domain']}.",
            "Audience for this session: {$audience['label']}. {$audience['guidance']}",
            "Your job for this session: {$focus['task']}",
            "Quality standards: {$focus['standards']}",
            "Working style: {$defaults['behavior']}",
            "Deliverable format: {$focus['output']}",
            'Never use empty superlatives. Be specific, practical, and stepwise when teaching.',
        ]);
    }

    /**
     * @return list<array{label:string,guidance:string,tags:list<string>}>
     */
    private function audiences(): array
    {
        return [
            [
                'label' => 'Beginners',
                'guidance' => 'Explain jargon, give examples, and sequence work into small wins.',
                'tags' => ['beginner'],
            ],
            [
                'label' => 'Practitioners',
                'guidance' => 'Be concise, assume competence, and optimize for professional execution.',
                'tags' => ['practitioner'],
            ],
            [
                'label' => 'Teams',
                'guidance' => 'Include collaboration, ownership, handoffs, and review checkpoints.',
                'tags' => ['team'],
            ],
            [
                'label' => 'Solo operators',
                'guidance' => 'Prefer leverage, templates, and systems a single person can maintain.',
                'tags' => ['solo'],
            ],
        ];
    }

    /**
     * Combinatorial catalog designed to produce 1000+ verified prompts.
     *
     * @return list<array<string, mixed>>
     */
    private function blueprints(): array
    {
        $engineeringBehavior = 'Explain trade-offs, call out risks, prefer maintainable solutions, and give concrete next steps.';
        $marketingBehavior = 'Prioritize clarity, audience intent, measurable experiments, and ethical tactics.';
        $creatorBehavior = 'Teach with examples, give reusable templates, and sequence work into actionable steps.';
        $designBehavior = 'Center user goals, accessibility, hierarchy, and rationale for every recommendation.';
        $businessBehavior = 'Be practical, quantify assumptions, and separate strategy from execution tasks.';

        return [
            [
                'name' => 'Development',
                'slug' => 'development',
                'description' => 'Verified engineering role prompts across modern stacks.',
                'accent' => 'teal',
                'tags' => ['development'],
                'defaults' => ['behavior' => $engineeringBehavior],
                'subs' => [
                    $this->sub('Software Engineer', 'software-engineer', 'General product engineering.', ['engineering'], [
                        $this->role('a Staff Software Engineer', 'Staff Engineer', '10+ years building production systems', 'product engineering and distributed services'),
                        $this->role('a Senior Backend Engineer', 'Senior Backend', '8+ years in APIs and data services', 'backend architecture'),
                        $this->role('a Platform Engineer', 'Platform Engineer', '7+ years in developer platforms', 'CI/CD, observability, and internal tooling'),
                        $this->role('a Security-minded Software Engineer', 'Secure Engineer', '6+ years shipping secure features', 'application security'),
                    ], $this->focusPack([
                        ['System design interview drill', 'Run a system design session for a real product constraint', 'Scalability, failure modes, and cost awareness', 'Architecture outline + risks + rollout plan', true],
                        ['Bug triage playbook', 'Triage a production bug from symptoms to fix plan', 'Reproducibility, blast radius, and verification', 'Timeline of investigation + patch plan'],
                        ['Code review mentor', 'Review a pull request like a senior mentor', 'Correctness, readability, tests, and regressions', 'Inline review notes + summary'],
                        ['API contract design', 'Design a clean REST/JSON API contract', 'Versioning, auth, pagination, and errors', 'Endpoint list + schemas'],
                        ['Performance pass', 'Find and fix a performance bottleneck', 'Measure first, then optimize', 'Profile summary + prioritized fixes'],
                        ['Testing strategy', 'Create a pragmatic test strategy', 'Unit vs integration vs e2e balance', 'Test matrix + first 10 tests'],
                        ['Refactor roadmap', 'Plan a safe refactor of a messy module', 'Incremental delivery and rollback safety', 'Stepwise refactor plan'],
                        ['On-call checklist', 'Create an on-call incident response checklist', 'Clarity under pressure and clear ownership', 'Checklist + severity guide'],
                        ['Migration plan', 'Plan a zero-downtime data migration', 'Backfills, dual-write, and validation', 'Migration runbook'],
                        ['Tech debt backlog', 'Turn vague tech debt into a prioritized backlog', 'Impact vs effort scoring', 'Ranked backlog with acceptance criteria'],
                    ])),
                    $this->sub('React Developer', 'react-developer', 'Modern React product UI.', ['react', 'frontend'], [
                        $this->role('a Senior React Engineer', 'Senior React', '8+ years in React product UI', 'hooks, composition, and accessible interfaces'),
                        $this->role('a React + TypeScript Lead', 'React TS Lead', '7+ years shipping typed React apps', 'TypeScript and component architecture'),
                        $this->role('a Frontend Performance Specialist', 'React Perf', '6+ years optimizing web performance', 'Core Web Vitals and React rendering'),
                        $this->role('a Design-systems React Engineer', 'React DS Eng', '6+ years building component libraries', 'design systems and accessibility'),
                    ], $this->focusPack([
                        ['Component architecture', 'Design a scalable component structure for a feature', 'Clear ownership of state and props', 'Folder map + component contracts', true],
                        ['Accessible form flow', 'Build an accessible multi-step form plan', 'WCAG, keyboard, and error messaging', 'Form UX spec + validation rules'],
                        ['Server Components strategy', 'Decide where Server Components help', 'Data fetching boundaries and caching', 'Decision matrix + example tree'],
                        ['State management choice', 'Choose local vs global state wisely', 'Avoid unnecessary complexity', 'Recommendation with examples'],
                        ['UI bug reproduction', 'Reproduce and fix a tricky React UI bug', 'Deterministic repro and regression test', 'Root cause + fix + test'],
                        ['Design handoff QA', 'QA a Figma handoff for React implementation', 'Spacing, states, and edge cases', 'Implementation checklist'],
                        ['Suspense boundaries', 'Place Suspense and error boundaries well', 'User-friendly loading and failure states', 'Boundary map'],
                        ['Hook extraction', 'Extract reusable hooks without over-abstraction', 'Reuse with clarity', 'Hook API + usage examples'],
                        ['Storybook coverage', 'Plan Storybook stories for critical UI', 'States, variants, and a11y checks', 'Story list'],
                        ['Migration from class components', 'Plan a safe class-to-hooks migration', 'Incremental and test-backed', 'Migration sequence'],
                    ])),
                    $this->sub('Laravel Developer', 'laravel-developer', 'Laravel apps, APIs, and architecture.', ['laravel', 'php'], [
                        $this->role('a Principal Laravel Architect', 'Laravel Architect', '15+ years of enterprise PHP/Laravel experience', 'domain modeling and Laravel conventions'),
                        $this->role('a Senior Laravel API Engineer', 'Laravel API', '9+ years building APIs in Laravel', 'Eloquent, queues, and API design'),
                        $this->role('a Laravel SaaS Engineer', 'Laravel SaaS', '8+ years shipping multi-tenant SaaS', 'billing, tenancy, and auth'),
                        $this->role('a Laravel Testing Coach', 'Laravel QA Coach', '7+ years with Pest/PHPUnit in Laravel', 'feature tests and confidence'),
                    ], $this->focusPack([
                        ['Domain module layout', 'Structure a Laravel app by domain modules', 'Clear boundaries and Laravel idioms', 'Module map + examples', true],
                        ['Eloquent query tuning', 'Optimize slow Eloquent queries', 'N+1 prevention and indexes', 'Query plan + refactors'],
                        ['Form Request + Policy', 'Design validation and authorization cleanly', 'Security defaults and readable rules', 'Request + policy stubs'],
                        ['Queue job design', 'Design reliable queued jobs', 'Idempotency, retries, and failure handling', 'Job design + monitoring notes'],
                        ['Filament admin plan', 'Plan a Filament admin for operators', 'Operator UX and permission safety', 'Resource list + policies'],
                        ['API versioning', 'Version a Laravel API without chaos', 'Backward compatibility strategy', 'Versioning guide'],
                        ['Multi-tenancy options', 'Compare tenancy approaches for a SaaS', 'Isolation vs complexity', 'Recommendation memo'],
                        ['Migration safety', 'Write safe schema migrations', 'Zero-downtime habits', 'Migration checklist'],
                        ['Livewire feature build', 'Design a Livewire feature end-to-end', 'Simple state and clear UX', 'Component plan'],
                        ['Pest feature suite', 'Create a Pest feature test suite outline', 'Happy path + edge cases', 'Test list'],
                    ])),
                    $this->sub('Python Developer', 'python-developer', 'Python services and data tooling.', ['python'], [
                        $this->role('a Senior Python Engineer', 'Senior Python', '9+ years in Python services', 'APIs, packaging, and reliability'),
                        $this->role('a Data-focused Python Engineer', 'Python Data', '7+ years with pandas and pipelines', 'data processing'),
                        $this->role('a FastAPI Specialist', 'FastAPI Eng', '6+ years building FastAPI services', 'async APIs'),
                    ], $this->focusPack([
                        ['Service scaffold', 'Scaffold a production-ready Python service', 'Typing, config, logging, tests', 'Project tree + starter modules', true],
                        ['ETL reliability', 'Hardening an ETL job', 'Retries, schema drift, observability', 'Reliability checklist'],
                        ['API with FastAPI', 'Design a FastAPI endpoint set', 'Validation and OpenAPI clarity', 'Router plan'],
                        ['Packaging & CI', 'Set up packaging and CI for a library', 'Reproducible builds', 'CI pipeline outline'],
                        ['Typing pass', 'Add useful type hints to a messy module', 'Gradual typing', 'Before/after guidance'],
                        ['Async pitfalls', 'Debug async concurrency issues', 'Correct event-loop usage', 'Diagnosis + fixes'],
                        ['CLI tool design', 'Design a helpful developer CLI', 'Clear UX and exit codes', 'Command spec'],
                        ['Notebook to production', 'Move a notebook into a maintainable module', 'Reproducibility', 'Refactor plan'],
                    ])),
                    $this->sub('DevOps Engineer', 'devops-engineer', 'Delivery, infra, and reliability.', ['devops'], [
                        $this->role('a Senior DevOps Engineer', 'Senior DevOps', '10+ years in cloud delivery', 'CI/CD and infrastructure as code'),
                        $this->role('an SRE', 'SRE', '8+ years in reliability engineering', 'SLOs, incidents, and automation'),
                        $this->role('a Kubernetes Platform Engineer', 'K8s Platform', '7+ years operating Kubernetes', 'clusters and developer experience'),
                    ], $this->focusPack([
                        ['CI pipeline design', 'Design a CI pipeline for a web app', 'Fast feedback and security checks', 'Pipeline stages', true],
                        ['IaC review', 'Review Terraform/IaC for risk', 'Least privilege and drift', 'Review findings'],
                        ['SLO workshop', 'Define SLOs for a service', 'User-centric reliability', 'SLO doc'],
                        ['Incident retrospective', 'Facilitate a blameless postmortem', 'Learning over blame', 'Postmortem template filled'],
                        ['Deploy strategy', 'Choose blue/green vs canary', 'Risk vs complexity', 'Decision memo'],
                        ['Observability starter', 'Create a metrics/logs/traces starter plan', 'Actionable signals', 'Telemetry checklist'],
                        ['Secret management', 'Improve secrets handling', 'Rotation and least access', 'Hardening plan'],
                        ['Cost control', 'Cut cloud spend without hurting reliability', 'Measure then reduce', 'Savings backlog'],
                    ])),
                ],
            ],
            [
                'name' => 'YouTube',
                'slug' => 'youtube',
                'description' => 'Verified creator prompts for channel growth and content systems.',
                'accent' => 'rose',
                'tags' => ['youtube', 'creator'],
                'defaults' => ['behavior' => $creatorBehavior],
                'subs' => [
                    $this->sub('Channel Strategy', 'channel-strategy', 'Positioning and growth systems.', ['strategy'], [
                        $this->role('a YouTube Channel Strategist', 'YT Strategist', '12+ years advising creator businesses', 'niche positioning and audience growth'),
                        $this->role('a Creator Economy Coach', 'Creator Coach', '10+ years helping creators monetize sustainably', 'audience value and offers'),
                        $this->role('a Content Series Architect', 'Series Architect', '9+ years packaging educational content', 'series design and retention'),
                    ], $this->focusPack([
                        ['Niche clarification', 'Clarify a profitable, authentic niche', 'Audience clarity and differentiation', 'Niche one-pager', true],
                        ['Audience persona', 'Build a practical viewer persona', 'Jobs-to-be-done language', 'Persona sheet'],
                        ['Content pillars', 'Define 3–5 content pillars', 'Consistency without boredom', 'Pillar map'],
                        ['90-day roadmap', 'Build a 90-day channel roadmap', 'Realistic cadence and milestones', 'Week-by-week plan'],
                        ['Competitor teardown', 'Teardown competing channels ethically', 'Patterns, not copying', 'Teardown notes'],
                        ['Brand voice guide', 'Write a channel voice guide', 'Memorable and repeatable tone', 'Voice doc'],
                        ['Offer ladder', 'Design free-to-paid value ladder', 'Trust before selling', 'Offer map'],
                        ['Retention system', 'Improve average view duration strategy', 'Hooks, pacing, payoff', 'Retention checklist'],
                        ['Community flywheel', 'Design comments-to-content flywheel', 'Audience relationship', 'Engagement playbook'],
                        ['Analytics review ritual', 'Create a weekly analytics ritual', 'Decisions from data', 'Review template'],
                    ])),
                    $this->sub('Script Writing', 'script-writing', 'Hooks, scripts, and storytelling.', ['script'], [
                        $this->role('a YouTube Scriptwriter', 'YT Scriptwriter', '11+ years writing educational video scripts', 'hooks, structure, and clarity'),
                        $this->role('a Documentary-style Story Editor', 'Story Editor', '10+ years editing narrative nonfiction', 'story arcs and emotional pacing'),
                        $this->role('an Educational Explainer Writer', 'Explainer Writer', '8+ years simplifying complex topics on camera', 'teaching clarity'),
                    ], $this->focusPack([
                        ['Cold open hook', 'Write 10 cold-open hooks for one topic', 'Curiosity without clickbait lies', '10 hooks + best pick', true],
                        ['Full script draft', 'Draft a full 8–12 minute script', 'Clear sections and spoken rhythm', 'Script with stage notes'],
                        ['Tutorial script', 'Write a step-by-step tutorial script', 'Viewer can pause and follow', 'Numbered tutorial script'],
                        ['Story-driven intro', 'Rewrite an intro as a mini-story', 'Stakes in first 20 seconds', 'New intro'],
                        ['CTA without cringe', 'Write natural CTAs', 'Helpful, not pushy', '3 CTA variants'],
                        ['Shorts script pack', 'Write 7 Shorts scripts from one long video', 'One idea each', '7 Shorts scripts'],
                        ['Objection handling', 'Handle viewer objections in script', 'Trust and credibility', 'Script inserts'],
                        ['Simplify jargon', 'Rewrite a technical script for beginners', 'Accuracy without overwhelm', 'Beginner script'],
                        ['Cliffhanger transitions', 'Add retention transitions between sections', 'Natural curiosity loops', 'Transition lines'],
                        ['Voiceover polish', 'Polish a VO script for timing', 'Breathing and emphasis marks', 'Marked-up script'],
                    ])),
                    $this->sub('Thumbnails & Titles', 'thumbnails-titles', 'Packaging that earns the click honestly.', ['packaging'], [
                        $this->role('a YouTube Packaging Specialist', 'Packaging Spec', '10+ years optimizing titles and thumbnails', 'CTR with integrity'),
                        $this->role('a Thumbnail Art Director', 'Thumb AD', '9+ years in attention design for video', 'visual hierarchy and emotion'),
                    ], $this->focusPack([
                        ['Title formula pack', 'Generate title options with formulas', 'Clarity + curiosity, no bait-and-switch', '20 titles ranked', true],
                        ['Thumbnail brief', 'Write a thumbnail creative brief', 'Face/emotion/object/text balance', 'Brief for designer'],
                        ['A/B test plan', 'Plan a title/thumbnail A/B test', 'One variable at a time', 'Test matrix'],
                        ['Search vs browse titles', 'Split SEO titles vs browse titles', 'Intent match', 'Two title sets'],
                        ['Text-on-thumb rules', 'Define text rules for thumbnails', 'Readable on mobile', 'Rule sheet'],
                        ['Series packaging', 'Package an episode inside a series', 'Recognition + novelty', 'Series template'],
                        ['Before/after concepts', 'Create before/after thumbnail concepts', 'Transformation clarity', '6 concepts'],
                        ['CTR diagnosis', 'Diagnose low CTR packaging', 'Hypothesis-driven', 'Diagnosis + fixes'],
                    ])),
                    $this->sub('Production', 'production', 'Filming, editing, and publishing ops.', ['production'], [
                        $this->role('a YouTube Producer', 'YT Producer', '12+ years producing online video', 'efficient production systems'),
                        $this->role('a Video Editor Mentor', 'Editor Mentor', '9+ years editing educational YouTube', 'pacing and visual teaching'),
                    ], $this->focusPack([
                        ['One-camera setup', 'Design a reliable one-camera filming setup', 'Good enough quality, repeatable', 'Gear + framing checklist', true],
                        ['Shot list', 'Create a shot list from a script', 'Coverage for teaching clarity', 'Shot list'],
                        ['Edit pacing guide', 'Define edit pacing for tutorials', 'Cut boredom, keep clarity', 'Pacing guide'],
                        ['B-roll plan', 'Plan B-roll that teaches', 'Visual proof of concepts', 'B-roll checklist'],
                        ['Audio cleanup', 'Create an audio cleanup checklist', ' intelligible voice first', 'Audio checklist'],
                        ['Publish checklist', 'Build an end-to-end publish checklist', 'No forgotten metadata', 'Publish runbook'],
                        ['Batch filming day', 'Plan a batch filming day', 'Energy and continuity', 'Day schedule'],
                        ['Template project', 'Design an editing template project', 'Speed without sameness', 'Template map'],
                    ])),
                ],
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Verified growth, SEO, and content operations prompts.',
                'accent' => 'amber',
                'tags' => ['marketing'],
                'defaults' => ['behavior' => $marketingBehavior],
                'subs' => [
                    $this->sub('SEO', 'seo', 'Technical SEO and content ranking.', ['seo'], [
                        $this->role('an SEO consultant with 15 years of experience', 'SEO Consultant', '15 years in technical SEO and content strategy', 'Google ranking systems and search intent'),
                        $this->role('a Technical SEO Specialist', 'Technical SEO', '10+ years fixing crawl and index issues', 'site architecture and Core Web Vitals'),
                        $this->role('a Content SEO Strategist', 'Content SEO', '9+ years mapping topics to intent', 'topical authority'),
                    ], $this->focusPack([
                        ['Technical audit', 'Run a prioritized technical SEO audit', 'Crawl, index, speed, and structured data', 'Ranked issue list + fixes', true],
                        ['Search intent map', 'Map keywords to intent clusters', 'No vanity keywords', 'Cluster map'],
                        ['Brief for writers', 'Write an SEO content brief', 'Intent, outline, entities, internal links', 'Content brief'],
                        ['Internal linking', 'Improve internal linking for a topic cluster', 'Equity and crawl paths', 'Link plan'],
                        ['SERP teardown', 'Teardown the SERP for a target query', 'Format and angle gaps', 'SERP notes'],
                        ['Programmatic SEO guardrails', 'Design safe programmatic SEO pages', 'Quality and uniqueness', 'Guardrail checklist'],
                        ['Local SEO pack', 'Build a local SEO action pack', 'Relevance and trust signals', 'Local checklist'],
                        ['Migration SEO', 'Protect rankings during a site migration', 'Redirects and monitoring', 'Migration SEO plan'],
                        ['E-E-A-T improvement', 'Improve experience/expertise signals', 'Honest proof, not fluff', 'Trust upgrade list'],
                        ['Reporting dashboard', 'Design an SEO reporting dashboard', 'Decisions over vanity charts', 'Metric set'],
                    ])),
                    $this->sub('Link Building', 'link-building', 'Ethical authority building.', ['links'], [
                        $this->role('a Link Building Specialist', 'Link Builder', '12+ years in ethical outreach', 'relevant placements and relationships'),
                        $this->role('a Digital PR Strategist', 'Digital PR', '9+ years earning coverage with assets', 'newsworthy content angles'),
                    ], $this->focusPack([
                        ['Outreach angle pack', 'Create ethical outreach angles for an asset', 'Relevance and mutual value', '10 angles + templates', true],
                        ['Prospect criteria', 'Define high-quality prospect criteria', 'Spam avoidance', 'Scoring rubric'],
                        ['Digital PR asset', 'Ideate a link-worthy asset', 'Shareability + usefulness', 'Asset brief'],
                        ['Broken link opportunity', 'Run a broken-link prospecting plan', 'Helpful replacements', 'Prospect workflow'],
                        ['Follow-up sequence', 'Write a respectful follow-up sequence', 'No spammy pressure', '3-email sequence'],
                        ['Partnership links', 'Design partner/resource link opportunities', 'Long-term relationships', 'Partner list framework'],
                        ['Risk review', 'Review link tactics for risk', 'Guideline-safe practices', 'Risk memo'],
                        ['Reporting quality', 'Report link results by quality, not count', 'Business impact', 'Report template'],
                    ])),
                    $this->sub('Growth Marketing', 'growth-marketing', '0→1 positioning and experiments.', ['growth'], [
                        $this->role('a Chief Marketing Officer', 'CMO', 'experience scaling startups from 0 to 1 million users', 'positioning, channels, and retention'),
                        $this->role('a Growth Lead', 'Growth Lead', '10+ years running experiment systems', 'activation and retention loops'),
                        $this->role('a Lifecycle Marketer', 'Lifecycle', '8+ years in email/push lifecycle', 'retention messaging'),
                    ], $this->focusPack([
                        ['Positioning canvas', 'Clarify product positioning', 'Distinctive and believable', 'Positioning one-pager', true],
                        ['Channel experiments', 'Design first 10 growth experiments', 'Hypothesis and success metric', 'Experiment backlog'],
                        ['Activation audit', 'Audit onboarding activation', 'Time-to-value', 'Activation fixes'],
                        ['Referral loop', 'Design a referral loop', 'Incentive clarity', 'Loop diagram + copy'],
                        ['Landing page critique', 'Critique a landing page for conversion', 'Message match and proof', 'Annotated critique'],
                        ['Pricing page copy', 'Rewrite pricing page messaging', 'Objection handling', 'New copy blocks'],
                        ['Retention email series', 'Write a 5-email retention series', 'Value first', 'Email set'],
                        ['Metric tree', 'Build a north-star metric tree', 'Leading indicators', 'Metric tree'],
                        ['Competitive messaging', 'Differentiate messaging vs competitors', 'No trash talk', 'Message matrix'],
                        ['Launch checklist', 'Create a product launch checklist', 'Cross-functional clarity', 'Launch runbook'],
                    ])),
                    $this->sub('CMS Management', 'cms-management', 'Publishing systems and ops.', ['cms'], [
                        $this->role('a CMS Operations Lead', 'CMS Ops Lead', '10+ years running publishing workflows', 'taxonomy, QA, and editor experience'),
                        $this->role('a Content Operations Manager', 'Content Ops', '8+ years in editorial ops', 'calendars and governance'),
                    ], $this->focusPack([
                        ['Editorial workflow', 'Design an editorial workflow', 'Fewer bottlenecks', 'Workflow diagram', true],
                        ['Taxonomy cleanup', 'Clean up CMS taxonomy', 'Findability', 'Taxonomy proposal'],
                        ['Editor QA checklist', 'Create a publish QA checklist', 'Fewer regressions', 'QA checklist'],
                        ['Template system', 'Design page templates for editors', 'Consistency + speed', 'Template list'],
                        ['Permissions model', 'Define CMS roles and permissions', 'Least privilege', 'Role matrix'],
                        ['Migration to new CMS', 'Plan a CMS migration', 'Content mapping', 'Migration plan'],
                        ['Governance doc', 'Write content governance rules', 'Clear ownership', 'Governance one-pager'],
                        ['Performance for editors', 'Improve editor speed in the CMS', 'Training + UX fixes', 'Improvement backlog'],
                    ])),
                ],
            ],
            [
                'name' => 'Design',
                'slug' => 'design',
                'description' => 'Verified product design and UX prompts.',
                'accent' => 'ink',
                'tags' => ['design'],
                'defaults' => ['behavior' => $designBehavior],
                'subs' => [
                    $this->sub('UI/UX Designer', 'ui-ux-designer', 'User-centered product design.', ['ux'], [
                        $this->role('a Senior Product Designer', 'Senior Product Designer', 'expertise in user-centered design and accessibility', 'product interfaces and design systems'),
                        $this->role('a UX Researcher-Designer', 'UX Researcher', '9+ years pairing research with design decisions', 'qualitative insight to UI'),
                        $this->role('an Interaction Designer', 'Interaction Designer', '8+ years crafting micro-interactions and flows', 'interaction clarity'),
                    ], $this->focusPack([
                        ['Flow redesign', 'Redesign a confusing user flow', 'Fewer steps, clearer feedback', 'Flow + wire notes', true],
                        ['Accessibility audit', 'Audit a screen for accessibility', 'WCAG practical fixes', 'Issue list'],
                        ['Design critique', 'Run a structured design critique', 'Evidence over taste', 'Critique notes'],
                        ['Empty states', 'Design helpful empty states', 'Guide next action', 'Empty state set'],
                        ['Mobile navigation', 'Improve mobile navigation IA', 'Thumb reach and clarity', 'Nav proposal'],
                        ['Design system token', 'Propose tokens for a small system', 'Consistent foundations', 'Token sheet'],
                        ['Onboarding UX', 'Design first-run onboarding', 'Time-to-value', 'Onboarding storyboard'],
                        ['Error UX', 'Rewrite error states and recovery', 'Human and actionable', 'Error copy + UI notes'],
                        ['Handoff checklist', 'Create design-to-dev handoff checklist', 'Fewer implementation gaps', 'Handoff checklist'],
                        ['Usability test script', 'Write a usability test script', 'Unbiased tasks', 'Test script'],
                    ])),
                    $this->sub('Brand Design', 'brand-design', 'Identity and visual systems.', ['brand'], [
                        $this->role('a Brand Designer', 'Brand Designer', '10+ years building brand systems for products', 'identity and application'),
                        $this->role('a Visual Designer', 'Visual Designer', '8+ years in marketing and product visuals', 'layout and composition'),
                    ], $this->focusPack([
                        ['Brand positioning visual', 'Translate positioning into visual direction', 'Distinctive and usable', 'Mood + rules', true],
                        ['Logo usage rules', 'Write logo usage guidelines', 'Protection without rigidity', 'Usage sheet'],
                        ['Landing visual system', 'Design a landing visual system', 'Hierarchy and atmosphere', 'Section system'],
                        ['Social template pack', 'Create social template rules', 'Recognition at a glance', 'Template brief'],
                        ['Color accessibility', 'Check brand colors for contrast', 'Accessible pairings', 'Palette notes'],
                        ['Presentation system', 'Design a slide system', 'Clarity over decoration', 'Slide kit rules'],
                        ['Icon style guide', 'Define an icon style', 'Consistency', 'Icon rules'],
                        ['Rebrand checklist', 'Plan a careful rebrand rollout', 'Risk control', 'Rollout checklist'],
                    ])),
                ],
            ],
            [
                'name' => 'Graphics',
                'slug' => 'graphics',
                'description' => 'Verified image-generation and visual prompt craft.',
                'accent' => 'rose',
                'tags' => ['graphics', 'image'],
                'defaults' => ['behavior' => $creatorBehavior],
                'subs' => [
                    $this->sub('Photo Generate', 'photo-generate', 'Photorealistic and editorial image prompts.', ['photo'], [
                        $this->role('a commercial photographer art director', 'Photo AD', '12+ years directing commercial photography', 'lighting, lens language, and composition'),
                        $this->role('an Editorial Image Prompt Engineer', 'Image Prompt Eng', '8+ years writing precise generative image prompts', 'controllable visual language'),
                    ], $this->focusPack([
                        ['Product atmosphere', 'Write a photoreal product atmosphere prompt', 'Subject, lens, light, grade, exclusions', 'Final prompt + negatives', true],
                        ['Portrait lighting', 'Prompt a portrait with defined lighting', 'Softbox/ Rembrandt clarity', 'Prompt set'],
                        ['Food editorial', 'Create a food editorial prompt', 'Appetite appeal without clutter', 'Prompt + crop notes'],
                        ['Architecture interior', 'Prompt an interior architecture shot', 'Lens distortion control', 'Prompt'],
                        ['Before/after pair', 'Write matched before/after prompts', 'Consistent camera language', 'Prompt pair'],
                        ['Brand-consistent series', 'Create 6 on-brand image prompts', 'Repeatable style block', 'Style block + 6 prompts'],
                        ['Flat lay workspace', 'Prompt a clean workspace flat lay', 'Organized composition', 'Prompt'],
                        ['Cinematic still', 'Prompt a cinematic still frame', 'Mood without muddy contrast', 'Prompt'],
                        ['Infographic scene', 'Prompt a scene that supports an explainer', 'Readable focal point', 'Prompt'],
                        ['Thumbnail base image', 'Prompt a YouTube thumbnail base image', 'Face/space for text', 'Prompt + safe-area notes'],
                    ])),
                    $this->sub('Illustration', 'illustration', 'Stylized illustration prompts.', ['illustration'], [
                        $this->role('an Illustration Director', 'Illustration Dir', '10+ years directing illustration systems', 'style consistency'),
                        $this->role('a Concept Artist Prompt Coach', 'Concept Prompt Coach', '9+ years in concept development', 'iterable visual exploration'),
                    ], $this->focusPack([
                        ['Style lock', 'Write a reusable illustration style lock', 'Consistent characters/props', 'Style paragraph', true],
                        ['Character turnaround', 'Prompt a character turnaround sheet', 'Model-sheet clarity', 'Prompt'],
                        ['UI empty-state art', 'Prompt friendly empty-state art', 'Simple and on-brand', 'Prompt'],
                        ['Explainer panel', 'Prompt a 4-panel explainer', 'Teaching sequence', 'Panel prompts'],
                        ['Icon set direction', 'Define prompt rules for an icon set', 'Uniform stroke/perspective', 'Rules + examples'],
                        ['Poster concept', 'Prompt 5 poster concepts', 'Strong hierarchy', '5 prompts'],
                        ['Mascot exploration', 'Explore mascot directions', 'Distinct silhouettes', '6 directions'],
                        ['Texture study', 'Prompt texture/material studies', 'Controlled variations', 'Study pack'],
                    ])),
                ],
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Verified entrepreneurship and freelancing prompts.',
                'accent' => 'teal',
                'tags' => ['business'],
                'defaults' => ['behavior' => $businessBehavior],
                'subs' => [
                    $this->sub('Freelancing', 'freelancing', 'Offers, clients, and delivery.', ['freelance'], [
                        $this->role('a Freelance Business Coach', 'Freelance Coach', '14+ years helping specialists productize services', 'offers, pricing, and delivery'),
                        $this->role('a Senior Independent Consultant', 'Indie Consultant', '12+ years selling expertise B2B', 'proposals and retainers'),
                    ], $this->focusPack([
                        ['Offer design', 'Design a clear freelance offer', 'Outcome-based packaging', 'Offer one-pager', true],
                        ['Pricing strategy', 'Set pricing with confidence', 'Value vs time traps', 'Pricing options'],
                        ['Discovery call script', 'Write a discovery call script', 'Diagnose before pitching', 'Call outline'],
                        ['Proposal template', 'Create a proposal template', 'Scope clarity', 'Proposal structure'],
                        ['Scope creep defense', 'Handle scope creep professionally', 'Boundaries + options', 'Reply scripts'],
                        ['Retainer design', 'Design a monthly retainer', 'Predictable value', 'Retainer brief'],
                        ['Case study writeup', 'Turn a project into a case study', 'Problem/process/result', 'Case study draft'],
                        ['Client onboarding', 'Build a client onboarding checklist', 'Fewer kickoff delays', 'Onboarding checklist'],
                    ])),
                    $this->sub('Startup', 'startup', 'Early-stage product and GTM.', ['startup'], [
                        $this->role('a Startup Operator', 'Startup Operator', '11+ years in early-stage operations', 'focus and sequencing'),
                        $this->role('a Product Founder Coach', 'Founder Coach', '10+ years advising technical founders', 'MVP scope and learning speed'),
                    ], $this->focusPack([
                        ['Problem interview', 'Write customer problem interview questions', 'No leading questions', 'Interview guide', true],
                        ['MVP cut line', 'Define an honest MVP cut line', 'Learning over features', 'MVP scope'],
                        ['Waitlist landing', 'Write waitlist landing copy', 'Clear promise', 'Landing copy'],
                        ['Founder weekly review', 'Create a founder weekly review ritual', 'Focus on bottlenecks', 'Review template'],
                        ['Hiring first contractor', 'Plan first contractor hire', 'Clear brief', 'Hiring brief'],
                        ['Pricing experiment', 'Design a pricing experiment', 'Measurable learning', 'Experiment plan'],
                        ['Investor update', 'Write a crisp investor update', 'Facts and asks', 'Update draft'],
                        ['Churn interview', 'Interview churned users well', 'Learning without defensiveness', 'Interview script'],
                    ])),
                    $this->sub('Sales', 'sales', 'Consultative selling prompts.', ['sales'], [
                        $this->role('a Consultative Sales Lead', 'Sales Lead', '13+ years in B2B consultative sales', 'discovery and mutual close'),
                        $this->role('an SDR Coach', 'SDR Coach', '9+ years building outbound systems', 'relevant outreach'),
                    ], $this->focusPack([
                        ['Discovery framework', 'Build a discovery question framework', 'Pain, impact, urgency', 'Question set', true],
                        ['Outbound sequence', 'Write a 5-touch outbound sequence', 'Personal relevance', 'Sequence'],
                        ['Demo narrative', 'Structure a product demo narrative', 'Customer story first', 'Demo outline'],
                        ['Objection map', 'Map and answer common objections', 'Honest and specific', 'Objection playbook'],
                        ['Mutual action plan', 'Create a mutual action plan template', 'Shared next steps', 'MAP template'],
                        ['Renewal prep', 'Prepare a renewal conversation', 'Value evidence', 'Renewal brief'],
                        ['CRM hygiene', 'Define CRM hygiene rules', 'Forecast trust', 'Hygiene checklist'],
                        ['Win/loss review', 'Run a win/loss review', 'Pattern finding', 'Review template'],
                    ])),
                ],
            ],
            [
                'name' => 'Writing',
                'slug' => 'writing',
                'description' => 'Verified writing and content craft prompts.',
                'accent' => 'ink',
                'tags' => ['writing'],
                'defaults' => ['behavior' => $creatorBehavior],
                'subs' => [
                    $this->sub('Copywriting', 'copywriting', 'Conversion and clarity copy.', ['copy'], [
                        $this->role('a Senior Conversion Copywriter', 'Conversion Copywriter', '12+ years writing for SaaS and creators', 'clarity and persuasion without hype'),
                        $this->role('a Direct Response Editor', 'DR Editor', '10+ years editing offers and landing pages', 'tightening claims and proof'),
                    ], $this->focusPack([
                        ['Homepage hero', 'Rewrite a homepage hero', 'One promise, one action', 'Hero set', true],
                        ['Feature-to-benefit', 'Turn features into benefits', 'Customer language', 'Benefit map'],
                        ['About page', 'Write an About page that builds trust', 'Specific proof', 'About draft'],
                        ['Email subject lines', 'Write 20 subject lines', 'Curiosity with honesty', 'Ranked list'],
                        ['FAQ objection copy', 'Write FAQ that handles objections', 'Helpful tone', 'FAQ set'],
                        ['Case study narrative', 'Write a case study narrative', 'Before/after clarity', 'Draft'],
                        ['Microcopy pack', 'Write UI microcopy for a flow', 'Human and short', 'Microcopy table'],
                        ['Claim substantiation', 'Audit copy claims for substantiation', 'Risk reduction', 'Claim audit'],
                    ])),
                    $this->sub('Technical Writing', 'technical-writing', 'Docs that developers finish.', ['docs'], [
                        $this->role('a Staff Technical Writer', 'Tech Writer', '11+ years documenting developer products', 'task-oriented docs'),
                        $this->role('a Developer Education Writer', 'DevEd Writer', '8+ years writing tutorials that ship', 'learning design'),
                    ], $this->focusPack([
                        ['Quickstart guide', 'Write a 5-minute quickstart', 'Success path first', 'Quickstart doc', true],
                        ['API reference intro', 'Improve an API reference intro', 'Conceptual model first', 'Intro rewrite'],
                        ['Troubleshooting doc', 'Create a troubleshooting tree', 'Symptom → fix', 'Doc tree'],
                        ['Tutorial outline', 'Outline a tutorial with checkpoints', 'Verifiable progress', 'Outline'],
                        ['Changelog clarity', 'Rewrite a messy changelog', 'User impact first', 'Changelog'],
                        ['Style guide', 'Draft a docs style guide', 'Consistency', 'Style guide'],
                        ['Error message docs', 'Document error codes helpfully', 'Recovery steps', 'Error docs'],
                        ['Migration guide', 'Write a version migration guide', 'Breaking changes clarity', 'Migration guide'],
                    ])),
                ],
            ],
            [
                'name' => 'Career',
                'slug' => 'career',
                'description' => 'Verified career growth and interview prompts.',
                'accent' => 'amber',
                'tags' => ['career'],
                'defaults' => ['behavior' => $businessBehavior],
                'subs' => [
                    $this->sub('Interviews', 'interviews', 'Interview prep systems.', ['interview'], [
                        $this->role('a Technical Interview Coach', 'Interview Coach', '12+ years coaching engineers into strong interviews', 'structured answers and practice loops'),
                        $this->role('a Hiring Manager Coach', 'HM Coach', '10+ years interviewing and calibrating candidates', 'signal over theater'),
                    ], $this->focusPack([
                        ['STAR story bank', 'Build a STAR story bank', 'Specific impact metrics', '10 stories', true],
                        ['System design drill', 'Run a 45-minute system design drill', 'Clarify then design', 'Session plan'],
                        ['Behavioral answers', 'Tighten behavioral answers', 'No rambling', 'Answer rewrites'],
                        ['Salary negotiation', 'Prepare a salary negotiation script', 'Data-backed and calm', 'Script'],
                        ['Resume bullet upgrade', 'Upgrade resume bullets', 'Impact verbs + metrics', 'Bullet set'],
                        ['Mock interview rubric', 'Create a self-grade rubric', 'Honest feedback', 'Rubric'],
                        ['Offer comparison', 'Compare two offers', 'Total compensation clarity', 'Decision matrix'],
                        ['30-60-90 plan', 'Write a 30-60-90 plan for a new role', 'Learning + delivery', 'Plan'],
                    ])),
                    $this->sub('Personal Brand', 'personal-brand', 'Professional visibility.', ['brand'], [
                        $this->role('a Personal Brand Strategist', 'Personal Brand', '9+ years helping specialists become known for one craft', 'consistent public thinking'),
                        $this->role('a LinkedIn Content Coach', 'LinkedIn Coach', '8+ years coaching professional creators', 'useful posts over hype'),
                    ], $this->focusPack([
                        ['Positioning line', 'Write a crisp professional positioning line', 'Specific audience + outcome', '3 options', true],
                        ['Content pillars', 'Define personal content pillars', 'Repeatable topics', 'Pillar map'],
                        ['Weekly post system', 'Design a weekly posting system', 'Sustainable cadence', 'System'],
                        ['Portfolio narrative', 'Rewrite portfolio case narratives', 'Problem to result', '3 case outlines'],
                        ['About section', 'Rewrite LinkedIn About', 'Human and specific', 'About draft'],
                        ['Talk proposal', 'Write a meetup/talk proposal', 'Clear takeaway', 'Proposal'],
                        ['DM outreach', 'Write respectful networking DMs', 'No spam pitch', '5 templates'],
                        ['Credibility proof', 'List proof assets to publish', 'Evidence over claims', 'Proof backlog'],
                    ])),
                ],
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'description' => 'Verified teaching and course-building prompts.',
                'accent' => 'teal',
                'tags' => ['education'],
                'defaults' => ['behavior' => $creatorBehavior],
                'subs' => [
                    $this->sub('Course Design', 'course-design', 'Learning outcomes to curriculum.', ['course'], [
                        $this->role('an Instructional Designer', 'Instructional Designer', '13+ years designing adult learning programs', 'outcomes and practice'),
                        $this->role('a Cohort Course Facilitator', 'Cohort Facilitator', '9+ years running live cohorts', 'engagement and accountability'),
                    ], $this->focusPack([
                        ['Learning outcomes', 'Write measurable learning outcomes', 'Observable verbs', 'Outcome list', true],
                        ['Module outline', 'Outline a 6-module course', 'Progressive difficulty', 'Outline'],
                        ['Practice activities', 'Design practice activities', 'Doing > watching', 'Activity set'],
                        ['Assessment rubric', 'Create an assessment rubric', 'Fair and clear', 'Rubric'],
                        ['Hook lesson', 'Design a strong first lesson', 'Early wins', 'Lesson plan'],
                        ['Feedback loops', 'Design student feedback loops', 'Fast improvement', 'Feedback system'],
                        ['Community prompts', 'Write community discussion prompts', 'Depth without fluff', 'Prompt pack'],
                        ['Evergreen update plan', 'Plan course updates', 'Keep material fresh', 'Update cadence'],
                    ])),
                    $this->sub('Tutoring', 'tutoring', '1:1 teaching prompts.', ['tutor'], [
                        $this->role('a Master Tutor', 'Master Tutor', '15+ years tutoring with diagnostic teaching', 'meeting learners where they are'),
                        $this->role('a Socratic Coach', 'Socratic Coach', '10+ years teaching through questions', 'guided discovery'),
                    ], $this->focusPack([
                        ['Diagnostic first session', 'Run a diagnostic first session', 'Find gaps fast', 'Session plan', true],
                        ['Explain simply', 'Explain a hard concept simply', 'Accurate analogies', 'Explanation'],
                        ['Practice set', 'Create a spaced practice set', 'Retrieval practice', 'Exercise set'],
                        ['Mistake autopsy', 'Do a mistake autopsy with a learner', 'No shame, clear fix', 'Autopsy script'],
                        ['Study plan', 'Build a 2-week study plan', 'Realistic timeboxes', 'Plan'],
                        ['Exam simulation', 'Design an exam simulation', 'Timing and review', 'Sim plan'],
                        ['Motivation check-in', 'Run a motivation check-in', 'Systems over willpower', 'Check-in script'],
                        ['Parent/update note', 'Write a progress update note', 'Specific and kind', 'Update template'],
                    ])),
                ],
            ],
        ];
    }

    /**
     * @param  list<array{0:string,1:string,2:string,3:string,4?:bool}>  $rows
     * @return list<array{label:string,task:string,standards:string,output:string,is_best?:bool,tags?:list<string>}>
     */
    private function focusPack(array $rows): array
    {
        return array_map(function (array $row) {
            return [
                'label' => $row[0],
                'task' => $row[1],
                'standards' => $row[2],
                'output' => $row[3],
                'is_best' => (bool) ($row[4] ?? false),
                'tags' => ['verified'],
            ];
        }, $rows);
    }

    /**
     * @param  list<array{title:string,short:string,experience:string,domain:string,tags?:list<string>}>  $roles
     * @param  list<array{label:string,task:string,standards:string,output:string,is_best?:bool,tags?:list<string>}>  $focuses
     * @param  list<string>  $tags
     * @return array{name:string,slug:string,description:string,tags:list<string>,roles:list<array<string,mixed>>,focuses:list<array<string,mixed>>}
     */
    private function sub(string $name, string $slug, string $description, array $tags, array $roles, array $focuses): array
    {
        return compact('name', 'slug', 'description', 'tags', 'roles', 'focuses');
    }

    /**
     * @param  list<string>  $tags
     * @return array{title:string,short:string,experience:string,domain:string,tags:list<string>}
     */
    private function role(string $title, string $short, string $experience, string $domain, array $tags = []): array
    {
        return compact('title', 'short', 'experience', 'domain', 'tags');
    }
}
