<div>
    <x-admin.page-header :title="$product?->exists ? 'Sửa sản phẩm #'.$sku : 'Thêm sản phẩm mới'" description="Cập nhật thông tin chung, nội dung và SEO đa ngôn ngữ" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @if($modal)
                <button class="button button-secondary" type="button" wire:click="$dispatch('product-saved')"><x-ui.icon name="x" size="18" /> Đóng</button>
            @else
                <a class="button button-secondary" href="{{ route('admin.products.index') }}" wire:navigate><x-ui.icon name="arrow-left" size="18" /> Quay lại</a>
            @endif
            @if($product?->exists)<a class="button button-ghost" href="{{ route('admin.products.preview', $product) }}" target="_blank"><x-ui.icon name="eye" size="18" /> Xem trước</a>@endif
            <x-ui.button type="submit" form="product-form" icon="save">Lưu sản phẩm</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="product-form" wire:submit="save" data-dirty-form>
        @if($errors->any())<div class="validation-summary" role="alert"><x-ui.icon name="alert" /><div><strong>Vui lòng kiểm tra lại thông tin.</strong><p>Có {{ $errors->count() }} trường cần chỉnh sửa.</p></div></div>@endif
        <div class="product-form-grid">
            <div class="product-form-sidebar">
                <x-form.section title="Thông tin chung" description="Dữ liệu dùng chung cho mọi ngôn ngữ" icon="package">
                    <div class="form-stack">
                        <x-form.input name="sku" label="Mã sản phẩm (SKU)" wire:model.blur="sku" required />
                        <x-form.select name="product_category_id" label="Danh mục" :options="$categories->mapWithKeys(fn($item) => [$item->id => $item->getTranslation('name', 'vi', false)])->all()" wire:model="product_category_id" placeholder="Chọn danh mục" />
                        <x-form.input name="scientific_name" label="Tên khoa học" wire:model.blur="scientific_name" />
                        <div class="form-field">
                            <label for="featured-image">Ảnh đại diện</label>
                            @if($featured_image)<div class="media-preview has-image"><img src="{{ $featured_image->temporaryUrl() }}" alt=""></div>
                            @elseif($product?->featuredMedia && !$remove_image)<div class="media-preview has-image"><img src="{{ $product->featuredMedia->url }}" alt=""></div>@endif
                            <input id="featured-image" class="input" type="file" wire:model="featured_image" accept=".jpg,.jpeg,.png,.webp">
                            <div wire:loading wire:target="featured_image">Đang tải ảnh...</div>
                            @if($featured_image || ($product?->featuredMedia && !$remove_image))<button class="button button-ghost" type="button" wire:click="removeFeaturedImage">Xóa ảnh</button>@endif
                            <x-form.field-error name="featured_image" />
                        </div>
                        <x-form.input name="sort_order" label="Thứ tự hiển thị" type="number" wire:model="sort_order" min="0" required />
                        <div class="switch-group"><x-form.switch name="is_featured" label="Sản phẩm nổi bật" wire:model="is_featured" /><x-form.switch name="is_active" label="Đang hiển thị" wire:model="is_active" /></div>
                        <fieldset class="product-locale-selector">
                            <legend>Ngôn ngữ sản phẩm</legend>
                            <p>Chọn ngôn ngữ cần nhập cho sản phẩm này.</p>
                            @foreach($locales as $locale => $label)
                                <label @class(['is-required' => $locale === 'vi'])>
                                    <input type="checkbox" value="{{ $locale }}" wire:model.live="enabled_locales" @disabled($locale === 'vi')>
                                    <span>
                                        <strong>{{ $label }}</strong>
                                        <small>{{ $locale === 'vi' ? 'Ngôn ngữ gốc, luôn được bật' : 'Bật để nhập nội dung '.$label }}</small>
                                    </span>
                                </label>
                            @endforeach
                            <x-form.field-error name="enabled_locales" />
                        </fieldset>
                    </div>
                </x-form.section>
            </div>

            <div class="product-form-content">
                <x-form.language-tabs :locales="$locales" :enabled-locales="$enabled_locales">
                    @foreach($locales as $locale => $label)
                        <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak @if(!in_array($locale, $enabled_locales, true)) hidden @endif>
                            <x-form.section title="Nội dung {{ $label }}" description="Thông tin hiển thị cho phiên bản {{ $label }}" icon="languages">
                                <fieldset class="localized-fields" @disabled(!in_array($locale, $enabled_locales, true))>
                                    <x-form.input name="title[{{ $locale }}]" label="Tên sản phẩm" wire:model.blur="title.{{ $locale }}" :required="in_array($locale, $enabled_locales, true)" />
                                    <div class="form-field"><label for="slug-{{ $locale }}">Đường dẫn (slug)</label><div class="slug-input"><span>/products/</span><input id="slug-{{ $locale }}" class="input" wire:model.blur="slug.{{ $locale }}" @if(in_array($locale, $enabled_locales, true)) required @endif><button type="button" wire:click="generateSlug('{{ $locale }}')">Tạo lại</button></div><x-form.field-error name="slug.{{ $locale }}" /></div>
                                    <div class="product-editor-tabs" x-data="{ editorTab: 'short' }">
                                        <div class="product-editor-tab-list" role="tablist" aria-label="Nội dung sản phẩm">
                                            <button id="product-short-tab-{{ $locale }}" type="button" role="tab"
                                                :class="{ 'is-active': editorTab === 'short' }"
                                                :aria-selected="editorTab === 'short'"
                                                aria-controls="product-short-panel-{{ $locale }}"
                                                @click="editorTab = 'short'">Mô tả ngắn</button>
                                            <button id="product-content-tab-{{ $locale }}" type="button" role="tab"
                                                :class="{ 'is-active': editorTab === 'content' }"
                                                :aria-selected="editorTab === 'content'"
                                                aria-controls="product-content-panel-{{ $locale }}"
                                                @click="editorTab = 'content'">Nội dung</button>
                                        </div>
                                        <div id="product-short-panel-{{ $locale }}" role="tabpanel" aria-labelledby="product-short-tab-{{ $locale }}" x-show="editorTab === 'short'">
                                            <x-form.ckeditor5-editor name="short_description[{{ $locale }}]" label="Mô tả ngắn" :model="'short_description.'.$locale" :value="$short_description[$locale] ?? ''" rows="8" placeholder="Nhập mô tả ngắn..." />
                                        </div>
                                        <div id="product-content-panel-{{ $locale }}" role="tabpanel" aria-labelledby="product-content-tab-{{ $locale }}" x-show="editorTab === 'content'" x-cloak>
                                            <x-form.ckeditor5-editor name="content[{{ $locale }}]" label="Nội dung" :model="'content.'.$locale" :value="$content[$locale] ?? ''" rows="12" />
                                        </div>
                                    </div>
                                    <details class="seo-panel" open><summary>Thiết lập SEO</summary><div class="seo-grid">
                                        <x-form.input name="seo_title[{{ $locale }}]" label="Tiêu đề SEO" wire:model.blur="seo_title.{{ $locale }}" maxlength="255" />
                                        <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model.blur="meta_description.{{ $locale }}" rows="3" maxlength="500" />
                                    </div></details>
                                    <x-form.input name="locale_published_at[{{ $locale }}]" label="Ngày xuất bản" type="datetime-local" wire:model="locale_published_at.{{ $locale }}" helper="Có thể để trống để dùng thời điểm tạo sản phẩm." />
                                </fieldset>
                            </x-form.section>
                        </section>
                    @endforeach
                </x-form.language-tabs>
            </div>
        </div>
        <div class="mobile-form-actions">
            @if($modal)<button class="button button-secondary" type="button" wire:click="$dispatch('product-saved')">Hủy</button>
            @else<a class="button button-secondary" href="{{ route('admin.products.index') }}" wire:navigate>Hủy</a>@endif
            <x-ui.button type="submit" icon="save">Lưu sản phẩm</x-ui.button>
        </div>
    </form>
</div>
