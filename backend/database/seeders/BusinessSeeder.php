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
        $positionIds = $this->seedRecruitment($adminId);
        $this->seedRecruitmentApplications($adminId, $positionIds);
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
