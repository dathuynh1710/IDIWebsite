<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use App\Models\InvestorDocument;
use App\Models\JobApplication;
use App\Models\JobPosition;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.admin')]
#[Title('Bảng điều khiển')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $modules = $this->availableModules($user);
        $reviewCount = $modules->sum(fn (array $module): int => $module['reviewable'] ? $this->reviewCount($module['model']) : 0);
        $activeCount = $modules->sum(fn (array $module): int => $module['model']::where('is_active', true)->count());

        $stats = collect([
            $user->can('contacts.manage') ? [
                'label' => 'Thư liên hệ chưa xem',
                'value' => ContactMessage::where('status', 'new')->count(),
                'icon' => 'mail',
                'tone' => 'blue',
                'route' => 'admin.contacts.index',
                'hint' => 'Cần phản hồi',
            ] : null,
            $user->can('recruitment.view') ? [
                'label' => 'Hồ sơ ứng tuyển mới',
                'value' => JobApplication::where('status', 'new')->count(),
                'icon' => 'users',
                'tone' => 'green',
                'route' => 'admin.recruitment.applications.index',
                'hint' => 'Chờ xem xét',
            ] : null,
            $modules->isNotEmpty() ? [
                'label' => 'Bản dịch cần duyệt',
                'value' => $reviewCount,
                'icon' => 'languages',
                'tone' => 'orange',
                'route' => null,
                'hint' => 'Trên các nội dung',
            ] : null,
            $modules->isNotEmpty() ? [
                'label' => 'Nội dung đang hiển thị',
                'value' => $activeCount,
                'icon' => 'eye',
                'tone' => 'purple',
                'route' => null,
                'hint' => 'Đang hoạt động',
            ] : null,
        ])->filter()->values();

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'moduleProgress' => $this->moduleProgress($modules),
            'recentItems' => $this->recentItems($modules),
            'recentActivities' => $user->can('activity.view')
                ? Activity::with('causer')->where('log_name', 'admin')->latest()->limit(5)->get()
                : collect(),
            'quickActions' => $this->quickActions($user),
            'breadcrumbs' => [['label' => 'Bảng điều khiển']],
        ]);
    }

    private function availableModules($user): Collection
    {
        return collect([
            ['label' => 'Sản phẩm', 'model' => Product::class, 'permission' => 'products.view', 'update_permission' => 'products.update', 'route' => 'admin.products.index', 'edit_route' => 'admin.products.edit', 'icon' => 'package', 'reviewable' => true],
            ['label' => 'Tin tức', 'model' => Post::class, 'permission' => 'posts.view', 'update_permission' => 'posts.update', 'route' => 'admin.news.posts.index', 'edit_route' => 'admin.news.posts.edit', 'icon' => 'newspaper', 'reviewable' => true],
            ['label' => 'Recipes', 'model' => Recipe::class, 'permission' => 'recipes.view', 'update_permission' => 'recipes.update', 'route' => 'admin.recipes.index', 'edit_route' => 'admin.recipes.edit', 'icon' => 'book-open', 'reviewable' => true],
            ['label' => 'Quan hệ cổ đông', 'model' => InvestorDocument::class, 'permission' => 'investors.view', 'update_permission' => 'investors.update', 'route' => 'admin.investors.documents.index', 'edit_route' => 'admin.investors.documents.edit', 'icon' => 'chart', 'reviewable' => false],
            ['label' => 'Giới thiệu', 'model' => Page::class, 'permission' => 'pages.view', 'update_permission' => 'pages.update', 'route' => 'admin.about-pages.index', 'edit_route' => 'admin.about-pages.edit', 'icon' => 'info', 'reviewable' => true],
            ['label' => 'Tuyển dụng', 'model' => JobPosition::class, 'permission' => 'recruitment.view', 'update_permission' => 'recruitment.update', 'route' => 'admin.recruitment.positions.index', 'edit_route' => 'admin.recruitment.positions.edit', 'icon' => 'briefcase', 'reviewable' => true],
        ])->filter(fn (array $module): bool => $user->can($module['permission']))
            ->map(fn (array $module): array => array_merge($module, ['can_update' => $user->can($module['update_permission'])]))
            ->values();
    }

    private function moduleProgress(Collection $modules): Collection
    {
        return $modules->map(function (array $module): array {
            $total = $module['model']::count();
            $active = $module['model']::where('is_active', true)->count();

            return array_merge($module, [
                'total' => $total,
                'active' => $active,
                'percent' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
            ]);
        });
    }

    private function recentItems(Collection $modules): Collection
    {
        return $modules->flatMap(function (array $module): Collection {
            return $module['model']::latest('updated_at')->limit(3)->get()->map(fn (Model $item): array => [
                'title' => $this->itemTitle($item),
                'module' => $module['label'],
                'icon' => $module['icon'],
                'url' => $module['can_update'] ? route($module['edit_route'], $item) : route($module['route']),
                'updated_at' => $item->updated_at,
                'active' => (bool) $item->is_active,
            ]);
        })->sortByDesc('updated_at')->take(6)->values();
    }

    private function itemTitle(Model $item): string
    {
        foreach (['title', 'name'] as $field) {
            $value = $item->getAttribute($field);

            if (is_array($value)) {
                $value = $value['vi'] ?? collect($value)->first();
            }

            if (is_string($value) && trim($value) !== '') {
                return strip_tags($value);
            }
        }

        return (string) ($item->getAttribute('code') ?: $item->getAttribute('sku') ?: '#'.$item->getKey());
    }

    private function reviewCount(string $model): int
    {
        return $model::where('translation_status', 'like', '%review%')->count();
    }

    private function quickActions($user): Collection
    {
        return collect([
            ['permission' => 'posts.create', 'label' => 'Viết tin mới', 'description' => 'Tạo và xuất bản tin tức', 'route' => 'admin.news.posts.create', 'icon' => 'newspaper'],
            ['permission' => 'products.create', 'label' => 'Thêm sản phẩm', 'description' => 'Bổ sung sản phẩm vào danh mục', 'route' => 'admin.products.create', 'icon' => 'package'],
            ['permission' => 'recipes.create', 'label' => 'Thêm Recipe', 'description' => 'Chia sẻ công thức chế biến', 'route' => 'admin.recipes.create', 'icon' => 'book-open'],
            ['permission' => 'investor-documents.create', 'label' => 'Thêm tài liệu cổ đông', 'description' => 'Đăng báo cáo hoặc công bố', 'route' => 'admin.investors.documents.create', 'icon' => 'file'],
            ['permission' => 'recruitment.create', 'label' => 'Đăng tuyển dụng', 'description' => 'Tạo vị trí tuyển dụng mới', 'route' => 'admin.recruitment.positions.create', 'icon' => 'briefcase'],
            ['permission' => 'contacts.manage', 'label' => 'Xem thư liên hệ', 'description' => 'Tiếp nhận yêu cầu mới', 'route' => 'admin.contacts.index', 'icon' => 'mail'],
        ])->filter(fn (array $action): bool => $user->can($action['permission']))->take(4)->values();
    }
}
