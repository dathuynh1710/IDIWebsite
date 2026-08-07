<div class="recruitment-settings-page">
    <x-admin.page-header title="Cấu hình tuyển dụng" description="Quản lý nội dung, hình ảnh, SEO và cách tiếp nhận hồ sơ." :breadcrumbs="$breadcrumbs" class="recruitment-settings-heading">
        <x-slot:actions><x-ui.button type="submit" form="recruitment-settings" icon="save">Lưu cấu hình</x-ui.button></x-slot:actions>
    </x-admin.page-header>

    <form id="recruitment-settings" wire:submit="save" data-dirty-form>
        @if($errors->any())<div class="validation-summary" role="alert"><x-ui.icon name="alert" /><div><strong>Vui lòng kiểm tra lại cấu hình.</strong><p>Có {{ $errors->count() }} trường cần chỉnh sửa.</p></div></div>@endif

        <section class="card recruitment-settings-content-card">
            <header class="recruitment-settings-section-heading"><div><span class="recruitment-settings-section-icon"><x-ui.icon name="languages" size="20" /></span><div><h2>Nội dung trang tuyển dụng</h2><p>Biên tập độc lập cho Tiếng Việt, English và 中文.</p></div></div></header>
            <x-form.language-tabs :locales="$locales">
                @foreach($locales as $locale => $label)
                    <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                        <div class="recruitment-settings-locale-panel" x-data="{ contentTab: 'intro' }">
                            <x-form.input name="page_title[{{ $locale }}]" label="Tiêu đề trang — {{ $label }}" wire:model.blur="page_title.{{ $locale }}" :required="$locale === 'vi'" />

                            <div class="product-editor-tabs recruitment-settings-editor-tabs">
                                <div class="product-editor-tab-list recruitment-settings-editor-tab-list" role="tablist" aria-label="Cấu hình nội dung {{ $label }}">
                                    @foreach(['intro' => 'Giới thiệu', 'benefits' => 'Lợi ích', 'contact' => 'Liên hệ', 'seo' => 'SEO'] as $tab => $caption)
                                        <button type="button" role="tab" :class="{ 'is-active': contentTab === '{{ $tab }}' }" :aria-selected="contentTab === '{{ $tab }}'" @click="contentTab = '{{ $tab }}'">{{ $caption }}</button>
                                    @endforeach
                                </div>
                                <div role="tabpanel" x-show="contentTab === 'intro'">
                                    <x-form.ckeditor5-editor name="description[{{ $locale }}]" label="Nội dung giới thiệu" :model="'description.'.$locale" :value="$description[$locale] ?? ''" rows="12" placeholder="Giới thiệu môi trường làm việc và cơ hội nghề nghiệp tại IDI..." />
                                </div>
                                <div role="tabpanel" x-show="contentTab === 'benefits'" x-cloak>
                                    <x-form.ckeditor5-editor name="benefits_content[{{ $locale }}]" label="Nội dung lợi ích" :model="'benefits_content.'.$locale" :value="$benefits_content[$locale] ?? ''" rows="12" placeholder="Nhập các lợi ích, chế độ và môi trường làm việc..." />
                                </div>
                                <div role="tabpanel" x-show="contentTab === 'contact'" x-cloak>
                                    <x-form.ckeditor5-editor name="contact_content[{{ $locale }}]" label="Nội dung liên hệ chung" :model="'contact_content.'.$locale" :value="$contact_content[$locale] ?? ''" rows="12" placeholder="Nhập email, điện thoại hoặc hướng dẫn liên hệ tuyển dụng..." />
                                </div>
                                <div class="recruitment-settings-seo-panel" role="tabpanel" x-show="contentTab === 'seo'" x-cloak>
                                    <div class="seo-grid">
                                        <x-form.input name="seo_title[{{ $locale }}]" label="Friendly Title" wire:model.blur="seo_title.{{ $locale }}" maxlength="255" helper="Nên dài khoảng 50–70 ký tự." />
                                        <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta Description" wire:model.blur="meta_description.{{ $locale }}" rows="3" maxlength="500" helper="Nên dài khoảng 120–160 ký tự." />
                                        <x-form.input name="meta_keywords[{{ $locale }}]" label="Meta Keyword" wire:model.blur="meta_keywords.{{ $locale }}" maxlength="1000" placeholder="tuyển dụng, việc làm, IDI Seafood" />
                                        <div class="snippet-preview"><small>Xem trước kết quả tìm kiếm</small><strong>{{ $seo_title[$locale] ?: ($page_title[$locale] ?: 'Tuyển dụng IDI Seafood') }}</strong><span>idiseafood.com/{{ $locale }}/{{ $locale === 'vi' ? 'tuyen-dung' : ($locale === 'en' ? 'careers' : 'zhaopin') }}</span><p>{{ $meta_description[$locale] ?: 'Khám phá cơ hội nghề nghiệp và môi trường làm việc tại IDI Seafood.' }}</p></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endforeach
            </x-form.language-tabs>
        </section>

        <div class="recruitment-settings-bottom-grid">
            <section class="card recruitment-media-settings-card">
                <header class="recruitment-settings-section-heading"><div><span class="recruitment-settings-section-icon"><x-ui.icon name="image" size="20" /></span><div><h2>Hình ảnh trang tuyển dụng</h2><p>Ảnh JPG, PNG hoặc WebP; hệ thống tự quản lý thư mục tải lên.</p></div></div></header>
                <div class="recruitment-media-settings-body">
                    <div class="recruitment-hero-upload-grid">
                        <div class="form-field"><label>Banner máy tính</label>
                            @if($hero_desktop)<div class="recruitment-image-preview is-desktop"><img src="{{ $hero_desktop->temporaryUrl() }}" alt="Banner máy tính vừa chọn"></div>@elseif($heroDesktopUrl)<div class="recruitment-image-preview is-desktop"><img src="{{ $heroDesktopUrl }}" alt="Banner máy tính hiện tại"></div>@else<div class="recruitment-image-placeholder is-desktop"><x-ui.icon name="image" size="28" /><span>Đề nghị 1920 × 900</span></div>@endif
                            <input class="input" type="file" wire:model="hero_desktop" accept=".jpg,.jpeg,.png,.webp">
                            @if($hero_desktop || $heroDesktopUrl)<button class="button button-ghost recruitment-remove-media" type="button" wire:click="removeHero('desktop')">Bỏ ảnh</button>@endif
                            <x-form.field-error name="hero_desktop" />
                        </div>
                        <div class="form-field"><label>Banner di động</label>
                            @if($hero_mobile)<div class="recruitment-image-preview is-mobile"><img src="{{ $hero_mobile->temporaryUrl() }}" alt="Banner di động vừa chọn"></div>@elseif($heroMobileUrl)<div class="recruitment-image-preview is-mobile"><img src="{{ $heroMobileUrl }}" alt="Banner di động hiện tại"></div>@else<div class="recruitment-image-placeholder is-mobile"><x-ui.icon name="image" size="28" /><span>Đề nghị 900 × 1200</span></div>@endif
                            <input class="input" type="file" wire:model="hero_mobile" accept=".jpg,.jpeg,.png,.webp">
                            @if($hero_mobile || $heroMobileUrl)<button class="button button-ghost recruitment-remove-media" type="button" wire:click="removeHero('mobile')">Bỏ ảnh</button>@endif
                            <x-form.field-error name="hero_mobile" />
                        </div>
                    </div>

                    <div class="form-field recruitment-gallery-field"><label>Thư viện hình ảnh <small>(tối đa 3 ảnh)</small></label>
                        @if($galleryImageUrls || $gallery_uploads)
                            <div class="recruitment-gallery-preview">
                                @foreach($galleryImageUrls as $index => $url)<article><img src="{{ $url }}" alt="Ảnh thư viện {{ $index + 1 }}"><button type="button" wire:click="removeGalleryImage({{ $index }})" aria-label="Bỏ ảnh {{ $index + 1 }}"><x-ui.icon name="x" size="15" /></button></article>@endforeach
                                @foreach($gallery_uploads as $index => $image)<article><img src="{{ $image->temporaryUrl() }}" alt="Ảnh mới {{ $index + 1 }}"><span>Mới</span></article>@endforeach
                            </div>
                        @endif
                        <input class="input" type="file" wire:model="gallery_uploads" accept=".jpg,.jpeg,.png,.webp" multiple>
                        <p class="field-help">Dùng ảnh ngang, đề nghị 870 × 480; có thể chọn nhiều ảnh cùng lúc.</p>
                        <span class="field-help" wire:loading wire:target="hero_desktop,hero_mobile,gallery_uploads">Đang tải ảnh lên…</span>
                        <x-form.field-error name="gallery_uploads" />
                        <x-form.field-error name="gallery_uploads.*" />
                    </div>
                </div>
            </section>

            <section class="card recruitment-operation-settings-card">
                <header class="recruitment-settings-section-heading"><div><span class="recruitment-settings-section-icon"><x-ui.icon name="settings" size="20" /></span><div><h2>Tiếp nhận hồ sơ</h2><p>Các thiết lập vận hành cần thiết cho module tuyển dụng.</p></div></div></header>
                <div class="form-stack recruitment-operation-settings-body">
                    <x-form.switch name="application_enabled" label="Cho phép gửi hồ sơ trực tuyến" helper="Tắt khi doanh nghiệp tạm ngưng tiếp nhận CV." wire:model="application_enabled" />
                </div>
            </section>
        </div>

        <div class="mobile-form-actions"><a class="button button-secondary" href="{{ route('admin.recruitment.positions.index') }}" wire:navigate>Quản lý tuyển dụng</a><x-ui.button type="submit" icon="save">Lưu cấu hình</x-ui.button></div>
    </form>
</div>
