<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Sản phẩm')]
class Index extends Component
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

    public function mount(): void
    {
        Gate::authorize('products.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'category', 'active', 'date_from'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'category', 'active', 'date_from');
        $this->resetPage();
    }

    public function delete(int $productId): void
    {
        Gate::authorize('products.delete');
        Product::findOrFail($productId)->delete();
        $this->dispatch('admin-toast', message: 'Đã chuyển sản phẩm vào thùng rác.', type: 'success');
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
            $copy->created_by = auth()->id();
            $copy->updated_by = auth()->id();
            $copy->save();

            return $copy;
        });

        $this->dispatch('admin-toast', message: "Đã nhân bản sản phẩm {$copy->sku}.", type: 'success');
    }

    public function render()
    {
        $filters = [
            'search' => trim($this->search),
            'category' => $this->category,
            'active' => $this->active,
            'date_from' => $this->date_from,
        ];

        return view('livewire.admin.products.index', [
            'products' => Product::query()->with(['category', 'featuredMedia'])
                ->filtered($filters)->orderBy('sort_order')->latest('updated_at')->paginate(15),
            'categories' => ProductCategory::orderBy('sort_order')->get(),
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Sản phẩm'],
            ],
        ]);
    }
}
