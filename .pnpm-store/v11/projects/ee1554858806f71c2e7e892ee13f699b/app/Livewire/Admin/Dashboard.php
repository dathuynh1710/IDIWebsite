<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Bảng điều khiển')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'stats' => [
                ['label' => 'Sản phẩm', 'value' => Product::count(), 'icon' => 'package', 'tone' => 'blue'],
                ['label' => 'Danh mục', 'value' => ProductCategory::count(), 'icon' => 'folder', 'tone' => 'green'],
                ['label' => 'Bản dịch chờ duyệt', 'value' => Product::where('translation_status', 'like', '%review%')->count(), 'icon' => 'languages', 'tone' => 'orange'],
                ['label' => 'Lượt xem 7 ngày', 'value' => DB::table('product_view_statistics')->where('view_date', '>=', now()->subDays(6)->toDateString())->sum('view_count'), 'icon' => 'chart', 'tone' => 'purple'],
            ],
            'recentProducts' => Product::with('category')->latest('updated_at')->limit(6)->get(),
            'breadcrumbs' => [['label' => 'Bảng điều khiển']],
        ]);
    }
}
