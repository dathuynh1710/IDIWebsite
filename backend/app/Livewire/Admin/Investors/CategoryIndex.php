<?php

namespace App\Livewire\Admin\Investors;

use App\Livewire\AdminComponent;
use App\Models\DocumentCategory;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class CategoryIndex extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $active = '';

    #[Url(except: 'vi')]
    public string $locale = 'vi';

    public array $selected = [];

    public array $sortOrders = [];

    public function mount(): void
    {
        Gate::authorize('investors.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'active', 'locale'], true)) {
            $this->resetPage();
        }
    }

    public function toggleVisibility(int $id): void
    {
        Gate::authorize('investors.update');
        $category = DocumentCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active, 'updated_by' => auth()->id()]);
        $this->toastState($category->is_active, 'danh mục tài liệu');
    }

    public function delete(int $id): void
    {
        Gate::authorize('investors.delete');
        $category = DocumentCategory::withCount(['documents', 'children'])->findOrFail($id);
        abort_if($category->documents_count > 0 || $category->children_count > 0, 422, 'Danh mục vẫn còn danh mục con hoặc tài liệu.');
        $category->delete();
        $this->toast('Đã chuyển danh mục vào thùng rác.');
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'sortOrders.*' => ['nullable', 'integer', 'min:0']]);
        Gate::authorize($action === 'delete' ? 'investors.delete' : 'investors.update');
        abort_unless(in_array($action, ['show', 'hide', 'reorder', 'delete'], true), 422);
        foreach (DocumentCategory::whereKey($this->selected)->withCount(['documents', 'children'])->get() as $category) {
            if ($action === 'delete' && $category->documents_count === 0 && $category->children_count === 0) {
                $category->delete();
            } elseif ($action === 'reorder') {
                $category->update(['sort_order' => (int) ($this->sortOrders[$category->id] ?? 0), 'updated_by' => auth()->id()]);
            } elseif (in_array($action, ['show', 'hide'], true)) {
                $category->update(['is_active' => $action === 'show', 'updated_by' => auth()->id()]);
            }
        }
        $this->selected = [];
        $this->toastBulk($action, 'danh mục tài liệu');
    }

    public function render()
    {
        $categories = DocumentCategory::with('parent')->withCount('documents')
            ->filtered(trim($this->search), $this->active, $this->locale)
            ->orderBy('parent_id')->orderByDesc('sort_order')->paginate(15);
        foreach ($categories as $category) {
            $this->sortOrders[$category->id] ??= $category->sort_order;
        }

        return view('livewire.admin.investors.category-index', [
            'categories' => $categories,
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quan hệ cổ đông'], ['label' => 'Danh mục']],
        ]);
    }
}
