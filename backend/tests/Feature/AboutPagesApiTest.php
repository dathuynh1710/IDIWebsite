<?php

namespace Tests\Feature;

use App\Models\Page;
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

    public function test_page_can_be_resolved_by_code_or_localized_slug_and_falls_back_to_vietnamese(): void
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
            ->assertOk()
            ->assertJsonPath('data.locale', 'vi')
            ->assertJsonPath('data.requestedLocale', 'en')
            ->assertJsonPath('data.content', '<p>Nội dung được quản lý từ CMS.</p>')
            ->assertJsonPath('data.seo.title', 'Thông điệp IDI');

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
