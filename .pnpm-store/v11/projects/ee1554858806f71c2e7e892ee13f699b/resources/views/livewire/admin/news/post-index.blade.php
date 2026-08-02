<div>
    <x-admin.page-header title="Quản lý tin tức" description="Tìm kiếm, xuất bản và quản lý bài viết theo từng ngôn ngữ." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><a class="button button-primary" href="{{ route('admin.news.posts.create') }}" wire:navigate><x-ui.icon name="plus" size="18" /> Thêm tin mới</a></x-slot:actions>
    </x-admin.page-header>
    <section class="card filter-card"><div class="news-filter-grid">
        <div class="form-field filter-search"><label>Tìm kiếm</label><div><x-ui.icon name="search" size="17" /><input class="input" wire:model.live.debounce.350ms="search" placeholder="Tiêu đề, đường dẫn hoặc mã..."></div></div>
        <div class="form-field"><label>Chuyên mục</label><select class="select" wire:model.live="category"><option value="">Tất cả</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->getTranslation('name', $locale, false) ?: $cat->getTranslation('name', 'vi', false) }}</option>@endforeach</select></div>
        <x-form.input name="date_from" label="Từ ngày" type="date" wire:model.live="date_from" />
        <x-form.input name="date_to" label="Đến ngày" type="date" wire:model.live="date_to" />
        <x-form.select name="active" label="Trạng thái" :options="['' => 'Tất cả', '1' => 'Đang hiện', '0' => 'Đang ẩn']" wire:model.live="active" />
        <x-form.select name="locale" label="Ngôn ngữ" :options="['vi' => 'VI', 'en' => 'EN', 'zh' => '中文']" wire:model.live="locale" />
    </div></section>
    <section class="card category-list-card">
        <div class="category-toolbar"><div class="category-toolbar-actions"><button class="button button-success" wire:click="bulk('show')" @disabled(!$selected)>Hiện</button><button class="button button-secondary" wire:click="bulk('hide')" @disabled(!$selected)>Ẩn</button><button class="button button-secondary" wire:click="bulk('reorder')" @disabled(!$selected)>Cập nhật thứ tự</button><button class="button button-danger" wire:click="bulk('delete')" wire:confirm="Xóa các tin đã chọn?" @disabled(!$selected)>Xóa</button></div><span>{{ $posts->total() }} tin</span></div>
        @if($posts->isEmpty())<x-ui.empty-state title="Chưa có tin tức" description="Tạo bài viết đầu tiên hoặc thay đổi bộ lọc." icon="newspaper" />
        @else<div class="table-responsive news-desktop-list"><table class="data-table category-table"><thead><tr><th></th><th>Thứ tự</th><th>Tiêu đề ({{ strtoupper($locale) }})</th><th>Chuyên mục</th><th>Bản dịch</th><th>Trạng thái</th><th></th></tr></thead><tbody>
            @foreach($posts as $item)<tr wire:key="post-{{ $item->id }}" @class(['is-muted-row' => !$item->is_active])>
                <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}"></td><td><input class="input order-input" type="number" wire:model="sortOrders.{{ $item->id }}"></td>
                <td><div class="product-cell"><div class="product-thumb">@if($item->featuredMedia)<img src="{{ $item->featuredMedia->url }}" alt="">@else<x-ui.icon name="image" />@endif</div><div><strong>{{ $item->getTranslation('title', $locale, false) ?: 'Chưa có bản dịch' }}</strong><small><x-ui.icon name="link" size="12" /> /{{ $locale }}/news/{{ $item->getTranslation('slug', $locale, false) }} · {{ $item->updated_at->format('d/m/Y H:i') }}</small></div></div></td>
                <td>{{ $item->category?->getTranslation('name', $locale, false) ?: '—' }}</td>
                <td><div class="translation-dots">@foreach(['vi','en','zh'] as $code)<span class="{{ filled($item->getTranslation('title', $code, false)) ? 'is-complete' : '' }}">{{ strtoupper($code) }}</span>@endforeach</div></td>
                <td><div class="news-status-stack"><x-ui.badge :tone="$item->is_active ? 'success' : 'neutral'">{{ $item->is_active ? 'Hiện' : 'Ẩn' }}</x-ui.badge>@if($item->is_featured)<x-ui.badge tone="warning">Tiêu điểm</x-ui.badge>@endif</div></td>
                <td><div class="row-actions"><a class="icon-button" href="{{ route('admin.news.posts.edit', $item) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="18" /></a><a class="icon-button is-dark" href="{{ route('admin.news.posts.preview', ['post' => $item, 'locale' => $locale]) }}" target="_blank" title="Xem trước"><x-ui.icon name="eye" size="18" /></a><button class="icon-button" wire:click="duplicate({{ $item->id }})" title="Nhân bản"><x-ui.icon name="copy" size="18" /></button><button class="icon-button is-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xóa tin này?" title="Xóa"><x-ui.icon name="trash" size="18" /></button></div></td>
            </tr>@endforeach
        </tbody></table></div><x-ui.pagination :paginator="$posts" />@endif
    </section>
</div>
