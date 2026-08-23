<div>
    <x-admin.page-header title="Quản lý Recipes" description="Quản lý danh sách công thức đa ngôn ngữ của website" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('recipes.create')<a class="button button-primary" href="{{ route('admin.recipes.create') }}" wire:navigate><x-ui.icon name="plus" size="18" /> Thêm Recipe</a>@endcan
        </x-slot:actions>
    </x-admin.page-header>

    <nav class="locale-filter card" aria-label="Ngôn ngữ nội dung">
        <span>Ngôn ngữ đang xem:</span>
        @foreach(config('admin.locales') as $code => $label)
            <button type="button" wire:click="$set('locale', '{{ $code }}')" class="{{ $locale === $code ? 'is-active' : '' }}"><span>{{ strtoupper($code) }}</span>{{ $label }}</button>
        @endforeach
    </nav>

    <section class="filter-card card">
        <div class="recipe-filter-grid">
            <div class="filter-search"><label for="recipe-search">Tìm kiếm</label><div><x-ui.icon name="search" size="18" /><input id="recipe-search" class="input" wire:model.live.debounce.300ms="search" placeholder="Tiêu đề, mã hoặc đường dẫn"></div></div>
            <div><label for="recipe-from">Ngày đăng từ</label><input id="recipe-from" class="input" type="date" wire:model.live="date_from"></div>
            <div><label for="recipe-to">Đến ngày</label><input id="recipe-to" class="input" type="date" wire:model.live="date_to"></div>
            <div><label for="recipe-status">Trạng thái</label><select id="recipe-status" class="select" wire:model.live="active"><option value="">Tất cả</option><option value="1">Đang hiển thị</option><option value="0">Đang ẩn</option></select></div>
            <div class="filter-actions"><button class="button button-ghost" type="button" wire:click="resetFilters">Đặt lại</button></div>
        </div>
    </section>

    <section class="card category-list-card">
        @if($recipes->isEmpty())
            <x-ui.empty-state title="Không tìm thấy công thức" description="Thử thay đổi bộ lọc hoặc thêm công thức mới." />
        @else
            <div class="category-toolbar">
                <div class="category-toolbar-actions">
                    <button class="button button-success" type="button" wire:click="bulk('show')" @disabled($selected === [])>Hiện</button>
                    <button class="button button-secondary" type="button" wire:click="bulk('hide')" @disabled($selected === [])>Ẩn</button>
                    <button class="button button-secondary" type="button" wire:click="bulk('reorder')" @disabled($selected === [])>Cập nhật thứ tự</button>
                    <button class="button button-danger" type="button" wire:click="requestBulkDelete" @disabled($selected === [])>Xóa</button>
                </div>
                <span class="category-selection-count">Đã chọn {{ count($selected) }} / {{ $recipes->total() }} công thức</span>
            </div>
            <div class="table-responsive recipe-desktop-list">
                <table class="data-table category-table">
                    <thead><tr><th class="selection-column"></th><th class="order-column">Thứ tự</th><th>Công thức ({{ strtoupper($locale) }})</th><th>Ngày đăng</th><th>Video</th><th>Bản dịch</th><th>Trạng thái</th><th class="table-actions-heading">Thao tác</th></tr></thead>
                    <tbody>
                        @foreach($recipes as $item)
                            @php $translations = $item->getTranslations('title'); $statuses = $item->getTranslations('translation_status'); @endphp
                            <tr wire:key="recipe-{{ $item->id }}" @class(['is-muted-row' => !$item->is_active])>
                                <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}" aria-label="Chọn công thức {{ $item->id }}"></td>
                                <td><input class="input order-input" type="number" min="0" wire:model="sortOrders.{{ $item->id }}" aria-label="Thứ tự {{ $item->id }}"></td>
                                <td><div class="product-cell"><div class="product-thumb" x-data="{ failed: false }">@if($item->featuredMedia)<img src="{{ $item->featuredMedia->url }}" alt="" x-show="!failed" x-on:error="failed=true"><span x-show="failed" x-cloak><x-ui.icon name="image" /></span>@else<x-ui.icon name="image" />@endif</div><div><strong>{{ $item->getTranslation('title', $locale, false) ?: 'Chưa có bản dịch' }}</strong></div></div></td>
                                <td>{{ ($date = $item->getTranslation('locale_published_at', $locale, false)) ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') : '—' }}</td>
                                <td><x-ui.badge :tone="$item->videoMedia ? 'info' : 'neutral'">{{ $item->videoMedia ? 'Có video' : 'Không' }}</x-ui.badge></td>
                                <td><div class="translation-dots">@foreach(['vi','en','zh'] as $code)<span class="{{ filled($translations[$code] ?? null) ? 'is-complete' : '' }}" title="{{ strtoupper($code) }}: {{ $statuses[$code] ?? 'chưa có' }}">{{ strtoupper($code) }}</span>@endforeach</div></td>
                                <td><x-ui.badge :tone="$item->is_active ? 'success' : 'neutral'">{{ $item->is_active ? 'Hiện' : 'Ẩn' }}</x-ui.badge></td>
                                <td><div class="row-actions">
                                    @can('recipes.update')<a class="icon-button" href="{{ route('admin.recipes.edit', $item) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="18" /></a>@endcan
                                    @can('recipes.view')<a class="icon-button is-dark" href="{{ route('admin.recipes.preview', ['recipe' => $item, 'locale' => $locale]) }}" target="_blank" rel="noopener" title="Mở trang xem trước" aria-label="Mở trang xem trước"><x-ui.icon name="external-link" size="18" /></a>@endcan
                                    @can('recipes.update')<button class="icon-button" type="button" wire:click="toggleVisibility({{ $item->id }})" title="{{ $item->is_active ? 'Ẩn khỏi website' : 'Hiện trên website' }}" aria-label="{{ $item->is_active ? 'Ẩn khỏi website' : 'Hiện trên website' }}"><x-ui.icon :name="$item->is_active ? 'eye-off' : 'eye'" size="18" /></button>@endcan
                                    @can('recipes.create')<button class="icon-button" type="button" wire:click="duplicate({{ $item->id }})" title="Nhân bản"><x-ui.icon name="copy" size="18" /></button>@endcan
                                    @can('recipes.delete')<button class="icon-button is-danger" type="button" wire:click="requestDelete({{ $item->id }})" title="Xóa"><x-ui.icon name="trash" size="18" /></button>@endcan
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="recipe-mobile-list">
                @foreach($recipes as $item)
                    <article class="product-mobile-card" wire:key="mobile-recipe-{{ $item->id }}"><div class="product-cell"><div class="product-thumb">@if($item->featuredMedia)<img src="{{ $item->featuredMedia->url }}" alt="">@else<x-ui.icon name="image" />@endif</div><div><strong>{{ $item->getTranslation('title', $locale, false) ?: 'Chưa có bản dịch' }}</strong><small>Đăng {{ ($date = $item->getTranslation('locale_published_at', $locale, false)) ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') : '—' }}</small></div></div><div class="mobile-card-actions">@can('recipes.update')<a class="button button-secondary" href="{{ route('admin.recipes.edit', $item) }}" wire:navigate><x-ui.icon name="edit" size="18" /> Sửa</a>@endcan</div></article>
                @endforeach
            </div>
            <x-ui.pagination :paginator="$recipes" :per-page-options="[5, 10, 20, 50, 100]" />
        @endif
    </section>

    @if($pendingDeleteId || $pendingBulkDelete)
        <div
            class="modal-backdrop contact-delete-modal"
            wire:key="recipe-delete-confirmation"
            wire:click.self="cancelDelete"
            x-data
            x-init="$nextTick(() => $refs.cancelButton.focus())"
            x-on:keydown.escape.window="$wire.cancelDelete()"
        >
            <section class="modal-card contact-delete-card" role="alertdialog" aria-modal="true" aria-labelledby="recipe-delete-title" aria-describedby="recipe-delete-description">
                <div class="modal-icon contact-delete-icon"><x-ui.icon name="alert" size="30" /></div>
                <h2 id="recipe-delete-title">Xóa Recipe?</h2>
                <p id="recipe-delete-description">
                    @if($pendingBulkDelete)
                        Bạn sắp chuyển <strong>{{ count($selected) }} công thức</strong> đã chọn vào thùng rác.
                    @else
                        Bạn sắp chuyển công thức <strong>“{{ $pendingDeleteName }}”</strong> vào thùng rác.
                    @endif
                </p>
                <p class="contact-delete-warning">Công thức sẽ không còn xuất hiện trong danh sách quản lý.</p>
                <div class="modal-actions contact-delete-actions">
                    <button class="button button-secondary" type="button" wire:click="cancelDelete" x-ref="cancelButton">Không, giữ lại</button>
                    <button class="button button-danger" type="button" wire:click="confirmDelete" wire:loading.attr="disabled" wire:target="confirmDelete">
                        <x-ui.icon name="trash" size="17" />
                        <span wire:loading.remove wire:target="confirmDelete">Có, xóa Recipe</span>
                        <span wire:loading wire:target="confirmDelete">Đang xóa...</span>
                    </button>
                </div>
            </section>
        </div>
    @endif
</div>
