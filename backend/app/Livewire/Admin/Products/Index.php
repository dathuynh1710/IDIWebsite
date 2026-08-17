<?php

namespace App\Livewire\Admin\Products;

use App\Livewire\AdminComponent;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Sản phẩm')]
class Index extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: '')]
    public string $active = '';

    #[Url(except: '')]
    public string $featured = '';

    #[Url(except: '')]
    public string $date_from = '';

    #[Url(as: 'per_page', except: 10, history: true)]
    public int $perPage = 10;

    public array $selected = [];

    public array $sortOrders = [];

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public bool $pendingBulkDelete = false;

    public function mount(): void
    {
        Gate::authorize('products.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'category', 'active', 'featured', 'date_from'], true)) {
            $this->selected = [];
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'category', 'active', 'featured', 'date_from');
        $this->selected = [];
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $perPage = (int) $value;
        $this->perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 10;
        $this->selected = [];
        $this->resetPage();
    }

    public function saveSortOrders(): void
    {
        Gate::authorize('products.update');
        $validated = $this->validate([
            'sortOrders' => ['required', 'array'],
            'sortOrders.*' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['sortOrders'] as $productId => $sortOrder) {
                Product::whereKey((int) $productId)->update([
                    'sort_order' => (int) $sortOrder,
                    'updated_by' => auth()->id(),
                ]);
            }
        });

        $this->toast('Đã cập nhật thứ tự sản phẩm.');
    }

    public function toggleFeatured(int $productId): void
    {
        Gate::authorize('products.update');
        $product = Product::findOrFail($productId);
        $product->update([
            'is_featured' => ! $product->is_featured,
            'updated_by' => auth()->id(),
        ]);

        $this->toast($product->is_featured ? 'Đã đánh dấu sản phẩm nổi bật.' : 'Đã bỏ đánh dấu sản phẩm nổi bật.');
    }

    public function bulk(string $action): void
    {
        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'distinct'],
        ], ['selected.required' => 'Vui lòng chọn ít nhất một sản phẩm.']);

        Gate::authorize($action === 'delete' ? 'products.delete' : 'products.update');
        if (! in_array($action, ['show', 'hide', 'delete'], true)) {
            $this->toast('Thao tác với sản phẩm không hợp lệ.', 'error');

            return;
        }

        DB::transaction(function () use ($action): void {
            if (in_array($action, ['show', 'hide'], true)) {
                Product::whereKey($this->selected)->update([
                    'is_active' => $action === 'show',
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);
            } else {
                Product::whereKey($this->selected)->get()->each->delete();
            }
        });

        $this->selected = [];
        $this->toast(match ($action) {
            'show' => 'Đã hiển thị các sản phẩm đã chọn.',
            'hide' => 'Đã ẩn các sản phẩm đã chọn.',
            'delete' => 'Đã chuyển các sản phẩm đã chọn vào thùng rác.',
        });
    }

    public function delete(int $productId): void
    {
        Gate::authorize('products.delete');
        Product::findOrFail($productId)->delete();
        $this->toast('Đã chuyển sản phẩm vào thùng rác.');
    }

    public function requestDelete(int $productId): void
    {
        Gate::authorize('products.delete');
        $product = Product::findOrFail($productId);
        $this->pendingDeleteId = $product->id;
        $this->pendingDeleteName = $product->getTranslation('title', 'vi', false) ?: $product->sku ?: '#'.$product->id;
        $this->pendingBulkDelete = false;
    }

    public function requestBulkDelete(): void
    {
        Gate::authorize('products.delete');
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'selected.*' => ['integer', 'distinct', 'exists:products,id']]);
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
        Gate::authorize('products.delete');
        if ($this->pendingBulkDelete) {
            $this->bulk('delete');
            $this->cancelDelete();

            return;
        }

        if (! $this->pendingDeleteId) {
            $this->toast('Không tìm thấy sản phẩm cần xóa. Vui lòng thử lại.', 'error');
            $this->cancelDelete();

            return;
        }
        $productId = $this->pendingDeleteId;
        $this->delete($productId);
        $this->selected = array_values(array_diff($this->selected, [$productId, (string) $productId]));
        $this->cancelDelete();
    }

    public function duplicate(int $productId): void
    {
        Gate::authorize('products.create');
        $product = Product::findOrFail($productId);

        $copy = DB::transaction(function () use ($product): Product {
            $copy = $product->replicate();
            $suffix = now()->format('His');
            $copy->sku = Str::limit($product->sku.'-COPY-'.$suffix, 100, '');
            $copy->title = collect($product->getTranslations('title'))->map(fn ($title) => $title.' (Bản sao)')->all();
            $copy->slug = collect($product->getTranslations('slug'))->map(fn ($slug) => Str::limit($slug.'-copy-'.$suffix, 255, ''))->all();
            $copy->translation_status = ['vi' => 'draft', 'en' => 'draft', 'zh' => 'draft'];
            $copy->locale_published_at = [];
            $copy->sort_order = ((int) Product::max('sort_order')) + 1;
            $copy->created_by = auth()->id();
            $copy->updated_by = auth()->id();
            $copy->save();

            return $copy;
        });

        $this->toast("Đã nhân bản sản phẩm {$copy->sku}.");
    }

    public function render()
    {
        $filters = [
            'search' => trim($this->search),
            'category' => $this->category,
            'active' => $this->active,
            'featured' => $this->featured,
            'date_from' => $this->date_from,
        ];

        $products = Product::query()->with(['category', 'featuredMedia'])
            ->filtered($filters)->orderByDesc('sort_order')->latest('updated_at')->paginate($this->perPage);
        foreach ($products as $product) {
            $this->sortOrders[$product->id] ??= $product->sort_order;
        }

        return view('livewire.admin.products.index', [
            'products' => $products,
            'categories' => ProductCategory::orderBy('sort_order')->get(),
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Sản phẩm'],
            ],
        ]);
    }
}
