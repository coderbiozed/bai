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

class InstantSolutionsSeeder extends Seeder
{
    private const VERIFIER = 'bAI Verified Curriculum';

    private const TIP = 'Quick-test prompt: paste your content after this instruction. Use it to check how clear and useful the AI answer is — then compare with longer role prompts in the library.';

    public function run(): void
    {
        $now = Carbon::now();
        $ai = AiRecommendation::for('instant-solutions', 'quick-test');

        $category = Category::query()->updateOrCreate(
            ['slug' => 'instant-solutions'],
            [
                'name' => 'Instant Solutions',
                'description' => 'Hot quick-test prompts for beginners — everyday tasks, ready to copy and try in seconds.',
                'accent' => 'lime',
                'sort_order' => 0,
            ]
        );

        $subcategory = Subcategory::query()->updateOrCreate(
            [
                'category_id' => $category->id,
                'slug' => 'quick-test',
            ],
            [
                'name' => 'Quick Test',
                'description' => 'Instant reusable prompts for writing, email, code, research, learning, and decisions.',
                'sort_order' => 1,
            ]
        );

        $tagCache = [];
        $count = 0;

        foreach ($this->prompts() as $index => $promptData) {
            $slug = Str::slug($promptData['title']);
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
                $prompt->recordVersion('Instant solutions seed');
            }

            $tagIds = [];
            foreach ($promptData['tags'] as $tagName) {
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

        $this->command?->info("Instant solution prompts upserted: {$count}");
    }

    /**
     * @return list<array{title:string,body:string,is_best?:bool,tags:list<string>}>
     */
    private function prompts(): array
    {
        $baseTags = ['verified', 'quick-test', 'beginner', 'instant'];

        return [
            [
                'title' => 'Writing & Rewriting — Instant polish',
                'body' => "Rewrite the following text professionally and clearly. Keep the original meaning, remove unnecessary words, fix grammar, and make it sound natural and polished. Give me the final version directly, ready to copy and use.\n\n[Paste your text here]",
                'is_best' => true,
                'tags' => array_merge($baseTags, ['writing']),
            ],
            [
                'title' => 'Email — Ready to send',
                'body' => "Write a professional email based on the information below. Keep it concise, polite, and natural. Include a clear subject line and make the email ready to send without additional explanation.\n\n[Paste your notes / goal / recipient context here]",
                'is_best' => true,
                'tags' => array_merge($baseTags, ['email']),
            ],
            [
                'title' => 'Coding / Bug Fix — Production-ready',
                'body' => "Analyze the following code and identify the problem. Explain the root cause briefly, then provide the corrected code. Do not change unrelated functionality. Make the solution production-ready and mention any important edge cases.\n\n[Paste your code here]",
                'is_best' => true,
                'tags' => array_merge($baseTags, ['coding']),
            ],
            [
                'title' => 'Error Troubleshooting — Simple first',
                'body' => "I am getting the following error. Identify the most likely cause, explain why it happens, and give me a step-by-step solution. Start with the simplest and safest fix before suggesting more advanced solutions.\n\n[Paste the error message / stack trace / context here]",
                'is_best' => false,
                'tags' => array_merge($baseTags, ['debugging']),
            ],
            [
                'title' => 'Work Update — Manager-ready',
                'body' => "Convert my rough notes into a concise professional work update. Clearly separate completed work, fixes, testing, and remaining tasks. Use simple professional language suitable for sharing with a manager or client.\n\n[Paste your rough notes here]",
                'is_best' => true,
                'tags' => array_merge($baseTags, ['work']),
            ],
            [
                'title' => 'Research & Comparison — Clear options',
                'body' => "Research this topic and give me a clear comparison of the available options. Focus on the most important differences, advantages, disadvantages, cost/value, and practical recommendation. Use current information where relevant.\n\n[Paste the topic / options to compare here]",
                'is_best' => false,
                'tags' => array_merge($baseTags, ['research']),
            ],
            [
                'title' => 'Learn Something Quickly — Simple explanation',
                'body' => "Explain the following topic in the simplest possible way. Start with a one-paragraph overview, then explain it step by step with a practical example. Avoid unnecessary technical jargon.\n\n[Paste the topic here]",
                'is_best' => true,
                'tags' => array_merge($baseTags, ['learning']),
            ],
            [
                'title' => 'Document / Requirements — Spec from notes',
                'body' => "Turn the following rough requirements into a clear technical specification. Organize it into objective, features, requirements, database changes, routes/API, frontend changes, validation, testing, and deployment considerations. Do not invent requirements that were not provided.\n\n[Paste your rough requirements here]",
                'is_best' => false,
                'tags' => array_merge($baseTags, ['docs']),
            ],
            [
                'title' => 'Instant Decision — Recommend first',
                'body' => "Help me make a quick decision about the following options. Compare them based on cost, time, difficulty, risk, scalability, and long-term benefit. Give me your recommended option first, followed by a short explanation.\n\n[Paste your options / decision context here]",
                'is_best' => true,
                'tags' => array_merge($baseTags, ['decision']),
            ],
            [
                'title' => 'Fix & Improve Anything — Final version first',
                'body' => "Review the following content/code/plan and find anything that is incorrect, unclear, inefficient, inconsistent, or unnecessary. Fix the issues while preserving the original goal. Give me the improved final version first, followed by a brief list of what you changed.\n\n[Paste your content / code / plan here]",
                'is_best' => false,
                'tags' => array_merge($baseTags, ['improve']),
            ],
            [
                'title' => 'Do It for Me — Master prompt',
                'body' => "Act as an expert in this task. Understand my goal, identify what needs to be done, and provide the most practical solution. Avoid unnecessary explanation or questions unless essential information is missing. Give me a ready-to-use final result first, followed by only the key points I need to know.\n\n[Paste your goal / task / materials here]",
                'is_best' => true,
                'tags' => array_merge($baseTags, ['master']),
            ],
        ];
    }
}
