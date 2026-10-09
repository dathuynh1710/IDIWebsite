<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\AboutPageRoutes;
use Database\Seeders\ContentSeeder;
use Database\Seeders\CoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AboutPagesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_data_contains_the_three_real_idi_about_pages(): void
    {
        $this->seed([CoreSeeder::class, ContentSeeder::class]);

        $this->assertDatabaseHas('pages', [
            'code' => 'ABOUT_MESSAGE',
            'template' => 'about',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('pages', [
            'code' => 'ABOUT_HISTORY',
            'template' => 'about-history',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('pages', [
            'code' => 'ABOUT_VALUES',
            'template' => 'about-values',
            'is_active' => true,
        ]);

        $message = Page::where('code', 'ABOUT_MESSAGE')->firstOrFail();
        $this->assertSame('A Message from I.D.I', $message->getTranslation('title', 'en'));
        $this->assertSame('a-message-from-i-d-i', $message->getTranslation('slug', 'en'));
        $this->assertStringContainsString('Executive Advisor', $message->getTranslation('content', 'en'));
        $this->assertStringContainsString('sustainability is not a choice', $message->getTranslation('summary', 'en'));
        $this->assertSame('I.D.I 致辞', $message->getTranslation('title', 'zh'));
        $this->assertSame('i-d-i-zhi-ci', $message->getTranslation('slug', 'zh'));
        $this->assertStringContainsString('执行顾问', $message->getTranslation('content', 'zh'));
        $this->assertStringContainsString('可持续发展不是一种选择', $message->getTranslation('summary', 'zh'));

        $values = Page::where('code', 'ABOUT_VALUES')->firstOrFail();
        $this->assertSame("I.D.I's Values", $values->getTranslation('title', 'en'));
        $this->assertSame('核心价值观', $values->getTranslation('title', 'zh'));
        $this->assertStringContainsString('We act with honesty and integrity', $values->getTranslation('content', 'en'));
        $this->assertStringContainsString('我们秉持诚实与诚信', $values->getTranslation('content', 'zh'));
        $this->assertStringContainsString('/assets/media/about/values/gt1.jpg', $values->getTranslation('content', 'en'));

        $this->getJson('/api/about/ABOUT_VALUES?locale=en')
            ->assertOk()
            ->assertJsonPath(
                'data.content',
                fn (string $content): bool => str_contains(
                    $content,
                    config('app.url').'/assets/media/about/values/gt1.jpg'
                )
            );

        $history = Page::where('code', 'ABOUT_HISTORY')->firstOrFail();
        $this->assertSame('A History of Innovation', $history->getTranslation('title', 'en'));
        $this->assertSame('发展与创新历程', $history->getTranslation('title', 'zh'));
        $this->assertStringContainsString('A history of responsibility', $history->getTranslation('content', 'en'));
        $this->assertStringContainsString('责任相伴的发展历程', $history->getTranslation('content', 'zh'));

        $this->getJson('/api/about/ABOUT_HISTORY')
            ->assertOk()
            ->assertJsonPath('data.title', 'Lịch sử hình thành và đổi mới')
            ->assertJsonFragment(['content' => Page::where('code', 'ABOUT_HISTORY')->firstOrFail()->getTranslation('content', 'vi')]);
    }

    public function test_index_returns_only_active_about_pages_in_display_order(): void
    {
        $this->module();
        $values = $this->page('ABOUT_VALUES', 'about-values', 'Giá trị cốt lõi', 3);
        $message = $this->page('ABOUT_MESSAGE', 'about', 'Thông điệp của công ty', 0);
        $this->page('ABOUT_HISTORY', 'about-history', 'Lịch sử hình thành và đổi mới', 1, false);
        Page::create([
            'code' => 'OTHER_PAGE',
            'template' => 'default',
            'title' => ['vi' => 'Trang khác'],
            'slug' => ['vi' => 'trang-khac'],
            'is_active' => true,
        ]);

        $this->getJson('/api/about?locale=vi')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('items.0.id', $message->id)
            ->assertJsonPath('items.1.id', $values->id)
            ->assertJsonPath('module.title', 'Giới thiệu');
    }

    public function test_page_can_be_resolved_by_code_or_localized_slug_and_missing_translation_returns_not_found(): void
    {
        $this->module();
        $page = $this->page('ABOUT_MESSAGE', 'about', 'Thông điệp của công ty');
        $page->update([
            'slug' => ['vi' => 'thong-diep-cua-cong-ty'],
            'summary' => ['vi' => 'Thông điệp phát triển bền vững.'],
            'content' => ['vi' => '<p>Nội dung được quản lý từ CMS.</p>'],
            'seo_title' => ['vi' => 'Thông điệp IDI'],
        ]);

        $this->getJson('/api/about/ABOUT_MESSAGE?locale=en')
            ->assertNotFound();

        $this->getJson('/api/about/ABOUT_MESSAGE?locale=zh-CN')
            ->assertNotFound();

        $this->getJson('/api/about/thong-diep-cua-cong-ty?locale=vi')
            ->assertOk()
            ->assertJsonPath('data.code', 'ABOUT_MESSAGE');

        $page->update(['code' => null]);
        $this->getJson('/api/about/ABOUT_MESSAGE?locale=vi')
            ->assertOk()
            ->assertJsonPath('data.template', 'about');
    }

    public function test_frontend_payload_reflects_admin_content_updates(): void
    {
        $this->module();
        $page = $this->page('ABOUT_HISTORY', 'about-history', 'Lịch sử hình thành và đổi mới');

        $page->setTranslation('content', 'vi', '<h2>Cột mốc mới</h2><p>Nội dung vừa cập nhật.</p>')->save();

        $this->getJson('/api/about/ABOUT_HISTORY')
            ->assertOk()
            ->assertJsonPath('data.content', '<h2>Cột mốc mới</h2><p>Nội dung vừa cập nhật.</p>');
    }

    public function test_hidden_page_and_disabled_module_are_not_public(): void
    {
        $this->module();
        $this->page('ABOUT_VALUES', 'about-values', 'Giá trị cốt lõi', 0, false);

        $this->getJson('/api/about/ABOUT_VALUES')->assertNotFound();

        DB::table('modules')->where('code', 'about')->update(['is_active' => false]);
        $this->getJson('/api/about')->assertNotFound();
    }

    public function test_arbitrary_about_page_exposes_the_same_paths_as_route_sync(): void
    {
        $this->seed(CoreSeeder::class);
        $page = $this->page('ABOUT_LEADERSHIP', 'about-leadership', 'Leadership');
        $page->update([
            'title' => ['vi' => 'Vietnamese', 'en' => 'English', 'zh' => 'Chinese'],
            'slug' => ['vi' => 'lanh-dao', 'en' => 'leadership', 'zh' => 'ling-dao'],
            'content' => ['vi' => '<p>VI</p>', 'en' => '<p>EN</p>', 'zh' => '<p>ZH</p>'],
        ]);
        AboutPageRoutes::sync($page);
        foreach (['vi' => 'lanh-dao', 'en' => 'leadership', 'zh' => 'ling-dao'] as $locale => $slug) {
            $path = DB::table('localized_routes')->where('routeable_id', $page->id)
                ->where('routeable_type', Page::class)->where('locale', $locale)->value('full_path');
            $this->getJson("/api/about/{$slug}?locale={$locale}&bySlug=1")
                ->assertOk()->assertJsonPath('data.id', $page->id)
                ->assertJsonPath('data.locale', $locale)
                ->assertJsonPath("data.localizedPaths.{$locale}", $path)
                ->assertJsonPath('data.content', '<p>'.strtoupper($locale).'</p>');
        }
        $this->getJson('/api/about?locale=en')->assertJsonPath('items.0.localizedPaths.en', '/en/about/leadership');
        $page->forgetTranslation('slug', 'zh')->save();
        $this->getJson('/api/about/ABOUT_LEADERSHIP?locale=vi')->assertJsonMissingPath('data.localizedPaths.zh');
        $page->forgetTranslation('title', 'en')->save();
        $this->getJson('/api/about/ABOUT_LEADERSHIP?locale=vi')->assertJsonMissingPath('data.localizedPaths.en');
    }

    public function test_localized_lookup_does_not_confuse_codes_or_vietnamese_fallback_with_localized_slugs(): void
    {
        $this->module();
        $first = $this->page('ABOUT_MESSAGE', 'about', 'First');
        $first->update(['title' => ['vi' => 'First', 'en' => 'First'], 'slug' => ['vi' => 'shared', 'en' => 'first']]);
        $second = $this->page('ABOUT_OTHER', 'about', 'Second');
        $second->update(['title' => ['vi' => 'Second', 'en' => 'Second'], 'slug' => ['vi' => 'second', 'en' => 'shared']]);
        $this->getJson('/api/about/shared?locale=en&bySlug=1')->assertOk()->assertJsonPath('data.id', $second->id);
        $this->getJson('/api/about/shared?locale=vi&bySlug=1')->assertOk()->assertJsonPath('data.id', $first->id);
        $this->getJson('/api/about/ABOUT_MESSAGE?locale=en&bySlug=1')->assertNotFound();
        $this->getJson('/api/about/second?locale=en&bySlug=1')->assertNotFound();
        $this->getJson('/api/about/ABOUT_MESSAGE?locale=en')->assertOk()->assertJsonPath('data.id', $first->id);
    }

    private function module(): void
    {
        DB::table('modules')->insert([
            'code' => 'about',
            'name' => 'About',
            'module_type' => 'content',
            'page_title' => json_encode(['vi' => 'Giới thiệu'], JSON_UNESCAPED_UNICODE),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function page(
        string $code,
        string $template,
        string $title,
        int $sortOrder = 0,
        bool $active = true,
    ): Page {
        return Page::create([
            'code' => $code,
            'template' => $template,
            'title' => ['vi' => $title],
            'slug' => ['vi' => strtolower(str_replace('_', '-', $code))],
            'sort_order' => $sortOrder,
            'is_active' => $active,
        ]);
    }
}
