<div>
    <x-admin.page-header :title="$itemId ? 'Sửa mục menu' : 'Thêm mục menu'" :description="$menu->name">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.menus.index', ['menuId' => $menu->id]) }}" wire:navigate>Quay lại</a>
            <x-ui.button type="submit" form="menu-item-form" icon="save">Lưu mục menu</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>
    <form id="menu-item-form" wire:submit="save" class="category-form-grid">
        <aside class="category-form-sidebar">
            <x-form.section title="Cấu trúc và liên kết" description="Thứ tự nhỏ hiển thị trước trong cùng cấp." icon="menu">
                <div class="form-stack">
                    <x-form.select name="parent_id" label="Mục cha" :options="$parentOptions" placeholder="Không có (cấp gốc)" wire:model="parent_id" />
                    <x-form.input name="sort_order" label="Thứ tự" type="number" min="0" max="999999" wire:model="sort_order" />
                    <x-form.switch name="is_active" label="Hiển thị (ẩn cha sẽ ẩn cả nhánh)" wire:model="is_active" />
                    <x-form.select name="link_type" label="Loại liên kết" :options="['internal' => 'URL nội bộ', 'external' => 'URL bên ngoài', 'page' => 'Trang CMS Giới thiệu', 'product_category' => 'Nhóm sản phẩm']" wire:model.live="link_type" />
                    @if($link_type === 'page')
                        <x-form.select name="page_id" label="Trang CMS" :options="$pageOptions" placeholder="Chọn trang" wire:model="page_id" />
                        <p>URL lấy theo slug của trang và ngôn ngữ đang xem. Thiếu bản dịch hoặc slug: nhãn vẫn hiển thị nhưng không có liên kết.</p>
                    @elseif($link_type === 'product_category')
                        <x-form.select name="product_category_id" label="Nhóm sản phẩm" :options="$categoryOptions" placeholder="Chọn nhóm" wire:model="product_category_id" />
                    @else
                        <x-form.input name="url" label="URL" wire:model="url" placeholder="/about hoặc https://example.com" />
                    @endif
                </div>
            </x-form.section>
        </aside>
        <div class="category-form-content">
            <x-form.section title="Nhãn menu" description="Nhãn thiếu bản dịch sẽ dùng tiếng Việt." icon="languages">
                <div class="form-stack">
                    @foreach($locales as $locale => $name)
                        <x-form.input name="label[{{ $locale }}]" :label="$name" wire:model="label.{{ $locale }}" :required="$locale === 'vi'" />
                    @endforeach
                </div>
            </x-form.section>
        </div>
    </form>
</div>
