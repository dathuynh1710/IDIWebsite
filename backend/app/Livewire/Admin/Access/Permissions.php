<?php

namespace App\Livewire\Admin\Access;

use App\Livewire\AdminComponent;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Danh mục quyền hạn')]
class Permissions extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $moduleFilter = '';

    public function mount(): void
    {
        Gate::authorize('permissions.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedModuleFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'moduleFilter');
        $this->resetPage();
    }

    public function render()
    {
        $query = Permission::withCount('roles')->where('guard_name', 'web')
            ->when(trim($this->search), function ($q): void {
                $term = '%'.trim($this->search).'%';
                $q->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('display_name', 'like', $term)->orWhere('description', 'like', $term));
            })->when($this->moduleFilter, fn ($q) => $q->where('module', $this->moduleFilter))
            ->orderBy('module')->orderBy('name');

        return view('livewire.admin.access.permissions', [
            'permissions' => $query->paginate(15),
            'modules' => Permission::where('guard_name', 'web')->whereNotNull('module')->distinct()->orderBy('module')->pluck('module'),
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quyền hạn']],
        ]);
    }
}
