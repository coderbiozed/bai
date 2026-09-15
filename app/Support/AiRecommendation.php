<?php

namespace App\Support;

use Illuminate\Support\Str;

class AiRecommendation
{
    /**
     * Suggested AI platform + model by category (and optional subcategory) slug.
     *
     * @return array{platform: string, model: string}
     */
    public static function for(?string $categorySlug, ?string $subcategorySlug = null): array
    {
        $categorySlug = Str::lower(trim((string) $categorySlug));
        $subcategorySlug = Str::lower(trim((string) $subcategorySlug));

        $bySub = [
            'photo-generate' => ['platform' => 'Midjourney', 'model' => 'v6.1'],
            'illustration' => ['platform' => 'Midjourney', 'model' => 'v6.1'],
            'image-creator' => ['platform' => 'ChatGPT', 'model' => 'GPT Image'],
            'product-photo-playbook' => ['platform' => 'ChatGPT', 'model' => 'GPT Image'],
            'shorts-maker-playbook' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'youtuber-playbook' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'script-writing' => ['platform' => 'Claude', 'model' => 'Sonnet 4'],
            'thumbnails-titles' => ['platform' => 'ChatGPT', 'model' => 'GPT Image'],
        ];

        if ($subcategorySlug !== '' && isset($bySub[$subcategorySlug])) {
            return $bySub[$subcategorySlug];
        }

        return match ($categorySlug) {
            'development' => ['platform' => 'Cursor', 'model' => 'Claude Sonnet'],
            'youtube' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'marketing' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'design' => ['platform' => 'Claude', 'model' => 'Sonnet 4'],
            'graphics' => ['platform' => 'Midjourney', 'model' => 'v6.1'],
            'business' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'writing' => ['platform' => 'Claude', 'model' => 'Sonnet 4'],
            'career' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'education' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'fintech' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'forex' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'travel' => ['platform' => 'Claude', 'model' => 'Sonnet 4'],
            'media' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'meta-manager' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'content' => ['platform' => 'Claude', 'model' => 'Sonnet 4'],
            'article-writer' => ['platform' => 'Claude', 'model' => 'Sonnet 4'],
            'blog-writer' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'admin' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'finances' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'seo-pro' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'cto' => ['platform' => 'Cursor', 'model' => 'Claude Sonnet'],
            'math-learning' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'english-learning' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            'instant-solutions' => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
            default => ['platform' => 'ChatGPT', 'model' => 'GPT-5'],
        };
    }
}
