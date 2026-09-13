<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\InteractsWithSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    use InteractsWithSeedData;

    private const SIZES = ['60g-120g', '120g-170g', '170g-220g', '220g-up'];

    public function run(): void
    {
        $adminId = (int) DB::table('users')->where('email', 'admin@idiseafood.local')->value('id');
        $mediaIds = $this->seedReferenceMedia($adminId);
        $categoryIds = $this->seedCategories($adminId, $mediaIds);
        $attributeIds = $this->seedAttributes();
        $productIds = $this->seedProducts($adminId, $categoryIds, $mediaIds);

        $this->seedProductAttributes($productIds, $attributeIds);
        $this->seedProductDocuments($productIds['fillet_clean'], $this->mediaId('product-catalog.pdf'));
        $this->seedProductStatistics($productIds);
    }

    /**
     * Names, groups, order, images and sizes mirror the public IDI catalog:
     * https://idiseafood.com/vn/san-pham.html.
     *
     * @return array<string, array<string, mixed>>
     */
    private function products(): array
    {
        return [
            'fillet_clean' => $this->product('IDI-PAN-001', 'fillet', 'Cá Fillet, Tạo Hình Sạch', 'Pangasius Fillet, Well Trimmed', 'ca-fillet-tao-hinh-sach', 'dm2.jpg', 15, 14, true),
            'fillet_skin_on_deep_trimmed' => $this->product('IDI-FIL-002', 'fillet', 'Cá Fillet, Còn Da, Vanh Dè Sát', 'Pangasius Fillet, Skin-on, Deep Trimmed', 'ca-fillet-con-da-vanh-de-sat', 'VDS_con_da_min_1.jpg', 1, 13),
            'fillet_skinless_belly_flap' => $this->product('IDI-FIL-003', 'fillet', 'Cá Fillet, Bỏ Da, Còn Dè', 'Pangasius Fillet, Skinless, Belly Flap-on', 'ca-fillet-bo-da-con-de', 'Bo_da_de_EU_min.jpg', 10, 12),
            'fillet_red_meat' => $this->product('IDI-FIL-004', 'fillet', 'Cá Fillet, Bỏ Da, Còn Thịt Đỏ, Vanh Dè Sát', 'Pangasius Fillet, Skinless, Red Meat-on, Deep Trimmed', 'ca-fillet-bo-da-con-thit-do-vanh-de-sat', 'VDS_con_thit_do_min.jpg', 9, 11),
            'fillet_co' => $this->product('IDI-FIL-005', 'fillet', 'Cá Fillet, Tạo Hình Sạch, Xông CO', 'Pangasius Fillet, Well Trimmed, CO Treated', 'ca-fillet-tao-hinh-sach-xong-co', 'Xong_CO_min.jpg', 14, 10),
            'fillet_skin_on_belly_flap' => $this->product('IDI-FIL-006', 'fillet', 'Cá Fillet, Còn Da, Còn Dè', 'Pangasius Fillet, Skin-on, Belly Flap-on', 'ca-fillet-con-da-con-de', 'Con_da_con_de_min.jpg', 12, 8),
            'portion_skin_on' => $this->product('IDI-POR-001', 'portions', 'Cá Cắt Khúc Từ Cá Fillet, Còn Da, Còn Dè', 'Portions from Pangasius Fillet, Skin-on, Belly Flap-on', 'ca-cat-khuc-tu-ca-fillet-con-da-con-de', 'dm3.jpg', 6, 9, true),
            'portion_whole_fish' => $this->product('IDI-POR-002', 'portions', 'Cá Cắt Khúc Từ Cá Nguyên Con Cắt Đầu Bằng', 'Portions from Whole Pangasius, Straight Head Cut', 'ca-cat-khuc-tu-ca-nguyen-con-cat-dau-bang', 'Cat_khuc_min.jpg', 13, 6),
            'portion_clean' => $this->product('IDI-POR-003', 'portions', 'Cá Cắt Khúc Từ Cá Fillet, Tạo Hình Sạch', 'Portions from Well-trimmed Pangasius Fillet', 'ca-cat-khuc-tu-ca-fillet-tao-hinh-sach', 'Cat_mieng_vuong_min.jpg', 7, 4),
            'whole_straight_head' => $this->product('IDI-WHO-001', 'whole', 'Cá Nguyên Con Cắt Đầu Bằng', 'Whole Pangasius, Straight Head Cut', 'ca-nguyen-con-cat-dau-bang', 'dm4.jpg', 5, 7, true),
            'whole_butterfly' => $this->product('IDI-WHO-002', 'whole', 'Cá Nguyên Con Xẻ Bướm Lưng', 'Whole Pangasius, Back Butterfly Cut', 'ca-nguyen-con-xe-buom-lung', 'Nguyen_con_xe_buom_min.jpg', 2, 3),
            'rose_roll' => $this->product('IDI-VAP-001', 'value_added', 'Cá Fillet, Tạo Hình Sạch, Cuộn Hoa Hồng', 'Well-trimmed Pangasius Fillet, Rose Roll', 'ca-fillet-tao-hinh-sach-cuon-hoa-hong', 'Hoa_hong_min.jpg', 11, 5, true),
            'skewered' => $this->product('IDI-VAP-002', 'value_added', 'Cá Cắt Khúc Từ Cá Fillet, Tạo Hình Sạch, Xiên Que', 'Well-trimmed Pangasius Fillet Portions, Skewered', 'ca-cat-khuc-tu-ca-fillet-tao-hinh-sach-xien-que', 'dm5.jpg', 4, 2),
            'belly' => $this->product('IDI-VAP-003', 'value_added', 'Ức Cá Tra', 'Pangasius Belly', 'uc-ca-tra', 'dm6.jpg', 3, 1),
        ];
    }

    /** @return array<string, mixed> */
    private function product(string $sku, string $category, string $viName, string $enName, string $slug, string $fileName, int $sourceId, int $sortOrder, bool $featured = false): array
    {
        return compact('sku', 'category', 'viName', 'enName', 'slug', 'fileName', 'sourceId', 'sortOrder', 'featured');
    }

    /** @return array<string, int> */
    private function seedReferenceMedia(int $adminId): array
    {
        $folderId = (int) DB::table('media_folders')->where('path', '/products')->value('id');
        $ids = [];

        foreach ($this->products() as $key => $product) {
            $ids[$key] = $this->upsertId('media', [
                'disk' => 'public_assets', 'directory' => 'media/products', 'file_name' => $product['fileName'],
            ], [
                'folder_id' => $folderId,
                'external_url' => null,
                'original_name' => $product['fileName'],
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'file_size' => null, 'width' => null, 'height' => null, 'checksum' => null,
                'title' => $this->translations($product['viName'], $product['enName'], $product['enName']),
                'alt_text' => $this->translations($product['viName'], $product['enName'], $product['enName']),
                'caption' => null, 'created_by' => $adminId, 'deleted_at' => null,
            ]);
        }

        return $ids;
    }

    /** @return array<string, int> */
    private function seedCategories(int $adminId, array $mediaIds): array
    {
        $definitions = [
            'fillet' => ['code' => 'PANGASIUS', 'media' => 'fillet_clean', 'names' => ['Cá Fillet', 'Pangasius Fillet', 'Pangasius Fillet'], 'slug' => 'pangasius-fillet', 'description' => 'Dòng sản phẩm chủ lực với nhiều tiêu chuẩn tạo hình cho thị trường xuất khẩu.'],
            'portions' => ['code' => 'PANGASIUS_PORTIONS', 'media' => 'portion_skin_on', 'names' => ['Cá cắt khúc', 'Pangasius Portions', 'Pangasius Portions'], 'slug' => 'pangasius-portions', 'description' => 'Các quy cách cắt khúc tiện dụng cho bán lẻ, food service và chế biến sâu.'],
            'whole' => ['code' => 'WHOLE_FISH', 'media' => 'whole_straight_head', 'names' => ['Cá Nguyên Con', 'Whole Fish', 'Whole Fish'], 'slug' => 'whole-fish', 'description' => 'Sản phẩm cá nguyên con được sơ chế, cấp đông và đóng gói theo chuẩn quốc tế.'],
            'value_added' => ['code' => 'VALUE_ADDED', 'media' => 'rose_roll', 'names' => ['Các sản phẩm khác', 'Other Products', 'Other Products'], 'slug' => 'value-added', 'description' => 'Danh mục giá trị gia tăng, đáp ứng nhu cầu trình bày và chế biến đa dạng.'],
        ];

        $ids = [];
        foreach ($definitions as $key => $definition) {
            $sortOrder = count($ids);
            $description = $definition['description'];
            $ids[$key] = $this->upsertId('product_categories', ['code' => $definition['code']], [
                'parent_id' => null,
                'featured_media_id' => $mediaIds[$definition['media']],
                'name' => $this->translations(...$definition['names']),
                'slug' => $this->translations($definition['slug'], $definition['slug'], $definition['slug']),
                'description' => $this->translations($description, $description, $description),
                'seo_title' => $this->translations(...$definition['names']),
                'meta_description' => $this->translations($description, $description, $description),
                'translation_status' => $this->publishedStatus(),
                'locale_published_at' => $this->publishedDates(),
                'sort_order' => $sortOrder,
                'is_active' => true, 'created_by' => $adminId, 'updated_by' => $adminId, 'deleted_at' => null,
            ]);
        }

        return $ids;
    }

    /** @return array<string, int> */
    private function seedAttributes(): array
    {
        return [
            'size' => $this->upsertId('attributes', ['code' => 'SIZE'], [
                'name' => $this->translations('Kích cỡ', 'Size', 'Size'), 'type' => 'multiselect',
                'unit' => null, 'options' => $this->json(self::SIZES), 'sort_order' => 0, 'is_active' => true,
            ]),
            'packing' => $this->upsertId('attributes', ['code' => 'PACKING'], [
                'name' => $this->translations('Hình thức cấp đông', 'Presentation', 'Presentation'), 'type' => 'multiselect',
                'unit' => null,
                'options' => $this->json(['Individually Quick Frozen', 'Block Frozen', 'Đông lạnh nhanh riêng lẻ', 'Đông lạnh khối']),
                'sort_order' => 1, 'is_active' => true,
            ]),
            'glazing' => $this->upsertId('attributes', ['code' => 'GLAZING'], [
                'name' => $this->translations('Tỷ lệ mạ băng', 'Glazing', 'Glazing'), 'type' => 'number',
                'unit' => $this->translations('%', '%', '%'), 'options' => null, 'sort_order' => 2, 'is_active' => true,
            ]),
        ];
    }

    /** @return array<string, int> */
    private function seedProducts(int $adminId, array $categoryIds, array $mediaIds): array
    {
        $ids = [];
        foreach ($this->products() as $key => $product) {
            $sourceUrl = "https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id={$product['sourceId']}";
            $details = $this->legacyDetails($product['sourceId']);
            $ids[$key] = $this->upsertId('products', ['sku' => $product['sku']], [
                'product_category_id' => $categoryIds[$product['category']],
                'featured_media_id' => $mediaIds[$key],
                'title' => $this->translations($product['viName'], $product['enName'], $product['enName']),
                'slug' => $this->translations($product['slug'], $product['slug'], $product['slug']),
                'short_description' => $this->translations(...array_values($details['product_specification'])),
                'description' => null, 'content' => null,
                'seo_title' => $this->translations($product['viName'].' | IDI Seafood', $product['enName'].' | IDI Seafood', $product['enName'].' | IDI Seafood'),
                'meta_description' => $this->translations(...array_values($details['product_specification'])),
                'og_title' => null, 'og_description' => null,
                'schema_extra' => $this->json(array_merge($details, [
                    'source_url' => $sourceUrl, 'source_product_id' => $product['sourceId'],
                ])),
                'translation_status' => $this->publishedStatus(), 'locale_published_at' => $this->publishedDates(),
                'sort_order' => $product['sortOrder'], 'is_featured' => $product['featured'], 'is_active' => true,
                'created_by' => $adminId, 'updated_by' => $adminId, 'deleted_at' => null,
            ]);
        }

        return $ids;
    }

    private function seedProductAttributes(array $productIds, array $attributeIds): void
    {
        $products = $this->products();

        foreach ($productIds as $key => $productId) {
            $presentation = $this->legacyDetails($products[$key]['sourceId'])['presentation'];
            foreach ([[$attributeIds['size'], self::SIZES, 0], [$attributeIds['packing'], $presentation, 1]] as [$attributeId, $value, $sortOrder]) {
                $this->upsertId('product_attributes', [
                    'product_id' => $productId, 'attribute_id' => $attributeId,
                ], [
                    'value' => $this->json($value), 'numeric_value' => null,
                    'boolean_value' => null, 'sort_order' => $sortOrder,
                ]);
            }
        }
    }

    /**
     * Product details transcribed from each legacy popup linked by source_product_id.
     * The older catalog contains Vietnamese copy for a subset of products and English copy for the rest.
     *
     * @return array{product_specification: array<string, string>, presentation: array<string, array<int, string>>, packaging: array<string, string>, nutrition: array<string, string>}
     */
    private function legacyDetails(int $_sourceId): array
    {
        return [
            'product_specification' => [
                'vi' => 'Phi lê có da, không xương, tách da, tách mỡ',
                'en' => 'Skin-on, Boneless, Belly-off, Fat-off Fillets',
                'zh' => '带皮、去骨、去腹肉、去脂鱼片',
            ],
            'presentation' => [
                'vi' => ['Đông lạnh nhanh riêng lẻ', 'Đông lạnh khối'],
                'en' => ['Individually Quick Frozen', 'Block Frozen'],
                'zh' => ['单体速冻', '块冻'],
            ],
            'packaging' => [
                'vi' => 'Bán sỉ đóng gói trong túi PE trơn, hoặc bán lẻ đóng gói trong túi in, gói hút chân không, v.v.',
                'en' => 'Wholesales packaging in plain PE bags, or Retail packaging in printed bags, vacuum pack, etc.',
                'zh' => '批发采用普通 PE 袋包装，零售采用印刷袋、真空包装等。',
            ],
            'nutrition' => [
                'calories' => '82.7 Kcal',
                'protein' => '15.50g',
                'fat' => '1.86g',
                'saturated_fat' => '0.66g',
            ],
        ];
    }

    private function seedProductDocuments(int $productId, int $mediaId): void
    {
        $this->upsertId('product_documents', ['product_id' => $productId, 'media_id' => $mediaId, 'locale' => null], [
            'title' => $this->translations('Danh mục sản phẩm IDI', 'IDI product catalog', 'IDI product catalog'),
            'document_type' => 'catalog', 'sort_order' => 0, 'is_active' => true,
        ]);
    }

    private function seedProductStatistics(array $productIds): void
    {
        foreach (array_values($productIds) as $offset => $productId) {
            foreach (['vi', 'en', 'zh'] as $localeOffset => $locale) {
                for ($day = 0; $day < 7; $day++) {
                    DB::table('product_view_statistics')->updateOrInsert(
                        ['product_id' => $productId, 'locale' => $locale, 'view_date' => now()->subDays($day)->toDateString()],
                        ['view_count' => 25 + ($offset * 11) + ($localeOffset * 7) + ($day * 3), 'created_at' => now(), 'updated_at' => now()]
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
