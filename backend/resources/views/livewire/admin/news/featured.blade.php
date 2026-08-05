<div>
    <x-admin.page-header title="Quản lý tin tiêu điểm" description="Chọn và sắp xếp tối đa {{ $limit }} bài viết nổi bật trên trang chủ." :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.news.posts.index') }}" wire:navigate>
                <x-ui.icon name="arrow-left" size="18" /> Quản lý tin tức
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <section class="card featured-manager-card">
        <header class="featured-manager-header">
            <div>
                <span class="featured-manager-eyebrow">Khu vực trang chủ</span>
                <h2>Tin đang nổi bật</h2>
                <p>Sắp xếp vị trí hiển thị hoặc bỏ những bài không còn cần ưu tiên.</p>
            </div>
            <div class="featured-capacity" aria-label="Đã dùng {{ $featured->count() }} trên {{ $limit }} vị trí">
                <div class="featured-capacity-copy">
                    <strong>{{ $featured->count() }}/{{ $limit }}</strong>
                    <span>{{ $remainingSlots > 0 ? $remainingSlots.' vị trí còn trống' : 'Đã dùng hết vị trí' }}</span>
                </div>
                <div class="featured-capacity-track" aria-hidden="true">
                    <span style="width: {{ $limit > 0 ? min(100, ($featured->count() / $limit) * 100) : 0 }}%"></span>
                </div>
            </div>
        </header>

        <div class="featured-slot-grid">
            @foreach($featured as $index => $item)
                <article class="featured-slot-card" wire:key="featured-slot-{{ $item->id }}">
                    <label class="featured-slot-select" title="Chọn để bỏ khỏi tin tiêu điểm">
                        <input type="checkbox" wire:model.live="featuredSelected" value="{{ $item->id }}">
                        <span aria-hidden="true"><x-ui.icon name="check" size="14" /></span>
                    </label>
                    <div class="featured-slot-image">
                        @if($item->featuredMedia)
                            <img src="{{ $item->featuredMedia->url }}" alt="">
                        @else
                            <span><x-ui.icon name="image" size="30" /> Chưa có ảnh</span>
                        @endif
                        <span class="featured-slot-number">Vị trí {{ $index + 1 }}</span>
                    </div>
                    <div class="featured-slot-body">
                        <span class="featured-slot-category">{{ $item->category?->getTranslation('name', $locale, false) ?: 'Chưa phân loại' }}</span>
                        <h3>{{ $item->getTranslation('title', $locale, false) ?: $item->getTranslation('title', 'vi', false) }}</h3>
                        <label class="featured-order-field">
                            <span>Ưu tiên</span>
                            <input class="input" type="number" min="0" wire:model="sortOrders.{{ $item->id }}" aria-label="Thứ tự ưu tiên của {{ $item->getTranslation('title', $locale, false) }}">
                        </label>
                    </div>
                </article>
            @endforeach

            @for($slot = $featured->count(); $slot < $limit; $slot++)
                <div class="featured-slot-card is-empty" aria-label="Vị trí {{ $slot + 1 }} đang trống">
                    <span class="featured-empty-icon"><x-ui.icon name="plus" size="24" /></span>
                    <strong>Vị trí {{ $slot + 1 }}</strong>
                    <small>Chọn bài viết ở danh sách bên dưới</small>
                </div>
            @endfor
        </div>

        <footer class="featured-manager-actions">
            <span>{{ count($featuredSelected) > 0 ? 'Đã chọn '.count($featuredSelected).' tin' : 'Chọn các tin cần bỏ khỏi khu vực này' }}</span>
            <div>
                <button class="button button-secondary" type="button" wire:click="updateOrder" @disabled($featured->isEmpty())>
                    <x-ui.icon name="save" size="17" /> Lưu thứ tự
                </button>
                <button class="button button-danger" type="button" wire:click="removeFeatured" @disabled(!$featuredSelected)>
                    <x-ui.icon name="trash" size="17" /> Bỏ tin đã chọn
                </button>
            </div>
        </footer>
    </section>

    <section class="card featured-library-card">
        <header class="featured-library-header">
            <div>
                <span class="featured-manager-eyebrow">Kho nội dung</span>
                <h2>Chọn bài viết</h2>
                <p>Chỉ hiển thị các bài đang hoạt động và đã bật bản dịch {{ strtoupper($locale) }}.</p>
            </div>
            <div class="featured-library-summary">
                <strong>{{ $available->total() }}</strong>
                <span>bài có thể chọn</span>
            </div>
        </header>

        <div class="featured-filter-bar">
            <div class="form-field featured-filter-search">
                <label for="featured-search">Tìm kiếm</label>
                <div><x-ui.icon name="search" size="17" /><input id="featured-search" class="input" wire:model.live.debounce.350ms="search" placeholder="Nhập tiêu đề bài viết..."></div>
            </div>
            <div class="form-field">
                <label for="featured-category">Chuyên mục</label>
                <select id="featured-category" class="select" wire:model.live="category">
                    <option value="">Tất cả chuyên mục</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->getTranslation('name', $locale, false) ?: $cat->getTranslation('name', 'vi', false) }}</option>
                    @endforeach
                </select>
            </div>
            <x-form.select name="locale" label="Ngôn ngữ" :options="['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文']" wire:model.live="locale" />
        </div>

        <div class="featured-selection-toolbar">
            <span>
                @if($remainingSlots === 0)
                    Đã đủ {{ $limit }} vị trí tiêu điểm
                @elseif(count($selected) > 0)
                    Đã chọn <strong>{{ count($selected) }}</strong> bài · Còn <strong>{{ $remainingSlots }}</strong> vị trí
                @else
                    Chọn tối đa {{ $remainingSlots }} bài để thêm
                @endif
            </span>
            <button class="button button-primary" type="button" wire:click="addFeatured" @disabled(!$selected || $remainingSlots === 0)>
                <x-ui.icon name="plus" size="17" /> Thêm vào tin tiêu điểm
            </button>
        </div>

        @if($available->isEmpty())
            <x-ui.empty-state title="Không có bài viết phù hợp" description="Thử thay đổi từ khóa, chuyên mục hoặc ngôn ngữ." icon="newspaper" />
        @else
            <div class="table-responsive featured-available-desktop">
                <table class="data-table featured-available-table">
                    <thead><tr><th></th><th>Bài viết</th><th>Chuyên mục</th><th>Cập nhật</th></tr></thead>
                    <tbody>
                        @foreach($available as $item)
                            <tr wire:key="available-featured-{{ $item->id }}">
                                <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}" aria-label="Chọn {{ $item->getTranslation('title', $locale, false) }}"></td>
                                <td><div class="product-cell"><div class="product-thumb">@if($item->featuredMedia)<img src="{{ $item->featuredMedia->url }}" alt="">@else<x-ui.icon name="image" />@endif</div><div><strong>{{ $item->getTranslation('title', $locale, false) }}</strong><small>/{{ $locale }}/news/{{ $item->getTranslation('slug', $locale, false) }}</small></div></div></td>
                                <td>{{ $item->category?->getTranslation('name', $locale, false) ?: 'Chưa phân loại' }}</td>
                                <td><time datetime="{{ $item->updated_at?->toIso8601String() }}">{{ $item->updated_at?->format('d/m/Y H:i') }}</time></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="featured-available-mobile">
                @foreach($available as $item)
                    <label class="featured-mobile-item" wire:key="mobile-available-featured-{{ $item->id }}">
                        <input type="checkbox" wire:model.live="selected" value="{{ $item->id }}">
                        <div class="product-thumb">@if($item->featuredMedia)<img src="{{ $item->featuredMedia->url }}" alt="">@else<x-ui.icon name="image" />@endif</div>
                        <span><strong>{{ $item->getTranslation('title', $locale, false) }}</strong><small>{{ $item->category?->getTranslation('name', $locale, false) ?: 'Chưa phân loại' }}</small></span>
                    </label>
                @endforeach
            </div>

            <x-ui.pagination :paginator="$available" :per-page-options="$perPageOptions" />
        @endif
    </section>
</div>
