<?php

namespace App\Livewire\Admin\News;

use App\Livewire\AdminComponent;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Featured extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: 'vi')]
    public string $locale = 'vi';

    #[Url(as: 'per_page', except: 10, history: true)]
    public int $perPage = 10;

    public array $selected = [];

    public array $featuredSelected = [];

    public array $sortOrders = [];

    public function mount(): void
    {
        Gate::authorize('posts.update');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'category', 'locale'], true)) {
            $this->selected = [];
            $this->resetPage('availablePage');
        }
    }

    public function updatedPerPage($value): void
    {
        $perPage = (int) $value;
        $this->perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 10;
        $this->selected = [];
        $this->resetPage('availablePage');
    }

    public function addFeatured(): void
    {
        Gate::authorize('posts.update');
        if ($this->selected === []) {
            return;
        }
        $limit = $this->limit();
        $current = Post::where('is_featured', true)->count();
        $ids = array_slice(array_values(array_diff($this->selected, Post::where('is_featured', true)->pluck('id')->all())), 0, max(0, $limit - $current));
        Post::whereKey($ids)->update(['is_featured' => true]);
        $this->selected = [];
        $this->resetPage('availablePage');
        $message = $ids === [] ? 'Danh sách tin tiêu điểm đã đủ vị trí.' : 'Đã thêm '.count($ids).' tin vào khu vực tiêu điểm.';
        $this->toast($message, $ids === [] ? 'error' : 'success');
    }

    public function removeFeatured(): void
    {
        Gate::authorize('posts.update');
        Post::whereKey($this->featuredSelected)->update(['is_featured' => false]);
        $this->featuredSelected = [];
        $this->toast('Đã bỏ các tin được chọn khỏi khu vực tiêu điểm.');
    }

    public function updateOrder(): void
    {
        Gate::authorize('posts.update');
        foreach ($this->sortOrders as $id => $order) {
            Post::whereKey($id)->where('is_featured', true)->update(['sort_order' => max(0, (int) $order)]);
        }
        $this->toast('Đã cập nhật thứ tự tin tiêu điểm.');
    }

    private function limit(): int
    {
        return (int) (DB::table('module_settings')->join('modules', 'modules.id', '=', 'module_settings.module_id')
            ->where('modules.code', 'news')->where('setting_key', 'featured_limit')->value('setting_value') ?: 3);
    }

    public function render()
    {
        $featured = Post::with(['featuredMedia', 'category'])->where('is_featured', true)->orderByDesc('sort_order')->get();
        foreach ($featured as $post) {
            $this->sortOrders[$post->id] ??= $post->sort_order;
        }
        $available = Post::with(['featuredMedia', 'category'])->where('is_active', true)->where('is_featured', false)
            ->when($this->search, fn ($q) => $q->where("title->{$this->locale}", 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('post_category_id', $this->category))
            ->where("translation_status->{$this->locale}", 'published')
            ->latest('updated_at')->paginate($this->perPage, ['*'], 'availablePage');
        $limit = $this->limit();

        return view('livewire.admin.news.featured', [
            'featured' => $featured, 'available' => $available, 'limit' => $limit,
            'remainingSlots' => max(0, $limit - $featured->count()),
            'perPageOptions' => [10, 20, 50, 100],
            'categories' => PostCategory::where('is_active', true)->get(),
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tin tức'], ['label' => 'Tin tiêu điểm']],
        ]);
    }
}
