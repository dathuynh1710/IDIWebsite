<div class="access-page">
    <x-admin.page-header title="Quản lý quản trị viên" description="Quản lý tài khoản truy cập CMS và vai trò được phân công" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('users.create')<button class="button button-primary" type="button" wire:click="create"><x-ui.icon name="plus" size="18" /> Thêm quản trị viên</button>@endcan
        </x-slot:actions>
    </x-admin.page-header>

    <section class="access-summary-grid">
        <article><span class="access-summary-icon is-blue"><x-ui.icon name="users" /></span><div><strong>{{ $users->total() }}</strong><small>Tổng tài khoản</small></div></article>
        <article><span class="access-summary-icon is-green"><x-ui.icon name="check" /></span><div><strong>{{ \App\Models\User::where('is_active', true)->count() }}</strong><small>Đang hoạt động</small></div></article>
        <article><span class="access-summary-icon is-purple"><x-ui.icon name="shield" /></span><div><strong>{{ $roles->count() }}</strong><small>Vai trò hiện có</small></div></article>
    </section>

    <section class="filter-card card">
        <div class="access-filter-grid">
            <div class="filter-search"><label for="user-search">Tìm kiếm</label><div><x-ui.icon name="search" size="18" /><input id="user-search" class="input" wire:model.live.debounce.300ms="search" placeholder="Họ tên, tài khoản hoặc email"></div></div>
            <div><label for="user-role">Vai trò</label><select id="user-role" class="select" wire:model.live="roleFilter"><option value="">Tất cả vai trò</option>@foreach($roles as $role)<option value="{{ $role->name }}">{{ $role->display_name ?: $role->name }}</option>@endforeach</select></div>
            <div><label for="user-status">Trạng thái</label><select id="user-status" class="select" wire:model.live="status"><option value="">Tất cả trạng thái</option><option value="active">Đang hoạt động</option><option value="inactive">Đã khóa</option></select></div>
            <button class="button button-ghost" type="button" wire:click="resetFilters">Đặt lại</button>
        </div>
    </section>

    <section class="card access-list-card">
        @if($users->isEmpty())
            <x-ui.empty-state title="Không tìm thấy quản trị viên" description="Hãy thay đổi bộ lọc hoặc tạo tài khoản quản trị mới." />
        @else
            <div class="table-responsive"><table class="data-table access-table users-table"><thead><tr><th>Quản trị viên</th><th>Vai trò / Nhóm</th><th>Trạng thái</th><th>Đăng nhập gần nhất</th><th class="table-actions-heading">Thao tác</th></tr></thead><tbody>
            @foreach($users as $user)<tr wire:key="admin-user-{{ $user->id }}">
                <td><div class="access-person"><span>{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span><div><strong>{{ $user->name }}</strong><small>{{ '@'.$user->username }} · {{ $user->email }}</small></div></div></td>
                <td><div class="chip-list">@forelse($user->roles as $role)<span class="access-chip {{ $role->name === 'super-admin' ? 'is-primary' : '' }}">{{ $role->display_name ?: $role->name }}</span>@empty<span class="access-empty-value">Chưa gán vai trò</span>@endforelse</div></td>
                <td><x-ui.badge :tone="$user->is_active ? 'success' : 'neutral'"><span class="status-dot"></span>{{ $user->is_active ? 'Hoạt động' : 'Đã khóa' }}</x-ui.badge></td>
                <td><div class="access-login"><strong>{{ $user->last_login_at?->format('H:i, d/m/Y') ?: 'Chưa đăng nhập' }}</strong>@if($user->last_login_ip)<small>IP {{ $user->last_login_ip }}</small>@endif</div></td>
                <td><div class="row-actions">@can('users.update')<button class="icon-button is-success" type="button" wire:click="edit({{ $user->id }})" title="Chỉnh sửa"><x-ui.icon name="edit" size="18" /></button>@endcan @can('users.delete')@if(!$user->is(auth()->user()) && !$user->hasRole('super-admin'))<button class="icon-button is-danger" type="button" wire:click="requestDelete({{ $user->id }})" title="Xóa"><x-ui.icon name="trash" size="18" /></button>@endif @endcan</div></td>
            </tr>@endforeach
            </tbody></table></div>
            <x-ui.pagination :paginator="$users" :per-page-options="[10, 20, 50]" />
        @endif
    </section>

    @if($showForm)
        <div class="access-modal" x-data x-on:keydown.escape.window="$wire.closeForm()"><button class="access-modal-backdrop" type="button" wire:click="closeForm" aria-label="Đóng"></button>
            <section class="access-modal-panel" role="dialog" aria-modal="true" aria-labelledby="user-form-title">
                <header><div><span>{{ $editingId ? 'Cập nhật tài khoản' : 'Tài khoản mới' }}</span><h2 id="user-form-title">{{ $editingId ? 'Chỉnh sửa quản trị viên' : 'Thêm quản trị viên' }}</h2></div><button class="icon-button" type="button" wire:click="closeForm"><x-ui.icon name="x" /></button></header>
                <form wire:submit="save"><div class="access-modal-body">
                    <div class="access-form-grid">
                        <div class="form-field"><label for="admin-name">Họ và tên <span>*</span></label><input id="admin-name" class="input" wire:model="name" autofocus>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="form-field"><label for="admin-username">Tên đăng nhập <span>*</span></label><input id="admin-username" class="input" wire:model="username" autocomplete="off">@error('username')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="form-field is-wide"><label for="admin-email">Email <span>*</span></label><input id="admin-email" class="input" type="email" wire:model="email">@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="form-field"><label for="admin-password">Mật khẩu {{ $editingId ? 'mới' : '' }} {{ $editingId ? '' : '*' }}</label><input id="admin-password" class="input" type="password" wire:model="password" autocomplete="new-password"><p class="field-help">{{ $editingId ? 'Để trống nếu không thay đổi.' : 'Tối thiểu 8 ký tự.' }}</p>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="form-field"><label for="admin-password-confirm">Xác nhận mật khẩu</label><input id="admin-password-confirm" class="input" type="password" wire:model="password_confirmation" autocomplete="new-password"></div>
                    </div>
                    <div class="access-form-section"><div class="access-form-section-title"><div><h3>Vai trò được phân công</h3><p>Quản trị viên nhận toàn bộ quyền từ các vai trò đã chọn.</p></div><x-ui.icon name="shield" /></div>
                        <div class="role-choice-grid">@foreach($roles as $role)@if($role->name !== 'super-admin' || auth()->user()->hasRole('super-admin'))<label class="role-choice {{ in_array((string)$role->id, array_map('strval', $roleIds), true) ? 'is-selected' : '' }}"><input type="checkbox" wire:model.live="roleIds" value="{{ $role->id }}"><span><strong>{{ $role->display_name ?: $role->name }}</strong><small>{{ $role->description ?: $role->name }}</small></span><x-ui.icon name="check" size="17" /></label>@endif @endforeach</div>@error('roleIds.*')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <label class="access-status-switch"><input type="checkbox" wire:model="is_active"><span class="switch-track"><span></span></span><span><strong>Cho phép đăng nhập</strong><small>Tài khoản bị khóa không thể truy cập CMS.</small></span></label>@error('is_active')<p class="field-error">{{ $message }}</p>@enderror
                </div><footer><button class="button button-secondary" type="button" wire:click="closeForm">Hủy</button><button class="button button-primary" type="submit" wire:loading.attr="disabled"><x-ui.icon name="save" size="17" /> {{ $editingId ? 'Lưu thay đổi' : 'Tạo quản trị viên' }}</button></footer></form>
            </section>
        </div>
    @endif

    @if($pendingDeleteId)<div class="modal-backdrop" wire:click.self="cancelDelete"><section class="modal-card" role="alertdialog"><div class="modal-icon"><x-ui.icon name="alert" size="30" /></div><h2>Xóa quản trị viên?</h2><p>Tài khoản <strong>“{{ $pendingDeleteName }}”</strong> sẽ không thể truy cập CMS.</p><div class="modal-actions"><button class="button button-secondary" wire:click="cancelDelete">Hủy</button><button class="button button-danger" wire:click="confirmDelete"><x-ui.icon name="trash" size="17" /> Xóa tài khoản</button></div></section></div>@endif
</div>
