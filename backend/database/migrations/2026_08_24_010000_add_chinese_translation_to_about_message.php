<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TRANSLATIONS = [
        'title' => 'I.D.I 致辞',
        'slug' => 'i-d-i-zhi-ci',
        'summary' => '在I.D.I，我们相信可持续发展不是一种选择，而是一种生活方式。我们已经并将继续创造多样化的价值，同时适应社会和环境变化。',
        'content' => <<<'HTML'
<h2>Mr. Le Van Chung</h2>
<p><em>执行顾问</em></p>
<p>在这个快速发展的世界中，I.D.I的使命一直保持不变，并且将继续如此：我们希望通过为人们提供安全、健康和美味的食物来丰富人们的生活并养育他们的身体。这一理念为我们过去十年来为成为社会的重要和不可或缺的一部分所做的不懈努力奠定了基础。</p>
<p>作为湄公河三角洲地区赏金的受益者，我们认为我们还有保护自然环境的公司职责。世界的水产养殖资源不是无限的，与大自然合一生活是我们面临的挑战。我们必须在可持续性和环境发展方面取得重大进展；为子孙后代保持河流和无数生态系统的清洁和健康是我们的工作。这种承诺指导着我们的日常行动和决策过程。同时，我们致力于通过围绕健康管理、多样性和领导力发展的举措进一步推动我们的有机增长。</p>
<p>I.D.I并非社会和经济变革的例外，这使得预测变得越来越困难。但是，生活方式和思维方式的更多多样性也使我们能够利用我们的经验来释放机会。我们必须坚持创新，同时恪守我们的价值观，以确保I.D.I从现在到未来还有一个世纪。</p>
HTML,
        'seo_title' => 'I.D.I 致辞',
        'meta_description' => 'I.D.I执行顾问Le Van Chung先生分享公司对可持续发展、社会、人才和环境的承诺。',
        'meta_keywords' => 'I.D.I, IDI Seafood, 公司致辞, 可持续发展',
        'translation_status' => 'published',
    ];

    private const MEDIA_TRANSLATIONS = [
        'title' => 'Le Van Chung先生',
        'alt_text' => 'Le Van Chung先生 - I.D.I执行顾问',
    ];

    public function up(): void
    {
        $page = DB::table('pages')->where('code', 'ABOUT_MESSAGE')->first();

        if (! $page) {
            return;
        }

        $updates = [];
        foreach (self::TRANSLATIONS as $field => $translation) {
            $updates[$field] = $this->withChineseTranslation($page->{$field} ?? null, $translation);
        }
        $updates['locale_published_at'] = $this->withChineseTranslation($page->locale_published_at, now()->toIso8601String());
        $updates['updated_at'] = now();
        DB::table('pages')->where('id', $page->id)->update($updates);

        if ($page->featured_media_id) {
            $media = DB::table('media')->where('id', $page->featured_media_id)->first();
            if ($media) {
                DB::table('media')->where('id', $media->id)->update([
                    'title' => $this->withChineseTranslation($media->title, self::MEDIA_TRANSLATIONS['title']),
                    'alt_text' => $this->withChineseTranslation($media->alt_text, self::MEDIA_TRANSLATIONS['alt_text']),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('localized_routes')->updateOrInsert([
            'routeable_type' => 'App\\Models\\Page',
            'routeable_id' => $page->id,
            'locale' => 'zh',
        ], [
            'route_name' => 'about.show',
            'slug' => self::TRANSLATIONS['slug'],
            'full_path' => '/zh/guanyu/'.self::TRANSLATIONS['slug'],
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
            $updates[$field] = $this->withoutMatchingChineseTranslation($page->{$field} ?? null, $translation);
        }
        $updates['updated_at'] = now();
        DB::table('pages')->where('id', $page->id)->update($updates);

        if ($page->featured_media_id) {
            $media = DB::table('media')->where('id', $page->featured_media_id)->first();
            if ($media) {
                DB::table('media')->where('id', $media->id)->update([
                    'title' => $this->withoutMatchingChineseTranslation($media->title, self::MEDIA_TRANSLATIONS['title']),
                    'alt_text' => $this->withoutMatchingChineseTranslation($media->alt_text, self::MEDIA_TRANSLATIONS['alt_text']),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('localized_routes')
            ->where('routeable_type', 'App\\Models\\Page')
            ->where('routeable_id', $page->id)
            ->where('locale', 'zh')
            ->where('slug', self::TRANSLATIONS['slug'])
            ->delete();
    }

    private function withChineseTranslation(mixed $value, string $translation): string
    {
        $translations = $this->decode($value);
        $translations['zh'] = $translation;

        return json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function withoutMatchingChineseTranslation(mixed $value, string $translation): string
    {
        $translations = $this->decode($value);
        if (($translations['zh'] ?? null) === $translation) {
            unset($translations['zh']);
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
