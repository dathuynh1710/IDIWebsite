<?php

namespace App\Livewire\Admin\ProductCategories;

use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Danh mục sản phẩm')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public array $selected = [];

    public array $sortOrders = [];

    public bool $showFormModal = false;

    public ?int $editingCategoryId = null;

    public function mount(): void
    {
        Gate::authorize('products.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'status');
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        Gate::authorize('products.create');
        $this->editingCategoryId = null;
        $this->showFormModal = true;
    }

    public function openEditModal(int $categoryId): void
    {
        Gate::authorize('products.update');
        ProductCategory::findOrFail($categoryId);
        $this->editingCategoryId = $categoryId;
        $this->showFormModal = true;
    }

    #[On('category-saved')]
    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->editingCategoryId = null;
    }

    public function toggleVisibility(int $categoryId): void
    {
        Gate::authorize('products.update');
        $category = ProductCategory::findOrFail($categoryId);
        $category->update(['is_active' => ! $category->is_active, 'updated_by' => auth()->id()]);
        $this->toast($category->is_active ? 'Đã hiển thị danh mục.' : 'Đã ẩn danh mục.');
    }

    public function delete(int $categoryId): void
    {
        Gate::authorize('products.delete');
        DB::transaction(function () use ($categoryId): void {
            ProductCategory::where('parent_id', $categoryId)->update(['parent_id' => null]);
            ProductCategory::findOrFail($categoryId)->delete();
        });
        $this->toast('Đã chuyển danh mục vào thùng rác. Sản phẩm liên quan không bị xóa.');
    }

    public function restore(int $categoryId): void
    {
        Gate::authorize('products.delete');
        ProductCategory::onlyTrashed()->findOrFail($categoryId)->restore();
        $this->toast('Đã khôi phục danh mục sản phẩm.');
    }

    public function bulk(string $action): void
    {
        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'distinct'],
            'sortOrders.*' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ], ['selected.required' => 'Vui lòng chọn ít nhất một danh mục.']);

        Gate::authorize($action === 'delete' ? 'products.delete' : 'products.update');
        abort_unless(in_array($action, ['show', 'hide', 'reorder', 'delete'], true), 422);

        DB::transaction(function () use ($action): void {
            if (in_array($action, ['show', 'hide'], true)) {
                ProductCategory::whereKey($this->selected)->update([
                    'is_active' => $action === 'show',
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);
            } elseif ($action === 'reorder') {
                foreach ($this->selected as $id) {
                    if (array_key_exists($id, $this->sortOrders)) {
                        ProductCategory::whereKey($id)->update([
                            'sort_order' => (int) $this->sortOrders[$id],
                            'updated_by' => auth()->id(),
                        ]);
                    }
                }
            } else {
                ProductCategory::whereIn('parent_id', $this->selected)->update(['parent_id' => null]);
                ProductCategory::whereKey($this->selected)->get()->each->delete();
            }
        });

        $this->selected = [];
        $this->toast(match ($action) {
            'show' => 'Đã hiển thị các danh mục đã chọn.',
            'hide' => 'Đã ẩn các danh mục đã chọn.',
            'reorder' => 'Đã cập nhật thứ tự danh mục.',
            'delete' => 'Đã chuyển các danh mục đã chọn vào thùng rác.',
        });
    }

    private function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('admin-toast', message: $message, type: $type);
    }

    public function render()
    {
        $query = ProductCategory::query()->with('parent')->withCount('products');

        match ($this->status) {
            'trashed' => $query->onlyTrashed(),
            'active' => $query->where('is_active', true),
            'hidden' => $query->where('is_active', false),
            default => null,
        };

        if ($search = trim($this->search)) {
            $query->where(fn ($builder) => $builder
                ->where('code', 'like', "%{$search}%")
                ->orWhere('name->vi', 'like', "%{$search}%")
                ->orWhere('slug->vi', 'like', "%{$search}%"));
        }

        $categories = $query->orderBy('sort_order')->latest('updated_at')->paginate(20);
        foreach ($categories as $category) {
            $this->sortOrders[$category->id] ??= $category->sort_order;
        }

        return view('livewire.admin.product-categories.index', [
            'categories' => $categories,
            'editingCategory' => $this->editingCategoryId
                ? ProductCategory::find($this->editingCategoryId)
                : null,
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Danh mục sản phẩm'],
            ],
        ]);
    }
}
