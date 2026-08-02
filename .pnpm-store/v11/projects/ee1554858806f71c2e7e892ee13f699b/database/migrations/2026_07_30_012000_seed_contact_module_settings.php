<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('modules')->updateOrInsert(['code' => 'contact'], [
            'name' => 'Contact',
            'module_type' => 'content',
            'page_title' => json_encode([
                'vi' => 'Liên hệ với chúng tôi',
                'en' => 'Contact us',
                'zh' => '联系我们',
            ], JSON_UNESCAPED_UNICODE),
            'description' => json_encode([
                'vi' => '<p>Quan tâm đến IDI Seafood hoặc sản phẩm của chúng tôi? Hãy gửi thông tin hoặc liên hệ trực tiếp tại các văn phòng bên dưới.</p>',
                'en' => '<p>Interested in IDI Seafood or our products? Send us a message or contact one of our offices below.</p>',
                'zh' => '<p>对 IDI Seafood 或我们的产品感兴趣？请发送信息或联系以下办事处。</p>',
            ], JSON_UNESCAPED_UNICODE),
            'seo_title' => json_encode([
                'vi' => 'Liên hệ IDI Seafood',
                'en' => 'Contact IDI Seafood',
                'zh' => '联系 IDI Seafood',
            ], JSON_UNESCAPED_UNICODE),
            'meta_description' => json_encode([
                'vi' => 'Liên hệ IDI Seafood để được tư vấn sản phẩm, xuất khẩu và hợp tác kinh doanh.',
                'en' => 'Contact IDI Seafood for product, export, and business partnership inquiries.',
                'zh' => '联系 IDI Seafood，咨询产品、出口及商业合作事宜。',
            ], JSON_UNESCAPED_UNICODE),
            'is_active' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $moduleId = (int) DB::table('modules')->where('code', 'contact')->value('id');
        foreach ([
            ['key' => 'form_enabled', 'value' => true, 'type' => 'boolean'],
            ['key' => 'spam_protection', 'value' => true, 'type' => 'boolean'],
            ['key' => 'notification_email', 'value' => 'info@idiseafood.com', 'type' => 'text'],
            ['key' => 'items_per_page', 'value' => 15, 'type' => 'number'],
            ['key' => 'success_message', 'value' => [
                'vi' => 'Cảm ơn bạn đã liên hệ. IDI Seafood sẽ phản hồi trong thời gian sớm nhất.',
                'en' => 'Thank you for contacting us. IDI Seafood will respond as soon as possible.',
                'zh' => '感谢您的联系。IDI Seafood 将尽快回复。',
            ], 'type' => 'json'],
        ] as $setting) {
            DB::table('module_settings')->updateOrInsert([
                'module_id' => $moduleId,
                'setting_key' => $setting['key'],
            ], [
                'setting_value' => json_encode($setting['value'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'setting_type' => $setting['type'],
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Preserve administrator-authored contact configuration on rollback.
    }
};
