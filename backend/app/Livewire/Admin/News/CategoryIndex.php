<?php

namespace App\Livewire\Admin\News;

use App\Models\Post;
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
    #[Url(as: 'per_page', except: 10, history: true)] public int $perPage = 10;
    public array $selected = [];
    public array $sortOrders = [];
    public ?int $pendingDeleteId = null;
    public string $pendingDeleteName = '';
    public int $pendingDeletePostsCount = 0;

    public function mount(): void
    {
        Gate::authorize('posts.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'active', 'locale'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function updatedPerPage($value): void
    {
        $perPage = (int) $value;
        $this->perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 10;
        $this->selected = [];
        $this->resetPage();
    }

    public function toggleVisibility(int $id): void
    {
        Gate::authorize('posts.update');
        $category = PostCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active, 'updated_by' => auth()->id()]);
        PostRoutes::syncCategory($category);
    }

    public function requestDelete(int $id): void
    {
        Gate::authorize('posts.delete');
        $category = PostCategory::withCount('posts')->findOrFail($id);

        $this->pendingDeleteId = $category->id;
        $this->pendingDeleteName = $category->getTranslation('name', $this->locale, false)
            ?: $category->getTranslation('name', 'vi', false)
            ?: '#'.$category->id;
        $this->pendingDeletePostsCount = $category->posts_count;
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
        $this->pendingDeletePostsCount = 0;
    }

    public function confirmDelete(): void
    {
        Gate::authorize('posts.delete');
        abort_unless($this->pendingDeleteId, 422);

        $category = PostCategory::findOrFail($this->pendingDeleteId);
        $this->deleteCategorySafely($category);
        $this->cancelDelete();
        $this->dispatch('admin-toast', message: 'Đã chuyển danh mục vào thùng rác.', type: 'success');
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'sortOrders.*' => ['nullable', 'integer', 'min:0']]);
        Gate::authorize($action === 'delete' ? 'posts.delete' : 'posts.update');
        abort_unless(in_array($action, ['show', 'hide', 'reorder', 'delete'], true), 422);
        foreach (PostCategory::whereKey($this->selected)->get() as $category) {
            if ($action === 'delete') {
                $this->deleteCategorySafely($category);
            } elseif ($action === 'reorder') {
                $category->update(['sort_order' => (int) ($this->sortOrders[$category->id] ?? 0)]);
            } elseif (in_array($action, ['show', 'hide'], true)) {
                $category->update(['is_active' => $action === 'show']);
                PostRoutes::syncCategory($category);
            }
        }
        $this->selected = [];
    }

    private function deleteCategorySafely(PostCategory $category): void
    {
        $categoryId = $category->id;
        DB::transaction(function () use ($category, $categoryId): void {
            Post::where('post_category_id', $categoryId)->update([
                'post_category_id' => null,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);
            PostCategory::where('parent_id', $categoryId)->update([
                'parent_id' => null,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);
            $category->delete();
            DB::table('localized_routes')->where('routeable_type', PostCategory::class)->where('routeable_id', $categoryId)->delete();
        });
    }

    public function render()
    {
        $categories = PostCategory::query()->with('parent')->withCount('posts')
            ->filtered(trim($this->search), $this->active, $this->locale)
            ->orderBy('parent_id')->orderByDesc('sort_order')->paginate($this->perPage);
        foreach ($categories as $category) {
            $this->sortOrders[$category->id] ??= $category->sort_order;
        }

        return view('livewire.admin.news.category-index', compact('categories') + [
            'perPageOptions' => [10, 20, 50, 100],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tin tức'], ['label' => 'Danh mục']],
        ]);
    }
}
