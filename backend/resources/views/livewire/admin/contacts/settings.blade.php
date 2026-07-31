<div>
    <x-admin.page-header title="Cấu hình liên lạc" description="Thiết lập trang liên hệ, biểu mẫu và các địa chỉ văn phòng bằng 3 ngôn ngữ" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.contacts.index') }}" wire:navigate><x-ui.icon name="mail" size="18" /> Quản lý thư</a>
            <x-ui.button type="submit" form="contact-settings-form" icon="save">Lưu cấu hình</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="contact-settings-form" wire:submit="saveSettings">
        @if($errors->any() && !$showLocationForm)<div class="validation-summary" role="alert"><x-ui.icon name="alert" /><div><strong>Vui lòng kiểm tra lại cấu hình.</strong><p>Có {{ $errors->count() }} trường cần chỉnh sửa.</p></div></div>@endif
        <div class="about-settings-grid">
            <div>
                <x-form.language-tabs :locales="$locales">
                    @foreach($locales as $locale => $label)
                        <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                            <x-form.section title="Nội dung {{ $label }}" description="Nội dung trang liên hệ dành cho phiên bản {{ $label }}" icon="languages">
                                <div class="localized-fields">
                                    <x-form.input name="page_title[{{ $locale }}]" label="Tiêu đề trang" wire:model.blur="page_title.{{ $locale }}" :required="$locale === 'vi'" />
                                    <div class="form-field rich-editor"><label for="contact-description-{{ $locale }}">Mô tả chung</label><textarea id="contact-description-{{ $locale }}" class="textarea rich-text-textarea" rows="9" wire:model="description.{{ $locale }}"></textarea><x-form.field-error name="description.{{ $locale }}" /></div>
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
            <aside>
                <x-form.section title="Cấu hình chung" description="Áp dụng cho toàn bộ trang liên hệ" icon="settings">
                    <div class="form-stack">
                        <x-form.input name="notification_email" label="Email nhận thông báo" type="email" wire:model.blur="notification_email" placeholder="info@idiseafood.com" />
                        <x-form.input name="items_per_page" label="Số thư mỗi trang quản trị" type="number" wire:model="items_per_page" min="5" max="100" required />
                        <div class="switch-group">
                            <x-form.switch name="form_enabled" label="Hiển thị biểu mẫu liên hệ" wire:model="form_enabled" />
                            <x-form.switch name="spam_protection" label="Bật bảo vệ chống spam" wire:model="spam_protection" />
                            <x-form.switch name="is_active" label="Kích hoạt trang liên hệ" wire:model="is_active" />
                        </div>
                    </div>
                </x-form.section>
            </aside>
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
                    <thead><tr><th>Thứ tự</th><th>Tên địa chỉ</th><th>Thông tin liên hệ</th><th>Bản dịch</th><th>Trạng thái</th><th class="table-actions-heading">Thao tác</th></tr></thead>
                    <tbody>
                        @foreach($locations as $location)
                            @php $names = $location->getTranslations('name'); @endphp
                            <tr wire:key="office-{{ $location->id }}" @class(['is-muted-row' => !$location->is_active])>
                                <td><span class="location-order">{{ $location->sort_order }}</span></td>
                                <td class="category-name-cell"><strong>{{ $location->getTranslation('name', 'vi', false) ?: 'Chưa có tên' }}</strong><small>{{ $location->code ?: '#'.$location->id }}</small><small>{{ $location->getTranslation('address', 'vi', false) }}</small></td>
                                <td><div class="location-contact-lines"><span>{{ $location->phone ?: '—' }}</span><span>{{ $location->email ?: '—' }}</span></div></td>
                                <td><div class="translation-dots">@foreach(['vi','en','zh'] as $code)<span class="{{ filled($names[$code] ?? null) ? 'is-complete' : '' }}">{{ strtoupper($code) }}</span>@endforeach</div></td>
                                <td><x-ui.badge :tone="$location->is_active ? 'success' : 'neutral'">{{ $location->is_active ? 'Hiện' : 'Ẩn' }}</x-ui.badge></td>
                                <td><div class="row-actions">
                                    <button class="icon-button" type="button" wire:click="editLocation({{ $location->id }})" title="Sửa"><x-ui.icon name="edit" size="18" /></button>
                                    <button class="icon-button" type="button" wire:click="toggleLocation({{ $location->id }})" title="{{ $location->is_active ? 'Ẩn' : 'Hiện' }}"><x-ui.icon :name="$location->is_active ? 'eye-off' : 'eye'" size="18" /></button>
                                    <button class="icon-button is-danger" type="button" wire:click="deleteLocation({{ $location->id }})" wire:confirm="Chuyển địa chỉ này vào thùng rác?" title="Xóa"><x-ui.icon name="trash" size="18" /></button>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="contact-location-mobile">
                @foreach($locations as $location)
                    <article><div><strong>{{ $location->getTranslation('name', 'vi', false) }}</strong><small>{{ $location->getTranslation('address', 'vi', false) }}</small></div><button class="button button-secondary" type="button" wire:click="editLocation({{ $location->id }})">Chỉnh sửa</button></article>
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
                                <x-form.textarea name="location_address[{{ $code }}]" label="Địa chỉ — {{ $label }}" wire:model.blur="location_address.{{ $code }}" rows="3" :required="$code === 'vi'" />
                            </div>
                        @endforeach
                    </div>
                    <div class="location-contact-grid">
                        <x-form.input name="location_phone" label="Điện thoại" wire:model.blur="location_phone" />
                        <x-form.input name="location_email" label="Email" type="email" wire:model.blur="location_email" />
                        <x-form.input name="location_latitude" label="Vĩ độ" type="number" step="0.0000001" wire:model="location_latitude" />
                        <x-form.input name="location_longitude" label="Kinh độ" type="number" step="0.0000001" wire:model="location_longitude" />
                    </div>
                    <x-form.textarea name="location_map_embed" label="Mã nhúng Google Maps" wire:model.blur="location_map_embed" rows="4" />
                    <x-form.switch name="location_is_active" label="Hiển thị địa chỉ trên website" wire:model="location_is_active" />
                </div>
                <footer class="contact-detail-footer"><button class="button button-secondary" type="button" wire:click="closeLocationForm">Hủy</button><x-ui.button type="submit" icon="save">Lưu địa chỉ</x-ui.button></footer>
            </form>
        </div>
    @endif
</div>
