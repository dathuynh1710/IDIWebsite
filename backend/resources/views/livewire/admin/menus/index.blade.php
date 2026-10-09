<div>
    <x-admin.page-header title="Quản lý menu" description="Quản lý cây menu chính và footer. Ẩn mục cha sẽ ẩn toàn bộ nhánh.">
        <x-slot:actions>
            @if($menu)
                <a class="button button-primary" href="{{ route('admin.menus.items.create', $menu) }}" wire:navigate>Thêm mục menu</a>
            @endif
        </x-slot:actions>
    </x-admin.page-header>
    <div class="form-stack">
        <x-form.section title="Vị trí menu" description="Website sử dụng main cho header/mobile và footer cho chân trang. Mã khác dành cho vị trí tích hợp sau." icon="menu">
            <x-form.select name="menuId" label="Chọn menu" :options="$menus" wire:model.live="menuId" />
            @if($menu)
                <p>Mã vị trí: <strong>{{ $menu->location }}</strong></p>
                <button type="button" class="button button-secondary" wire:click="toggleMenu">{{ $menu->is_active ? 'Ẩn toàn bộ menu' : 'Hiện toàn bộ menu' }}</button>
            @endif
            <details>
                <summary>Tạo vị trí menu mới</summary>
                <form wire:submit="createMenu" class="form-stack">
                    <x-form.input name="name" label="Tên quản trị" wire:model="name" />
                    <x-form.input name="location" label="Mã vị trí (main, footer...)" wire:model="location" />
                    <x-ui.button type="submit">Tạo menu</x-ui.button>
                </form>
            </details>
        </x-form.section>
        <div class="card">
            <x-ui.data-table label="Cây menu website">
            <table class="data-table">
                <thead><tr><th>Mục menu (theo cấp)</th><th>Loại liên kết</th><th>Thứ tự</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                <tbody>
                @forelse($rows as $row)
                    @php($item = $row['item'])
                    <tr wire:key="menu-item-{{ $item->id }}">
                        <td>{{ str_repeat('— ', $row['depth']) }}{{ $item->getTranslation('label', 'vi', false) }}</td>
                        <td>{{ ['page' => 'Trang CMS', 'internal' => 'URL nội bộ', 'external' => 'URL ngoài', 'product_category' => 'Nhóm sản phẩm'][$item->link_type] ?? $item->link_type }}</td>
                        <td>{{ $item->sort_order }}</td>
                        <td>{{ $item->is_active ? 'Hiển thị' : 'Ẩn' }}</td>
                        <td>
                            <a class="button button-secondary" href="{{ route('admin.menus.items.edit', [$menu, $item]) }}" wire:navigate>Sửa / chuyển / sắp xếp</a>
                            <button class="button button-secondary" type="button" wire:click="toggleVisibility({{ $item->id }})">{{ $item->is_active ? 'Ẩn' : 'Hiện' }}</button>
                            <button class="button button-danger" type="button" wire:click="requestDelete({{ $item->id }})">Xóa</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Chưa có mục menu.</td></tr>
                @endforelse
                </tbody>
            </table>
            </x-ui.data-table>
        </div>
    </div>
    @if($pendingDeleteId)
        <div class="modal-backdrop" wire:key="menu-delete" x-data @keydown.escape.window="$wire.cancelDelete()">
            <section class="modal-card" role="alertdialog" aria-modal="true" aria-labelledby="menu-delete-title">
                <h2 id="menu-delete-title">Xóa mục menu #{{ $pendingDeleteId }}?</h2>
                <p>Không thể hoàn tác. Nếu có mục con, hãy chuyển hoặc xóa các mục con trước. Nội dung CMS không bị xóa.</p>
                <x-form.field-error name="delete" />
                <div class="modal-actions">
                    <button class="button button-secondary" type="button" wire:click="cancelDelete">Hủy</button>
                    <button class="button button-danger" type="button" wire:click="confirmDelete" wire:loading.attr="disabled">Xác nhận xóa</button>
                </div>
            </section>
        </div>
    @endif
</div>
