<div class="recruitment-position-form-page">
    <x-admin.page-header
        :title="$position ? 'Cập nhật tuyển dụng' : 'Thêm tuyển dụng'"
        description="Biên tập nội dung tuyển dụng theo từng ngôn ngữ."
        :breadcrumbs="$breadcrumbs"
        class="recruitment-position-heading">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.recruitment.positions.index') }}" wire:navigate>Quay lại</a>
            @if($position)
                <a class="button button-ghost" href="{{ route('admin.recruitment.positions.preview', $position) }}" target="_blank"><x-ui.icon name="eye" size="18" /> Xem trước</a>
            @endif
            <x-ui.button type="submit" form="position-form" icon="save">Lưu tuyển dụng</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="position-form" wire:submit="save" data-dirty-form>
        <div class="product-form-grid recruitment-position-form-grid">
            <aside class="product-form-sidebar">
                <x-form.section title="Thông tin chung" description="Áp dụng cho tất cả ngôn ngữ" icon="briefcase">
                    <div class="form-stack">
                        <x-form.input name="code" label="Mã tuyển dụng" wire:model="code" placeholder="Ví dụ: REC-2026-001" />
                        <x-form.input name="department" label="Phòng ban" wire:model="department" placeholder="Ví dụ: Phòng Nhân sự" />
                        <div class="recruitment-common-grid">
                            <x-form.input name="quantity" label="Số lượng" type="number" wire:model="quantity" min="1" />
                            <x-form.input name="sort_order" label="Thứ tự" type="number" wire:model="sort_order" min="0" />
                        </div>
                        <x-form.input name="expires_at" label="Hạn nộp hồ sơ" type="date" wire:model="expires_at" />
                        <x-form.switch name="is_active" label="Hiển thị tuyển dụng" helper="Tắt để ẩn vị trí trên website mà không xóa dữ liệu." wire:model="is_active" />

                        <fieldset class="product-locale-selector news-locale-selector recruitment-locale-selector">
                            <legend>Ngôn ngữ tuyển dụng</legend>
                            <p>Bật ngôn ngữ cần đăng; tiếng Việt luôn được sử dụng.</p>
                            @foreach($locales as $locale => $label)
                                <label @class(['is-required' => $locale === 'vi'])>
                                    <input type="checkbox" value="{{ $locale }}" wire:model.live="enabled_locales" @disabled($locale === 'vi')>
                                    <span class="news-locale-copy">
                                        <strong>{{ $label }}</strong>
                                        <small>{{ $locale === 'vi' ? 'Ngôn ngữ gốc, luôn bật' : (in_array($locale, $enabled_locales, true) ? 'Đang hiển thị bản dịch' : 'Đang tắt bản dịch') }}</small>
                                    </span>
                                    <span class="switch-track" aria-hidden="true"><span></span></span>
                                </label>
                            @endforeach
                            <x-form.field-error name="enabled_locales" />
                        </fieldset>
                    </div>
                </x-form.section>
            </aside>

            <div class="product-form-content recruitment-position-form-content">
                <x-form.language-tabs :locales="$locales" :enabled-locales="$enabled_locales">
                    @foreach($locales as $locale => $label)
                        <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak @if(!in_array($locale, $enabled_locales, true)) hidden @endif>
                            <x-form.section title="Nội dung {{ $label }}" description="Thông tin hiển thị trên trang tuyển dụng phiên bản {{ $label }}." icon="languages">
                                <fieldset class="localized-fields" @disabled(!in_array($locale, $enabled_locales, true))>
                                    <div class="recruitment-primary-fields">
                                        <x-form.input name="title[{{ $locale }}]" label="Tiêu đề tuyển dụng" wire:model.blur="title.{{ $locale }}" :required="in_array($locale, $enabled_locales, true)" />
                                        <x-form.input name="location[{{ $locale }}]" label="Nơi làm việc" wire:model="location.{{ $locale }}" />
                                    </div>

                                    <div class="form-field">
                                        <label for="position-slug-{{ $locale }}">Friendly URL</label>
                                        <div class="slug-input">
                                            <span>/{{ $locale }}/careers/</span>
                                            <input id="position-slug-{{ $locale }}" class="input" wire:model="slug.{{ $locale }}" aria-label="Friendly URL {{ $label }}">
                                            <button type="button" wire:click="generateSlug('{{ $locale }}')">Tạo lại</button>
                                        </div>
                                        <p class="field-help">Tự động tạo từ tiêu đề; nên giữ ngắn gọn, không dấu và không có ký tự đặc biệt.</p>
                                        <x-form.field-error name="slug.{{ $locale }}" />
                                    </div>

                                    <x-form.textarea name="summary[{{ $locale }}]" label="Mô tả ngắn" wire:model="summary.{{ $locale }}" rows="3" maxlength="2000" helper="Dùng tại danh sách vị trí tuyển dụng và phần giới thiệu ngắn." />

                                    <div class="product-editor-tabs recruitment-editor-tabs" x-data="{ editorTab: 'description' }">
                                        <div class="product-editor-tab-list recruitment-editor-tab-list" role="tablist" aria-label="Nội dung tuyển dụng {{ $label }}">
                                            @foreach([
                                                'description' => 'Mô tả công việc',
                                                'requirements' => 'Yêu cầu công việc',
                                                'benefits' => 'Phúc lợi',
                                                'contact' => 'Liên hệ',
                                            ] as $tab => $caption)
                                                <button id="position-{{ $tab }}-tab-{{ $locale }}" type="button" role="tab"
                                                    :class="{ 'is-active': editorTab === '{{ $tab }}' }"
                                                    :aria-selected="editorTab === '{{ $tab }}'"
                                                    aria-controls="position-{{ $tab }}-panel-{{ $locale }}"
                                                    @click="editorTab = '{{ $tab }}'">{{ $caption }}</button>
                                            @endforeach
                                        </div>

                                        <div id="position-description-panel-{{ $locale }}" role="tabpanel" aria-labelledby="position-description-tab-{{ $locale }}" x-show="editorTab === 'description'">
                                            <x-form.ckeditor5-editor name="description[{{ $locale }}]" label="Mô tả công việc" :model="'description.'.$locale" :value="$description[$locale] ?? ''" rows="14" placeholder="Nhập mô tả công việc, nhiệm vụ và trách nhiệm..." />
                                        </div>
                                        <div id="position-requirements-panel-{{ $locale }}" role="tabpanel" aria-labelledby="position-requirements-tab-{{ $locale }}" x-show="editorTab === 'requirements'" x-cloak>
                                            <x-form.ckeditor5-editor name="requirements[{{ $locale }}]" label="Yêu cầu công việc" :model="'requirements.'.$locale" :value="$requirements[$locale] ?? ''" rows="14" placeholder="Nhập trình độ, kinh nghiệm và kỹ năng yêu cầu..." />
                                        </div>
                                        <div id="position-benefits-panel-{{ $locale }}" role="tabpanel" aria-labelledby="position-benefits-tab-{{ $locale }}" x-show="editorTab === 'benefits'" x-cloak>
                                            <x-form.ckeditor5-editor name="benefits[{{ $locale }}]" label="Phúc lợi" :model="'benefits.'.$locale" :value="$benefits[$locale] ?? ''" rows="14" placeholder="Nhập mức lương, chế độ và quyền lợi..." />
                                        </div>
                                        <div id="position-contact-panel-{{ $locale }}" role="tabpanel" aria-labelledby="position-contact-tab-{{ $locale }}" x-show="editorTab === 'contact'" x-cloak>
                                            <x-form.ckeditor5-editor name="contact[{{ $locale }}]" label="Liên hệ" :model="'contact.'.$locale" :value="$contact[$locale] ?? ''" rows="14" placeholder="Nhập hướng dẫn nộp hồ sơ và thông tin liên hệ..." />
                                        </div>
                                    </div>

                                    <details class="seo-panel" open>
                                        <summary>Search Engine Optimization (SEO)</summary>
                                        <div class="seo-grid">
                                            <x-form.input name="seo_title[{{ $locale }}]" label="Friendly Title" wire:model.blur="seo_title.{{ $locale }}" maxlength="255" helper="Nên dài khoảng 50–70 ký tự." />
                                            <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta Description" wire:model.blur="meta_description.{{ $locale }}" rows="3" maxlength="500" helper="Nên dài khoảng 120–160 ký tự." />
                                            <x-form.input name="meta_keywords[{{ $locale }}]" label="Meta Keyword" wire:model.blur="meta_keywords.{{ $locale }}" maxlength="1000" placeholder="tuyển dụng, việc làm, IDI Seafood" />
                                        </div>
                                    </details>

                                    <x-form.input name="locale_published_at[{{ $locale }}]" label="Ngày đăng" type="datetime-local" wire:model="locale_published_at.{{ $locale }}" helper="Có thể để trống để dùng thời điểm lưu tuyển dụng." />
                                </fieldset>
                            </x-form.section>
                        </section>
                    @endforeach
                </x-form.language-tabs>
            </div>
        </div>

        <div class="mobile-form-actions">
            <a class="button button-secondary" href="{{ route('admin.recruitment.positions.index') }}" wire:navigate>Hủy</a>
            <x-ui.button type="submit" icon="save">Lưu tuyển dụng</x-ui.button>
        </div>
    </form>
</div>
