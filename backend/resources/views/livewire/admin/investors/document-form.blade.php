<div>
    <x-admin.page-header :title="$document ? 'Cập nhật QHCĐ' : 'Thêm QHCĐ mới'" description="Quản lý nội dung, tệp tải xuống và thông tin SEO của QHCĐ." :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.investors.documents.index') }}" wire:navigate>Quay lại</a>
            <x-ui.button type="submit" form="investor-document-form" icon="save">Lưu</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="investor-document-form" wire:submit="save" data-dirty-form>
        <div class="product-form-grid">
            <aside class="product-form-sidebar">
                <x-form.section title="Thông tin tin" description="Thiết lập dùng chung cho tất cả ngôn ngữ." icon="info">
                    <div class="form-stack">
                        <div class="form-field">
                            <label>Danh mục <span>*</span></label>
                            <select class="select" wire:model="document_category_id">
                                <option value="">Chọn danh mục</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->getTranslation('name', 'vi', false) }}</option>
                                @endforeach
                            </select>
                            <x-form.field-error name="document_category_id" />
                        </div>

                        <x-form.input name="published_on" label="Ngày đăng" type="date" wire:model="published_on" />
                        <x-form.switch name="is_active" label="Hiện" helper="Bật để hiển thị tài liệu trên website." wire:model="is_active" />

                        <div class="form-field">
                            <label>File download @if(! $document)<span>*</span>@endif</label>
                            @if($uploads['vi'] ?? false)
                                <div class="file-selection"><x-ui.icon name="file" size="20" /> {{ $uploads['vi']->getClientOriginalName() }}</div>
                            @elseif($currentFile && !($removeFiles['vi'] ?? false))
                                <div class="file-selection"><a href="{{ route('investors.documents.download', $currentFile) }}"><x-ui.icon name="file" size="20" /> {{ $currentFile->media->original_name }}</a></div>
                            @endif
                            <input class="input" type="file" wire:model="uploads.vi" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip">
                            <p class="field-help">Chọn một tệp để người dùng tải xuống. Dung lượng tối đa {{ $maxUploadMegabytes }} MB.</p>
                            @if(($uploads['vi'] ?? false) || ($currentFile && !($removeFiles['vi'] ?? false)))
                                <button class="button button-ghost" type="button" wire:click="removeFile('vi')">Xóa tệp</button>
                            @endif
                            <x-form.field-error name="uploads.vi" />
                        </div>

                        <details class="settings-advanced" @if(count($enabled_locales) > 1) open @endif>
                            <summary>Bản dịch ngôn ngữ</summary>
                            <fieldset class="product-locale-selector news-locale-selector">
                                <legend>Bật bản dịch cần nhập</legend>
                                <p>Khi chưa có bản dịch, mục này được thu gọn. Bật công tắc để thêm nội dung cho ngôn ngữ đó.</p>
                                @foreach($locales as $locale => $label)
                                    <label @class(['is-required' => $locale === 'vi'])>
                                        <input type="checkbox" value="{{ $locale }}" wire:model.live="enabled_locales" @disabled($locale === 'vi')>
                                        <span class="news-locale-copy"><strong>{{ $label }}</strong><small>{{ $locale === 'vi' ? 'Ngôn ngữ gốc, luôn được bật' : (in_array($locale, $enabled_locales, true) ? 'Đang nhập bản dịch' : 'Chưa có bản dịch') }}</small></span>
                                        <span class="switch-track" aria-hidden="true"><span></span></span>
                                    </label>
                                @endforeach
                            </fieldset>
                            <x-form.field-error name="enabled_locales" />
                        </details>
                    </div>
                </x-form.section>
            </aside>

            <div class="product-form-content">
                <x-form.language-tabs :locales="$locales" :enabled-locales="$enabled_locales">
                    @foreach($locales as $locale => $label)
                        <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak @if(!in_array($locale, $enabled_locales, true)) hidden @endif>
                            <x-form.section title="Nội dung {{ $label }}" description="Nhập tiêu đề và nội dung cho phiên bản {{ $label }}." icon="languages">
                                <fieldset class="localized-fields" @disabled(!in_array($locale, $enabled_locales, true))>
                                    <x-form.input name="title[{{ $locale }}]" label="Tiêu đề" wire:model.blur="title.{{ $locale }}" :required="in_array($locale, $enabled_locales, true)" />
                                    <x-form.ckeditor5-editor name="summary[{{ $locale }}]" label="Nội dung tin" :model="'summary.'.$locale" :value="$summary[$locale] ?? ''" rows="16" placeholder="Nhập nội dung tin..." />
                                </fieldset>
                            </x-form.section>
                        </section>
                    @endforeach
                </x-form.language-tabs>

                <x-form.section title="Search Engine Optimization" description="Thông tin hiển thị trên công cụ tìm kiếm." icon="search">
                    <div class="seo-grid">
                        <x-form.input name="slug" label="Friendly URL" wire:model="slug" required />
                        <x-form.input name="seo_title" label="Friendly Title" wire:model="seo_title" maxlength="255" />
                        <x-form.textarea name="meta_description" label="Meta Description" wire:model="meta_description" rows="3" maxlength="500" />
                        <x-form.input name="meta_keywords" label="Meta Keyword" wire:model="meta_keywords" maxlength="1000" />
                    </div>
                </x-form.section>
            </div>
        </div>

        <div class="mobile-form-actions">
            <a class="button button-secondary" href="{{ route('admin.investors.documents.index') }}" wire:navigate>Hủy</a>
            <x-ui.button type="submit" icon="save">Lưu</x-ui.button>
        </div>
    </form>
</div>
