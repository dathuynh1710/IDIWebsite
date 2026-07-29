<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\InteractsWithSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    use InteractsWithSeedData;

    public function run(): void
    {
        $adminId = (int) DB::table('users')->where('email', 'admin@idiseafood.local')->value('id');
        $pangasiusMediaId = $this->mediaId('pangasius-fillet.jpg');
        $valueAddedMediaId = $this->mediaId('breaded-pangasius.jpg');
        $catalogMediaId = $this->mediaId('product-catalog.pdf');

        $categoryIds = $this->seedCategories($adminId, $pangasiusMediaId, $valueAddedMediaId);
        $attributeIds = $this->seedAttributes();
        $productIds = $this->seedProducts(
            $adminId,
            $categoryIds,
            $pangasiusMediaId,
            $valueAddedMediaId
        );

        $this->seedProductAttributes($productIds, $attributeIds);
        $this->seedProductDocuments($productIds['fillet'], $catalogMediaId);
        $this->seedProductStatistics($productIds);
    }

    /**
     * @return array<string, int>
     */
    private function seedCategories(int $adminId, int $pangasiusMediaId, int $valueAddedMediaId): array
    {
        return [
            'pangasius' => $this->upsertId('product_categories', ['code' => 'PANGASIUS'], [
                'parent_id' => null,
                'featured_media_id' => $pangasiusMediaId,
                'name' => $this->translations('Cá tra', 'Pangasius', '巴沙鱼'),
                'slug' => $this->translations('ca-tra', 'pangasius', 'basha-yu'),
                'description' => $this->translations(
                    'Sản phẩm cá tra chất lượng cao từ vùng nuôi đạt chuẩn.',
                    'High-quality pangasius from certified farming areas.',
                    '来自认证养殖区的优质巴沙鱼产品。'
                ),
                'seo_title' => $this->translations('Sản phẩm cá tra', 'Pangasius products', '巴沙鱼产品'),
                'meta_description' => null,
                'translation_status' => $this->publishedStatus(),
                'locale_published_at' => $this->publishedDates(),
                'sort_order' => 0,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'deleted_at' => null,
            ]),
            'value_added' => $this->upsertId('product_categories', ['code' => 'VALUE_ADDED'], [
                'parent_id' => null,
                'featured_media_id' => $valueAddedMediaId,
                'name' => $this->translations('Sản phẩm giá trị gia tăng', 'Value-added products', '增值产品'),
                'slug' => $this->translations('san-pham-gia-tri-gia-tang', 'value-added-products', 'zengzhi-chanpin'),
                'description' => $this->translations(
                    'Các sản phẩm tiện lợi được chế biến từ cá tra.',
                    'Convenient products made from pangasius.',
                    '以巴沙鱼加工而成的便捷产品。'
                ),
                'seo_title' => null,
                'meta_description' => null,
                'translation_status' => $this->publishedStatus(),
                'locale_published_at' => $this->publishedDates(),
                'sort_order' => 1,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'deleted_at' => null,
            ]),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function seedAttributes(): array
    {
        return [
            'size' => $this->upsertId('attributes', ['code' => 'SIZE'], [
                'name' => $this->translations('Kích cỡ', 'Size', '规格'),
                'type' => 'select',
                'unit' => $this->translations('g/miếng', 'g/piece', '克/片'),
                'options' => $this->json(['120-170', '170-220', '220-up']),
                'sort_order' => 0,
                'is_active' => true,
            ]),
            'packing' => $this->upsertId('attributes', ['code' => 'PACKING'], [
                'name' => $this->translations('Quy cách đóng gói', 'Packing', '包装方式'),
                'type' => 'select',
                'unit' => null,
                'options' => $this->json(['IQF', 'Block', 'Vacuum']),
                'sort_order' => 1,
                'is_active' => true,
            ]),
            'glazing' => $this->upsertId('attributes', ['code' => 'GLAZING'], [
                'name' => $this->translations('Tỷ lệ mạ băng', 'Glazing', '包冰率'),
                'type' => 'number',
                'unit' => $this->translations('%', '%', '%'),
                'options' => null,
                'sort_order' => 2,
                'is_active' => true,
            ]),
        ];
    }

    /**
     * @param  array<string, int>  $categoryIds
     * @return array<string, int>
     */
    private function seedProducts(
        int $adminId,
        array $categoryIds,
        int $pangasiusMediaId,
        int $valueAddedMediaId
    ): array {
        return [
            'fillet' => $this->upsertId('products', ['sku' => 'IDI-PAN-001'], [
                'product_category_id' => $categoryIds['pangasius'],
                'featured_media_id' => $pangasiusMediaId,
                'scientific_name' => 'Pangasianodon hypophthalmus',
                'title' => $this->translations('Cá tra phi lê đông lạnh', 'Frozen pangasius fillet', '冷冻巴沙鱼柳'),
                'slug' => $this->translations('ca-tra-phi-le-dong-lanh', 'frozen-pangasius-fillet', 'lengdong-basha-yu-liu'),
                'short_description' => $this->translations(
                    'Phi lê trắng, vị nhẹ, phù hợp nhiều thị trường.',
                    'White, mild fillets suitable for global markets.',
                    '肉质洁白、口味温和，适合全球市场。'
                ),
                'description' => $this->translations(
                    'Được chế biến tại nhà máy đạt chuẩn quốc tế và cấp đông nhanh IQF.',
                    'Processed in internationally certified facilities and individually quick frozen.',
                    '在国际认证工厂加工并采用单体速冻技术。'
                ),
                'content' => $this->translations(
                    '<p>Sản phẩm được kiểm soát xuyên suốt từ vùng nuôi đến thành phẩm.</p>',
                    '<p>Controlled throughout the supply chain from farm to finished product.</p>',
                    '<p>从养殖到成品全供应链质量控制。</p>'
                ),
                'seo_title' => $this->translations('Cá tra phi lê đông lạnh IDI', 'IDI frozen pangasius fillet', 'IDI 冷冻巴沙鱼柳'),
                'meta_description' => null,
                'og_title' => null,
                'og_description' => null,
                'schema_extra' => null,
                'translation_status' => $this->publishedStatus(),
                'locale_published_at' => $this->publishedDates(),
                'sort_order' => 0,
                'is_featured' => true,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'deleted_at' => null,
            ]),
            'breaded' => $this->upsertId('products', ['sku' => 'IDI-VAP-001'], [
                'product_category_id' => $categoryIds['value_added'],
                'featured_media_id' => $valueAddedMediaId,
                'scientific_name' => 'Pangasianodon hypophthalmus',
                'title' => $this->translations('Cá tra tẩm bột', 'Breaded pangasius', '裹粉巴沙鱼'),
                'slug' => $this->translations('ca-tra-tam-bot', 'breaded-pangasius', 'guofen-basha-yu'),
                'short_description' => $this->translations(
                    'Sản phẩm tiện lợi, giòn ngon và dễ chế biến.',
                    'A convenient, crispy, easy-to-prepare product.',
                    '方便、酥脆且易于烹饪。'
                ),
                'description' => null,
                'content' => null,
                'seo_title' => null,
                'meta_description' => null,
                'og_title' => null,
                'og_description' => null,
                'schema_extra' => null,
                'translation_status' => $this->publishedStatus(),
                'locale_published_at' => $this->publishedDates(),
                'sort_order' => 1,
                'is_featured' => true,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'deleted_at' => null,
            ]),
        ];
    }

    /**
     * @param  array<string, int>  $products
     * @param  array<string, int>  $attributes
     */
    private function seedProductAttributes(array $products, array $attributes): void
    {
        foreach ([
            [$products['fillet'], $attributes['size'], $this->json(['120-170']), null, 0],
            [$products['fillet'], $attributes['packing'], $this->json(['IQF']), null, 1],
            [$products['fillet'], $attributes['glazing'], null, 20, 2],
            [$products['breaded'], $attributes['packing'], $this->json(['IQF']), null, 0],
        ] as [$productId, $attributeId, $value, $numericValue, $sortOrder]) {
            $this->upsertId('product_attributes', [
                'product_id' => $productId,
                'attribute_id' => $attributeId,
                'sort_order' => $sortOrder,
            ], [
                'value' => $value,
                'numeric_value' => $numericValue,
                'boolean_value' => null,
            ]);
        }
    }

    private function seedProductDocuments(int $productId, int $mediaId): void
    {
        $this->upsertId('product_documents', [
            'product_id' => $productId,
            'media_id' => $mediaId,
            'locale' => null,
        ], [
            'title' => $this->translations('Danh mục sản phẩm IDI', 'IDI product catalog', 'IDI 产品目录'),
            'document_type' => 'catalog',
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, int>  $productIds
     */
    private function seedProductStatistics(array $productIds): void
    {
        foreach (array_values($productIds) as $offset => $productId) {
            foreach (['vi', 'en', 'zh'] as $localeOffset => $locale) {
                for ($day = 0; $day < 7; $day++) {
                    $date = now()->subDays($day)->toDateString();
                    DB::table('product_view_statistics')->updateOrInsert(
                        [
                            'product_id' => $productId,
                            'locale' => $locale,
                            'view_date' => $date,
                        ],
                        [
                            'view_count' => 25 + ($offset * 11) + ($localeOffset * 7) + ($day * 3),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    private function mediaId(string $fileName): int
    {
        return (int) DB::table('media')->where('file_name', $fileName)->value('id');
    }

    private function publishedStatus(): string
    {
        return $this->json(['vi' => 'published', 'en' => 'published', 'zh' => 'published']);
    }

    private function publishedDates(): string
    {
        $date = now()->subDays(30)->toIso8601String();

        return $this->json(['vi' => $date, 'en' => $date, 'zh' => $date]);
    }
}
