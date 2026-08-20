<div>
    <x-admin.page-header :title="$page?->exists ? 'Cập nhật giới thiệu' : 'Thêm giới thiệu mới'" description="Biên tập nội dung và SEO riêng cho Tiếng Việt, English và 中文" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.about-pages.index') }}" wire:navigate><x-ui.icon name="arrow-left" size="18" /> Quay lại</a>
            @if($page?->exists)
                <a class="button button-ghost" href="{{ route('admin.about-pages.preview', $page) }}" target="_blank"><x-ui.icon name="eye" size="18" /> Xem trước</a>
            @endif
            <x-ui.button type="submit" form="about-page-form" icon="save">Lưu giới thiệu</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="about-page-form" wire:submit="save" data-dirty-form>


        <div class="product-form-grid">
            <aside class="product-form-sidebar">
                <x-form.section title="Thông tin chung" description="Áp dụng cho cả 3 ngôn ngữ" icon="info">
                    <div class="form-stack">
                        <x-form.select name="template" label="Loại giới thiệu" :options="$templates" wire:model="template" required />
                        <x-form.select name="parent_id" label="Nội dung cha" :options="$parents->mapWithKeys(fn($item) => [$item->id => $item->getTranslation('title', 'vi', false)])->all()" wire:model="parent_id" placeholder="Không có nội dung cha" />
                        <x-form.input name="code" label="Mã quản trị" wire:model.blur="code" placeholder="Ví dụ: ABOUT_MESSAGE" />
                        <div class="form-field">
                            <label for="about-featured-image">Ảnh đại diện</label>
                            @if($featured_image)
                                <div class="media-preview has-image"><img src="{{ $featured_image->temporaryUrl() }}" alt=""></div>
                            @elseif($page?->featuredMedia && !$remove_image)
                                <div class="media-preview has-image"><img src="{{ $page->featuredMedia->url }}" alt=""></div>
                            @else
                                <div class="media-preview"><span class="media-placeholder"><x-ui.icon name="image" size="32" /> Chưa có ảnh</span></div>
                            @endif
                            <input id="about-featured-image" class="input" type="file" wire:model="featured_image" accept=".jpg,.jpeg,.png,.webp">
                            <div wire:loading wire:target="featured_image">Đang tải ảnh...</div>
                            @if($featured_image || ($page?->featuredMedia && !$remove_image))
                                <button class="button button-ghost" type="button" wire:click="removeFeaturedImage">Xóa ảnh</button>
                            @endif
                            <x-form.field-error name="featured_image" />
                        </div>
                        <x-form.input name="sort_order" label="Thứ tự hiển thị" type="number" wire:model="sort_order" min="0" required />
                        <x-form.switch name="is_active" label="Đang hiển thị" wire:model="is_active" />
                    </div>
                </x-form.section>
            </aside>

            <div class="product-form-content">
                <x-form.language-tabs :locales="$locales">
                    @foreach($locales as $locale => $label)
                        <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                            <x-form.section title="Nội dung {{ $label }}" description="Bản dịch độc lập cho phiên bản {{ $label }}" icon="languages">
                                <div class="localized-fields">
                                    <x-form.input name="title[{{ $locale }}]" label="Tiêu đề" wire:model.blur="title.{{ $locale }}" :required="$locale === 'vi'" />
                                    <div class="form-field">
                                        <label for="about-slug-{{ $locale }}">Đường dẫn thân thiện</label>
                                        <div class="slug-input">
                                            <span>/{{ $locale }}/{{ $locale === 'vi' ? 'gioi-thieu' : ($locale === 'en' ? 'about' : 'guanyu') }}/</span>
                                            <input id="about-slug-{{ $locale }}" class="input" wire:model.blur="slug.{{ $locale }}">
                                            <button type="button" wire:click="generateSlug('{{ $locale }}')">Tạo lại</button>
                                        </div>
                                        <x-form.field-error name="slug.{{ $locale }}" />
                                    </div>
                                    <x-form.textarea name="summary[{{ $locale }}]" label="Mô tả ngắn" wire:model.blur="summary.{{ $locale }}" rows="4" maxlength="2000" />
                                    <x-form.ckeditor5-editor name="content[{{ $locale }}]" label="Nội dung" :model="'content.'.$locale" :value="$content[$locale] ?? ''" rows="16" />
                                    <details class="seo-panel" open>
                                        <summary>Search Engine Optimization (SEO)</summary>
                                        <div class="seo-grid">
                                            <x-form.input name="seo_title[{{ $locale }}]" label="Tiêu đề SEO" wire:model.blur="seo_title.{{ $locale }}" maxlength="255" />
                                            <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model.blur="meta_description.{{ $locale }}" rows="3" maxlength="500" />
                                            <x-form.input name="meta_keywords[{{ $locale }}]" label="Từ khóa SEO" wire:model.blur="meta_keywords.{{ $locale }}" maxlength="1000" placeholder="Ví dụ: IDI Seafood, giới thiệu, lịch sử phát triển" />
                                            <div class="snippet-preview">
                                                <small>Xem trước kết quả tìm kiếm</small>
                                                <strong>{{ $seo_title[$locale] ?: ($title[$locale] ?: 'Tiêu đề giới thiệu') }}</strong>
                                                <span>idiseafood.com/{{ $locale }}/{{ $locale === 'vi' ? 'gioi-thieu' : ($locale === 'en' ? 'about' : 'guanyu') }}/{{ $slug[$locale] ?: 'duong-dan' }}</span>
                                                <p>{{ $meta_description[$locale] ?: ($summary[$locale] ?: 'Mô tả trang giới thiệu sẽ hiển thị tại đây.') }}</p>
                                            </div>
                                        </div>
                                    </details>
                                </div>
                            </x-form.section>
                        </section>
                    @endforeach
                </x-form.language-tabs>
            </div>
        </div>

        <div class="mobile-form-actions">
            <a class="button button-secondary" href="{{ route('admin.about-pages.index') }}" wire:navigate>Hủy</a>
            <x-ui.button type="submit" icon="save">Lưu giới thiệu</x-ui.button>
        </div>
    </form>
</div>
