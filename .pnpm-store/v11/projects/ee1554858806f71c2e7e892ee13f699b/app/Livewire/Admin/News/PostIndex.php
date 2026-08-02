<?php

namespace App\Livewire\Admin\News;

use App\Models\Post;
use App\Models\PostCategory;
use App\Support\PostRoutes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class PostIndex extends Component
{
    use WithPagination;

    #[Url(except: '')] public string $search = '';
    #[Url(except: '')] public string $category = '';
    #[Url(except: '')] public string $active = '';
    #[Url(except: '')] public string $date_from = '';
    #[Url(except: '')] public string $date_to = '';
    #[Url(except: 'vi')] public string $locale = 'vi';
    public array $selected = [];
    public array $sortOrders = [];

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

    public function toggleVisibility(int $id): void
    {
        Gate::authorize('posts.update');
        $post = Post::findOrFail($id);
        $post->update(['is_active' => ! $post->is_active, 'updated_by' => auth()->id()]);
        PostRoutes::syncPost($post);
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
        $this->dispatch('admin-toast', message: 'Đã nhân bản tin dưới dạng bản nháp.', type: 'success');
    }

    public function delete(int $id): void
    {
        Gate::authorize('posts.delete');
        Post::findOrFail($id)->delete();
        DB::table('localized_routes')->where('routeable_type', Post::class)->where('routeable_id', $id)->delete();
        $this->dispatch('admin-toast', message: 'Đã chuyển tin vào thùng rác.', type: 'success');
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'sortOrders.*' => ['nullable', 'integer', 'min:0']]);
        Gate::authorize($action === 'delete' ? 'posts.delete' : 'posts.update');
        abort_unless(in_array($action, ['show', 'hide', 'reorder', 'delete'], true), 422);
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
    }

    public function render()
    {
        $perPage = (int) (DB::table('module_settings')->join('modules', 'modules.id', '=', 'module_settings.module_id')
            ->where('modules.code', 'news')->where('setting_key', 'items_per_page')->value('setting_value') ?: 12);
        $posts = Post::with(['category', 'featuredMedia', 'author'])
            ->filtered(['search' => trim($this->search), 'category' => $this->category, 'active' => $this->active, 'date_from' => $this->date_from, 'date_to' => $this->date_to, 'locale' => $this->locale])
            ->orderByDesc('sort_order')->latest('updated_at')->paginate($perPage);
        foreach ($posts as $post) {
            $this->sortOrders[$post->id] ??= $post->sort_order;
        }
        return view('livewire.admin.news.post-index', [
            'posts' => $posts, 'categories' => PostCategory::orderBy('sort_order')->get(),
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quản lý tin tức']],
        ]);
    }
}
