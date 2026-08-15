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

    #[Url(as: 'per_page', except: 15, history: true)]
    public int $perPage = 15;

    public array $selected = [];

    public array $sortOrders = [];

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public bool $pendingBulkDelete = false;

    public function mount(): void
    {
        Gate::authorize('investors.view');
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
        $this->perPage = max(5, min(100, (int) $value));
        $this->selected = [];
        $this->resetPage();
    }

    public function toggleVisibility(int $id): void
    {
        Gate::authorize('investors.update');
        $category = DocumentCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active, 'updated_by' => auth()->id()]);
        $this->toastState($category->is_active, 'danh mục tài liệu');
    }

    public function delete(int $id): bool
    {
        Gate::authorize('investors.delete');
        $category = DocumentCategory::withCount(['documents', 'children'])->findOrFail($id);
        if ($category->documents_count > 0 || $category->children_count > 0) {
            $this->toast('Không thể xóa danh mục vì vẫn còn danh mục con hoặc tài liệu.', 'error');

            return false;
        }
        $category->delete();
        $this->toast('Đã chuyển danh mục vào thùng rác.');

        return true;
    }

    public function requestDelete(int $id): void
    {
        Gate::authorize('investors.delete');
        $category = DocumentCategory::withCount(['documents', 'children'])->findOrFail($id);
        if ($category->documents_count > 0 || $category->children_count > 0) {
            $this->toast('Không thể xóa danh mục vì vẫn còn danh mục con hoặc tài liệu.', 'error');

            return;
        }
        $this->pendingDeleteId = $category->id;
        $this->pendingDeleteName = $category->getTranslation('name', $this->locale, false)
            ?: $category->getTranslation('name', 'vi', false)
            ?: '#'.$category->id;
        $this->pendingBulkDelete = false;
    }

    public function requestBulkDelete(): void
    {
        Gate::authorize('investors.delete');
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'selected.*' => ['integer', 'distinct', 'exists:document_categories,id']]);
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
        $this->pendingBulkDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->reset('pendingDeleteId', 'pendingDeleteName', 'pendingBulkDelete');
    }

    public function confirmDelete(): void
    {
        Gate::authorize('investors.delete');
        if ($this->pendingBulkDelete) {
            $this->bulk('delete');
            $this->cancelDelete();

            return;
        }

        if (! $this->pendingDeleteId) {
            $this->toast('Không tìm thấy danh mục cần xóa. Vui lòng thử lại.', 'error');
            $this->cancelDelete();

            return;
        }
        $categoryId = $this->pendingDeleteId;
        if ($this->delete($categoryId)) {
            $this->selected = array_values(array_diff($this->selected, [$categoryId, (string) $categoryId]));
        }
        $this->cancelDelete();
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'sortOrders.*' => ['nullable', 'integer', 'min:0']]);
        Gate::authorize($action === 'delete' ? 'investors.delete' : 'investors.update');
        if (! in_array($action, ['show', 'hide', 'reorder', 'delete'], true)) {
            $this->toast('Thao tác với danh mục QHCĐ không hợp lệ.', 'error');

            return;
        }
        $deletedCount = 0;
        $skippedCount = 0;
        foreach (DocumentCategory::whereKey($this->selected)->withCount(['documents', 'children'])->get() as $category) {
            if ($action === 'delete' && $category->documents_count === 0 && $category->children_count === 0) {
                $category->delete();
                $deletedCount++;
            } elseif ($action === 'delete') {
                $skippedCount++;
            } elseif ($action === 'reorder') {
                $category->update(['sort_order' => (int) ($this->sortOrders[$category->id] ?? 0), 'updated_by' => auth()->id()]);
            } elseif (in_array($action, ['show', 'hide'], true)) {
                $category->update(['is_active' => $action === 'show', 'updated_by' => auth()->id()]);
            }
        }
        $this->selected = [];
        if ($action === 'delete' && $skippedCount > 0) {
            $message = $deletedCount > 0
                ? "Đã xóa {$deletedCount} danh mục; bỏ qua {$skippedCount} danh mục vẫn còn danh mục con hoặc tài liệu."
                : 'Không thể xóa các danh mục đã chọn vì vẫn còn danh mục con hoặc tài liệu.';
            $this->toast($message, $deletedCount > 0 ? 'warning' : 'error');

            return;
        }
        $this->toastBulk($action, 'danh mục tài liệu');
    }

    public function render()
    {
        $categories = DocumentCategory::with('parent')->withCount('documents')
            ->filtered(trim($this->search), $this->active, $this->locale)
            ->orderBy('parent_id')->orderByDesc('sort_order')->paginate($this->perPage);
        foreach ($categories as $category) {
            $this->sortOrders[$category->id] ??= $category->sort_order;
        }

        return view('livewire.admin.investors.category-index', [
            'categories' => $categories,
            'perPageOptions' => collect([5, 10, 15, 20, 50, 100, $this->perPage])->unique()->sort()->values()->all(),
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quan hệ cổ đông'], ['label' => 'Danh mục']],
        ]);
    }
}
