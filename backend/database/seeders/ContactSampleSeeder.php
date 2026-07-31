<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSampleSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('email', 'admin@idiseafood.local')->value('id');
        foreach ([
            [
                'code' => 'HEAD_OFFICE',
                'name' => ['vi' => 'Trụ sở chính', 'en' => 'Head office', 'zh' => '总部'],
                'address' => ['vi' => 'Quốc lộ 80, Cụm công nghiệp Vàm Cống, Đồng Tháp, Việt Nam', 'en' => 'National Highway 80, Vam Cong Industrial Cluster, Dong Thap, Vietnam', 'zh' => '越南同塔省 Vam Cong 工业区 80 号国道'],
                'phone' => '+84 277 376 8899', 'email' => 'info@idiseafood.com',
                'lat' => 10.3460000, 'lng' => 105.5350000, 'sort' => 0, 'active' => true,
            ],
            [
                'code' => 'HCMC_OFFICE',
                'name' => ['vi' => 'Văn phòng đại diện Hồ Chí Minh', 'en' => 'Ho Chi Minh City representative office', 'zh' => '胡志明市代表处'],
                'address' => ['vi' => 'Quận 1, Thành phố Hồ Chí Minh, Việt Nam', 'en' => 'District 1, Ho Chi Minh City, Vietnam', 'zh' => '越南胡志明市第一郡'],
                'phone' => '+84 28 3820 8899', 'email' => 'sales@idiseafood.com',
                'lat' => 10.7756587, 'lng' => 106.7004238, 'sort' => 1, 'active' => true,
            ],
            [
                'code' => 'USA_OFFICE',
                'name' => ['vi' => 'Văn phòng đại diện Mỹ', 'en' => 'USA representative office', 'zh' => '美国代表处'],
                'address' => ['vi' => 'California, Hoa Kỳ', 'en' => 'California, United States', 'zh' => '美国加利福尼亚州'],
                'phone' => '+1 714 555 0188', 'email' => 'usa@idiseafood.com',
                'lat' => 33.6845673, 'lng' => -117.8265049, 'sort' => 2, 'active' => false,
            ],
        ] as $office) {
            OfficeLocation::updateOrCreate(['code' => $office['code']], [
                'name' => $office['name'], 'address' => $office['address'],
                'phone' => $office['phone'], 'email' => $office['email'],
                'latitude' => $office['lat'], 'longitude' => $office['lng'],
                'sort_order' => $office['sort'], 'is_active' => $office['active'],
            ]);
        }

        foreach ([
            ['Nguyễn Thùy Trang', 'trang.nguyen@example.com', '0901234567', 'Tư vấn mua cá basa phi lê', 'Tôi muốn nhận báo giá và quy cách đóng gói cho đơn hàng xuất khẩu sang thị trường Mỹ.', 'vi', 'new', 0],
            ['Trần Minh Hoàng', 'hoang.tran@example.com', '0912456789', 'Yêu cầu catalogue sản phẩm', 'Vui lòng gửi catalogue sản phẩm giá trị gia tăng và thông tin MOQ cho doanh nghiệp của chúng tôi.', 'vi', 'in_progress', 1],
            ['Lê Thu Hà', 'ha.le@example.com', '0987654321', 'Tham quan nhà máy', 'Công ty chúng tôi mong muốn đăng ký lịch tham quan nhà máy và trao đổi cơ hội hợp tác.', 'vi', 'resolved', 5],
            ['Emily Carter', 'emily.carter@example.com', '+1 415 555 0136', 'Frozen pangasius fillet inquiry', 'Please provide specifications, certifications, lead time, and a quotation for frozen pangasius fillets.', 'en', 'new', 0],
            ['Michael Tan', 'michael.tan@example.com', '+65 6123 4567', 'Distribution partnership', 'We are interested in becoming a distribution partner for IDI Seafood products in Singapore.', 'en', 'in_progress', 2],
            ['Sophia Wilson', 'sophia.wilson@example.com', '+44 20 7946 0182', 'Product sample follow-up', 'Thank you for the samples. Our quality team has completed the review and would like to discuss the next order.', 'en', 'resolved', 8],
            ['王海', 'wang.hai@example.com', '+86 138 0013 8000', '巴沙鱼产品咨询', '我们希望了解冷冻巴沙鱼柳的规格、认证、最低订购量和交货时间。', 'zh', 'new', 0],
            ['李敏', 'li.min@example.com', '+86 139 0013 9000', '中国市场合作', '我们有兴趣在中国市场推广 IDI Seafood 产品，请提供合作政策和产品目录。', 'zh', 'resolved', 12],
            ['Website Robot', 'robot-spam@example.net', null, 'SEO promotion service', 'Automated promotional message for an unrelated website optimization service.', 'en', 'spam', 3],
        ] as [$name, $email, $phone, $subject, $message, $locale, $status, $daysAgo]) {
            $contact = ContactMessage::updateOrCreate(['email' => $email, 'subject' => $subject], [
                'full_name' => $name,
                'phone' => $phone,
                'message' => $message,
                'locale' => $locale,
                'status' => $status,
                'assigned_to' => in_array($status, ['in_progress', 'resolved'], true) ? $adminId : null,
                'replied_at' => $status === 'resolved' ? now()->subDays(max(1, $daysAgo - 1)) : null,
            ]);
            $contact->forceFill([
                'created_at' => now()->subDays($daysAgo)->subMinutes(20 + ($daysAgo * 7)),
                'updated_at' => now()->subDays(max(0, $daysAgo - 1)),
            ])->saveQuietly();
        }
    }
}
