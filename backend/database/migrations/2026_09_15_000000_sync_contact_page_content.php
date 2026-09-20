<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $module = DB::table('modules')->where('code', 'contact')->first();
        if ($module) {
            $content = [
                'page_title' => [
                    'legacy' => ['vi' => 'Liên hệ với chúng tôi', 'en' => 'Contact us', 'zh' => '联系我们'],
                    'desired' => ['vi' => 'Chúng tôi sẵn sàng lắng nghe', 'en' => 'We are ready to listen', 'zh' => '我们随时倾听您的需求'],
                ],
                'description' => [
                    'legacy' => [
                        'vi' => '<p>Quan tâm đến IDI Seafood hoặc sản phẩm của chúng tôi? Hãy gửi thông tin hoặc liên hệ trực tiếp tại các văn phòng bên dưới.</p>',
                        'en' => '<p>Interested in IDI Seafood or our products? Send us a message or contact one of our offices below.</p>',
                        'zh' => '<p>对 IDI Seafood 或我们的产品感兴趣？请发送信息或联系以下办事处。</p>',
                    ],
                    'desired' => [
                        'vi' => '<p>Trao đổi với đội ngũ IDI về sản phẩm, xuất khẩu, hợp tác kinh doanh hoặc bất kỳ thông tin nào bạn cần.</p>',
                        'en' => '<p>Contact the IDI team about products, exports, business partnerships, or any information you need.</p>',
                        'zh' => '<p>欢迎就产品、出口、商务合作或您所需的任何信息与 IDI 团队联系。</p>',
                    ],
                ],
                'seo_title' => [
                    'legacy' => ['vi' => 'Liên hệ IDI Seafood', 'en' => 'Contact IDI Seafood', 'zh' => '联系 IDI Seafood'],
                    'desired' => ['vi' => 'Liên hệ | IDI Seafood', 'en' => 'Contact | IDI Seafood', 'zh' => '联系我们 | IDI Seafood'],
                ],
                'meta_description' => [
                    'legacy' => [
                        'vi' => 'Liên hệ IDI Seafood để được tư vấn sản phẩm, xuất khẩu và hợp tác kinh doanh.',
                        'en' => 'Contact IDI Seafood for product, export, and business partnership inquiries.',
                        'zh' => '联系 IDI Seafood，咨询产品、出口及商业合作事宜。',
                    ],
                    'desired' => [
                        'vi' => 'Liên hệ IDI Seafood để nhận báo giá, tư vấn sản phẩm cá tra, thông tin hợp tác và hỗ trợ xuất khẩu.',
                        'en' => 'Contact IDI Seafood for quotations, pangasius product advice, partnership opportunities, and export support.',
                        'zh' => '联系 IDI Seafood，获取报价、巴沙鱼产品咨询、合作信息和出口支持。',
                    ],
                ],
            ];
            $updates = [];
            foreach ($content as $field => $values) {
                $translations = json_decode($module->{$field} ?: '[]', true) ?: [];
                foreach ($values['desired'] as $locale => $desired) {
                    if (blank($translations[$locale] ?? null) || ($translations[$locale] ?? null) === $values['legacy'][$locale]) {
                        $translations[$locale] = $desired;
                    }
                }
                $updates[$field] = json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }
            $updates['updated_at'] = $now;
            DB::table('modules')->where('id', $module->id)->update($updates);

            $successSetting = DB::table('module_settings')
                ->where('module_id', $module->id)
                ->where('setting_key', 'success_message')
                ->first();
            if ($successSetting) {
                $success = json_decode($successSetting->setting_value ?: '[]', true) ?: [];
                $legacySuccess = [
                    'vi' => 'Cảm ơn bạn đã liên hệ. IDI Seafood sẽ phản hồi trong thời gian sớm nhất.',
                    'en' => 'Thank you for contacting us. IDI Seafood will respond as soon as possible.',
                    'zh' => '感谢您的联系。IDI Seafood 将尽快回复。',
                ];
                $desiredSuccess = [
                    'vi' => 'Chúng tôi đã tiếp nhận thông tin và sẽ phản hồi trong thời gian sớm nhất.',
                    'en' => 'We have received your information and will respond as soon as possible.',
                    'zh' => '我们已收到您的信息，并将尽快回复。',
                ];
                foreach ($desiredSuccess as $locale => $desired) {
                    if (blank($success[$locale] ?? null) || ($success[$locale] ?? null) === $legacySuccess[$locale]) {
                        $success[$locale] = $desired;
                    }
                }
                DB::table('module_settings')->where('id', $successSetting->id)->update([
                    'setting_value' => json_encode($success, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'updated_at' => $now,
                ]);
            }
        }

        $locations = [
            [
                'code' => 'HEAD_OFFICE',
                'name' => [
                    'vi' => 'Trụ sở chính',
                    'en' => 'Head office',
                    'zh' => '总部',
                ],
                'address' => [
                    'vi' => 'Quốc lộ 80, Cụm công nghiệp Vàm Cống, ấp An Thạnh, xã Lấp Vò, Tỉnh Đồng Tháp, Việt Nam',
                    'en' => 'National Road 80, Vam Cong Industrial Cluster, An Thanh Hamlet, Lap Vo Commune, Dong Thap Province, Vietnam',
                    'zh' => '越南同塔省垃圩社安盛邑汪贡工业区80号国道',
                ],
                'phone' => '+84 2773 680 383 · +84 2777 300 468',
                'fax' => '+84 2773 680 382',
                'email' => 'info@idiseafood.com',
                'map_embed' => '<iframe src="https://www.google.com/maps?q=IDI+Seafood+Vam+Cong+Dong+Thap&amp;output=embed" loading="lazy" allowfullscreen></iframe>',
                'sort_order' => 0,
            ],
            [
                'code' => 'HCMC_OFFICE',
                'name' => [
                    'vi' => 'Văn phòng đại diện Hồ Chí Minh',
                    'en' => 'Ho Chi Minh City representative office',
                    'zh' => '胡志明市代表处',
                ],
                'address' => [
                    'vi' => '9 Nguyễn Kim, phường 12, quận 5, Thành phố Hồ Chí Minh, Việt Nam',
                    'en' => '9 Nguyen Kim Street, Ward 12, District 5, Ho Chi Minh City, Vietnam',
                    'zh' => '越南胡志明市第五郡第十二坊阮金街9号',
                ],
                'phone' => '+84 932 824 888',
                'fax' => null,
                'email' => null,
                'map_embed' => '<iframe src="https://www.google.com/maps?q=9+Nguyen+Kim+Ward+12+District+5+Ho+Chi+Minh+City&amp;output=embed" loading="lazy" allowfullscreen></iframe>',
                'sort_order' => 1,
            ],
        ];

        foreach ($locations as $location) {
            DB::table('office_locations')->updateOrInsert(['code' => $location['code']], [
                'name' => json_encode($location['name'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'company' => null,
                'address' => json_encode($location['address'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'phone' => $location['phone'],
                'fax' => $location['fax'],
                'email' => $location['email'],
                'map_type' => 'embed',
                'map_embed' => $location['map_embed'],
                'map_url' => null,
                'map_image' => null,
                'sort_order' => $location['sort_order'],
                'is_active' => true,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('office_locations')->where('code', 'FACTORY_01')->update([
            'is_active' => false,
            'updated_at' => $now,
        ]);

        $legacyTypes = [
            'Báo giá xuất khẩu' => 'export_quote',
            'Tư vấn sản phẩm' => 'product_advice',
            'Hợp tác kinh doanh' => 'business_cooperation',
            'Quan hệ nhà đầu tư' => 'investor_relations',
            'Tuyển dụng' => 'careers',
            'Yêu cầu khác' => 'other',
        ];
        foreach ($legacyTypes as $label => $key) {
            DB::table('contact_messages')->where('inquiry_type', $label)->update(['inquiry_type' => $key]);
        }
    }

    public function down(): void
    {
        // Keep contact details and messages created or edited after this synchronization.
    }
};
