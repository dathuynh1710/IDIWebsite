<?php

namespace App\Livewire\Admin\Access;

use App\Livewire\AdminComponent;
use App\Models\Permission;
use App\Models\Role;
use App\Support\AdminAudit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Layout('layouts.admin')]
#[Title('Vai trò và phân quyền')]
class Roles extends AdminComponent
{
    #[Url(as: 'role', except: '')]
    public string $selectedRoleId = '';

    public string $copyRoleId = '';

    public string $matrixSearch = '';

    public array $permissionIds = [];

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $display_name = '';

    public string $description = '';

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public function mount(): void
    {
        Gate::authorize('roles.view');

        if (! $this->selectedRoleId || ! Role::whereKey($this->selectedRoleId)->exists()) {
            $this->selectedRoleId = (string) (Role::where('guard_name', 'web')->orderBy('name')->value('id') ?? '');
        }

        $this->loadSelectedRole();
    }

    public function updatedSelectedRoleId(): void
    {
        abort_unless(Role::whereKey($this->selectedRoleId)->where('guard_name', 'web')->exists(), 404);
        $this->copyRoleId = '';
        $this->loadSelectedRole();
    }

    public function create(): void
    {
        Gate::authorize('roles.create');
        $this->resetForm();
        $this->showForm = true;
    }

