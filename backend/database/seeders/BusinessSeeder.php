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

        $recipeId = $this->seedRecipes($adminId);
        $this->seedInvestorRelations($adminId);
        $positionIds = $this->seedRecruitment($adminId);
        $this->seedRecruitmentApplications($adminId, $positionIds);
        $this->seedOfficeLocations();

        $productId = (int) DB::table('products')->where('sku', 'IDI-PAN-001')->value('id');
        DB::table('product_recipe')->updateOrInsert([
            'product_id' => $productId,
            'recipe_id' => $recipeId,
        ]);
    }

    private function seedRecipes(int $adminId): int
    {
        $folderId = (int) DB::table('media_folders')->where('path', '/products')->value('id');
        $recipes = [
            [
                'code' => 'RECIPE_PANGASIUS_CURRY_COCONUT', 'featured' => true, 'sort' => 80,
                'image' => ['mon_an.png', 'https://www.idiseafood.com/vnt_upload/recipes/08_2026/mon_an.png'],
                'title' => ['Cà ri cá tra với dừa và sả', 'Pangasius fish curry with coconut and lemongrass', '椰香茅巴沙鱼咖喱'],
                'slug' => ['ca-ri-ca-tra-voi-dua-va-sa', 'pangasius-fish-curry-coconut-lemongrass', 'yezi-xiangmao-basha-yu-gali'],
                'summary' => [
                    'Món cà ri cá tra béo thơm với nước cốt dừa và sả, dùng cùng cơm jasmine, rau mùi, ớt đỏ và chanh.',
                    'A fragrant pangasius curry with coconut and lemongrass, served with jasmine rice, coriander, red chilli and lime.',
                    '香浓的椰奶香茅巴沙鱼咖喱，搭配茉莉香米、香菜、红辣椒和青柠。',
                ],
                'ingredients' => [
                    [['Cá tra phi lê I.D.I Corp đạt chứng nhận BAP', 'I.D.I Corp BAP-certified pangasius fillet', 'I.D.I Corp BAP 认证巴沙鱼柳'], '800', ['g', 'g', '克']],
                    [['Gạo thơm jasmine', 'Jasmine rice', '茉莉香米'], '300', ['g', 'g', '克']],
                    [['Rau mùi', 'Coriander', '香菜'], '1/2', ['bó', 'bunch', '把']],
                    [['Ớt đỏ', 'Red chilli', '红辣椒'], '1', ['quả', '', '个']],
                    [['Chanh', 'Lime', '青柠'], '1', ['quả', '', '个']],
                ],
                'steps' => [
                    ['Nấu sốt cà ri với nước cốt dừa và sả đến khi dậy mùi.', 'Cook the curry sauce with coconut milk and lemongrass until fragrant.', '用椰奶和香茅煮咖喱酱至香味四溢。'],
                    ['Cho cá tra cắt miếng lớn vào sốt và nấu vừa chín tới.', 'Add the large pangasius chunks and cook just until done.', '加入大块巴沙鱼，煮至刚熟。'],
                    ['Dùng nóng với cơm jasmine, rau mùi, ớt đỏ và chanh.', 'Serve hot with jasmine rice, coriander, red chilli and lime.', '搭配茉莉香米、香菜、红辣椒和青柠趁热享用。'],
                ],
            ],
            [
                'code' => 'RECIPE_PASSION_FRUIT_FILLET', 'featured' => false, 'sort' => 70,
                'image' => ['CATALOGUE_2020.png', 'https://www.idiseafood.com/vnt_upload/recipes/10_2024/CATALOGUE_2020.png'],
                'title' => ['Phi lê áp chảo với chanh dây', 'Pan Seared Fillet with Passion Fruit', '百香果煎鱼片'],
                'slug' => ['phi-le-ap-chao-voi-chanh-day', 'pan-seared-fillet-passion-fruit', 'baixiangguo-jian-yupian'],
                'summary' => ['Đun nóng 1 thìa cà phê dầu và xào hành tím cho đến khi vàng. Thêm rượu vang trắng và giảm còn một nửa. Thêm nước dùng cá và chanh dây, nêm muối. Giảm còn một nửa nữa, sau đó thêm mật ong và bơ.', 'Heat 1 tsp oil and sauté the shallots till golden. Add the white wine and reduce to half. Add fish stock and passion fruit, season with salt, reduce again, then add honey and butter.', '加热1茶匙油，将青葱炒至金黄色。加入白葡萄酒并收至一半，再加入鱼汤和百香果，以盐调味，最后加入蜂蜜和黄油。'],
            ],
            [
                'code' => 'RECIPE_CHINESE_SPICY_FISH', 'featured' => false, 'sort' => 60,
                'image' => ['z5820210877220_2b747df29a0a8e335636b84c68da6333.jpg', 'https://www.idiseafood.com/vnt_upload/recipes/10_2024/z5820210877220_2b747df29a0a8e335636b84c68da6333.jpg'],
                'title' => ['Cá cay kiểu Trung Hoa', 'Chinese-styled spicy fish', '中式麻辣鱼'],
                'slug' => ['ca-cay-kieu-trung-hoa', 'chinese-styled-spicy-fish', 'zhongshi-mala-yu'],
                'summary' => ['Đun nóng dầu trong chảo, xào tỏi, ớt, tỏi tây và gừng cho đến khi thơm.', 'Heat oil in the pan, stir fry garlic, chilli, leek and ginger till fragrant. Deep fry the fish portion and then put it into the pan.', '在煎锅中加热油，将大蒜、辣椒、韭菜和生姜炒香，再将鱼块炸好后放入锅中。'],
            ],
            [
                'code' => 'RECIPE_TEMPURA_PASSION_FRUIT', 'featured' => false, 'sort' => 50,
                'image' => ['z5820210889242_94f642a81fede0ba23b8007c1d111eb8.jpg', 'https://www.idiseafood.com/vnt_upload/recipes/10_2024/z5820210889242_94f642a81fede0ba23b8007c1d111eb8.jpg'],
                'title' => ['Cá Tempura sốt cam chanh dây', 'Tempura fish with orange and passion fruit sauce', '橙香百香果酱天妇罗鱼'],
                'slug' => ['ca-tempura-sot-cam-chanh-day', 'tempura-fish-orange-passion-fruit-sauce', 'chengxiang-baixiangguo-tianfuluo-yu'],
                'summary' => ['Nêm gia vị cho cá theo khẩu vị. Đảm bảo từng miếng phi lê được phủ hoàn toàn bằng bột tempura trước khi chiên giòn.', 'Add your preferred seasonings to the fish. Make sure each piece of fillet is fully covered by tempura flour before frying until crisp.', '按喜好给鱼调味，确保鱼片完全裹上天妇罗面糊后炸至酥脆。'],
            ],
            [
                'code' => 'RECIPE_FISH_SANDWICH', 'featured' => false, 'sort' => 40,
                'image' => ['z5820210882109_c463c04d65301d28e7518e5e51761803.jpg', 'https://www.idiseafood.com/vnt_upload/recipes/10_2024/z5820210882109_c463c04d65301d28e7518e5e51761803.jpg'],
                'title' => ['Sandwich cá', 'Fish sandwich', '鱼三明治'],
                'slug' => ['sandwich-ca', 'fish-sandwich', 'yu-sanmingzhi'],
                'summary' => ['Cắt phi lê tẩm bột chiên thành từng miếng, nướng hai lát bánh sandwich và thái các loại rau.', 'Cut fried breaded fillet into pieces, toast 2 slices of sandwich bread and slice all the vegetables.', '将炸好的面包屑鱼片切块，烤两片吐司并把蔬菜切片。'],
            ],
            [
                'code' => 'RECIPE_PANGASIUS_FILLET_STEAK', 'featured' => false, 'sort' => 30,
                'image' => ['z5820211493047_a352e685c6713e8f10815070b8bdfabe.jpg', 'https://www.idiseafood.com/vnt_upload/recipes/10_2024/z5820211493047_a352e685c6713e8f10815070b8bdfabe.jpg'],
                'title' => ['Bít tết phi lê cá tra', 'Pangasius fillet steak', '巴沙鱼柳排'],
                'slug' => ['bit-tet-phi-le-ca-tra', 'pangasius-fillet-steak', 'basha-yu-liupai'],
                'summary' => ['Rắc một nhúm muối, hương thảo khô và hạt tiêu lên phi lê cá sống.', 'Sprinkle a pinch of salt, dried rosemary and pepper onto the raw fish fillet.', '在生鱼片上撒少许盐、干迷迭香和胡椒粉。'],
            ],
            [
                'code' => 'RECIPE_FRIED_BREADED_FILLET', 'featured' => false, 'sort' => 20,
                'image' => ['z5820210887364_5091a4dd979800ffa9bf36571b71889b.jpg', 'https://www.idiseafood.com/vnt_upload/recipes/10_2024/z5820210887364_5091a4dd979800ffa9bf36571b71889b.jpg'],
                'title' => ['Phi lê tẩm bột chiên', 'Fried breaded fillet', '油炸面包屑鱼片'],
                'slug' => ['phi-le-tam-bot-chien', 'fried-breaded-fillet', 'youzha-mianbaoxie-yupian'],
                'summary' => ['Phi lê cá sau khi rửa sạch sẽ để ráo nước trước khi chế biến. Cho muối và tiêu vào phi lê và để một lúc.', 'Drain and dry the thoroughly washed fish fillet before cooking. Add salt and pepper and leave it for a while.', '鱼片洗净后沥干，烹饪前加入盐和胡椒粉腌制片刻。'],
            ],
            [
                'code' => 'RECIPE_FISH_CURRY', 'featured' => false, 'sort' => 10,
                'image' => ['CATALOGUE_2020_1.png', 'https://www.idiseafood.com/vnt_upload/recipes/10_2024/CATALOGUE_2020_1.png'],
                'title' => ['Cà ri cá', 'Fish Curry', '咖喱鱼'],
                'slug' => ['ca-ri-ca', 'fish-curry', 'gali-yu'],
                'summary' => ['Ướp cá với gừng tỏi băm, nghệ, bột ớt đỏ và muối; sau đó áp chảo cho đến khi vàng và để riêng.', 'Marinate fish with minced ginger and garlic, turmeric, red chilli powder and salt; then sear till golden and set aside.', '用姜蒜末、姜黄、红辣椒粉和盐腌鱼，然后煎至金黄备用。'],
                'steps' => [
                    ['Ướp cá với gừng tỏi băm, nghệ, bột ớt đỏ và muối; áp chảo đến khi vàng rồi để riêng.', 'Marinate fish with minced ginger and garlic, turmeric, red chilli powder and salt; sear till golden and set aside.', '用姜蒜末、姜黄、红辣椒粉和盐腌鱼，煎至金黄备用。'],
                    ['Xào hành, gừng tỏi và cà chua đến mềm; thêm dừa, hạt thì là và nghệ rồi xay nhuyễn.', 'Sauté onion, ginger-garlic paste and tomatoes until soft; add coconut, fennel seeds and turmeric, then blend.', '炒软洋葱、姜蒜和番茄，加入椰子、茴香籽和姜黄后搅打。'],
                    ['Đun sốt với lá nguyệt quế, ớt xanh và bột cà ri; thêm cá, nấu 3–4 phút rồi dùng với cơm.', 'Cook the sauce with bay leaf, green chilli and curry powder; add fish, cook for 3–4 minutes and serve with rice.', '加入月桂叶、青辣椒和咖喱粉煮酱，放入鱼再煮3至4分钟，配米饭食用。'],
                ],
            ],
        ];

        $firstRecipeId = 0;
        foreach ($recipes as $definition) {
            $mediaId = $this->upsertId('media', [
                'disk' => 'public', 'directory' => 'recipes/images', 'file_name' => $definition['image'][0],
            ], [
                'folder_id' => $folderId, 'original_name' => $definition['image'][0],
                'external_url' => $definition['image'][1], 'mime_type' => str_ends_with($definition['image'][0], '.png') ? 'image/png' : 'image/jpeg',
                'extension' => pathinfo($definition['image'][0], PATHINFO_EXTENSION), 'title' => $this->translations(...$definition['title']),
                'alt_text' => $this->translations(...$definition['title']), 'created_by' => $adminId, 'deleted_at' => null,
            ]);
            $summary = $definition['summary'];
            $contentLeft = $this->recipeContentLeft($definition['ingredients'] ?? []);
            $contentRight = $this->recipeContentRight($definition['steps'] ?? [$summary]);
            $recipeId = $this->upsertId('recipes', ['code' => $definition['code']], [
                'featured_media_id' => $mediaId, 'title' => $this->translations(...$definition['title']),
                'slug' => $this->translations(...$definition['slug']), 'summary' => $this->translations(...$summary),
                'content_left' => $this->translations(...$contentLeft), 'content_right' => $this->translations(...$contentRight),
                'seo_title' => $this->translations(...$definition['title']), 'meta_description' => $this->translations(...$summary),
                'translation_status' => $this->publishedStatus(), 'locale_published_at' => $this->publishedDates(),
                'sort_order' => $definition['sort'], 'is_featured' => $definition['featured'], 'is_active' => true,
                'created_by' => $adminId, 'updated_by' => $adminId, 'deleted_at' => null,
            ]);
            if ($firstRecipeId === 0) {
                $firstRecipeId = $recipeId;
            }

        }

        // Remove the original placeholder recipe now that the public recipe
        // library mirrors the current IDI Seafood source page.
        $legacyRecipeIds = DB::table('recipes')
            ->where('code', 'RECIPE_GRILLED_PANGASIUS')
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($legacyRecipeIds->isNotEmpty()) {
            DB::table('recipes')->whereIn('id', $legacyRecipeIds)->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('localized_routes')
                ->where('routeable_type', 'App\\Models\\Recipe')
                ->whereIn('routeable_id', $legacyRecipeIds)
                ->delete();
        }

        return $firstRecipeId;
    }

    private function recipeContentLeft(array $ingredients): array
    {
        $headings = ['Thành phần:', 'Ingredients:', '配料：'];

        return array_map(function (int $locale) use ($ingredients, $headings): string {
            if ($ingredients === []) {
                return '';
            }

            $items = array_map(function (array $ingredient) use ($locale): string {
                $amount = trim($ingredient[1].' '.($ingredient[2][$locale] ?? ''));

                return '<li>'.e(trim($amount.' '.($ingredient[0][$locale] ?? ''))).'</li>';
            }, $ingredients);

            return '<h2>'.$headings[$locale].'</h2><ul>'.implode('', $items).'</ul>';
        }, [0, 1, 2]);
    }

    private function recipeContentRight(array $steps): array
    {
        $headings = ['Cách làm:', 'Directions:', '做法：'];

        return array_map(function (int $locale) use ($steps, $headings): string {
            $items = array_map(
                fn (array $step): string => '<li>'.e($step[$locale] ?? '').'</li>',
                $steps
            );

            return '<h2>'.$headings[$locale].'</h2><ol>'.implode('', $items).'</ol>';
        }, [0, 1, 2]);
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

    /**
     * @return array<string, int>
     */
    private function seedRecruitment(int $adminId): array
    {
        $definitions = [
            'sales' => [
                'code' => 'SALES_EXPORT_01', 'department' => 'International Sales', 'quantity' => 2, 'sort' => 40,
                'title' => ['Chuyên viên kinh doanh xuất khẩu', 'Export sales executive', '出口销售专员'],
                'slug' => ['chuyen-vien-kinh-doanh-xuat-khau', 'export-sales-executive', 'chukou-xiaoshou-zhuanyuan'],
                'location' => ['Thành phố Hồ Chí Minh', 'Ho Chi Minh City', '胡志明市'],
                'summary' => ['Phát triển khách hàng và thị trường xuất khẩu thủy sản.', 'Develop seafood export customers and markets.', '开发水产品出口客户与市场。'],
                'description' => ['<ul><li>Tìm kiếm và chăm sóc khách hàng quốc tế.</li><li>Phối hợp báo giá, hợp đồng và thực hiện đơn hàng.</li></ul>', '<ul><li>Acquire and support international customers.</li><li>Coordinate quotations, contracts, and order execution.</li></ul>', '<ul><li>开发并维护国际客户。</li><li>协调报价、合同与订单执行。</li></ul>'],
                'requirements' => ['<ul><li>Tốt nghiệp Đại học khối kinh tế.</li><li>Tiếng Anh giao tiếp tốt.</li><li>Kinh nghiệm xuất khẩu là lợi thế.</li></ul>', '<ul><li>University degree in business or economics.</li><li>Good English communication.</li><li>Export experience is preferred.</li></ul>', '<ul><li>经济或商务相关专业本科。</li><li>良好的英语沟通能力。</li><li>有出口经验者优先。</li></ul>'],
                'benefits' => ['<p>Thu nhập cạnh tranh, thưởng doanh số và đầy đủ chế độ bảo hiểm.</p>', '<p>Competitive income, sales incentives, and full insurance benefits.</p>', '<p>有竞争力的薪酬、销售奖金及完整保险福利。</p>'],
            ],
            'quality' => [
                'code' => 'QA_SUPERVISOR_01', 'department' => 'Quality Assurance', 'quantity' => 1, 'sort' => 30,
                'title' => ['Giám sát đảm bảo chất lượng', 'Quality assurance supervisor', '质量保证主管'],
                'slug' => ['giam-sat-dam-bao-chat-luong', 'quality-assurance-supervisor', 'zhiliang-baozheng-zhuguan'],
                'location' => ['Nhà máy Vàm Cống, Đồng Tháp', 'Vam Cong Factory, Dong Thap', '同塔省 Vam Cong 工厂'],
                'summary' => ['Giám sát hệ thống chất lượng và an toàn thực phẩm tại nhà máy.', 'Supervise factory quality and food-safety systems.', '监督工厂质量与食品安全体系。'],
                'description' => ['<ul><li>Kiểm soát việc tuân thủ HACCP, BRC và tiêu chuẩn khách hàng.</li><li>Phân tích sự cố và theo dõi hành động khắc phục.</li></ul>', '<ul><li>Control compliance with HACCP, BRC, and customer standards.</li><li>Investigate incidents and monitor corrective actions.</li></ul>', '<ul><li>监督 HACCP、BRC 及客户标准的执行。</li><li>分析异常并跟进纠正措施。</li></ul>'],
                'requirements' => ['<ul><li>Tốt nghiệp Công nghệ thực phẩm hoặc ngành liên quan.</li><li>Có ít nhất 2 năm kinh nghiệm QA/QC thủy sản.</li></ul>', '<ul><li>Degree in food technology or a related field.</li><li>At least two years in seafood QA/QC.</li></ul>', '<ul><li>食品技术或相关专业。</li><li>至少两年水产 QA/QC 经验。</li></ul>'],
                'benefits' => ['<p>Phụ cấp ca, bữa ăn tại nhà máy và chương trình đào tạo chuyên môn.</p>', '<p>Shift allowance, factory meals, and professional training.</p>', '<p>轮班津贴、工厂餐食及专业培训。</p>'],
            ],
            'it' => [
                'code' => 'IT_SYSTEM_01', 'department' => 'Information Technology', 'quantity' => 2, 'sort' => 20,
                'title' => ['Nhân viên hệ thống công nghệ thông tin', 'IT systems specialist', '信息技术系统专员'],
                'slug' => ['nhan-vien-he-thong-cong-nghe-thong-tin', 'it-systems-specialist', 'xinxi-jishu-xitong-zhuanyuan'],
                'location' => ['Lấp Vò, Đồng Tháp', 'Lap Vo, Dong Thap', '同塔省立武县'],
                'summary' => ['Vận hành hạ tầng mạng, máy chủ và hỗ trợ người dùng nội bộ.', 'Operate network and server infrastructure and support internal users.', '运维网络、服务器基础设施并支持内部用户。'],
                'description' => ['<ul><li>Giám sát hệ thống mạng, máy chủ và sao lưu dữ liệu.</li><li>Tiếp nhận, xử lý yêu cầu hỗ trợ kỹ thuật.</li></ul>', '<ul><li>Monitor networks, servers, and data backups.</li><li>Handle internal technical support requests.</li></ul>', '<ul><li>监控网络、服务器与数据备份。</li><li>处理内部技术支持需求。</li></ul>'],
                'requirements' => ['<ul><li>Tốt nghiệp CNTT, Mạng máy tính hoặc tương đương.</li><li>Ưu tiên có kiến thức Windows Server, Linux và bảo mật.</li></ul>', '<ul><li>Degree in IT, networking, or equivalent.</li><li>Windows Server, Linux, and security knowledge is preferred.</li></ul>', '<ul><li>信息技术、网络或相关专业。</li><li>熟悉 Windows Server、Linux 与信息安全者优先。</li></ul>'],
                'benefits' => ['<p>Môi trường ổn định, trang thiết bị đầy đủ và lộ trình phát triển rõ ràng.</p>', '<p>Stable environment, modern equipment, and a clear career path.</p>', '<p>稳定的工作环境、完善设备与清晰职业发展路径。</p>'],
            ],
            'hr' => [
                'code' => 'HR_RECRUITMENT_01', 'department' => 'Human Resources', 'quantity' => 1, 'sort' => 10,
                'title' => ['Chuyên viên tuyển dụng và đào tạo', 'Recruitment and training specialist', '招聘与培训专员'],
                'slug' => ['chuyen-vien-tuyen-dung-va-dao-tao', 'recruitment-training-specialist', 'zhaopin-peixun-zhuanyuan'],
                'location' => ['Lấp Vò, Đồng Tháp', 'Lap Vo, Dong Thap', '同塔省立武县'],
                'summary' => ['Phụ trách tuyển dụng, hội nhập và hỗ trợ đào tạo nhân sự.', 'Manage recruitment, onboarding, and employee training support.', '负责招聘、入职及员工培训支持。'],
                'description' => ['<ul><li>Triển khai kế hoạch tuyển dụng theo nhu cầu phòng ban.</li><li>Tổ chức hội nhập và theo dõi đào tạo.</li></ul>', '<ul><li>Execute recruitment plans for business units.</li><li>Organize onboarding and track training activities.</li></ul>', '<ul><li>按部门需求执行招聘计划。</li><li>组织入职并跟进培训活动。</li></ul>'],
                'requirements' => ['<ul><li>Tốt nghiệp Quản trị nhân lực hoặc ngành phù hợp.</li><li>Kỹ năng giao tiếp và tổ chức tốt.</li></ul>', '<ul><li>Degree in human resources or a relevant field.</li><li>Strong communication and organization skills.</li></ul>', '<ul><li>人力资源或相关专业。</li><li>良好的沟通与组织能力。</li></ul>'],
                'benefits' => ['<p>Thưởng hiệu quả, khám sức khỏe định kỳ và hoạt động gắn kết nhân viên.</p>', '<p>Performance bonus, annual health checks, and employee activities.</p>', '<p>绩效奖金、年度体检与员工活动。</p>'],
            ],
        ];

        $ids = [];
        foreach ($definitions as $key => $position) {
            $ids[$key] = $this->upsertId('job_positions', ['code' => $position['code']], [
                'department' => $position['department'],
                'title' => $this->translations(...$position['title']),
                'slug' => $this->translations(...$position['slug']),
                'location' => $this->translations(...$position['location']),
                'summary' => $this->translations(...$position['summary']),
                'description' => $this->translations(...$position['description']),
                'requirements' => $this->translations(...$position['requirements']),
                'benefits' => $this->translations(...$position['benefits']),
                'contact' => $this->translations(
                    '<p>Ứng viên vui lòng nộp hồ sơ trực tuyến hoặc liên hệ Phòng Nhân sự IDI Seafood.</p>',
                    '<p>Please apply online or contact the IDI Seafood Human Resources Department.</p>',
                    '<p>请在线提交申请或联系 IDI Seafood 人力资源部。</p>'
                ),
                'seo_title' => $this->translations(...$position['title']),
                'meta_description' => $this->translations(...$position['summary']),
                'meta_keywords' => $this->translations('tuyển dụng, việc làm, IDI Seafood', 'careers, jobs, IDI Seafood', '招聘, 职位, IDI Seafood'),
                'quantity' => $position['quantity'],
                'expires_at' => now()->addDays(45 + $position['sort']),
                'translation_status' => $this->publishedStatus(),
                'locale_published_at' => $this->publishedDates(),
                'sort_order' => $position['sort'],
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'deleted_at' => null,
            ]);
        }

        return $ids;
    }

    /**
     * @param  array<string, int>  $positionIds
     */
    private function seedRecruitmentApplications(int $adminId, array $positionIds): void
    {
        $applications = [
            ['position' => 'quality', 'name' => 'Nguyễn Thanh Bình', 'email' => 'thanhbinhdtcc@gmail.com', 'phone' => '0909887205', 'address' => '426B/5, Ấp Tân Thạnh, Xã Lai Vung, Tỉnh Đồng Tháp', 'status' => 'new', 'submitted' => '2026-08-03 09:03:00', 'letter' => null],
            ['position' => 'quality', 'name' => 'Phạm Nguyễn Thị Thiên Kim', 'email' => 'pthienkim849@gmail.com', 'phone' => '0325219367', 'address' => 'Ấp Phước Lợi, xã Ô Lâm, huyện Tri Tôn, tỉnh An Giang', 'status' => 'new', 'submitted' => '2026-08-01 14:20:00', 'letter' => 'Em xin được ứng tuyển ạ.'],
            ['position' => 'it', 'name' => 'Đỗ Trung Kiên', 'email' => 'trungkien240398@gmail.com', 'phone' => '0388829090', 'address' => '818 Khóm Tân Thuận, xã Thanh Bình, tỉnh Đồng Tháp', 'status' => 'reviewing', 'submitted' => '2026-06-23 14:34:00', 'letter' => 'Tôi là kỹ sư tốt nghiệp chuyên ngành Kỹ thuật Máy tính, có kinh nghiệm làm việc trong lĩnh vực vận hành hệ thống mạng và thiết bị viễn thông. Tôi mong muốn tiếp tục phát triển chuyên môn và đóng góp vào sự ổn định, hiệu quả của hệ thống tại doanh nghiệp.'],
            ['position' => 'quality', 'name' => 'Đào Huỳnh Như', 'email' => 'daohuynhnhu2004@gmail.com', 'phone' => '0344242985', 'address' => 'Ấp Bình Hiệp B, xã Lấp Vò, tỉnh Đồng Tháp', 'status' => 'reviewing', 'submitted' => '2026-06-03 16:45:00', 'letter' => null],
            ['position' => 'sales', 'name' => 'Nguyễn Minh Trí', 'email' => 'nguyentran2008cb@gmail.com', 'phone' => '0363273179', 'address' => 'Ấp Hậu Vĩnh, xã Hội Cư, tỉnh Đồng Tháp', 'status' => 'reviewing', 'submitted' => '2026-05-15 15:19:00', 'letter' => 'Em xin ứng tuyển vị trí nhân viên thống kê kho ạ.'],
            ['position' => 'quality', 'name' => 'Phạm Thái Thiên', 'email' => 'thaithienca@gmail.com', 'phone' => '0862705185', 'address' => '22 Tôn Đức Thắng, khu vực Bình Hưng, phường Phước Thới, TP Cần Thơ', 'status' => 'reviewing', 'submitted' => '2026-03-31 19:31:00', 'letter' => 'Có kinh nghiệm làm QC sản xuất cá tra được 5,6 năm.'],
            ['position' => 'hr', 'name' => 'Võ Vân Đăng', 'email' => 'vodangdt@gmail.com', 'phone' => '0907628885', 'address' => 'Thường Lạc, Đồng Tháp', 'status' => 'reviewing', 'submitted' => '2026-03-21 15:29:00', 'letter' => null],
            ['position' => 'sales', 'name' => 'Lê Thùy Dương', 'email' => 'ltduong2102@gmail.com', 'phone' => '0919995410', 'address' => 'Thành phố Cao Lãnh, Đồng Tháp', 'status' => 'new', 'submitted' => '2026-03-18 10:12:00', 'letter' => 'Tôi mong muốn được làm việc trong môi trường chuyên nghiệp và gắn bó lâu dài cùng IDI Seafood.'],
        ];

        foreach ($applications as $index => $application) {
            $isReviewed = $application['status'] !== 'new';
            $applicationId = $this->upsertId('job_applications', [
                'job_position_id' => $positionIds[$application['position']],
                'email' => $application['email'],
            ], [
                'full_name' => $application['name'],
                'phone' => $application['phone'],
                'address' => $application['address'],
                'cover_letter' => $application['letter'],
                'cv_media_id' => null,
                'status' => $application['status'],
                'internal_note' => $isReviewed ? match ($application['status']) {
                    'shortlisted' => 'Hồ sơ phù hợp, mời phỏng vấn vòng chuyên môn.',
                    'rejected' => 'Kinh nghiệm hiện tại chưa phù hợp yêu cầu vị trí.',
                    'hired' => 'Đã hoàn tất phỏng vấn và gửi thư mời nhận việc.',
                    default => 'Đã liên hệ xác nhận thông tin ứng viên.',
                } : null,
                'reviewed_by' => $isReviewed ? $adminId : null,
                'reviewed_at' => $isReviewed ? now()->subDays(6 - $index) : null,
            ]);

            DB::table('job_applications')->where('id', $applicationId)->update(['created_at' => $application['submitted']]);
        }
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
