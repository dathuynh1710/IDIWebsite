@if($errors->any())
    <div class="validation-summary" role="alert">
        <x-ui.icon name="alert" />
        <div><strong>Vui lòng kiểm tra lại thông tin.</strong><p>Có {{ $errors->count() }} trường cần được chỉnh sửa.</p></div>
    </div>
@endif

<div class="category-form-grid">
    <aside class="category-form-sidebar">
        <x-form.section title="Thiết lập danh mục" description="Thông tin quản trị và trạng thái" icon="settings">
            <div class="form-stack">
                <x-form.input name="code" label="Mã danh mục" :value="$category?->code" placeholder="VD: CA-FILLET" helper="Mã nội bộ, viết hoa và không trùng nhau." />
                <x-form.select name="parent_id" label="Danh mục cha" :options="$parentOptions" :selected="$category?->parent_id" placeholder="Không có danh mục cha" />
                <x-form.input name="sort_order" label="Thứ tự hiển thị" type="number" min="0" max="999999" :value="$category?->sort_order ?? 0" required />
                <div class="switch-group">
                    <x-form.switch name="is_active" label="Hiển thị danh mục" helper="Tắt để tạm ẩn trên website." :checked="$category?->is_active ?? true" />
                </div>
            </div>
        </x-form.section>
    </aside>

    <div class="category-form-content">
        <x-form.language-tabs :locales="$locales" :initial="$errors->hasAny(['name.en', 'slug.en', 'name.zh', 'slug.zh']) ? ($errors->hasAny(['name.en', 'slug.en']) ? 'en' : 'zh') : 'vi'">
            @foreach($locales as $locale => $label)
                <section id="panel-{{ $locale }}" class="localized-fields form-section card" role="tabpanel" aria-labelledby="tab-{{ $locale }}" x-show="active === '{{ $locale }}'" x-cloak>
                    <header class="form-section-header">
                        <span><x-ui.icon name="languages" /></span>
                        <div><h2>Nội dung {{ $label }}</h2><p>Tên, đường dẫn và mô tả dành cho ngôn ngữ này.</p></div>
                    </header>
                    <div class="form-section-body form-stack" x-data="categorySlug(@js(old("name.{$locale}", $category?->getTranslation('name', $locale, false))), @js(old("slug.{$locale}", $category?->getTranslation('slug', $locale, false))))">
                        <x-form.input name="name[{{ $locale }}]" label="Tên danh mục" x-model="name" @input="onName()" :required="$locale === 'vi'" />
                        <div class="form-field">
                            <label for="slug-{{ $locale }}">Đường dẫn @if($locale === 'vi')<span aria-hidden="true">*</span>@endif</label>
                            <div class="slug-input">
                                <span>/{{ $locale }}/</span>
                                <input id="slug-{{ $locale }}" class="input @error("slug.{$locale}") is-invalid @enderror" name="slug[{{ $locale }}]" x-model="slug" @input="markSlugEdited()" @if($locale === 'vi') required @endif>
                                <button type="button" @click="regenerate()">Tạo lại</button>
                            </div>
                            <x-form.field-error name="slug.{{ $locale }}" />
                        </div>
                        <x-form.textarea name="description[{{ $locale }}]" label="Mô tả" :value="$category?->getTranslation('description', $locale, false)" rows="6" helper="Mô tả ngắn về nhóm sản phẩm." />
                    </div>
                </section>
            @endforeach
        </x-form.language-tabs>
    </div>
</div>

<div class="mobile-form-actions">
    <a class="button button-secondary" href="{{ route('admin.product-categories.index') }}">Hủy</a>
    <x-ui.button type="submit" icon="save">{{ $category ? 'Lưu thay đổi' : 'Tạo danh mục' }}</x-ui.button>
</div>
