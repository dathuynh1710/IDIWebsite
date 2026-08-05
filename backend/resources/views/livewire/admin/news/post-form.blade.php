<div>
    <x-admin.page-header
        :title="$post ? 'Cập nhật tin tức' : 'Thêm tin tức'"
        description="Biên tập nội dung, hình ảnh và SEO cho ba ngôn ngữ."
        :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.news.posts.index') }}" wire:navigate>Quay lại</a>
            @if($post)
                <a class="button button-ghost" href="{{ route('admin.news.posts.preview', $post) }}" target="_blank">
                    <x-ui.icon name="eye" size="18" /> Xem trước
                </a>
            @endif
            <x-ui.button type="submit" form="post-form" icon="save">Lưu bài viết</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="post-form" wire:submit="save" data-dirty-form>
        <div class="product-form-grid news-post-form-grid">
            <aside>
                <x-form.section title="Thông tin chung" description="Áp dụng cho tất cả ngôn ngữ" icon="info">
                    <div class="form-stack">
                        <div class="form-field">
                            <label>Chuyên mục <span>*</span></label>
                            <select class="select" wire:model="post_category_id">
                                <option value="">Chọn chuyên mục</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->getTranslation('name', 'vi', false) }}</option>
                                @endforeach
                            </select>
                            <x-form.field-error name="post_category_id" />
                        </div>

                        <x-form.input name="code" label="Mã quản trị" wire:model="code" helper="Mã nội bộ để tìm kiếm và đối soát; có thể để trống." />

                        <div class="form-field">
                            <label>Ảnh đại diện <span>*</span></label>
                            @if($featured_image)
                                <div class="media-preview news-media-preview has-image">
                                    <img src="{{ $featured_image->temporaryUrl() }}" alt="Ảnh đại diện đang chọn">
                                </div>
                            @elseif($post?->featuredMedia && !$remove_image)
                                <div class="media-preview news-media-preview has-image">
                                    <img src="{{ $post->featuredMedia->url }}" alt="Ảnh đại diện hiện tại">
                                </div>
                            @else
                                <div class="media-preview news-media-preview">
                                    <span class="media-placeholder"><x-ui.icon name="image" size="30" /> Ảnh đề nghị 1200 × 675</span>
                                </div>
                            @endif
                            <input class="input" type="file" wire:model="featured_image" accept=".jpg,.jpeg,.png,.webp">
                            <p class="field-help">Dùng ảnh ngang, dung lượng tối đa 8 MB.</p>
                            @if($featured_image || ($post?->featuredMedia && !$remove_image))
                                <button class="button button-ghost media-remove-button" type="button" wire:click="removeFeaturedImage">Xóa ảnh</button>
                            @endif
                            <x-form.field-error name="featured_image" />
                        </div>

                        <x-form.input name="sort_order" label="Thứ tự hiển thị" type="number" wire:model="sort_order" min="0" helper="Số nhỏ hơn được ưu tiên khi các bài có cùng thời điểm đăng." />

                        <div class="switch-group">
                            <x-form.switch name="is_featured" label="Tin nổi bật" helper="Đưa bài viết vào khu vực Featured News." wire:model="is_featured" />
                            <x-form.switch name="is_active" label="Hiển thị bài viết" helper="Tắt để ẩn bài trên website mà không xóa dữ liệu." wire:model="is_active" />
                        </div>

                        <fieldset class="product-locale-selector news-locale-selector">
                            <legend>Ngôn ngữ bài viết</legend>
                            <p>Chỉ bật những ngôn ngữ đã có nội dung dịch.</p>
                            @foreach($locales as $locale => $label)
                                <label @class(['is-required' => $locale === 'vi'])>
                                    <input type="checkbox" value="{{ $locale }}" wire:model.live="enabled_locales" @disabled($locale === 'vi')>
                                    <span class="news-locale-copy">
                                        <strong>{{ $label }}</strong>
                                        <small>{{ $locale === 'vi' ? 'Ngôn ngữ gốc, luôn được bật' : (in_array($locale, $enabled_locales, true) ? 'Đang sử dụng bản dịch' : 'Chưa sử dụng bản dịch') }}</small>
                                    </span>
                                    <span class="switch-track" aria-hidden="true"><span></span></span>
                                </label>
                            @endforeach
                            <x-form.field-error name="enabled_locales" />
                        </fieldset>
                    </div>
                </x-form.section>
            </aside>

            <div class="product-form-content news-post-form-content">
                <x-form.language-tabs :locales="$locales" :enabled-locales="$enabled_locales">
                    @foreach($locales as $locale => $label)
                        <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak @if(!in_array($locale, $enabled_locales, true)) hidden @endif>
                            <x-form.section title="Nội dung {{ $label }}" description="Nhập tiêu đề, nội dung, ngày đăng và thông tin SEO cho {{ $label }}." icon="languages">
                                <fieldset class="localized-fields" @disabled(!in_array($locale, $enabled_locales, true))>
                                    <x-form.input name="title[{{ $locale }}]" label="Tiêu đề" wire:model.blur="title.{{ $locale }}" :required="in_array($locale, $enabled_locales, true)" />

                                    <div class="form-field">
                                        <label>Friendly URL</label>
                                        <div class="slug-input">
                                            <span>/{{ $locale }}/news/</span>
                                            <input class="input" wire:model="slug.{{ $locale }}" aria-label="Friendly URL {{ $label }}">
                                            <button type="button" wire:click="generateSlug('{{ $locale }}')">Tạo lại</button>
                                        </div>
                                        <p class="field-help">Được tự động tạo từ tiêu đề; chỉ nên chỉnh trước khi bài viết được công khai.</p>
                                        <x-form.field-error name="slug.{{ $locale }}" />
                                    </div>

                                    <x-form.textarea name="excerpt[{{ $locale }}]" label="Mô tả ngắn" wire:model="excerpt.{{ $locale }}" rows="4" maxlength="2000" helper="Dùng ở danh sách tin và phần giới thiệu trên mạng xã hội." />

                                    <x-form.ckeditor5-editor
                                        name="content[{{ $locale }}]"
                                        label="Nội dung tin"
                                        :model="'content.'.$locale"
                                        :value="$content[$locale] ?? ''"
                                        rows="16"
                                        placeholder="Nhập nội dung bài viết..." />

                                    <x-form.input
                                        name="locale_published_at[{{ $locale }}]"
                                        label="Ngày đăng"
                                        type="datetime-local"
                                        wire:model="locale_published_at.{{ $locale }}"
                                        helper="Có thể để trống để dùng thời điểm tạo bài viết." />

                                    <details class="seo-panel" open>
                                        <summary>Search Engine Optimization (SEO)</summary>
                                        <div class="seo-grid">
                                            <x-form.input name="seo_title[{{ $locale }}]" label="Friendly Title" wire:model="seo_title.{{ $locale }}" maxlength="255" helper="Nên dài khoảng 50–60 ký tự." />
                                            <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta Description" wire:model="meta_description.{{ $locale }}" rows="3" maxlength="500" helper="Nên dài khoảng 120–160 ký tự." />
                                        </div>
                                    </details>

                                    <details class="seo-panel">
                                        <summary>Chia sẻ mạng xã hội</summary>
                                        <div class="seo-grid">
                                            <x-form.input name="og_title[{{ $locale }}]" label="Tiêu đề chia sẻ" wire:model="og_title.{{ $locale }}" maxlength="255" helper="Để trống để sử dụng Friendly Title." />
                                            <x-form.textarea name="og_description[{{ $locale }}]" label="Mô tả chia sẻ" wire:model="og_description.{{ $locale }}" rows="3" maxlength="500" helper="Để trống để sử dụng Meta Description." />
                                        </div>
                                    </details>
                                </fieldset>
                            </x-form.section>
                        </section>
                    @endforeach
                </x-form.language-tabs>
            </div>
        </div>

        <div class="mobile-form-actions">
            <a class="button button-secondary" href="{{ route('admin.news.posts.index') }}" wire:navigate>Hủy</a>
            <x-ui.button type="submit" icon="save">Lưu bài viết</x-ui.button>
        </div>
    </form>
</div>
