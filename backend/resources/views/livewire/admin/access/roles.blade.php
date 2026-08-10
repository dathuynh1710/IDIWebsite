<div class="access-page permission-matrix-page">
    <x-admin.page-header title="Phân quyền người dùng" description="Thiết lập quyền thao tác theo từng vai trò và module trong hệ thống" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.access.users') }}" wire:navigate><x-ui.icon name="users" size="18" /> Gán người dùng</a>
            @can('roles.create')<button class="button button-primary" type="button" wire:click="create"><x-ui.icon name="plus" size="18" /> Thêm vai trò</button>@endcan
        </x-slot:actions>
    </x-admin.page-header>

    <section class="permission-matrix-toolbar card">
        <div class="matrix-toolbar-heading">
            <span><x-ui.icon name="shield" size="23" /></span>
            <div><strong>Ma trận phân quyền</strong><small>Chọn vai trò, đánh dấu quyền cần cấp và nhấn Lưu phân quyền.</small></div>
        </div>
        <div class="matrix-toolbar-fields">
            <div class="form-field matrix-role-field">
                <label for="matrix-role">Nhóm người dùng <span>*</span></label>
                <select id="matrix-role" class="select" wire:model.live="selectedRoleId">
                    @foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->display_name ?: $role->name }} ({{ $role->users_count }} người dùng)</option>@endforeach
                </select>
            </div>
            <div class="form-field matrix-copy-field">
                <label for="matrix-copy-role">Sao chép quyền từ</label>
                <div><select id="matrix-copy-role" class="select" wire:model.live="copyRoleId"><option value="">— Chọn vai trò —</option>@foreach($roles as $role)@if((string)$role->id !== $selectedRoleId)<option value="{{ $role->id }}">{{ $role->display_name ?: $role->name }}</option>@endif @endforeach</select>
                    <button class="button button-secondary" type="button" wire:click="copyPermissions" wire:loading.attr="disabled" wire:target="copyPermissions" {{ !$copyRoleId || $selectedRole?->name === 'super-admin' ? 'disabled' : '' }}><x-ui.icon name="copy" size="17" /> Sao chép</button></div>
            </div>
            <div class="matrix-toolbar-actions">
                @can('roles.update')<button class="button button-secondary" type="button" wire:click="editSelected"><x-ui.icon name="edit" size="17" /> Sửa vai trò</button>
                <button class="button button-primary" type="button" wire:click="savePermissions" wire:loading.attr="disabled" wire:target="savePermissions" {{ !$selectedRole ? 'disabled' : '' }}><x-ui.icon name="save" size="17" /><span wire:loading.remove wire:target="savePermissions">Lưu phân quyền</span><span wire:loading wire:target="savePermissions">Đang lưu...</span></button>@endcan
            </div>
        </div>
    </section>

    @if($selectedRole)
        <section class="matrix-role-summary card">
            <div class="matrix-role-identity"><span class="role-card-icon {{ $selectedRole->name === 'super-admin' ? 'is-super' : '' }}"><x-ui.icon name="shield" /></span><div><div><strong>{{ $selectedRole->display_name ?: $selectedRole->name }}</strong>@if($selectedRole->is_system)<em>Vai trò hệ thống</em>@endif</div><code>{{ $selectedRole->name }}</code><p>{{ $selectedRole->description ?: 'Chưa có mô tả cho vai trò này.' }}</p></div></div>
            <div class="matrix-role-stats"><div><strong>{{ $selectedRole->users_count }}</strong><small>Người dùng</small></div><div><strong>{{ count(array_intersect(array_map('strval', $permissionIds), $visiblePermissionIds)) }}</strong><small>Quyền đã chọn</small></div></div>
        </section>

        @if($selectedRole->name === 'super-admin')<div class="system-notice matrix-system-notice"><x-ui.icon name="info" size="18" /><div><strong>Quản trị cao nhất luôn có toàn bộ quyền.</strong><span>Ma trận được khóa để tránh vô tình làm mất quyền quản trị hệ thống.</span></div></div>@endif

        <section class="permission-matrix-card card">
            <header class="permission-matrix-header">
                <div class="filter-search"><x-ui.icon name="search" size="18" /><input class="input" wire:model.live.debounce.250ms="matrixSearch" placeholder="Tìm module hoặc quyền hạn"></div>
                <div class="permission-matrix-legend"><span><i class="is-checked"></i> Được cấp</span><span><i></i> Chưa cấp</span><span><i class="is-empty">—</i> Không áp dụng</span></div>
            </header>
            @if($matrix->isEmpty())
                <x-ui.empty-state title="Không tìm thấy quyền phù hợp" description="Hãy thử tìm bằng tên module hoặc mã quyền khác." />
            @else
                <div class="permission-matrix-scroll">
                    <table class="permission-matrix-table">
                        <thead><tr>
                            <th><button type="button" wire:click="selectAll" {{ $selectedRole->name === 'super-admin' ? 'disabled' : '' }}><span class="matrix-master-check"><x-ui.icon name="check" size="14" /></span><span>Module / Menu</span></button></th>
                            @foreach($columns as $column => $label)<th><button type="button" wire:click="selectColumn('{{ $column }}')" {{ $selectedRole->name === 'super-admin' ? 'disabled' : '' }}><span>{{ $label }}</span><small>Chọn cột</small></button></th>@endforeach
                        </tr></thead>
                        <tbody>
                            @foreach($matrixGroups as $group)
                                <tr class="matrix-group-row"><th colspan="{{ count($columns) + 1 }}" scope="colgroup"><span>{{ $group['label'] }}</span><small>{{ $group['modules']->count() }} mục</small></th></tr>
                            @foreach($group['modules'] as $module => $row)@php $moduleIds=$row['permissions']->pluck('id')->map(fn($id)=>(string)$id)->all(); $moduleSelected=collect($moduleIds)->every(fn($id)=>in_array($id,array_map('strval',$permissionIds),true)); @endphp
                                <tr wire:key="permission-module-{{ \Illuminate\Support\Str::slug($module) }}">
                                    <th><button type="button" wire:click='selectModule(@json($row["permissions"]->pluck("id")->all()))' {{ $selectedRole->name === 'super-admin' ? 'disabled' : '' }}><span class="matrix-row-check {{ $moduleSelected ? 'is-selected' : '' }}"><x-ui.icon name="check" size="13" /></span><span><strong>{{ $module }}</strong></span></button></th>
                                    @foreach($columns as $column => $label)<td>
                                        @if($row['columns'][$column]->isEmpty())<span class="matrix-not-applicable">—</span>@else
                                            <div class="matrix-cell-options">@foreach($row['columns'][$column] as $permission)<label title="{{ $permission->display_name ?: $permission->name }} · {{ $permission->name }}"><input type="checkbox" wire:model.live="permissionIds" value="{{ $permission->id }}" {{ $selectedRole->name === 'super-admin' ? 'disabled' : '' }}><span><x-ui.icon name="check" size="14" /></span>@if($row['columns'][$column]->count() > 1)<small>{{ $permission->display_name ?: $permission->name }}</small>@endif</label>@endforeach</div>
                                        @endif
                                    </td>@endforeach
                                </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <footer class="permission-matrix-footer"><span><strong>{{ count(array_intersect(array_map('strval', $permissionIds), $visiblePermissionIds)) }}</strong> quyền đang được chọn cho <strong>{{ $selectedRole->display_name ?: $selectedRole->name }}</strong></span><div>@can('roles.delete')@if(!$selectedRole->is_system && $selectedRole->users_count === 0)<button class="button button-ghost matrix-delete-role" type="button" wire:click="requestDeleteSelected"><x-ui.icon name="trash" size="16" /> Xóa vai trò</button>@endif @endcan @can('roles.update')<button class="button button-primary" type="button" wire:click="savePermissions" {{ !$selectedRole ? 'disabled' : '' }}><x-ui.icon name="save" size="17" /> Lưu phân quyền</button>@endcan</div></footer>
            @endif
        </section>
    @else
        <section class="card"><x-ui.empty-state title="Chưa có vai trò để phân quyền" description="Hãy tạo vai trò đầu tiên để bắt đầu thiết lập quyền." /></section>
    @endif

    @if($showForm)<div class="access-modal" x-data x-on:keydown.escape.window="$wire.closeForm()"><button class="access-modal-backdrop" type="button" wire:click="closeForm" aria-label="Đóng"></button><section class="access-modal-panel role-details-panel" role="dialog" aria-modal="true" aria-labelledby="role-form-title">
        <header><div><span>Thông tin nhóm người dùng</span><h2 id="role-form-title">{{ $editingId ? 'Chỉnh sửa vai trò' : 'Thêm vai trò mới' }}</h2></div><button class="icon-button" type="button" wire:click="closeForm"><x-ui.icon name="x" /></button></header>
        <form wire:submit="save"><div class="access-modal-body access-form-grid">
            <div class="form-field"><label for="role-display-name">Tên hiển thị <span>*</span></label><input id="role-display-name" class="input" wire:model="display_name" autofocus>@error('display_name')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="role-name">Mã vai trò <span>*</span></label><input id="role-name" class="input" wire:model="name" {{ $name === 'super-admin' ? 'disabled' : '' }}><p class="field-help">Chữ thường, số, dấu chấm hoặc gạch ngang.</p>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field is-wide"><label for="role-description">Mô tả</label><textarea id="role-description" class="textarea" wire:model="description" placeholder="Mô tả phạm vi công việc của vai trò này"></textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
        </div><footer><button class="button button-secondary" type="button" wire:click="closeForm">Hủy</button><button class="button button-primary" type="submit"><x-ui.icon name="save" size="17" /> {{ $editingId ? 'Lưu thông tin' : 'Tạo vai trò' }}</button></footer></form>
    </section></div>@endif

    @if($pendingDeleteId)<div class="modal-backdrop" wire:click.self="cancelDelete"><section class="modal-card" role="alertdialog"><div class="modal-icon"><x-ui.icon name="alert" size="30" /></div><h2>Xóa vai trò?</h2><p>Vai trò <strong>“{{ $pendingDeleteName }}”</strong> sẽ bị xóa vĩnh viễn.</p><div class="modal-actions"><button class="button button-secondary" wire:click="cancelDelete">Hủy</button><button class="button button-danger" wire:click="confirmDelete">Xóa vai trò</button></div></section></div>@endif
</div>
