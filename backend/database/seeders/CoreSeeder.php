<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\InteractsWithSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CoreSeeder extends Seeder
{
    use InteractsWithSeedData;

    public function run(): void
    {
        $this->seedLocales();
        $adminId = $this->seedAdmin();
        $this->seedPermissions($adminId);
        $media = $this->seedMedia($adminId);
        $this->seedModules();
        $this->seedModuleSettings();

        DB::table('users')->where('id', $adminId)->update([
            'avatar_media_id' => $media['avatar'],
            'updated_at' => now(),
        ]);
    }

    private function seedLocales(): void
    {
        foreach ([
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'sort_order' => 0, 'is_default' => true],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'sort_order' => 1, 'is_default' => false],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'sort_order' => 2, 'is_default' => false],
        ] as $locale) {
            DB::table('locales')->updateOrInsert(
                ['code' => $locale['code']],
                array_merge($locale, [
                    'direction' => 'ltr',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    private function seedAdmin(): int
    {
        return $this->upsertId('users', ['email' => 'admin@idiseafood.local'], [
            'name' => 'IDI Seafood Administrator',
            'username' => 'admin',
            'email_verified_at' => now(),
            'password' => Hash::make('idi686868'),
            'is_active' => true,
            'remember_token' => null,
            'deleted_at' => null,
        ]);
    }

    private function seedPermissions(int $adminId): void
    {
        $permissionIds = [];

        foreach (config('access-control.permissions') as $permission) {
            $permissionIds[] = $this->upsertId('permissions', [
                'name' => $permission['name'],
                'guard_name' => 'web',
            ], [
                'display_name' => $permission['label'],
                'module' => $permission['module'],
                'description' => null,
                'is_system' => true,
            ]);
        }

        $adminRoleId = $this->upsertId('roles', [
            'name' => 'super-admin',
            'guard_name' => 'web',
        ], [
            'display_name' => 'Quản trị cao nhất',
            'description' => 'Có toàn quyền trên hệ thống và không thể bị xóa.',
            'is_system' => true,
        ]);

        $editorRoleId = $this->upsertId('roles', [
            'name' => 'editor',
            'guard_name' => 'web',
        ], [
            'display_name' => 'Biên tập viên',
            'description' => 'Quản lý nội dung website theo quyền được cấp.',
            'is_system' => true,
        ]);

        foreach ($permissionIds as $permissionId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $adminRoleId,
            ]);
        }

        $editorPermissions = DB::table('permissions')
            ->whereIn('name', [
                'dashboard.view',
                'media.manage',
                'products.manage',
                'posts.manage',
                'pages.manage',
                'recipes.manage',
            ])
            ->pluck('id');

        foreach ($editorPermissions as $permissionId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $editorRoleId,
            ]);
        }

        DB::table('model_has_roles')->updateOrInsert([
            'role_id' => $adminRoleId,
            'model_type' => User::class,
            'model_id' => $adminId,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function seedMedia(int $adminId): array
    {
        $folderIds = [];

        foreach ([
            'products' => ['name' => 'Products', 'path' => '/products'],
            'news' => ['name' => 'News', 'path' => '/news'],
            'documents' => ['name' => 'Documents', 'path' => '/documents'],
            'banners' => ['name' => 'Banners', 'path' => '/banners'],
            'branding' => ['name' => 'Branding', 'path' => '/branding'],
        ] as $key => $folder) {
            $folderIds[$key] = $this->upsertId('media_folders', ['path' => $folder['path']], [
                'parent_id' => null,
                'name' => $folder['name'],
                'sort_order' => 0,
                'created_by' => $adminId,
                'deleted_at' => null,
            ]);
        }

        $definitions = [
            'avatar' => ['folder' => 'branding', 'file' => 'admin-avatar.png', 'mime' => 'image/png', 'title' => ['Quản trị viên', 'Administrator', '管理员']],
            'logo' => ['folder' => 'branding', 'file' => 'idi-logo.png', 'mime' => 'image/png', 'title' => ['Logo IDI Seafood', 'IDI Seafood logo', 'IDI Seafood 标志']],
            'pangasius' => ['folder' => 'products', 'file' => 'pangasius-fillet.jpg', 'mime' => 'image/jpeg', 'title' => ['Cá tra phi lê', 'Pangasius fillet', '巴沙鱼柳']],
            'value_added' => ['folder' => 'products', 'file' => 'breaded-pangasius.jpg', 'mime' => 'image/jpeg', 'title' => ['Cá tra tẩm bột', 'Breaded pangasius', '裹粉巴沙鱼']],
            'news' => ['folder' => 'news', 'file' => 'factory-news.jpg', 'mime' => 'image/jpeg', 'title' => ['Nhà máy IDI', 'IDI factory', 'IDI 工厂']],
            'recipe' => ['folder' => 'products', 'file' => 'grilled-pangasius.jpg', 'mime' => 'image/jpeg', 'title' => ['Cá tra nướng', 'Grilled pangasius', '烤巴沙鱼']],
            'document' => ['folder' => 'documents', 'file' => 'annual-report-2025.pdf', 'mime' => 'application/pdf', 'title' => ['Báo cáo thường niên 2025', 'Annual report 2025', '2025 年年度报告']],
            'catalog' => ['folder' => 'documents', 'file' => 'product-catalog.pdf', 'mime' => 'application/pdf', 'title' => ['Danh mục sản phẩm', 'Product catalog', '产品目录']],
            'banner_desktop' => ['folder' => 'banners', 'file' => 'home-hero-desktop.jpg', 'mime' => 'image/jpeg', 'title' => ['Banner trang chủ', 'Homepage banner', '首页横幅']],
            'banner_mobile' => ['folder' => 'banners', 'file' => 'home-hero-mobile.jpg', 'mime' => 'image/jpeg', 'title' => ['Banner trang chủ mobile', 'Mobile homepage banner', '移动端首页横幅']],
        ];

        $ids = [];
        foreach ($definitions as $key => $media) {
            $extension = pathinfo($media['file'], PATHINFO_EXTENSION);
            $ids[$key] = $this->upsertId('media', [
                'disk' => 'public',
                'directory' => $media['folder'],
                'file_name' => $media['file'],
            ], [
                'folder_id' => $folderIds[$media['folder']],
                'original_name' => $media['file'],
                'mime_type' => $media['mime'],
                'extension' => $extension,
                'file_size' => null,
                'width' => str_starts_with($media['mime'], 'image/') ? 1600 : null,
                'height' => str_starts_with($media['mime'], 'image/') ? 900 : null,
                'checksum' => null,
                'title' => $this->translations(...$media['title']),
                'alt_text' => str_starts_with($media['mime'], 'image/')
                    ? $this->translations(...$media['title'])
                    : null,
                'caption' => null,
                'created_by' => $adminId,
                'deleted_at' => null,
            ]);
        }

        return $ids;
    }

    private function seedModules(): void
    {
        foreach ([
            ['code' => 'products', 'name' => 'Products', 'type' => 'catalog', 'title' => ['Sản phẩm', 'Products', '产品']],
            ['code' => 'recipes', 'name' => 'Recipes', 'type' => 'content', 'title' => ['Công thức bạn có thể thử', 'Recipes you can try', '值得尝试的食谱']],
            ['code' => 'about', 'name' => 'About', 'type' => 'content', 'title' => ['Giới thiệu', 'About us', '关于我们']],
            ['code' => 'news', 'name' => 'News', 'type' => 'content', 'title' => ['Tin tức', 'News', '新闻']],
            ['code' => 'investors', 'name' => 'Investor Relations', 'type' => 'documents', 'title' => ['Quan hệ cổ đông', 'Investor Relations', '投资者关系']],
            ['code' => 'careers', 'name' => 'Careers', 'type' => 'recruitment', 'title' => ['Tuyển dụng', 'Careers', '招聘']],
        ] as $module) {
            $this->upsertId('modules', ['code' => $module['code']], [
                'name' => $module['name'],
                'module_type' => $module['type'],
                'page_title' => $this->translations(...$module['title']),
                'description' => null,
                'seo_title' => $this->translations(...$module['title']),
                'meta_description' => null,
                'og_title' => null,
                'og_description' => null,
                'is_active' => true,
            ]);
        }
    }

    private function seedModuleSettings(): void
    {
        foreach ([
            ['module' => 'products', 'key' => 'items_per_page', 'value' => 12, 'type' => 'number'],
            ['module' => 'recipes', 'key' => 'items_per_page', 'value' => 12, 'type' => 'number'],
            ['module' => 'recipes', 'key' => 'show_placeholder_image', 'value' => true, 'type' => 'boolean'],
            ['module' => 'recipes', 'key' => 'thumbnail_size', 'value' => 180, 'type' => 'number'],
            ['module' => 'recipes', 'key' => 'max_upload_width', 'value' => 1600, 'type' => 'number'],
            ['module' => 'about', 'key' => 'items_per_page', 'value' => 10, 'type' => 'number'],
            ['module' => 'about', 'key' => 'show_placeholder_image', 'value' => true, 'type' => 'boolean'],
            ['module' => 'about', 'key' => 'thumbnail_size', 'value' => 150, 'type' => 'number'],
            ['module' => 'about', 'key' => 'max_upload_width', 'value' => 1200, 'type' => 'number'],
            ['module' => 'news', 'key' => 'items_per_page', 'value' => 9, 'type' => 'number'],
            ['module' => 'investors', 'key' => 'default_year', 'value' => 2025, 'type' => 'number'],
            ['module' => 'careers', 'key' => 'application_enabled', 'value' => true, 'type' => 'boolean'],
        ] as $setting) {
            $moduleId = (int) DB::table('modules')->where('code', $setting['module'])->value('id');
            $this->upsertId('module_settings', [
                'module_id' => $moduleId,
                'setting_key' => $setting['key'],
            ], [
                'setting_value' => json_encode($setting['value'], JSON_THROW_ON_ERROR),
                'setting_type' => $setting['type'],
            ]);
        }
    }
}