    public function editSelected(): void
    {
        Gate::authorize('roles.update');
        abort_unless($this->selectedRoleId, 404);
        $role = Role::findOrFail($this->selectedRoleId);
        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->display_name = $role->display_name ?: $role->name;
        $this->description = $role->description ?: '';
        $this->showForm = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize($this->editingId ? 'roles.update' : 'roles.create');
        $role = $this->editingId ? Role::findOrFail($this->editingId) : new Role(['guard_name' => 'web']);
        $isSuperAdmin = $role->exists && $role->name === 'super-admin';
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9._-]+$/', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($this->editingId)],
            'display_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($isSuperAdmin) {
            $validated['name'] = 'super-admin';
        }

        $role->fill($validated);
        $role->guard_name = 'web';
        $role->save();

        $this->selectedRoleId = (string) $role->id;
        $this->loadSelectedRole();
        $this->showForm = false;
        $this->toast($this->editingId ? 'Đã cập nhật thông tin vai trò.' : 'Đã tạo vai trò mới. Hãy chọn quyền trong ma trận bên dưới.');
        $this->resetForm();
    }

    public function savePermissions(): void
    {
        Gate::authorize('roles.update');
        $role = Role::with('permissions')->findOrFail($this->selectedRoleId);
        $this->validate([
            'permissionIds' => ['array'],
            'permissionIds.*' => ['integer', Rule::exists('permissions', 'id')->where('guard_name', 'web')],
        ]);

        $oldPermissions = $role->permissions->pluck('name')->sort()->values()->all();
        $permissions = $role->name === 'super-admin'
            ? Permission::where('guard_name', 'web')->get()
            : Permission::whereKey($this->permissionIds)->where('guard_name', 'web')->get();
        $role->syncPermissions($permissions);
        $newPermissions = $permissions->pluck('name')->sort()->values()->all();

        if ($oldPermissions !== $newPermissions) {
            AdminAudit::log('permissions_assigned', 'Vai trò', 'Cập nhật quyền của vai trò “'.($role->display_name ?: $role->name).'”', $role, [
                'permissions_before' => $oldPermissions,
                'permissions_after' => $newPermissions,
                'subject_label' => $role->display_name ?: $role->name,
            ]);
        }

        $this->loadSelectedRole();
        $this->toast('Đã lưu ma trận phân quyền cho vai trò “'.($role->display_name ?: $role->name).'”.');
    }

    public function copyPermissions(): void
    {
        Gate::authorize('roles.update');
        $target = Role::findOrFail($this->selectedRoleId);
        abort_if($target->name === 'super-admin', 422, 'Không thể thay đổi quyền của Quản trị cao nhất.');
        $source = Role::with('permissions')->findOrFail($this->copyRoleId);
        $this->permissionIds = $source->permissions->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->toast('Đã sao chép quyền từ “'.($source->display_name ?: $source->name).'”. Nhấn Lưu phân quyền để áp dụng.');
    }

    public function selectModule(array $ids): void
    {
        Gate::authorize('roles.update');
        $this->guardEditableRole();
        $this->toggleIds($ids);
    }

    public function selectColumn(string $column): void
    {
        Gate::authorize('roles.update');
        $this->guardEditableRole();
        abort_unless(in_array($column, ['view', 'create', 'update', 'delete', 'manage', 'other'], true), 422);

        $ids = Permission::where('guard_name', 'web')->get()
            ->filter(fn (Permission $permission) => $this->permissionColumn($permission->name) === $column)
            ->pluck('id')->all();
        $this->toggleIds($ids);
    }

    public function selectAll(): void
    {
        Gate::authorize('roles.update');
        $this->guardEditableRole();
        $this->toggleIds(Permission::where('guard_name', 'web')->pluck('id')->all());
    }

    public function requestDeleteSelected(): void
    {
        Gate::authorize('roles.delete');
        $role = Role::withCount('users')->findOrFail($this->selectedRoleId);
        abort_if($role->is_system || $role->users_count > 0, 422, 'Vai trò hệ thống hoặc đang được sử dụng không thể xóa.');
        $this->pendingDeleteId = $role->id;
        $this->pendingDeleteName = $role->display_name ?: $role->name;
    }

    public function confirmDelete(): void
    {
        Gate::authorize('roles.delete');
        $role = Role::withCount('users')->findOrFail($this->pendingDeleteId);
        abort_if($role->is_system || $role->users_count > 0, 422);
        $role->delete();
        $this->cancelDelete();
        $this->selectedRoleId = (string) (Role::where('guard_name', 'web')->orderBy('name')->value('id') ?? '');
        $this->loadSelectedRole();
        $this->toast('Đã xóa vai trò.');
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function loadSelectedRole(): void
    {
        $role = $this->selectedRoleId ? Role::with('permissions')->find($this->selectedRoleId) : null;
        $this->permissionIds = $role
            ? $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->all()
            : [];
    }

    private function guardEditableRole(): void
    {
        abort_if(Role::findOrFail($this->selectedRoleId)->name === 'super-admin', 422, 'Quản trị cao nhất luôn có toàn bộ quyền hệ thống.');
    }

    private function toggleIds(array $ids): void
    {
        $current = collect($this->permissionIds)->map('strval');
        $target = collect($ids)->map('strval');
        $allSelected = $target->isNotEmpty() && $target->every(fn ($id) => $current->contains($id));
        $this->permissionIds = ($allSelected ? $current->diff($target) : $current->merge($target))->unique()->values()->all();
    }

    private function permissionColumn(string $name): string
    {
        $suffix = Str::afterLast($name, '.');

        return in_array($suffix, ['view', 'create', 'update', 'delete', 'manage'], true) ? $suffix : 'other';
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'display_name', 'description');
        $this->resetValidation();
    }

    public function render()
    {
        $roles = Role::withCount(['users', 'permissions'])->where('guard_name', 'web')
            ->orderByDesc('is_system')->orderBy('display_name')->orderBy('name')->get();
        $selectedRole = $roles->firstWhere('id', (int) $this->selectedRoleId);
        $columns = [
            'view' => 'Xem',
            'create' => 'Thêm',
            'update' => 'Sửa',
            'delete' => 'Xóa',
            'manage' => 'Toàn quyền',
            'other' => 'Khác',
        ];
        $permissions = Permission::where('guard_name', 'web')
            ->when(trim($this->matrixSearch), function ($query): void {
                $term = '%'.trim($this->matrixSearch).'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('display_name', 'like', $term)->orWhere('module', 'like', $term));
            })->orderBy('module')->orderBy('name')->get();
        $matrix = $permissions->groupBy(fn (Permission $permission) => $permission->module ?: 'Khác')
            ->map(function ($group) use ($columns) {
                $row = collect(array_keys($columns))->mapWithKeys(fn ($column) => [$column => collect()]);
                foreach ($group as $permission) {
                    $column = $this->permissionColumn($permission->name);
                    $row[$column]->push($permission);
                }

                return ['permissions' => $group, 'columns' => $row];
            });

        return view('livewire.admin.access.roles', [
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'columns' => $columns,
            'matrix' => $matrix,
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Vai trò & Quyền']],
        ]);
    }
}
