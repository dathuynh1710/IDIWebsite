<div>
    <x-admin.page-header title="Cấu hình liên lạc" description="Thiết lập trang liên hệ, biểu mẫu và các địa chỉ văn phòng bằng 3 ngôn ngữ" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.contacts.index') }}" wire:navigate><x-ui.icon name="mail" size="18" /> Quản lý thư</a>
            <x-ui.button type="submit" form="contact-settings-form" icon="save">Lưu cấu hình</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="contact-settings-form" wire:submit="saveSettings">
        @if($errors->any() && !$showLocationForm)<div class="validation-summary" role="alert"><x-ui.icon name="alert" /><div><strong>Vui lòng kiểm tra lại cấu hình.</strong><p>Có {{ $errors->count() }} trường cần chỉnh sửa.</p></div></div>@endif
        <div class="contact-settings-grid">
            <div class="contact-settings-content">
                <x-form.language-tabs :locales="$locales">
                    @foreach($locales as $locale => $label)
                        <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                            <x-form.section title="Nội dung {{ $label }}" description="Nội dung trang liên hệ dành cho phiên bản {{ $label }}" icon="languages">
                                <div class="localized-fields">
                                    <x-form.input name="page_title[{{ $locale }}]" label="Tiêu đề trang" wire:model.blur="page_title.{{ $locale }}" :required="$locale === 'vi'" />
                                    <x-form.ckeditor5-editor name="description[{{ $locale }}]" label="Mô tả chung" :model="'description.'.$locale" :value="$description[$locale] ?? ''" />
                                    <x-form.textarea name="success_message[{{ $locale }}]" label="Thông báo sau khi gửi thành công" wire:model.blur="success_message.{{ $locale }}" rows="3" maxlength="1000" />
                                    <details class="seo-panel" open><summary>Search Engine Optimization (SEO)</summary><div class="seo-grid">
                                        <x-form.input name="seo_title[{{ $locale }}]" label="Tiêu đề SEO" wire:model.blur="seo_title.{{ $locale }}" maxlength="255" />
                                        <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model.blur="meta_description.{{ $locale }}" rows="3" maxlength="500" />
                                        <div class="snippet-preview"><small>Xem trước kết quả tìm kiếm</small><strong>{{ $seo_title[$locale] ?: ($page_title[$locale] ?: 'Liên hệ IDI Seafood') }}</strong><span>idiseafood.com/{{ $locale }}/{{ $locale === 'vi' ? 'lien-he' : ($locale === 'en' ? 'contact' : 'lianxi') }}</span><p>{{ $meta_description[$locale] ?: 'Liên hệ với IDI Seafood để được hỗ trợ và tư vấn.' }}</p></div>
                                    </div></details>
                                </div>
                            </x-form.section>
                        </section>
                    @endforeach
                </x-form.language-tabs>
            </div>
        </div>
    </form>

    <section class="card contact-location-card">
        <header class="contact-location-header">
            <div><span class="contact-location-icon"><x-ui.icon name="map-pin" size="20" /></span><div><h2>Địa chỉ liên hệ</h2><p>Quản lý trụ sở và văn phòng hiển thị trên website.</p></div></div>
            <button class="button button-primary" type="button" wire:click="createLocation"><x-ui.icon name="plus" size="18" /> Thêm địa chỉ</button>
        </header>
        @if($locations->isEmpty())
            <x-ui.empty-state title="Chưa có địa chỉ liên hệ" description="Thêm trụ sở hoặc văn phòng đại diện đầu tiên." />
        @else
            <div class="table-responsive contact-location-desktop">
                <table class="data-table">
                    <thead><tr><th>Thứ tự</th><th>Tên địa chỉ</th><th>Trạng thái</th><th class="table-actions-heading">Thao tác</th></tr></thead>
                    <tbody>
                        @foreach($locations as $location)
                            <tr wire:key="office-{{ $location->id }}" @class(['is-muted-row' => !$location->is_active])>
                                <td><div class="location-order-field"><input type="number" value="{{ $location->sort_order }}" min="0" max="999999" inputmode="numeric" aria-label="Thứ tự hiển thị của {{ $location->getTranslation('name', 'vi', false) ?: 'địa chỉ #'.$location->id }}" @class(['location-order-input', 'is-invalid' => $errors->has('location_sort_orders.'.$location->id)]) wire:change="updateLocationSortOrder({{ $location->id }}, $event.target.value)" wire:keydown.enter.prevent="$el.blur()"><x-form.field-error name="location_sort_orders.{{ $location->id }}" /></div></td>
                                <td class="category-name-cell"><strong>{{ $location->getTranslation('name', 'vi', false) ?: 'Chưa có tên' }}</strong><small>{{ $location->code ?: '#'.$location->id }}</small></td>
                                <td><x-ui.badge :tone="$location->is_active ? 'success' : 'neutral'">{{ $location->is_active ? 'Hiện' : 'Ẩn' }}</x-ui.badge></td>
                                <td><div class="row-actions">
                                    <button class="icon-button" type="button" wire:click="editLocation({{ $location->id }})" title="Xem và chỉnh sửa"><x-ui.icon name="edit" size="18" /></button>
                                    <button class="icon-button" type="button" wire:click="toggleLocation({{ $location->id }})" title="{{ $location->is_active ? 'Ẩn' : 'Hiện' }}"><x-ui.icon :name="$location->is_active ? 'eye-off' : 'eye'" size="18" /></button>
                                    <button class="icon-button is-danger" type="button" wire:click="requestDelete({{ $location->id }})" title="Xóa" aria-label="Xóa địa chỉ {{ $location->getTranslation('name', 'vi', false) }}"><x-ui.icon name="trash" size="18" /></button>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="contact-location-mobile">
                @foreach($locations as $location)
                    <article><div class="contact-location-mobile-summary"><div><strong>{{ $location->getTranslation('name', 'vi', false) ?: 'Chưa có tên' }}</strong><small>{{ $location->code ?: '#'.$location->id }}</small></div><x-ui.badge :tone="$location->is_active ? 'success' : 'neutral'">{{ $location->is_active ? 'Hiện' : 'Ẩn' }}</x-ui.badge></div><button class="button button-secondary" type="button" wire:click="editLocation({{ $location->id }})">Xem và chỉnh sửa</button></article>
                @endforeach
            </div>
        @endif
    </section>

    <div class="mobile-form-actions"><a class="button button-secondary" href="{{ route('admin.contacts.index') }}" wire:navigate>Quản lý thư</a><x-ui.button type="submit" form="contact-settings-form" icon="save">Lưu cấu hình</x-ui.button></div>

    @if($showLocationForm)
        <div class="contact-detail-modal" x-data="{ locale: 'vi' }" x-on:keydown.escape.window="$wire.closeLocationForm()" role="dialog" aria-modal="true" aria-labelledby="location-form-title">
            <button class="contact-detail-backdrop" type="button" wire:click="closeLocationForm" aria-label="Đóng"></button>
            <form class="contact-detail-panel location-form-panel" wire:submit="saveLocation">
                <header class="contact-detail-header"><div><span>Địa chỉ liên hệ</span><strong id="location-form-title">{{ $editingLocationId ? 'Cập nhật văn phòng' : 'Thêm văn phòng mới' }}</strong></div><button class="icon-button" type="button" wire:click="closeLocationForm" aria-label="Đóng"><x-ui.icon name="x" /></button></header>
                <div class="contact-detail-body">
                    @if($errors->any())<div class="validation-summary" role="alert"><strong>Vui lòng kiểm tra lại thông tin địa chỉ.</strong></div>@endif
                    <div class="location-form-common">
                        <x-form.input name="location_code" label="Mã quản trị" wire:model.blur="location_code" placeholder="HEAD_OFFICE" />
                        <x-form.input name="location_sort_order" label="Thứ tự" type="number" wire:model="location_sort_order" min="0" required />
                    </div>
                    <div class="location-language-tabs">
                        <div class="tab-list" role="tablist" aria-label="Ngôn ngữ địa chỉ">
                            @foreach($locales as $code => $label)<button type="button" @click="locale='{{ $code }}'" :class="{ 'is-active': locale === '{{ $code }}' }"><span class="locale-code">{{ strtoupper($code) }}</span>{{ $label }}</button>@endforeach
                        </div>
                        @foreach($locales as $code => $label)
                            <div class="form-stack" x-show="locale === '{{ $code }}'" x-cloak>
                                <x-form.input name="location_name[{{ $code }}]" label="Tên văn phòng — {{ $label }}" wire:model.blur="location_name.{{ $code }}" :required="$code === 'vi'" />
                                <x-form.input name="location_company[{{ $code }}]" label="Công ty — {{ $label }}" wire:model.blur="location_company.{{ $code }}" />
                                <x-form.textarea name="location_address[{{ $code }}]" label="Địa chỉ — {{ $label }}" wire:model.blur="location_address.{{ $code }}" rows="3" :required="$code === 'vi'" />
                            </div>
                        @endforeach
                    </div>
                    <div class="location-contact-grid">
                        <x-form.input name="location_phone" label="Điện thoại" wire:model.blur="location_phone" />
                        <x-form.input name="location_email" label="Email" type="email" wire:model.blur="location_email" />
                        <x-form.input name="location_fax" label="Fax" wire:model.blur="location_fax" />
                    </div>
                    <section class="location-map-settings" aria-labelledby="location-map-title">
                        <div class="location-map-heading">
                            <div><span>Bản đồ</span><strong id="location-map-title">Chọn cách hiển thị bản đồ</strong></div>
                            <small>Chỉ nội dung của lựa chọn đang bật được hiển thị trên website.</small>
                        </div>
                        <div class="location-map-options" role="radiogroup" aria-label="Kiểu bản đồ">
                            @foreach([
                                'embed' => ['Google Maps Embed', 'Dán mã iframe'],
                                'google_maps' => ['Google Maps', 'Dùng liên kết chia sẻ'],
                                'image' => ['Ảnh bản đồ', 'Tải ảnh JPG, PNG hoặc WebP'],
                                'none' => ['Không hiển thị', 'Ẩn bản đồ tại địa chỉ này'],
                            ] as $type => [$label, $description])
                                <label @class(['location-map-option', 'is-selected' => $location_map_type === $type])>
                                    <input type="radio" name="location_map_type" value="{{ $type }}" wire:model.live="location_map_type">
                                    <span><strong>{{ $label }}</strong><small>{{ $description }}</small></span>
                                </label>
                            @endforeach
                        </div>

                        <div class="location-map-content" wire:key="location-map-{{ $location_map_type }}">
                            @if($location_map_type === 'embed')
                                <x-form.textarea name="location_map_embed" label="Mã nhúng Google Maps" wire:model.blur="location_map_embed" rows="5" required />
                                <div class="location-map-example">
                                    <span>Ví dụ:</span>
                                    <code>&lt;iframe src=&quot;https://www.google.com/maps/embed?pb=...&quot; width=&quot;100%&quot; height=&quot;450&quot; style=&quot;border:0&quot; allowfullscreen&gt;&lt;/iframe&gt;</code>
                                </div>
                                <div class="location-map-actions">
                                    <a class="button button-primary" href="https://www.google.com/maps" target="_blank" rel="noopener noreferrer">Tạo bản đồ</a>
                                    <span>HOẶC</span>
                                    <a class="button button-secondary" href="https://www.google.com/maps/d/" target="_blank" rel="noopener noreferrer">Tạo bản đồ tùy chỉnh</a>
                                </div>
                            @elseif($location_map_type === 'google_maps')
                                <x-form.input name="location_map_url" label="Liên kết Google Maps" type="url" wire:model.blur="location_map_url" placeholder="https://maps.app.goo.gl/..." helper="Mở địa điểm trên Google Maps, chọn Chia sẻ rồi sao chép liên kết." required />
                                <div class="location-map-example"><span>Ví dụ:</span><code>https://maps.app.goo.gl/AbCdEf123456</code></div>
                                <div class="location-map-actions"><a class="button button-primary" href="https://www.google.com/maps" target="_blank" rel="noopener noreferrer">Mở Google Maps</a></div>
                            @elseif($location_map_type === 'image')
                                <div class="form-field">
                                    <label for="location-map-image">Ảnh bản đồ <span aria-hidden="true">*</span></label>
                                    @if($location_map_image)
                                        <div class="location-map-image-preview"><img src="{{ $location_map_image->temporaryUrl() }}" alt="Xem trước ảnh bản đồ vừa chọn"></div>
                                    @elseif($existingMapImageUrl)
                                        <div class="location-map-image-preview"><img src="{{ $existingMapImageUrl }}" alt="Ảnh bản đồ hiện tại"></div>
                                    @endif
                                    <input id="location-map-image" class="input" type="file" wire:model="location_map_image" accept=".jpg,.jpeg,.png,.webp">
                                    <p class="field-help">Nên dùng ảnh ngang, rõ nét; dung lượng tối đa 8 MB.</p>
                                    <span class="field-help" wire:loading wire:target="location_map_image">Đang tải ảnh lên…</span>
                                    <x-form.field-error name="location_map_image" />
                                </div>
                            @else
                                <div class="location-map-none"><x-ui.icon name="map-pin" size="22" /><div><strong>Không hiển thị bản đồ</strong><p>Thông tin địa chỉ, điện thoại và email vẫn được hiển thị bình thường.</p></div></div>
                            @endif
                        </div>
                    </section>
                    <x-form.switch name="location_is_active" label="Hiển thị địa chỉ trên website" wire:model="location_is_active" />
                </div>
                <footer class="contact-detail-footer"><button class="button button-secondary" type="button" wire:click="closeLocationForm">Hủy</button><x-ui.button type="submit" icon="save">Lưu địa chỉ</x-ui.button></footer>
            </form>
        </div>
    @endif

    @if($pendingDeleteId)
        <x-ui.delete-confirmation-modal wire-key="contact-location-delete-confirmation" title="Xóa địa chỉ liên hệ?" confirm-label="Có, xóa địa chỉ" warning="Địa chỉ sẽ không còn xuất hiện trên website.">
            Bạn sắp chuyển địa chỉ <strong>“{{ $pendingDeleteName }}”</strong> vào thùng rác.
        </x-ui.delete-confirmation-modal>
    @endif
</div>
