<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\InteractsWithSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresentationSeeder extends Seeder
{
    use InteractsWithSeedData;

    public function run(): void
    {
        $this->seedHomepageSlider();
        $this->seedSiteSettings();
        $this->seedSocialLinks();
    }

    private function seedHomepageSlider(): void
    {
        $sliderId = $this->upsertId('sliders', ['code' => 'HOME_HERO'], [
            'name' => 'Homepage Hero',
            'description' => $this->translations(
                'Banner chính trang chủ.',
                'Main homepage banner.',
                '首页主横幅。'
            ),
            'is_active' => true,
            'deleted_at' => null,
        ]);

        $itemId = $this->upsertId('slider_items', [
            'slider_id' => $sliderId,
            'sort_order' => 0,
        ], [
            'title' => $this->translations(
                'Tinh hoa cá tra Việt Nam',
                'The finest Vietnamese pangasius',
                '优质越南巴沙鱼'
            ),
            'subtitle' => $this->translations(
                'Chất lượng quốc tế từ chuỗi giá trị bền vững.',
                'International quality from a sustainable value chain.',
                '源自可持续价值链的国际品质。'
            ),
            'button_label' => $this->translations('Khám phá sản phẩm', 'Explore products', '探索产品'),
            'link' => $this->json([
                'vi' => '/vi/san-pham',
                'en' => '/en/products',
                'zh' => '/zh/chanpin',
            ]),
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addYear(),
            'is_active' => true,
            'deleted_at' => null,
        ]);

        foreach ([
            'desktop' => 'home-hero-desktop.jpg',
            'mobile' => 'home-hero-mobile.jpg',
        ] as $device => $fileName) {
            $mediaId = (int) DB::table('media')->where('file_name', $fileName)->value('id');

            foreach (['vi', 'en', 'zh'] as $locale) {
                $this->upsertId('slider_item_media', [
                    'slider_item_id' => $itemId,
                    'locale' => $locale,
                    'device' => $device,
                ], [
                    'media_id' => $mediaId,
                ]);
            }
        }
    }

    private function seedSiteSettings(): void
    {
        $logoMediaId = (int) DB::table('media')->where('file_name', 'idi-logo.png')->value('id');

        foreach ([
            ['key' => 'site.name', 'value' => $this->translations('IDI Seafood', 'IDI Seafood', 'IDI Seafood'), 'type' => 'text', 'translatable' => true, 'group' => 'general', 'sort' => 0],
            ['key' => 'site.description', 'value' => $this->translations('Thủy sản chất lượng cho thị trường toàn cầu', 'Quality seafood for global markets', '面向全球市场的优质水产品'), 'type' => 'text', 'translatable' => true, 'group' => 'general', 'sort' => 1],
            ['key' => 'site.default_locale', 'value' => json_encode('vi', JSON_THROW_ON_ERROR), 'type' => 'text', 'translatable' => false, 'group' => 'general', 'sort' => 2],
            ['key' => 'site.logo_media_id', 'value' => json_encode($logoMediaId, JSON_THROW_ON_ERROR), 'type' => 'media', 'translatable' => false, 'group' => 'branding', 'sort' => 0],
            ['key' => 'contact.email', 'value' => json_encode('info@idiseafood.com', JSON_THROW_ON_ERROR), 'type' => 'text', 'translatable' => false, 'group' => 'contact', 'sort' => 0],
            ['key' => 'contact.phone', 'value' => json_encode('+84 277 376 8899', JSON_THROW_ON_ERROR), 'type' => 'text', 'translatable' => false, 'group' => 'contact', 'sort' => 1],
        ] as $setting) {
            $this->upsertId('site_settings', ['key_name' => $setting['key']], [
                'value' => $setting['value'],
                'type' => $setting['type'],
                'is_translatable' => $setting['translatable'],
                'group_name' => $setting['group'],
                'sort_order' => $setting['sort'],
            ]);
        }
    }

    private function seedSocialLinks(): void
    {
        foreach ([
            ['platform' => 'facebook', 'label' => ['Facebook', 'Facebook', 'Facebook'], 'url' => 'https://www.facebook.com/', 'icon' => 'facebook'],
            ['platform' => 'linkedin', 'label' => ['LinkedIn', 'LinkedIn', 'LinkedIn'], 'url' => 'https://www.linkedin.com/', 'icon' => 'linkedin'],
            ['platform' => 'youtube', 'label' => ['YouTube', 'YouTube', 'YouTube'], 'url' => 'https://www.youtube.com/', 'icon' => 'youtube'],
        ] as $sortOrder => $social) {
            $this->upsertId('social_links', ['platform' => $social['platform']], [
                'label' => $this->translations(...$social['label']),
                'url' => $social['url'],
                'icon' => $social['icon'],
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]);
        }
    }
}
