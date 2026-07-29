<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\InteractsWithSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessSeeder extends Seeder
{
    use InteractsWithSeedData;

    public function run(): void
    {
        $adminId = (int) DB::table('users')->where('email', 'admin@idiseafood.local')->value('id');

        $recipeId = $this->seedRecipe($adminId);
        $this->seedInvestorRelations($adminId);
        $this->seedRecruitment($adminId);
        $this->seedOfficeLocations();

        $productId = (int) DB::table('products')->where('sku', 'IDI-PAN-001')->value('id');
        DB::table('product_recipe')->updateOrInsert([
            'product_id' => $productId,
            'recipe_id' => $recipeId,
        ]);
    }

    private function seedRecipe(int $adminId): int
    {
        $mediaId = (int) DB::table('media')->where('file_name', 'grilled-pangasius.jpg')->value('id');

        $recipeId = $this->upsertId('recipes', ['code' => 'RECIPE_GRILLED_PANGASIUS'], [
            'featured_media_id' => $mediaId,
            'title' => $this->translations('Cá tra nướng sả', 'Lemongrass grilled pangasius', '香茅烤巴沙鱼'),
            'slug' => $this->translations('ca-tra-nuong-sa', 'lemongrass-grilled-pangasius', 'xiangmao-kao-basha-yu'),
            'summary' => $this->translations(
                'Món ăn thơm ngon, dễ thực hiện cho bữa cơm gia đình.',
                'A flavorful, easy dish for family meals.',
                '一道美味且易做的家庭菜肴。'
            ),
            'content' => null,
            'servings' => '4',
            'preparation_time' => 20,
            'cooking_time' => 25,
            'difficulty' => 'easy',
            'seo_title' => null,
            'meta_description' => null,
            'translation_status' => $this->publishedStatus(),
            'locale_published_at' => $this->publishedDates(),
            'sort_order' => 0,
            'is_featured' => true,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);

        foreach ([
            ['name' => ['Cá tra phi lê', 'Pangasius fillet', '巴沙鱼柳'], 'quantity' => '600', 'unit' => ['g', 'g', '克'], 'sort' => 0],
            ['name' => ['Sả băm', 'Minced lemongrass', '香茅末'], 'quantity' => '3', 'unit' => ['muỗng canh', 'tbsp', '汤匙'], 'sort' => 1],
            ['name' => ['Nước mắm', 'Fish sauce', '鱼露'], 'quantity' => '2', 'unit' => ['muỗng canh', 'tbsp', '汤匙'], 'sort' => 2],
        ] as $ingredient) {
            $this->upsertId('recipe_ingredients', [
                'recipe_id' => $recipeId,
                'sort_order' => $ingredient['sort'],
            ], [
                'name' => $this->translations(...$ingredient['name']),
                'quantity' => $ingredient['quantity'],
                'unit' => $this->translations(...$ingredient['unit']),
                'note' => null,
            ]);
        }

        foreach ([
            ['Ướp cá với sả và gia vị trong 15 phút.', 'Marinate the fish with lemongrass and seasoning for 15 minutes.', '用香茅和调味料腌鱼 15 分钟。'],
            ['Nướng cá ở 200°C đến khi vàng đều.', 'Grill at 200°C until evenly golden.', '以 200°C 烤至金黄。'],
            ['Dùng nóng cùng rau và cơm.', 'Serve hot with vegetables and rice.', '搭配蔬菜和米饭趁热享用。'],
        ] as $sortOrder => $instruction) {
            $this->upsertId('recipe_steps', [
                'recipe_id' => $recipeId,
                'sort_order' => $sortOrder,
            ], [
                'media_id' => $sortOrder === 1 ? $mediaId : null,
                'instruction' => $this->translations(...$instruction),
            ]);
        }

        return $recipeId;
    }

    private function seedInvestorRelations(int $adminId): void
    {
        $categoryId = $this->upsertJsonId(
            'document_categories',
            'slug',
            'vi',
            'bao-cao-thuong-nien',
            [
                'parent_id' => null,
                'name' => $this->translations('Báo cáo thường niên', 'Annual reports', '年度报告'),
                'slug' => $this->translations('bao-cao-thuong-nien', 'annual-reports', 'niandu-baogao'),
                'description' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'deleted_at' => null,
            ]);

        $documentId = $this->upsertId('investor_documents', [
            'document_number' => 'AR-2025',
        ], [
            'document_category_id' => $categoryId,
            'title' => $this->translations('Báo cáo thường niên 2025', 'Annual report 2025', '2025 年年度报告'),
            'summary' => $this->translations(
                'Tổng kết hoạt động kinh doanh và định hướng phát triển.',
                'Business performance review and development outlook.',
                '经营业绩回顾与发展展望。'
            ),
            'year' => 2025,
            'quarter' => null,
            'published_on' => '2026-03-31',
            'sort_order' => 0,
            'is_featured' => true,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);

        $mediaId = (int) DB::table('media')->where('file_name', 'annual-report-2025.pdf')->value('id');
        $this->upsertId('investor_document_files', [
            'investor_document_id' => $documentId,
            'media_id' => $mediaId,
            'locale' => 'vi',
        ], [
            'display_name' => $this->translations(
                'Báo cáo thường niên 2025 - Tiếng Việt',
                'Annual report 2025 - Vietnamese',
                '2025 年年度报告 - 越南语'
            ),
            'sort_order' => 0,
        ]);
    }

    private function seedRecruitment(int $adminId): void
    {
        $this->upsertId('job_positions', ['code' => 'SALES_EXPORT_01'], [
            'department' => 'International Sales',
            'title' => $this->translations('Chuyên viên kinh doanh xuất khẩu', 'Export sales executive', '出口销售专员'),
            'slug' => $this->translations('chuyen-vien-kinh-doanh-xuat-khau', 'export-sales-executive', 'chukou-xiaoshou-zhuanyuan'),
            'location' => $this->translations('Đồng Tháp, Việt Nam', 'Dong Thap, Vietnam', '越南同塔省'),
            'summary' => $this->translations(
                'Phát triển khách hàng và thị trường xuất khẩu.',
                'Develop export customers and markets.',
                '开发出口客户与市场。'
            ),
            'description' => $this->translations(
                '<p>Quản lý khách hàng, báo giá và phối hợp thực hiện đơn hàng.</p>',
                '<p>Manage customers, quotations, and order execution.</p>',
                '<p>管理客户、报价并协调订单执行。</p>'
            ),
            'requirements' => $this->translations(
                '<ul><li>Tiếng Anh giao tiếp tốt</li><li>Kinh nghiệm xuất khẩu là lợi thế</li></ul>',
                '<ul><li>Good English communication</li><li>Export experience is preferred</li></ul>',
                '<ul><li>良好的英语沟通能力</li><li>有出口经验者优先</li></ul>'
            ),
            'benefits' => $this->translations(
                'Thu nhập cạnh tranh và đầy đủ chế độ.',
                'Competitive compensation and benefits.',
                '有竞争力的薪酬与福利。'
            ),
            'quantity' => 2,
            'expires_at' => now()->addMonths(2),
            'translation_status' => $this->publishedStatus(),
            'locale_published_at' => $this->publishedDates(),
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);
    }

    private function seedOfficeLocations(): void
    {
        foreach ([
            [
                'code' => 'HEAD_OFFICE',
                'name' => ['Văn phòng IDI Seafood', 'IDI Seafood office', 'IDI Seafood 办公室'],
                'address' => ['Quốc lộ 80, huyện Lấp Vò, Đồng Tháp', 'National Highway 80, Lap Vo, Dong Thap', '同塔省立武县 80 号国道'],
                'phone' => '+84 277 376 8899',
                'email' => 'info@idiseafood.com',
                'lat' => 10.3460000,
                'lng' => 105.5350000,
            ],
            [
                'code' => 'FACTORY_01',
                'name' => ['Nhà máy chế biến', 'Processing factory', '加工厂'],
                'address' => ['Khu công nghiệp Vàm Cống, Đồng Tháp', 'Vam Cong Industrial Park, Dong Thap', '同塔省 Vam Cong 工业园'],
                'phone' => '+84 277 376 8800',
                'email' => 'factory@idiseafood.com',
                'lat' => 10.3600000,
                'lng' => 105.5200000,
            ],
        ] as $sortOrder => $office) {
            $this->upsertId('office_locations', ['code' => $office['code']], [
                'name' => $this->translations(...$office['name']),
                'address' => $this->translations(...$office['address']),
                'phone' => $office['phone'],
                'email' => $office['email'],
                'map_embed' => null,
                'latitude' => $office['lat'],
                'longitude' => $office['lng'],
                'sort_order' => $sortOrder,
                'is_active' => true,
                'deleted_at' => null,
            ]);
        }
    }

    private function publishedStatus(): string
    {
        return $this->json(['vi' => 'published', 'en' => 'published', 'zh' => 'published']);
    }

    private function publishedDates(): string
    {
        $date = now()->subDays(7)->toIso8601String();

        return $this->json(['vi' => $date, 'en' => $date, 'zh' => $date]);
    }
}
