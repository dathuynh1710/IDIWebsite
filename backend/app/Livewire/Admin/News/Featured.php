<?php

namespace App\Livewire\Admin\News;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Featured extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';
    public string $locale = 'vi';
    public array $selected = [];
    public array $featuredSelected = [];
    public array $sortOrders = [];

    public function mount(): void
    {
        Gate::authorize('posts.update');
    }

    public function addFeatured(): void
    {
        $limit = $this->limit();
        $current = Post::where('is_featured', true)->count();
        $ids = array_slice(array_values(array_diff($this->selected, Post::where('is_featured', true)->pluck('id')->all())), 0, max(0, $limit - $current));
        Post::whereKey($ids)->update(['is_featured' => true]);
        $this->selected = [];
        $this->dispatch('admin-toast', message: 'Đã cập nhật danh sách tin tiêu điểm.', type: 'success');
    }

    public function removeFeatured(): void
    {
        Post::whereKey($this->featuredSelected)->update(['is_featured' => false]);
        $this->featuredSelected = [];
    }

    public function updateOrder(): void
    {
        foreach ($this->sortOrders as $id => $order) {
            Post::whereKey($id)->where('is_featured', true)->update(['sort_order' => max(0, (int) $order)]);
        }
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
            ->latest()->paginate(12);
        return view('livewire.admin.news.featured', [
            'featured' => $featured, 'available' => $available, 'limit' => $this->limit(),
            'categories' => PostCategory::where('is_active', true)->get(),
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tin tức'], ['label' => 'Tin tiêu điểm']],
        ]);
    }
}
