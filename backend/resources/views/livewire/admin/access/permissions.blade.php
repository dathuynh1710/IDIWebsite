<div class="access-page">
    <x-admin.page-header title="Danh mục quyền hạn" description="Danh sách các quyền đang được sử dụng trong hệ thống" :breadcrumbs="$breadcrumbs" />

    <section class="access-intro card">
        <span><x-ui.icon name="info" size="24" /></span>
        <div>
            <strong>Danh mục quyền hệ thống</strong>
            <p>Màn hình này chỉ dùng để tra cứu mã quyền, module và số vai trò đang sử dụng.</p>
        </div>
        <a href="{{ route('admin.access.roles') }}" wire:navigate>Gán quyền cho vai trò <x-ui.icon name="chevron-right" size="16" /></a>
    </section>

    <section class="filter-card card">
        <div class="permission-filter-grid">
            <div class="filter-search">
                <label for="permission-search">Tìm kiếm</label>
                <div><x-ui.icon name="search" size="18" /><input id="permission-search" class="input" wire:model.live.debounce.300ms="search" placeholder="Tên, mã hoặc mô tả quyền"></div>
            </div>
            <div>
                <label for="permission-module">Module</label>
                <select id="permission-module" class="select" wire:model.live="moduleFilter"><option value="">Tất cả module</option>@foreach($modules as $moduleName)<option>{{ $moduleName }}</option>@endforeach</select>
            </div>
            <button class="button button-ghost" wire:click="resetFilters">Đặt lại</button>
        </div>
    </section>

    <section class="card access-list-card">
        @if($permissions->isEmpty())
            <x-ui.empty-state title="Không tìm thấy quyền hạn" description="Hãy thay đổi bộ lọc để tìm quyền phù hợp." />
        @else
            <div class="table-responsive">
                <table class="data-table permission-table">
                    <thead><tr><th>Quyền hạn</th><th>Module</th><th>Loại</th><th>Vai trò sử dụng</th></tr></thead>
                    <tbody>
                        @foreach($permissions as $permission)
                            <tr wire:key="permission-{{ $permission->id }}">
                                <td><div class="permission-name"><strong>{{ $permission->display_name ?: $permission->name }}</strong><code>{{ $permission->name }}</code>@if($permission->description)<small>{{ $permission->description }}</small>@endif</div></td>
                                <td><span class="access-chip">{{ $permission->module ?: 'Khác' }}</span></td>
                                <td><x-ui.badge :tone="$permission->is_system ? 'info' : 'neutral'">{{ $permission->is_system ? 'Hệ thống' : 'Tùy chỉnh' }}</x-ui.badge></td>
                                <td><strong>{{ $permission->roles_count }}</strong> vai trò</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-ui.pagination :paginator="$permissions" />
        @endif
    </section>
</div>
