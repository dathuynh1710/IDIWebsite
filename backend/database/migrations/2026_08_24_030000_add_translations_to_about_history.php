<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TRANSLATIONS = [
        'en' => [
            'title' => 'A History of Innovation',
            'slug' => 'a-history-of-innovation',
            'summary' => 'Since I.D.I embarked on its journey in 2008, the company has learned and transformed in many ways.',
            'content' => <<<'HTML'
<p>From its humble beginnings, when a few pioneers recognized the opportunity presented by Vietnam's wonderful pangasius species, I.D.I has grown to become a leader in its field.</p>
<p>I.D.I earns its share of success by developing and following an integrated strategy where our goals and missions are aligned with our guiding principles: Planet, People and Product.</p>
<p>With a clearly defined vision of sustainability, a strong global presence and extensive leadership experience, we are committed to building not only an excellent organization, but also enduring value for the world and the food industry.</p>
<h2>Development milestones</h2>
<h3>2008</h3>
<p>A few pioneers recognized the potential of the pangasius species, and the first processing plant was built to introduce high-quality products worldwide.</p>
<h3>2010</h3>
<p>Became one of 10 seafood producers in Vietnam recognized by the Ministry of Agriculture and Rural Development for having the best quality in the industry.</p>
<h3>2011</h3>
<p>Jumped into the top five pangasius exporters in Vietnam.</p>
<p>Became listed on the Vietnamese Stock Exchange.</p>
<h3>2016</h3>
<p>Ranked among the 500 fastest-growing companies in Vietnam.</p>
<p>Named among Vietnam's 50 most efficient companies by Vietnam Business Review.</p>
<h3>2017</h3>
<p>Constructed additional facilities to raise processing capacity to 1,000 tons per day and better meet fast-rising global demand.</p>
<h3>2019</h3>
<p>Invested in research programs for hatchery development and broodstock quality, as well as innovation in pond management.</p>
<h2>A history of responsibility</h2>
<p>As one of the largest producers of pangasius in the world, we understand that good business comes with great responsibility. Our value depends on the strong relationships we have built with trust, care and quality over time.</p>
HTML,
            'seo_title' => 'A History of Innovation',
            'meta_description' => "Explore I.D.I's journey of growth and innovation since 2008 through its defining development milestones.",
            'meta_keywords' => 'I.D.I, IDI Seafood, company history, innovation, milestones',
            'translation_status' => 'published',
        ],
        'zh' => [
            'title' => '发展与创新历程',
            'slug' => 'fa-zhan-yu-chuang-xin-li-cheng',
            'summary' => '自2008年I.D.I开启发展征程以来，公司在众多领域不断学习、成长与转型。',
            'content' => <<<'HTML'
<p>I.D.I从朴素的起点出发。当一批先行者发现越南优质巴沙鱼品种所蕴藏的机遇后，公司逐步成长为该领域的领先企业。</p>
<p>I.D.I通过制定并践行一体化战略取得成功，使企业目标和使命始终与“地球、人类、产品”三大指导原则保持一致。</p>
<p>凭借清晰的可持续发展愿景、强大的全球影响力和丰富的领导经验，我们不仅致力于建设卓越的组织，也致力于为世界和食品行业创造长久价值。</p>
<h2>发展里程碑</h2>
<h3>2008</h3>
<p>先行者发现了巴沙鱼品种的巨大潜力，首座加工厂随之建成，将高品质产品推向全球市场。</p>
<h3>2010</h3>
<p>成为越南农业与农村发展部认可的十家行业优质水产品生产企业之一。</p>
<h3>2011</h3>
<p>跃居越南巴沙鱼出口企业前五名。</p>
<p>在越南证券交易所挂牌上市。</p>
<h3>2016</h3>
<p>入选越南发展速度最快的500家企业。</p>
<p>获《Vietnam Business Review》评选为越南经营效率最高的50家企业之一。</p>
<h3>2017</h3>
<p>扩建设施，将日加工能力提升至1,000吨，以更好地满足全球快速增长的市场需求。</p>
<h3>2019</h3>
<p>投资开展孵化设施、亲本种群质量及池塘管理创新等研究项目。</p>
<h2>责任相伴的发展历程</h2>
<p>作为全球最大的巴沙鱼生产商之一，我们深知卓越经营始终伴随着重大责任。我们的价值源于长期以来以信任、关怀与品质建立的稳固客户关系。</p>
HTML,
            'seo_title' => 'I.D.I发展与创新历程',
            'meta_description' => '通过重要发展里程碑，了解I.D.I自2008年以来的成长与创新历程。',
            'meta_keywords' => 'I.D.I, IDI Seafood, 企业历史, 创新历程, 发展里程碑',
            'translation_status' => 'published',
        ],
    ];

    private const ROUTES = [
        'en' => ['slug' => 'a-history-of-innovation', 'path' => '/en/about/a-history-of-innovation'],
        'zh' => ['slug' => 'fa-zhan-yu-chuang-xin-li-cheng', 'path' => '/zh/guanyu/fa-zhan-yu-chuang-xin-li-cheng'],
    ];

    public function up(): void
    {
        $page = DB::table('pages')->where('code', 'ABOUT_HISTORY')->first();

        if (! $page) {
            return;
        }

        $updates = [];
        foreach (self::TRANSLATIONS as $locale => $translations) {
            foreach ($translations as $field => $translation) {
                $currentValue = $updates[$field] ?? $page->{$field} ?? null;
                $updates[$field] = $this->withTranslation($currentValue, $locale, $translation);
            }
            $currentDates = $updates['locale_published_at'] ?? $page->locale_published_at;
            $updates['locale_published_at'] = $this->withTranslation($currentDates, $locale, now()->toIso8601String());
        }
        $updates['updated_at'] = now();
        DB::table('pages')->where('id', $page->id)->update($updates);

        foreach (self::ROUTES as $locale => $route) {
            DB::table('localized_routes')->updateOrInsert([
                'routeable_type' => 'App\\Models\\Page',
                'routeable_id' => $page->id,
                'locale' => $locale,
            ], [
                'route_name' => 'about.show',
                'slug' => $route['slug'],
                'full_path' => $route['path'],
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
    }

    public function down(): void
    {
        $page = DB::table('pages')->where('code', 'ABOUT_HISTORY')->first();

        if (! $page) {
            return;
        }

        $updates = [];
        foreach (self::TRANSLATIONS as $locale => $translations) {
            foreach ($translations as $field => $translation) {
                $currentValue = $updates[$field] ?? $page->{$field} ?? null;
                $updates[$field] = $this->withoutMatchingTranslation($currentValue, $locale, $translation);
            }
        }
        $updates['updated_at'] = now();
        DB::table('pages')->where('id', $page->id)->update($updates);

        foreach (self::ROUTES as $locale => $route) {
            DB::table('localized_routes')
                ->where('routeable_type', 'App\\Models\\Page')
                ->where('routeable_id', $page->id)
                ->where('locale', $locale)
                ->where('slug', $route['slug'])
                ->delete();
        }
    }

    private function withTranslation(mixed $value, string $locale, string $translation): string
    {
        $translations = $this->decode($value);
        $translations[$locale] = $translation;

        return json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function withoutMatchingTranslation(mixed $value, string $locale, string $translation): string
    {
        $translations = $this->decode($value);
        if (($translations[$locale] ?? null) === $translation) {
            unset($translations[$locale]);
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
