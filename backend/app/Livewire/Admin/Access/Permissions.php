<?php

namespace App\Livewire\Admin\Access;

use App\Livewire\AdminComponent;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.admin')]
#[Title('Quản lý quyền hạn')]
class Permissions extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $moduleFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $display_name = '';

    public string $module = '';

    public string $description = '';

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

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

    public function create(): void
    {
        Gate::authorize('permissions.create');
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        Gate::authorize('permissions.update');
        $permission = Permission::findOrFail($id);
        $this->editingId = $permission->id;
        $this->name = $permission->name;
        $this->display_name = $permission->display_name ?: $permission->name;
        $this->module = $permission->module ?: '';
        $this->description = $permission->description ?: '';
        $this->showForm = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize($this->editingId ? 'permissions.update' : 'permissions.create');
        $permission = $this->editingId ? Permission::findOrFail($this->editingId) : new Permission(['guard_name' => 'web']);
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150', 'regex:/^[a-z0-9._-]+$/', Rule::unique('permissions', 'name')->where('guard_name', 'web')->ignore($this->editingId)],
            'display_name' => ['required', 'string', 'max:180'],
            'module' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($permission->exists && $permission->is_system) {
            $validated['name'] = $permission->name;
        }

        $permission->fill($validated);
        $permission->guard_name = 'web';
        $permission->save();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->showForm = false;
        $this->toast($this->editingId ? 'Đã cập nhật quyền hạn.' : 'Đã tạo quyền hạn mới.');
        $this->resetForm();
    }

    public function requestDelete(int $id): void
    {
        Gate::authorize('permissions.delete');
        $permission = Permission::withCount('roles')->findOrFail($id);
        abort_if($permission->is_system || $permission->roles_count > 0, 422, 'Quyền hệ thống hoặc đang được sử dụng không thể xóa.');
        $this->pendingDeleteId = $permission->id;
        $this->pendingDeleteName = $permission->display_name ?: $permission->name;
    }

    public function confirmDelete(): void
    {
        Gate::authorize('permissions.delete');
        $permission = Permission::withCount('roles')->findOrFail($this->pendingDeleteId);
        abort_if($permission->is_system || $permission->roles_count > 0, 422);
        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->cancelDelete();
        $this->toast('Đã xóa quyền hạn.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'display_name', 'module', 'description');
        $this->resetValidation();
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
