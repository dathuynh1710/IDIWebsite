<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return view('admin.product-categories.index', [
            'categories' => ProductCategory::withCount('products')->orderBy('sort_order')->paginate(20),
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Danh mục sản phẩm'],
            ],
        ]);
    }
}
