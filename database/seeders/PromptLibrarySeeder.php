<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\Subcategory;
use App\Models\Tag;
use App\Support\AiRecommendation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromptLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $tip = 'Avoid absolute claims like “the world\'s best”. AI models respond better to prompts that specify experience, domain, responsibilities, standards, and desired behavior rather than superlatives.';

        $catalog = [
            [
                'name' => 'Development',
                'slug' => 'development',
                'description' => 'Engineering roles, frameworks, and architecture prompts.',
                'accent' => 'teal',
                'subcategories' => [
                    [
                        'name' => 'Software Engineer',
                        'slug' => 'software-engineer',
                        'description' => 'General and specialized engineering role prompts.',
                        'prompts' => [
                            [
                                'title' => 'Staff Software Engineer — Distributed Systems',
                                'body' => 'Act as a Staff Software Engineer at Google specializing in distributed systems. Prioritize reliability, clear trade-offs, observability, and production-ready designs. Explain decisions with constraints, failure modes, and measurable outcomes.',
                                'is_best' => true,
                                'tags' => ['role', 'backend', 'systems'],
                            ],
                        ],
                    ],
                    [
                        'name' => 'React Developer',
                        'slug' => 'react-developer',
                        'description' => 'Frontend React craft, accessibility, and performance.',
                        'prompts' => [
                            [
                                'title' => 'Senior React Engineer — Product UI',
                                'body' => 'Act as a Senior React Engineer with deep expertise in modern React (hooks, concurrent features, Server Components where relevant). Write maintainable, accessible, performant UI. Prefer clear component boundaries, sensible state ownership, and production patterns over clever abstractions.',
                                'is_best' => true,
                                'tags' => ['role', 'frontend', 'react'],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Laravel Developer',
                        'slug' => 'laravel-developer',
                        'description' => 'Laravel architecture, Eloquent, and API design.',
                        'prompts' => [
                            [
                                'title' => 'Principal Laravel Architect — Enterprise',
                                'body' => 'Act as a Principal Laravel Architect with 15+ years of enterprise experience. Design clear domain boundaries, idiomatic Eloquent usage, robust validation, queues, and testable services. Prefer Laravel conventions, security defaults, and maintainable structure over premature complexity.',
                                'is_best' => true,
                                'tags' => ['role', 'backend', 'laravel'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Design',
                'slug' => 'design',
                'description' => 'Product design, UX writing, and visual systems.',
                'accent' => 'ink',
                'subcategories' => [
                    [
                        'name' => 'UI/UX Designer',
                        'slug' => 'ui-ux-designer',
                        'description' => 'User-centered product design prompts.',
                        'prompts' => [
                            [
                                'title' => 'Senior Product Designer — Accessibility First',
                                'body' => 'Act as a Senior Product Designer from Apple with expertise in user-centered design and accessibility. Prioritize clarity, hierarchy, inclusive interaction, and calm visual systems. Critique and propose designs with rationale tied to user goals and constraints.',
                                'is_best' => true,
                                'tags' => ['role', 'design', 'a11y'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Growth, SEO, content, and CMS operations.',
                'accent' => 'amber',
                'subcategories' => [
                    [
                        'name' => 'Growth Marketing',
                        'slug' => 'growth-marketing',
                        'description' => '0→1 positioning and acquisition strategy.',
                        'prompts' => [
                            [
                                'title' => 'CMO — Startup Scale 0 to 1M Users',
                                'body' => 'Act as a Chief Marketing Officer with experience scaling startups from 0 to 1 million users. Focus on positioning, channel experiments, retention loops, and measurable experiments. Avoid vanity metrics; recommend clear hypotheses and success criteria.',
                                'is_best' => true,
                                'tags' => ['role', 'marketing', 'growth'],
                            ],
                        ],
                    ],
                    [
                        'name' => 'SEO',
                        'slug' => 'seo',
                        'description' => 'Technical SEO and content ranking strategy.',
                        'prompts' => [
                            [
                                'title' => 'SEO Consultant — Technical & Content',
                                'body' => 'Act as an SEO consultant with 15 years of experience in technical SEO, content strategy, and Google ranking factors. Diagnose issues with crawlability, intent match, internal linking, and content quality. Give prioritized, actionable recommendations with expected impact.',
                                'is_best' => true,
                                'tags' => ['role', 'seo', 'content'],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Link Building',
                        'slug' => 'link-building',
                        'description' => 'Ethical outreach and authority building.',
                        'prompts' => [
                            [
                                'title' => 'Link Building Specialist — Ethical Outreach',
                                'body' => 'Act as a link building specialist focused on ethical, relationship-driven outreach. Prefer relevant placements, useful assets, and sustainable tactics. Outline outreach angles, target criteria, and quality checks that avoid spammy patterns.',
                                'is_best' => false,
                                'tags' => ['seo', 'outreach'],
                            ],
                        ],
                    ],
                    [
                        'name' => 'CMS Management',
                        'slug' => 'cms-management',
                        'description' => 'Content operations and publishing workflows.',
                        'prompts' => [
                            [
                                'title' => 'CMS Operations Lead — Publishing Systems',
                                'body' => 'Act as a CMS operations lead responsible for content workflows, taxonomy, publishing QA, and editor experience. Recommend practical process and information architecture improvements that reduce errors and speed up publishing.',
                                'is_best' => false,
                                'tags' => ['cms', 'operations'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Graphics',
                'slug' => 'graphics',
                'description' => 'Image generation and visual prompt craft.',
                'accent' => 'rose',
                'subcategories' => [
                    [
                        'name' => 'Photo Generate',
                        'slug' => 'photo-generate',
                        'description' => 'Photorealistic and editorial image prompts.',
                        'prompts' => [
                            [
                                'title' => 'Editorial Photo Prompt — Product Atmosphere',
                                'body' => 'Act as a commercial photographer art director. Write precise image-generation prompts with subject, lens feel, lighting, environment, color grade, composition, and exclusions. Prefer concrete visual language over vague adjectives.',
                                'is_best' => true,
                                'tags' => ['image', 'photo', 'prompt-craft'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $tagCache = [];

        foreach ($catalog as $categoryIndex => $categoryData) {
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

                foreach ($subData['prompts'] as $promptData) {
                    $ai = AiRecommendation::for($categoryData['slug'], $subData['slug']);

                    $prompt = Prompt::query()->updateOrCreate(
                        [
                            'subcategory_id' => $subcategory->id,
                            'slug' => Str::slug($promptData['title']),
                        ],
                        [
                            'title' => $promptData['title'],
                            'body' => $promptData['body'],
                            'tip_note' => $tip,
                            'recommended_platform' => $ai['platform'],
                            'recommended_model' => $ai['model'],
                            'is_best' => $promptData['is_best'],
                            'is_verified' => true,
                            'verified_by' => 'bAI Verified Curriculum',
                            'verified_at' => now(),
                            'is_public' => true,
                            'is_free' => true,
                            'status' => 'published',
                        ]
                    );

                    if ($prompt->versions()->count() === 0) {
                        $prompt->recordVersion('Initial seed');
                    }

                    $tagIds = [];
                    foreach ($promptData['tags'] as $tagName) {
                        $slug = Str::slug($tagName);
                        if (! isset($tagCache[$slug])) {
                            $tagCache[$slug] = Tag::query()->firstOrCreate(
                                ['slug' => $slug],
                                ['name' => $tagName]
                            );
                        }
                        $tagIds[] = $tagCache[$slug]->id;
                    }
                    $prompt->tags()->sync($tagIds);
                }
            }
        }
    }
}
