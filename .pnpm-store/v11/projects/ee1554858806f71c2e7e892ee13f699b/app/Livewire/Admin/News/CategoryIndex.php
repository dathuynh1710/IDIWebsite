<?php

namespace App\Livewire\Admin\News;

use App\Models\PostCategory;
use App\Support\PostRoutes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class CategoryIndex extends Component
{
    use WithPagination;

    #[Url(except: '')] public string $search = '';
    #[Url(except: '')] public string $active = '';
    #[Url(except: 'vi')] public string $locale = 'vi';
    public array $selected = [];
    public array $sortOrders = [];

    public function mount(): void
    {
        Gate::authorize('posts.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'active', 'locale'], true)) {
            $this->resetPage();
        }
    }

    public function toggleVisibility(int $id): void
    {
        Gate::authorize('posts.update');
        $category = PostCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active, 'updated_by' => auth()->id()]);
        PostRoutes::syncCategory($category);
    }

    public function delete(int $id): void
    {
        Gate::authorize('posts.delete');
        $category = PostCategory::withCount('posts')->findOrFail($id);
        abort_if($category->posts_count > 0, 422, 'Danh mục còn tin tức.');
        $category->delete();
        DB::table('localized_routes')->where('routeable_type', PostCategory::class)->where('routeable_id', $id)->delete();
        $this->dispatch('admin-toast', message: 'Đã chuyển danh mục vào thùng rác.', type: 'success');
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'sortOrders.*' => ['nullable', 'integer', 'min:0']]);
        Gate::authorize($action === 'delete' ? 'posts.delete' : 'posts.update');
        abort_unless(in_array($action, ['show', 'hide', 'reorder', 'delete'], true), 422);
        foreach (PostCategory::whereKey($this->selected)->withCount('posts')->get() as $category) {
            if ($action === 'delete' && $category->posts_count === 0) {
                $category->delete();
            } elseif ($action === 'reorder') {
                $category->update(['sort_order' => (int) ($this->sortOrders[$category->id] ?? 0)]);
            } elseif (in_array($action, ['show', 'hide'], true)) {
                $category->update(['is_active' => $action === 'show']);
                PostRoutes::syncCategory($category);
            }
        }
        $this->selected = [];
    }

    public function render()
    {
        $categories = PostCategory::query()->with('parent')->withCount('posts')
            ->filtered(trim($this->search), $this->active, $this->locale)
            ->orderBy('parent_id')->orderByDesc('sort_order')->paginate(15);
        foreach ($categories as $category) {
            $this->sortOrders[$category->id] ??= $category->sort_order;
        }

        return view('livewire.admin.news.category-index', compact('categories') + [
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tin tức'], ['label' => 'Danh mục']],
        ]);
    }
}
