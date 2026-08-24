<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TRANSLATIONS = [
        'en' => [
            'title' => "I.D.I's Values",
            'slug' => 'i-d-i-s-values',
            'summary' => "Passion, innovation, sharing and responsibility are the values that shape I.D.I's corporate culture.",
            'content' => <<<'HTML'
<h2>I.D.I's Values</h2>
<h3>Passion</h3>
<p>Passion and dedication are the key ingredients to our success; they are embedded in every aspect of our operations and at the heart of our culture.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt1.jpg" alt="Passion"></p>
<h3>Innovation</h3>
<p>At I.D.I, innovation is the norm—for everything we do and all that we produce. It is what guides us to the top of an ever-evolving seafood industry.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/07_2024/congnhan.png" alt="Innovation"></p>
<h3>Sharing</h3>
<p>I.D.I encourages an open and transparent environment. Within I.D.I, opportunities are shared among all our employees, enabling them to make important contributions across the spectrum of our business.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt3.jpg" alt="Sharing"></p>
<h3>Responsibility</h3>
<p>Enjoying nature's bounty comes with great responsibility. Our continued respect and contribution to society and the environment remain essential to our identity and our success.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt4.jpg" alt="Responsibility"></p>
<h2>Strengths</h2>
<h3>We are passionate</h3>
<p>We simply love pangasius. We want to understand everything about it, how it tastes at its best, and how we can produce it as purely and responsibly as possible.</p>
<h3>We act with honesty and integrity</h3>
<p>We believe in honest and transparent cooperation with partners and stakeholders in the value chain. This is why we seek long-term, sustainable partners in our supply and customer base who share the same values.</p>
<h3>We are reliable</h3>
<p>I.D.I offers an extensive range of natural fresh fish products. From this wide selection, we recommend the products that best fit our customers' needs. We have become a dependable partner to many customers across retail, wholesale and food service.</p>
<h2>Mission</h2>
<h3>Sustainable aquaculture</h3>
<p>We want to enable sustainable increases in livelihoods from aquaculture production without creating adverse socio-economic or environmental impacts.</p>
<h3>Value chain and nutrition</h3>
<p>We strive to explore the full potential of pangasius and increase access to and consumption of nutritious, sustainably raised fish, especially in developing regions.</p>
<h3>Social responsibility</h3>
<p>We believe it is important for I.D.I to contribute to social initiatives, support the development of small-scale aquaculture and enhance education in communities to reduce poverty and strengthen food security in priority regions.</p>
HTML,
            'seo_title' => "I.D.I's Values",
            'meta_description' => "Discover the core values, strengths and mission that shape I.D.I's corporate culture and sustainable development.",
            'meta_keywords' => 'I.D.I, IDI Seafood, core values, strengths, mission',
            'translation_status' => 'published',
        ],
        'zh' => [
            'title' => '核心价值观',
            'slug' => 'he-xin-jia-zhi-guan',
            'summary' => '热情、创新、分享与责任，是塑造I.D.I企业文化的核心价值观。',
            'content' => <<<'HTML'
<h2>核心价值观</h2>
<h3>热情</h3>
<p>热情与奉献是我们取得成功的关键，它们融入经营活动的每一个环节，也是企业文化的核心。</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt1.jpg" alt="热情"></p>
<h3>创新</h3>
<p>在I.D.I，创新是我们开展一切工作和生产所有产品的准则，引领我们在不断发展的水产行业中持续迈向更高水平。</p>
<p><img src="https://idiseafood.com/vnt_upload/about/07_2024/congnhan.png" alt="创新"></p>
<h3>分享</h3>
<p>I.D.I倡导开放、透明的工作环境，与所有员工共享发展机会，使每个人都能在公司的各个业务领域作出重要贡献。</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt3.jpg" alt="分享"></p>
<h3>责任</h3>
<p>享受大自然的馈赠也意味着承担重大责任。我们始终尊重社会与环境并持续作出贡献，这是I.D.I保持自身特色并取得成功的重要基础。</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt4.jpg" alt="责任"></p>
<h2>我们的优势</h2>
<h3>我们充满热情</h3>
<p>我们热爱巴沙鱼，希望深入了解它的一切：如何呈现最佳风味，以及如何以最纯净、最负责任的方式进行生产。</p>
<h3>我们秉持诚实与诚信</h3>
<p>我们重视与价值链中的合作伙伴及利益相关方开展诚实、透明的合作。因此，我们在供应链和客户群体中寻求拥有共同价值观的长期、可持续合作伙伴。</p>
<h3>我们值得信赖</h3>
<p>I.D.I提供丰富多样的天然鲜鱼产品，并根据客户需求推荐最合适的选择。如今，我们已成为零售、批发及餐饮服务等众多客户值得信赖的合作伙伴。</p>
<h2>使命</h2>
<h3>可持续水产养殖</h3>
<p>我们希望通过水产养殖生产可持续地改善生计，同时避免对社会经济或环境造成不利影响。</p>
<h3>价值链与营养</h3>
<p>我们致力于充分发掘巴沙鱼的潜力，提高营养丰富、可持续养殖鱼类的可获得性与消费量，尤其关注发展中地区。</p>
<h3>社会责任</h3>
<p>我们认为I.D.I应积极参与社会项目，推动小规模水产养殖发展并加强社区教育，从而减少贫困，保障重点地区的粮食安全。</p>
HTML,
            'seo_title' => 'I.D.I核心价值观',
            'meta_description' => '了解塑造I.D.I企业文化与可持续发展的核心价值观、企业优势和使命。',
            'meta_keywords' => 'I.D.I, IDI Seafood, 核心价值观, 企业优势, 使命',
            'translation_status' => 'published',
        ],
    ];

    private const ROUTES = [
        'en' => ['slug' => 'i-d-i-s-values', 'path' => '/en/about/i-d-i-s-values'],
        'zh' => ['slug' => 'he-xin-jia-zhi-guan', 'path' => '/zh/guanyu/he-xin-jia-zhi-guan'],
    ];

    public function up(): void
    {
        $page = DB::table('pages')->where('code', 'ABOUT_VALUES')->first();

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
        $page = DB::table('pages')->where('code', 'ABOUT_VALUES')->first();

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
