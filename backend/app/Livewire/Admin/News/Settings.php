<?php

namespace App\Livewire\Admin\News;

use App\Livewire\AdminComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class Settings extends AdminComponent
{
    public array $page_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_keywords = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $og_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $og_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public bool $module_enabled = true;

    public int $items_per_page = 12;

    public int $category_items_per_page = 10;

    public int $archive_items_per_page = 10;

    public int $featured_limit = 3;

    public int $related_limit = 6;

    public int $thumbnail_size = 320;

    public int $thumbnail_height = 200;

    public int $max_upload_width = 1600;

    public int $image_quality = 85;

    public int $detail_image_width = 1200;

    public int $detail_image_height = 675;

    public bool $crop_images = true;

    public bool $watermark_enabled = false;

    public bool $show_featured_section = true;

    public bool $show_category_navigation = true;

    public bool $show_related_articles = true;

    public bool $show_author = true;

    public bool $show_published_date = true;

    public bool $show_view_count = true;

    public bool $show_reading_time = true;

    public bool $show_tags = true;

    public bool $show_article_source = true;

    public bool $show_breadcrumb = true;

    public bool $show_social_share = true;

    public bool $show_previous_next = true;

    public bool $show_placeholder_image = true;

    public bool $allow_print = true;

    public bool $allow_comments = false;

    public bool $moderate_comments = true;

    public bool $comment_spam_protection = true;

    public bool $fetch_remote_images = false;

    public int $max_upload_size = 10;

    public string $allowed_file_types = 'jpg, jpeg, png, webp';

    public bool $auto_rename_files = true;

    public bool $allow_webp = true;

    public bool $allow_svg = false;

    public bool $rebuild_seo_links = false;

    public bool $cache_homepage = true;

    public bool $cache_category = true;

    public bool $cache_detail = true;

    public bool $lazy_load_images = true;

    public bool $performance_webp = true;

    public bool $sitemap_enabled = true;

    public function mount(): void
    {
        Gate::authorize('posts.update');
        $module = DB::table('modules')->where('code', 'news')->first();
        if (! $module) {
            return;
        }
        $this->module_enabled = (bool) $module->is_active;
        foreach (['page_title', 'description', 'seo_title', 'meta_description', 'og_title', 'og_description'] as $field) {
            $this->{$field} = array_merge(['vi' => '', 'en' => '', 'zh' => ''], json_decode($module->{$field} ?: '{}', true) ?: []);
        }
        $settings = DB::table('module_settings')->where('module_id', $module->id)->pluck('setting_value', 'setting_key');
        foreach ($settings as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = json_decode($value, true);
            }
        }
    }

    public function save(): void
    {
        Gate::authorize('posts.update');
        $data = $this->validate([
            'page_title.vi' => ['required', 'string', 'max:255'], 'page_title.en' => ['nullable', 'string', 'max:255'], 'page_title.zh' => ['nullable', 'string', 'max:255'],
            'description.*' => ['nullable', 'string', 'max:2000'],
            'seo_title.*' => ['nullable', 'string', 'max:255'], 'meta_description.*' => ['nullable', 'string', 'max:500'], 'meta_keywords.*' => ['nullable', 'string', 'max:1000'],
            'og_title.*' => ['nullable', 'string', 'max:255'], 'og_description.*' => ['nullable', 'string', 'max:500'],
            'module_enabled' => ['boolean'],
            'items_per_page' => ['required', 'integer', 'min:1', 'max:100'], 'category_items_per_page' => ['required', 'integer', 'min:1', 'max:100'],
            'archive_items_per_page' => ['required', 'integer', 'min:1', 'max:100'], 'featured_limit' => ['required', 'integer', 'min:1', 'max:20'], 'related_limit' => ['required', 'integer', 'min:0', 'max:30'],
            'thumbnail_size' => ['required', 'integer', 'min:100', 'max:2000'], 'thumbnail_height' => ['required', 'integer', 'min:100', 'max:2000'],
            'max_upload_width' => ['required', 'integer', 'min:320', 'max:5000'], 'image_quality' => ['required', 'integer', 'min:40', 'max:100'],
            'detail_image_width' => ['required', 'integer', 'min:320', 'max:5000'], 'detail_image_height' => ['required', 'integer', 'min:180', 'max:5000'],
            'crop_images' => ['boolean'], 'watermark_enabled' => ['boolean'],
            'show_featured_section' => ['boolean'], 'show_category_navigation' => ['boolean'], 'show_related_articles' => ['boolean'],
            'show_author' => ['boolean'], 'show_published_date' => ['boolean'], 'show_view_count' => ['boolean'], 'show_reading_time' => ['boolean'],
            'show_tags' => ['boolean'], 'show_article_source' => ['boolean'], 'show_breadcrumb' => ['boolean'], 'show_social_share' => ['boolean'],
            'show_previous_next' => ['boolean'], 'show_placeholder_image' => ['boolean'], 'allow_print' => ['boolean'],
            'allow_comments' => ['boolean'], 'moderate_comments' => ['boolean'], 'comment_spam_protection' => ['boolean'], 'fetch_remote_images' => ['boolean'],
            'max_upload_size' => ['required', 'integer', 'min:1', 'max:100'], 'allowed_file_types' => ['required', 'string', 'max:255'],
            'auto_rename_files' => ['boolean'], 'allow_webp' => ['boolean'], 'allow_svg' => ['boolean'], 'rebuild_seo_links' => ['boolean'],
            'cache_homepage' => ['boolean'], 'cache_category' => ['boolean'], 'cache_detail' => ['boolean'], 'lazy_load_images' => ['boolean'],
            'performance_webp' => ['boolean'], 'sitemap_enabled' => ['boolean'],
        ], [], [
            'page_title.vi'       => 'Tiêu đề trang (Tiếng Việt)',
            'page_title.en'       => 'Tiêu đề trang (English)',
            'page_title.zh'       => 'Tiêu đề trang (中文)',
            'description.vi'      => 'Mô tả (Tiếng Việt)',
            'description.en'      => 'Mô tả (English)',
            'description.zh'      => 'Mô tả (中文)',
            'seo_title.vi'        => 'Tiêu đề SEO (Tiếng Việt)',
            'seo_title.en'        => 'Tiêu đề SEO (English)',
            'seo_title.zh'        => 'Tiêu đề SEO (中文)',
            'meta_description.vi' => 'Meta description (Tiếng Việt)',
            'meta_description.en' => 'Meta description (English)',
            'meta_description.zh' => 'Meta description (中文)',
            'meta_keywords.vi'    => 'Từ khóa meta (Tiếng Việt)',
            'meta_keywords.en'    => 'Từ khóa meta (English)',
            'meta_keywords.zh'    => 'Từ khóa meta (中文)',
            'og_title.vi'         => 'OG Title (Tiếng Việt)',
            'og_title.en'         => 'OG Title (English)',
            'og_title.zh'         => 'OG Title (中文)',
            'og_description.vi'   => 'OG Description (Tiếng Việt)',
            'og_description.en'   => 'OG Description (English)',
            'og_description.zh'   => 'OG Description (中文)',
            'items_per_page'          => 'Bài viết mỗi trang',
            'category_items_per_page' => 'Bài viết mỗi trang (danh mục)',
            'archive_items_per_page'  => 'Bài viết mỗi trang (lưu trữ)',
            'featured_limit'          => 'Số bài viết nổi bật',
            'related_limit'           => 'Số bài viết liên quan',
            'items_per_page'          => 'Bài viết mỗi trang',
            'max_upload_size'         => 'Dung lượng tải lên tối đa (MB)',
            'allowed_file_types'      => 'Định dạng file cho phép',
        ]);
        DB::table('modules')->updateOrInsert(['code' => 'news'], [
            'name' => 'News', 'module_type' => 'content',
            'page_title' => json_encode($data['page_title'], JSON_UNESCAPED_UNICODE),
            'description' => json_encode($data['description'], JSON_UNESCAPED_UNICODE),
            'seo_title' => json_encode($data['seo_title'], JSON_UNESCAPED_UNICODE),
            'meta_description' => json_encode($data['meta_description'], JSON_UNESCAPED_UNICODE),
            'og_title' => json_encode($data['og_title'], JSON_UNESCAPED_UNICODE),
            'og_description' => json_encode($data['og_description'], JSON_UNESCAPED_UNICODE),
            'is_active' => $data['module_enabled'], 'updated_at' => now(), 'created_at' => now(),
        ]);
        $moduleId = DB::table('modules')->where('code', 'news')->value('id');
        $moduleFields = ['page_title', 'description', 'seo_title', 'meta_description', 'og_title', 'og_description', 'module_enabled'];
        foreach (array_diff_key($data, array_flip($moduleFields)) as $key => $value) {
            $type = is_bool($value) ? 'boolean' : (is_int($value) ? 'number' : 'json');
            DB::table('module_settings')->updateOrInsert(
                ['module_id' => $moduleId, 'setting_key' => $key],
                ['setting_value' => json_encode($value, JSON_UNESCAPED_UNICODE), 'setting_type' => $type, 'updated_at' => now(), 'created_at' => now()]
            );
        }
        $this->toast('Đã lưu cấu hình tin tức.');
    }

    public function render()
    {
        return view('livewire.admin.news.settings', [
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tin tức'], ['label' => 'Cấu hình']],
        ]);
    }
}
