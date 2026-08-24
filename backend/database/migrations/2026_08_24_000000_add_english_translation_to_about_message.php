<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TRANSLATIONS = [
        'title' => 'A Message from I.D.I',
        'slug' => 'a-message-from-i-d-i',
        'summary' => 'At I.D.I, we believe that sustainability is not a choice, but a way of life. We have, and will continue to create diverse value while adapting to social and environmental changes.',
        'content' => <<<'HTML'
<h2>Mr. Le Van Chung</h2>
<p><em>Executive Advisor</em></p>
<p>In this fast-evolving world, I.D.I’s mission has remained constant, and will continue to be so: We want to enrich people’s lives and nurture their bodies by providing them with safe, healthy and delicious food. This philosophy has laid the foundation for our tireless efforts in the past 10 years to become a valued and indispensable part of society.</p>
<p>As beneficiaries of the Mekong Delta Region’s bounty, we believe that we also have corporate duties to protect the natural environment. The world’s aquaculture resources are not infinite, it is our challenge to live as one with nature. We must take great strides towards sustainability and environmental development; it is our job to keep the rivers and countless ecosystems clean and healthy for future generations. This commitment is what guides our day-to-day actions and our decision-making process. At the same time, we strive to fuel our organic growth still further through initiatives around health management, diversity, and leadership development.</p>
<p>I.D.I is not an exception to social and economic change, making predictions increasingly difficult. However, greater diversity in lifestyles and ways of thinking also allows us to unlock opportunities by leveraging on our experience. We must embrace innovation, while staying true to our values, to ensure that I.D.I will still exist a century from now—and beyond.</p>
HTML,
        'seo_title' => 'A Message from I.D.I',
        'meta_description' => 'Executive Advisor Le Van Chung shares I.D.I’s commitment to sustainable growth, people, society and the environment.',
        'meta_keywords' => 'I.D.I, IDI Seafood, company message, sustainable development',
        'translation_status' => 'published',
    ];

    public function up(): void
    {
        $page = DB::table('pages')->where('code', 'ABOUT_MESSAGE')->first();

        if (! $page) {
            return;
        }

        $updates = [];
        foreach (self::TRANSLATIONS as $field => $translation) {
            $updates[$field] = $this->withEnglishTranslation($page->{$field} ?? null, $translation);
        }
        $updates['locale_published_at'] = $this->withEnglishTranslation($page->locale_published_at, now()->toIso8601String());
        $updates['updated_at'] = now();

        DB::table('pages')->where('id', $page->id)->update($updates);

        DB::table('localized_routes')->updateOrInsert([
            'routeable_type' => 'App\\Models\\Page',
            'routeable_id' => $page->id,
            'locale' => 'en',
        ], [
            'route_name' => 'about.show',
            'slug' => self::TRANSLATIONS['slug'],
            'full_path' => '/en/about/'.self::TRANSLATIONS['slug'],
            'status' => 'published',
            'published_at' => now(),
            'robots_index' => true,
            'robots_follow' => true,
            'include_in_sitemap' => true,
            'canonical_override' => null,
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }

    public function down(): void
    {
        $page = DB::table('pages')->where('code', 'ABOUT_MESSAGE')->first();

        if (! $page) {
            return;
        }

        $updates = [];
        foreach (self::TRANSLATIONS as $field => $translation) {
            $updates[$field] = $this->withoutMatchingEnglishTranslation($page->{$field} ?? null, $translation);
        }
        $updates['updated_at'] = now();
        DB::table('pages')->where('id', $page->id)->update($updates);

        DB::table('localized_routes')
            ->where('routeable_type', 'App\\Models\\Page')
            ->where('routeable_id', $page->id)
            ->where('locale', 'en')
            ->where('slug', self::TRANSLATIONS['slug'])
            ->delete();
    }

    private function withEnglishTranslation(mixed $value, string $translation): string
    {
        $translations = $this->decode($value);
        $translations['en'] = $translation;

        return json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function withoutMatchingEnglishTranslation(mixed $value, string $translation): string
    {
        $translations = $this->decode($value);
        if (($translations['en'] ?? null) === $translation) {
            unset($translations['en']);
        }

        return json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function decode(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }
};
