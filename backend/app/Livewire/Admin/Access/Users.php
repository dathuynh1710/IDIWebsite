<?php

namespace App\Livewire\Admin\Access;

use App\Livewire\AdminComponent;
use App\Models\Role;
use App\Models\User;
use App\Support\AdminAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Quản lý quản trị viên')]
class Users extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $roleFilter = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(as: 'per_page', except: 10)]
    public int $perPage = 10;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $is_active = true;

    public array $roleIds = [];

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public function mount(): void
    {
        Gate::authorize('users.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'roleFilter', 'status', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'roleFilter', 'status');
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('users.create');
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        Gate::authorize('users.update');
        $user = User::with('roles')->findOrFail($id);
        abort_if($user->hasRole('super-admin') && ! auth()->user()->hasRole('super-admin'), 403);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->is_active = $user->is_active;
        $this->roleIds = $user->roles->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->password = $this->password_confirmation = '';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        Gate::authorize($this->editingId ? 'users.update' : 'users.create');
        $user = $this->editingId ? User::findOrFail($this->editingId) : new User;

        if (! auth()->user()->hasRole('super-admin')) {
            abort_if($user->exists && $user->hasRole('super-admin'), 403);
            $superAdminRoleId = Role::where('name', 'super-admin')->where('guard_name', 'web')->value('id');
            abort_if($superAdminRoleId && in_array((string) $superAdminRoleId, array_map('strval', $this->roleIds), true), 403);
        }

        if ($user->is(auth()->user()) && ! $this->is_active) {
            $this->addError('is_active', 'Bạn không thể tự khóa tài khoản đang sử dụng.');

            return;
        }

        $passwordRules = $this->editingId
            ? ['nullable', 'string', 'min:8', 'confirmed']
            : ['required', 'string', 'min:8', 'confirmed'];

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'username')->ignore($this->editingId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'password' => $passwordRules,
            'is_active' => ['boolean'],
            'roleIds' => ['array'],
            'roleIds.*' => ['integer', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ], [], [
            'name' => 'họ tên', 'username' => 'tên đăng nhập', 'email' => 'email',
            'password' => 'mật khẩu', 'roleIds' => 'vai trò',
        ]);

        DB::transaction(function () use ($user, $validated): void {
            $user->fill(collect($validated)->only(['name', 'username', 'email', 'is_active'])->all());
            if ($this->password !== '') {
                $user->password = $this->password;
            }
            $user->save();

            $roles = Role::whereKey($this->roleIds)->where('guard_name', 'web')->get();
            $oldRoles = $user->roles()->pluck('name')->sort()->values()->all();
            $user->syncRoles($roles);
            $newRoles = $roles->pluck('name')->sort()->values()->all();
            if ($oldRoles !== $newRoles) {
                AdminAudit::log('roles_assigned', 'Quản trị viên', 'Gán vai trò cho quản trị viên “'.$user->name.'”', $user, [
                    'roles_before' => $oldRoles,
                    'roles_after' => $newRoles,
                    'subject_label' => $user->name,
                ]);
            }
        });

        $this->showForm = false;
        $this->toast($this->editingId ? 'Đã cập nhật quản trị viên.' : 'Đã tạo quản trị viên mới.');
        $this->resetForm();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function requestDelete(int $id): void
    {
        Gate::authorize('users.delete');
        $user = User::with('roles')->findOrFail($id);
        if ($user->is(auth()->user())) {
            $this->toast('Bạn không thể tự xóa tài khoản đang sử dụng.', 'error');

            return;
        }
        if ($user->hasRole('super-admin')) {
            $this->toast('Không thể xóa tài khoản Quản trị cao nhất.', 'error');

            return;
        }
        $this->pendingDeleteId = $user->id;
        $this->pendingDeleteName = $user->name;
    }

    public function confirmDelete(): void
    {
        Gate::authorize('users.delete');
        $user = $this->pendingDeleteId ? User::with('roles')->find($this->pendingDeleteId) : null;
        if (! $user) {
            $this->cancelDelete();
            $this->toast('Không tìm thấy quản trị viên cần xóa. Vui lòng thử lại.', 'error');

            return;
        }
        if ($user->is(auth()->user()) || $user->hasRole('super-admin')) {
            $this->cancelDelete();
            $this->toast('Không thể xóa tài khoản đang sử dụng hoặc tài khoản Quản trị cao nhất.', 'error');

            return;
        }
        $user->delete();
        $this->cancelDelete();
        $this->toast('Đã xóa quản trị viên.');
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'username', 'email', 'password', 'password_confirmation', 'roleIds');
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $query = User::query()->with('roles')
            ->when(trim($this->search), fn ($q) => $q->where(function ($q): void {
                $term = '%'.trim($this->search).'%';
                $q->where('name', 'like', $term)->orWhere('username', 'like', $term)->orWhere('email', 'like', $term);
            }))
            ->when($this->roleFilter, fn ($q) => $q->role($this->roleFilter))
            ->when($this->status !== '', fn ($q) => $q->where('is_active', $this->status === 'active'))
            ->latest();

        return view('livewire.admin.access.users', [
            'users' => $query->paginate(in_array($this->perPage, [10, 20, 50], true) ? $this->perPage : 10),
            'roles' => Role::where('guard_name', 'web')->orderBy('display_name')->orderBy('name')->get(),
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quản trị viên']],
        ]);
    }
}
