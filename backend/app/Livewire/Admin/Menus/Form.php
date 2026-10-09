<?php

namespace App\Livewire\Admin\Menus;

use App\Livewire\AdminComponent;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\ProductCategory;
use App\Support\MenuTree;
use App\Support\MenuUrl;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('layouts.admin')]
class Form extends AdminComponent
{
    #[Locked]
    public Menu $menu;

    #[Locked]
    public ?int $itemId = null;

    public ?int $parent_id = null;

    public array $label = ['vi' => '', 'en' => '', 'zh' => ''];

    public string $link_type = 'internal';

    public string $url = '';

    public ?int $page_id = null;

    public ?int $product_category_id = null;

    public int $sort_order = 0;

    public bool $is_active = true;

    public function mount(Menu $menu, ?MenuItem $item = null): void
    {
        Gate::authorize('settings.manage');
        $this->menu = $menu;
        if ($item?->exists) {
            abort_unless($item->menu_id === $menu->id, 404);
            $this->itemId = $item->id;
            foreach (['parent_id', 'link_type', 'page_id', 'product_category_id', 'sort_order', 'is_active'] as $field) {
                $this->{$field} = $item->{$field};
            }
            $this->url = $item->url ?? '';
            $this->label = array_merge($this->label, $item->getTranslations('label'));
        }
    }

    public function save(): void
    {
        Gate::authorize('settings.manage');
        $data = $this->validate([
            'parent_id' => ['nullable', 'integer'],
            'label' => ['required', 'array:vi,en,zh'],
            'label.vi' => ['required', 'string', 'max:255'],
            'label.en' => ['nullable', 'string', 'max:255'],
            'label.zh' => ['nullable', 'string', 'max:255'],
            'link_type' => ['required', Rule::in(['internal', 'external', 'page', 'product_category'])],
            'url' => ['nullable', 'string', 'max:2048'],
            'page_id' => ['nullable', 'integer'],
            'product_category_id' => ['nullable', 'integer'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['boolean'],
        ]);
        if (in_array($this->link_type, ['internal', 'external'], true) && ! MenuUrl::valid($this->url, $this->link_type)) {
            $this->addError('url', 'Dùng đường dẫn nội bộ bắt đầu bằng / hoặc URL ngoài http://, https:// hợp lệ.');

            return;
        }
        if ($this->link_type === 'page' && ! Page::about()->whereKey($this->page_id)->exists()) {
            $this->addError('page_id', 'Hãy chọn trang CMS Giới thiệu có route công khai.');

            return;
        }
        if ($this->link_type === 'product_category' && ! ProductCategory::whereKey($this->product_category_id)->exists()) {
            $this->addError('product_category_id', 'Hãy chọn nhóm sản phẩm.');

            return;
        }
        $data['url'] = in_array($this->link_type, ['internal', 'external'], true) ? $this->url : null;
        $data['page_id'] = $this->link_type === 'page' ? $this->page_id : null;
        $data['product_category_id'] = $this->link_type === 'product_category' ? $this->product_category_id : null;
        $item = MenuTree::save($this->menu, $this->itemId, $data);
        $this->itemId = $item->id;
        $this->toast('Đã lưu mục menu.');
        $this->redirectRoute('admin.menus.items.edit', ['menu' => $this->menu, 'item' => $item], navigate: true);
    }

    public function render()
    {
        Gate::authorize('settings.manage');
        $rows = MenuTree::rows($this->menu->items()->orderBy('sort_order')->orderBy('id')->get());

        return view('livewire.admin.menus.form', [
            'parentOptions' => collect($rows)->reject(fn ($row) => $row['item']->id === $this->itemId)->mapWithKeys(fn ($row) => [$row['item']->id => str_repeat('— ', $row['depth']).$row['item']->getTranslation('label', 'vi', false)])->all(),
            'pageOptions' => Page::about()->orderBy('sort_order')->get()->mapWithKeys(fn ($page) => [$page->id => '#'.$page->id.' '.$page->code.' — '.$page->getTranslation('title', 'vi', false)])->all(),
            'categoryOptions' => ProductCategory::orderBy('sort_order')->get()->mapWithKeys(fn ($category) => [$category->id => '#'.$category->id.' '.$category->getTranslation('name', 'vi', false)])->all(),
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
        ])->title('Mục menu - '.config('admin.name'));
    }
}
