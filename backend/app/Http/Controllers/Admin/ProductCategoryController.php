<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductCategoryRequest;
use App\Http\Requests\Admin\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    private const LOCALES = [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
        'zh' => '中文',
    ];

    public function index(Request $request): View
    {
        Gate::authorize('products.view');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,hidden,trashed'],
        ]);

        $query = ProductCategory::query()
            ->with('parent')
            ->withCount('products');

        if (($filters['status'] ?? null) === 'trashed') {
            $query->onlyTrashed();
        } elseif (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'hidden') {
            $query->where('is_active', false);
        }

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name->vi', 'like', "%{$search}%")
                    ->orWhere('slug->vi', 'like', "%{$search}%");
            });
        }

        return view('admin.product-categories.index', [
            'categories' => $query->orderBy('sort_order')->latest('updated_at')->paginate(20)->withQueryString(),
            'filters' => $filters,
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Danh mục sản phẩm'],
            ],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('products.create');

        return view('admin.product-categories.create', $this->formData());
    }

    public function store(StoreProductCategoryRequest $request): RedirectResponse
    {
        $category = ProductCategory::create(array_merge($request->validated(), [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]));

        return redirect()
            ->route('admin.product-categories.edit', $category)
            ->with(Toast::success('Đã thêm danh mục sản phẩm.'));
    }

    public function edit(ProductCategory $category): View
    {
        Gate::authorize('products.update');

        return view('admin.product-categories.edit', $this->formData($category));
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $category): RedirectResponse
    {
        $category->update(array_merge($request->validated(), [
            'updated_by' => $request->user()->id,
        ]));

        return redirect()
            ->route('admin.product-categories.edit', $category)
            ->with(Toast::success('Đã cập nhật danh mục sản phẩm.'));
    }

    public function visibility(Request $request, ProductCategory $category): RedirectResponse
    {
        Gate::authorize('products.update');

        $validated = $request->validate(['is_active' => ['required', 'boolean']]);
        $category->update([
            'is_active' => (bool) $validated['is_active'],
            'updated_by' => $request->user()->id,
        ]);

        return back()->with(Toast::success($category->is_active ? 'Đã hiển thị danh mục.' : 'Đã ẩn danh mục.'));
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        Gate::authorize('products.delete');

        DB::transaction(function () use ($category): void {
            ProductCategory::where('parent_id', $category->id)->update(['parent_id' => null]);
            $category->delete();
        });

        return redirect()
            ->route('admin.product-categories.index')
            ->with(Toast::success('Đã chuyển danh mục vào thùng rác. Sản phẩm liên quan không bị xóa.'));
    }

    public function restore(int $categoryId): RedirectResponse
    {
        Gate::authorize('products.delete');

        $category = ProductCategory::onlyTrashed()->findOrFail($categoryId);
        $category->restore();

        return back()->with(Toast::success('Đã khôi phục danh mục sản phẩm.'));
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:show,hide,reorder,delete'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'distinct', 'exists:product_categories,id'],
            'sort_orders' => ['nullable', 'array'],
            'sort_orders.*' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ], [
            'category_ids.required' => 'Vui lòng chọn ít nhất một danh mục.',
        ]);

        Gate::authorize($validated['action'] === 'delete' ? 'products.delete' : 'products.update');

        $ids = $validated['category_ids'];
        $userId = $request->user()->id;

        DB::transaction(function () use ($validated, $ids, $userId): void {
            if ($validated['action'] === 'show' || $validated['action'] === 'hide') {
                ProductCategory::whereKey($ids)->update([
                    'is_active' => $validated['action'] === 'show',
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
            }

            if ($validated['action'] === 'reorder') {
                foreach ($ids as $id) {
                    if (array_key_exists($id, $validated['sort_orders'] ?? [])) {
                        ProductCategory::whereKey($id)->update([
                            'sort_order' => (int) $validated['sort_orders'][$id],
                            'updated_by' => $userId,
                        ]);
                    }
                }
            }

            if ($validated['action'] === 'delete') {
                ProductCategory::whereIn('parent_id', $ids)->update(['parent_id' => null]);
                ProductCategory::whereKey($ids)->get()->each->delete();
            }
        });

        $message = match ($validated['action']) {
            'show' => 'Đã hiển thị các danh mục đã chọn.',
            'hide' => 'Đã ẩn các danh mục đã chọn.',
            'reorder' => 'Đã cập nhật thứ tự danh mục.',
            'delete' => 'Đã chuyển các danh mục đã chọn vào thùng rác.',
        };

        return back()->with(Toast::success($message));
    }

    private function formData(?ProductCategory $category = null): array
    {
        return [
            'category' => $category,
            'locales' => self::LOCALES,
            'parentOptions' => ProductCategory::query()
                ->when($category, fn ($query) => $query->whereKeyNot($category->id))
                ->orderBy('sort_order')
                ->get()
                ->mapWithKeys(fn (ProductCategory $item): array => [
                    $item->id => $item->getTranslation('name', 'vi', false),
                ])
                ->all(),
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Danh mục sản phẩm', 'route' => 'admin.product-categories.index'],
                ['label' => $category ? 'Chỉnh sửa' : 'Thêm mới'],
            ],
        ];
    }
}
