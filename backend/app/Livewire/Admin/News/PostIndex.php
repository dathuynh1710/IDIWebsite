<?php

namespace App\Livewire\Admin\News;

use App\Livewire\AdminComponent;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\PostRoutes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class PostIndex extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: '')]
    public string $active = '';

    #[Url(except: '')]
    public string $date_from = '';

    #[Url(except: '')]
    public string $date_to = '';

    #[Url(except: 'vi')]
    public string $locale = 'vi';

    #[Url(as: 'per_page', except: 10, history: true)]
    public int $perPage = 10;

    public array $selected = [];

    public array $sortOrders = [];

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public bool $pendingBulkDelete = false;

    public function mount(): void
    {
        Gate::authorize('posts.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'category', 'active', 'date_from', 'date_to', 'locale'], true)) {
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
        $post = Post::findOrFail($id);
        $post->update(['is_active' => ! $post->is_active, 'updated_by' => auth()->id()]);
        PostRoutes::syncPost($post);
        $this->toastState($post->is_active, 'tin tức');
    }

    public function duplicate(int $id): void
    {
        Gate::authorize('posts.create');
        $source = Post::findOrFail($id);
        $post = $source->replicate();
        $suffix = now()->format('His');
        $post->title = collect($source->getTranslations('title'))->map(fn ($v) => "{$v} (Bản sao)")->all();
        $post->slug = collect($source->getTranslations('slug'))->map(fn ($v) => Str::limit("{$v}-copy-{$suffix}", 255, ''))->all();
        $post->translation_status = ['vi' => 'draft', 'en' => 'draft', 'zh' => 'draft'];
        $post->locale_published_at = [];
        $post->is_active = false;
        $post->is_featured = false;
        $post->created_by = auth()->id();
        $post->updated_by = auth()->id();
        $post->save();
        PostRoutes::syncPost($post);
        $this->toast('Đã nhân bản tin dưới dạng bản nháp.');
    }

    public function delete(int $id): void
    {
        Gate::authorize('posts.delete');
        Post::findOrFail($id)->delete();
        DB::table('localized_routes')->where('routeable_type', Post::class)->where('routeable_id', $id)->delete();
        $this->toast('Đã chuyển tin vào thùng rác.');
    }

    public function requestDelete(int $id): void
    {
        Gate::authorize('posts.delete');
        $post = Post::findOrFail($id);

        $this->pendingDeleteId = $post->id;
        $this->pendingDeleteName = $post->getTranslation('title', $this->locale, false)
            ?: $post->getTranslation('title', 'vi', false)
            ?: '#'.$post->id;
        $this->pendingBulkDelete = false;
    }

    public function requestBulkDelete(): void
    {
        Gate::authorize('posts.delete');
        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'distinct', 'exists:posts,id'],
        ], ['selected.required' => 'Vui lòng chọn ít nhất một tin tức.']);

        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
        $this->pendingBulkDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->resetDeleteConfirmation();
    }

    public function confirmDelete(): void
    {
        Gate::authorize('posts.delete');

        if ($this->pendingBulkDelete) {
            $this->validate([
                'selected' => ['required', 'array', 'min:1'],
                'selected.*' => ['integer', 'distinct', 'exists:posts,id'],
            ]);

            DB::transaction(function (): void {
                foreach (Post::whereKey($this->selected)->get() as $post) {
                    $post->delete();
                    DB::table('localized_routes')->where('routeable_type', Post::class)->where('routeable_id', $post->id)->delete();
                }
            });
            $this->selected = [];
            $this->resetDeleteConfirmation();
            $this->toast('Đã chuyển các tin được chọn vào thùng rác.');

            return;
        }

        if (! $this->pendingDeleteId) {
            $this->toast('Không tìm thấy tin tức cần xóa. Vui lòng thử lại.', 'error');
            $this->cancelDelete();

            return;
        }
        $postId = $this->pendingDeleteId;
        $this->delete($postId);
        $this->selected = array_values(array_filter(
            $this->selected,
            fn ($selectedId) => (int) $selectedId !== $postId,
        ));
        $this->resetDeleteConfirmation();
    }

    private function resetDeleteConfirmation(): void
    {
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
        $this->pendingBulkDelete = false;
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'sortOrders.*' => ['nullable', 'integer', 'min:0']]);
        Gate::authorize($action === 'delete' ? 'posts.delete' : 'posts.update');
        if (! in_array($action, ['show', 'hide', 'reorder', 'delete'], true)) {
            $this->toast('Thao tác với tin tức không hợp lệ.', 'error');

            return;
        }
        foreach (Post::whereKey($this->selected)->get() as $post) {
            if ($action === 'delete') {
                $post->delete();
            } elseif ($action === 'reorder') {
                $post->update(['sort_order' => (int) ($this->sortOrders[$post->id] ?? 0)]);
            } else {
                $post->update(['is_active' => $action === 'show']);
                PostRoutes::syncPost($post);
            }
        }
        $this->selected = [];
        $this->toastBulk($action, 'tin tức');
    }

    public function render()
    {
        $posts = Post::with(['category', 'featuredMedia', 'author'])
            ->filtered(['search' => trim($this->search), 'category' => $this->category, 'active' => $this->active, 'date_from' => $this->date_from, 'date_to' => $this->date_to, 'locale' => $this->locale])
            ->orderByDesc('sort_order')->latest('updated_at')->paginate($this->perPage);
        foreach ($posts as $post) {
            $this->sortOrders[$post->id] ??= $post->sort_order;
        }

        return view('livewire.admin.news.post-index', [
            'posts' => $posts, 'categories' => PostCategory::orderBy('sort_order')->get(),
            'perPageOptions' => [10, 20, 50, 100],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quản lý tin tức']],
        ]);
    }
}
