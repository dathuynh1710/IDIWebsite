<div>
    <x-admin.page-header title="Quản lý danh mục tin tức" description="Tổ chức chuyên mục và đường dẫn theo từng ngôn ngữ." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><a class="button button-primary" href="{{ route('admin.news.categories.create') }}" wire:navigate><x-ui.icon name="plus" size="18" /> Thêm danh mục</a></x-slot:actions>
    </x-admin.page-header>
    <section class="card filter-card"><div class="category-filter-grid">
        <x-form.input name="search" label="Tìm kiếm" wire:model.live.debounce.350ms="search" placeholder="Tên danh mục..." />
        <x-form.select name="active" label="Trạng thái" :options="['' => 'Tất cả', '1' => 'Đang hiện', '0' => 'Đang ẩn']" wire:model.live="active" />
        <x-form.select name="locale" label="Ngôn ngữ" :options="['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文']" wire:model.live="locale" />
    </div></section>
    <section class="card category-list-card">
        <div class="category-toolbar"><div class="category-toolbar-actions">
            <button class="button button-success" wire:click="bulk('show')" @disabled(!$selected)>Hiện</button>
            <button class="button button-secondary" wire:click="bulk('hide')" @disabled(!$selected)>Ẩn</button>
            <button class="button button-secondary" wire:click="bulk('reorder')" @disabled(!$selected)>Cập nhật thứ tự</button>
            <button class="button button-danger" wire:click="bulk('delete')" wire:confirm="Xóa các danh mục đã chọn?" @disabled(!$selected)>Xóa</button>
        </div><span class="category-selection-count">{{ $categories->total() }} danh mục</span></div>
        @if($categories->isEmpty())<x-ui.empty-state title="Chưa có danh mục" description="Hãy tạo danh mục đầu tiên cho tin tức." icon="folder" />
        @else<div class="table-responsive"><table class="data-table category-table"><thead><tr><th></th><th>Thứ tự</th><th>Tiêu đề ({{ strtoupper($locale) }})</th><th>Danh mục cha</th><th>Số tin</th><th>Bản dịch</th><th>Trạng thái</th><th></th></tr></thead><tbody>
            @foreach($categories as $item)<tr wire:key="category-{{ $item->id }}" @class(['is-muted-row' => !$item->is_active])>
                <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}"></td>
                <td><input class="input order-input" type="number" wire:model="sortOrders.{{ $item->id }}"></td>
                <td class="category-name-cell"><strong>{{ $item->getTranslation('name', $locale, false) ?: 'Chưa có bản dịch' }}</strong><small><x-ui.icon name="link" size="13" /> /{{ $locale }}/tin-tuc/{{ $item->getTranslation('slug', $locale, false) }}</small></td>
                <td>{{ $item->parent?->getTranslation('name', $locale, false) ?: '— Gốc —' }}</td><td><span class="category-product-count">{{ $item->posts_count }}</span></td>
                <td><div class="translation-dots">@foreach(['vi','en','zh'] as $code)<span class="{{ filled($item->getTranslation('name', $code, false)) ? 'is-complete' : '' }}">{{ strtoupper($code) }}</span>@endforeach</div></td>
                <td><x-ui.badge :tone="$item->is_active ? 'success' : 'neutral'">{{ $item->is_active ? 'Hiện' : 'Ẩn' }}</x-ui.badge></td>
                <td><div class="row-actions"><a class="icon-button" href="{{ route('admin.news.categories.edit', $item) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="18" /></a><button class="icon-button is-dark" wire:click="toggleVisibility({{ $item->id }})" title="Ẩn/hiện"><x-ui.icon :name="$item->is_active ? 'eye-off' : 'eye'" size="18" /></button><button class="icon-button is-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xóa danh mục này?" title="Xóa"><x-ui.icon name="trash" size="18" /></button></div></td>
            </tr>@endforeach
        </tbody></table></div><x-ui.pagination :paginator="$categories" />@endif
    </section>
</div>
