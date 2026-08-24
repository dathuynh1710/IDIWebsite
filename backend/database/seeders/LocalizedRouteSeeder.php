<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\InteractsWithSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalizedRouteSeeder extends Seeder
{
    use InteractsWithSeedData;

    public function run(): void
    {
        $adminId = (int) DB::table('users')->where('email', 'admin@idiseafood.local')->value('id');

        foreach ($this->routeableDefinitions() as $definition) {
            $routeableId = $this->resolveId($definition);

            foreach ($definition['routes'] as $locale => $route) {
                $this->upsertId('localized_routes', [
                    'locale' => $locale,
                    'full_path' => $route['path'],
                ], [
                    'routeable_type' => $definition['type'],
                    'routeable_id' => $routeableId,
                    'route_name' => $definition['route_name'],
                    'slug' => $route['slug'],
                    'status' => 'published',
                    'published_at' => now()->subDays(7),
                    'robots_index' => true,
                    'robots_follow' => true,
                    'include_in_sitemap' => true,
                    'canonical_override' => null,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function routeableDefinitions(): array
    {
        return [
            $this->definition('product_categories', 'code', 'PANGASIUS', 'App\\Models\\ProductCategory', 'product-categories.show', [
                'vi' => ['slug' => 'ca-tra', 'path' => '/vi/san-pham/ca-tra'],
                'en' => ['slug' => 'pangasius', 'path' => '/en/products/pangasius'],
                'zh' => ['slug' => 'basha-yu', 'path' => '/zh/chanpin/basha-yu'],
            ]),
            $this->definition('product_categories', 'code', 'VALUE_ADDED', 'App\\Models\\ProductCategory', 'product-categories.show', [
                'vi' => ['slug' => 'san-pham-gia-tri-gia-tang', 'path' => '/vi/san-pham/san-pham-gia-tri-gia-tang'],
                'en' => ['slug' => 'value-added-products', 'path' => '/en/products/value-added-products'],
                'zh' => ['slug' => 'zengzhi-chanpin', 'path' => '/zh/chanpin/zengzhi-chanpin'],
            ]),
            $this->definition('products', 'sku', 'IDI-PAN-001', 'App\\Models\\Product', 'products.show', [
                'vi' => ['slug' => 'ca-tra-phi-le-dong-lanh', 'path' => '/vi/san-pham/ca-tra-phi-le-dong-lanh'],
                'en' => ['slug' => 'frozen-pangasius-fillet', 'path' => '/en/products/frozen-pangasius-fillet'],
                'zh' => ['slug' => 'lengdong-basha-yu-liu', 'path' => '/zh/chanpin/lengdong-basha-yu-liu'],
            ]),
            $this->definition('products', 'sku', 'IDI-VAP-001', 'App\\Models\\Product', 'products.show', [
                'vi' => ['slug' => 'ca-tra-tam-bot', 'path' => '/vi/san-pham/ca-tra-tam-bot'],
                'en' => ['slug' => 'breaded-pangasius', 'path' => '/en/products/breaded-pangasius'],
                'zh' => ['slug' => 'guofen-basha-yu', 'path' => '/zh/chanpin/guofen-basha-yu'],
            ]),
            $this->definition('pages', 'code', 'ABOUT_MESSAGE', 'App\\Models\\Page', 'about.show', [
                'vi' => ['slug' => 'thong-diep-cua-cong-ty', 'path' => '/vi/gioi-thieu/thong-diep-cua-cong-ty'],
                'en' => ['slug' => 'a-message-from-i-d-i', 'path' => '/en/about/a-message-from-i-d-i'],
                'zh' => ['slug' => 'i-d-i-zhi-ci', 'path' => '/zh/guanyu/i-d-i-zhi-ci'],
            ]),
            $this->definition('pages', 'code', 'ABOUT_HISTORY', 'App\\Models\\Page', 'about.show', [
                'vi' => ['slug' => 'lich-su-doi-moi', 'path' => '/vi/gioi-thieu/lich-su-doi-moi'],
                'en' => ['slug' => 'a-history-of-innovation', 'path' => '/en/about/a-history-of-innovation'],
                'zh' => ['slug' => 'fa-zhan-yu-chuang-xin-li-cheng', 'path' => '/zh/guanyu/fa-zhan-yu-chuang-xin-li-cheng'],
            ]),
            $this->definition('pages', 'code', 'ABOUT_VALUES', 'App\\Models\\Page', 'about.show', [
                'vi' => ['slug' => 'gia-tri-cot-loi', 'path' => '/vi/gioi-thieu/gia-tri-cot-loi'],
                'en' => ['slug' => 'i-d-i-s-values', 'path' => '/en/about/i-d-i-s-values'],
                'zh' => ['slug' => 'he-xin-jia-zhi-guan', 'path' => '/zh/guanyu/he-xin-jia-zhi-guan'],
            ]),
            $this->definition('pages', 'code', 'SUSTAINABILITY', 'App\\Models\\Page', 'pages.show', [
                'vi' => ['slug' => 'phat-trien-ben-vung', 'path' => '/vi/phat-trien-ben-vung'],
                'en' => ['slug' => 'sustainability', 'path' => '/en/sustainability'],
                'zh' => ['slug' => 'kechixu-fazhan', 'path' => '/zh/kechixu-fazhan'],
            ]),
            $this->definition('recipes', 'code', 'RECIPE_PANGASIUS_CURRY_COCONUT', 'App\\Models\\Recipe', 'recipes.show', [
                'vi' => ['slug' => 'ca-ri-ca-tra-voi-dua-va-sa', 'path' => '/vi/cong-thuc/ca-ri-ca-tra-voi-dua-va-sa'],
                'en' => ['slug' => 'pangasius-fish-curry-coconut-lemongrass', 'path' => '/en/recipes/pangasius-fish-curry-coconut-lemongrass'],
                'zh' => ['slug' => 'yezi-xiangmao-basha-yu-gali', 'path' => '/zh/shipu/yezi-xiangmao-basha-yu-gali'],
            ]),
            $this->definition('investor_documents', 'document_number', 'AR-2025', 'App\\Models\\InvestorDocument', 'investor-documents.show', [
                'vi' => ['slug' => 'bao-cao-thuong-nien-2025', 'path' => '/vi/quan-he-co-dong/bao-cao-thuong-nien-2025'],
                'en' => ['slug' => 'annual-report-2025', 'path' => '/en/investors/annual-report-2025'],
                'zh' => ['slug' => '2025-niandu-baogao', 'path' => '/zh/touzizhe/2025-niandu-baogao'],
            ]),
            $this->definition('job_positions', 'code', 'SALES_EXPORT_01', 'App\\Models\\JobPosition', 'careers.show', [
                'vi' => ['slug' => 'chuyen-vien-kinh-doanh-xuat-khau', 'path' => '/vi/tuyen-dung/chuyen-vien-kinh-doanh-xuat-khau'],
                'en' => ['slug' => 'export-sales-executive', 'path' => '/en/careers/export-sales-executive'],
                'zh' => ['slug' => 'chukou-xiaoshou-zhuanyuan', 'path' => '/zh/zhaopin/chukou-xiaoshou-zhuanyuan'],
            ]),
            $this->definition('job_positions', 'code', 'QA_SUPERVISOR_01', 'App\\Models\\JobPosition', 'careers.show', [
                'vi' => ['slug' => 'giam-sat-dam-bao-chat-luong', 'path' => '/vi/tuyen-dung/giam-sat-dam-bao-chat-luong'],
                'en' => ['slug' => 'quality-assurance-supervisor', 'path' => '/en/careers/quality-assurance-supervisor'],
                'zh' => ['slug' => 'zhiliang-baozheng-zhuguan', 'path' => '/zh/zhaopin/zhiliang-baozheng-zhuguan'],
            ]),
            $this->definition('job_positions', 'code', 'IT_SYSTEM_01', 'App\\Models\\JobPosition', 'careers.show', [
                'vi' => ['slug' => 'nhan-vien-he-thong-cong-nghe-thong-tin', 'path' => '/vi/tuyen-dung/nhan-vien-he-thong-cong-nghe-thong-tin'],
                'en' => ['slug' => 'it-systems-specialist', 'path' => '/en/careers/it-systems-specialist'],
                'zh' => ['slug' => 'xinxi-jishu-xitong-zhuanyuan', 'path' => '/zh/zhaopin/xinxi-jishu-xitong-zhuanyuan'],
            ]),
            $this->definition('job_positions', 'code', 'HR_RECRUITMENT_01', 'App\\Models\\JobPosition', 'careers.show', [
                'vi' => ['slug' => 'chuyen-vien-tuyen-dung-va-dao-tao', 'path' => '/vi/tuyen-dung/chuyen-vien-tuyen-dung-va-dao-tao'],
                'en' => ['slug' => 'recruitment-training-specialist', 'path' => '/en/careers/recruitment-training-specialist'],
                'zh' => ['slug' => 'zhaopin-peixun-zhuanyuan', 'path' => '/zh/zhaopin/zhaopin-peixun-zhuanyuan'],
            ]),
        ];
    }

    /**
     * @param  array<string, array{slug: string, path: string}>  $routes
     * @return array<string, mixed>
     */
    private function definition(
        string $table,
        string $key,
        string $value,
        string $type,
        string $routeName,
        array $routes
    ): array {
        return compact('table', 'key', 'value', 'type', 'routes') + [
            'route_name' => $routeName,
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function resolveId(array $definition): int
    {
        return (int) DB::table($definition['table'])
            ->where($definition['key'], $definition['value'])
            ->value('id');
    }
}
