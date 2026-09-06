<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sourceIds = [
            'IDI-PAN-001' => 15,
            'IDI-FIL-002' => 1,
            'IDI-FIL-003' => 10,
            'IDI-FIL-004' => 9,
            'IDI-FIL-005' => 14,
            'IDI-FIL-006' => 12,
            'IDI-POR-001' => 6,
            'IDI-POR-002' => 13,
            'IDI-POR-003' => 7,
            'IDI-WHO-001' => 5,
            'IDI-WHO-002' => 2,
            'IDI-VAP-001' => 11,
            'IDI-VAP-002' => 4,
            'IDI-VAP-003' => 3,
        ];

        $presentationAttributeId = DB::table('attributes')->where('code', 'PACKING')->value('id');
        $products = DB::table('products')->whereIn('sku', array_keys($sourceIds))->get(['id', 'sku', 'schema_extra']);

        foreach ($products as $product) {
            $details = $this->legacyDetails($sourceIds[$product->sku]);
            $existing = json_decode((string) $product->schema_extra, true);
            $schemaExtra = array_replace(is_array($existing) ? $existing : [], [
                'product_specification' => $details['product_specification'],
                'packaging' => $details['packaging'],
                'nutrition' => $details['nutrition'],
            ]);

            DB::table('products')->where('id', $product->id)->update([
                'schema_extra' => json_encode($schemaExtra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);

            if ($presentationAttributeId) {
                DB::table('product_attributes')->updateOrInsert(
                    ['product_id' => $product->id, 'attribute_id' => $presentationAttributeId],
                    [
                        'value' => json_encode($details['presentation'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'numeric_value' => null,
                        'boolean_value' => null,
                        'sort_order' => 1,
                        'updated_at' => now(),
                    ],
                );
            }
        }

        if ($presentationAttributeId) {
            DB::table('attributes')->where('id', $presentationAttributeId)->update([
                'name' => json_encode(['vi' => 'Hình thức cấp đông', 'en' => 'Presentation', 'zh' => 'Presentation'], JSON_UNESCAPED_UNICODE),
                'options' => json_encode([
                    'Individually Quick Frozen',
                    'Block Frozen',
                    'Đông lạnh nhanh riêng lẻ',
                    'Đông lạnh khối',
                ], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: legacy fields and migrated product details are retained.
    }

    /** @return array{product_specification: array<string, string>, presentation: array<string, array<int, string>>, packaging: array<string, string>, nutrition: array<string, string>} */
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
};
