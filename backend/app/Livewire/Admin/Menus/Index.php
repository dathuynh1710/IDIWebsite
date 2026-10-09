<?php

namespace App\Livewire\Admin\Menus;

use App\Livewire\AdminComponent;
use App\Models\Menu;
use App\Support\MenuTree;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;

#[Layout('layouts.admin')]
class Index extends AdminComponent
{
    #[Url]
    public ?int $menuId = null;

    public string $name = '';

    public string $location = '';

    #[Locked]
    public ?int $pendingDeleteId = null;

    public function mount(): void
    {
        Gate::authorize('settings.manage');
        $this->menuId ??= Menu::orderBy('id')->value('id');
    }

    public function updatedMenuId(): void
    {
        $this->cancelDelete();
    }

    public function createMenu(): void
    {
        Gate::authorize('settings.manage');
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_-]*$/', 'unique:menus,location'],
        ]);
        $this->menuId = Menu::create($data)->id;
        $this->reset('name', 'location');
        $this->toast('Đã tạo vị trí menu.');
    }

    public function toggleMenu(): void
    {
        Gate::authorize('settings.manage');
        $menu = Menu::findOrFail($this->menuId);
        $menu->update(['is_active' => ! $menu->is_active]);
    }

    public function toggleVisibility(int $id): void
    {
        Gate::authorize('settings.manage');
        DB::transaction(function () use ($id): void {
            $menu = Menu::whereKey($this->menuId)->lockForUpdate()->firstOrFail();
            $item = $menu->items()->findOrFail($id);
            $item->update(['is_active' => ! $item->is_active]);
        });
    }

    public function requestDelete(int $id): void
    {
        Gate::authorize('settings.manage');
        Menu::findOrFail($this->menuId)->items()->findOrFail($id);
        $this->resetErrorBag();
        $this->pendingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteId = null;
        $this->resetErrorBag();
    }

    public function confirmDelete(): void
    {
        Gate::authorize('settings.manage');
        if ($this->pendingDeleteId) {
            MenuTree::delete(Menu::findOrFail($this->menuId), $this->pendingDeleteId);
            $this->cancelDelete();
            $this->toast('Đã xóa mục menu.');
        }
    }

    public function render()
    {
        Gate::authorize('settings.manage');
        $menu = $this->menuId ? Menu::findOrFail($this->menuId) : null;

        return view('livewire.admin.menus.index', [
            'menus' => Menu::orderBy('id')->pluck('name', 'id')->all(),
            'menu' => $menu,
            'rows' => $menu ? MenuTree::rows($menu->items()->orderBy('sort_order')->orderBy('id')->get()) : [],
        ])->title('Quản lý menu - '.config('admin.name'));
    }
}
