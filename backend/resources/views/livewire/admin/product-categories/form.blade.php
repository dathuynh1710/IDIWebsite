<div>
    <x-admin.page-header :title="$category?->exists ? 'Sửa danh mục: '.$name['vi'] : 'Thêm danh mục sản phẩm'" description="Cập nhật tên, đường dẫn, thứ tự và trạng thái hiển thị" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.product-categories.index') }}" wire:navigate><x-ui.icon name="arrow-left" size="18" /> Quay lại</a>
            <x-ui.button type="submit" form="category-form" icon="save">Lưu danh mục</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="category-form" wire:submit="save">
        @if($errors->any())<div class="validation-summary" role="alert"><x-ui.icon name="alert" /><div><strong>Vui lòng kiểm tra lại thông tin.</strong><p>Có {{ $errors->count() }} trường cần chỉnh sửa.</p></div></div>@endif
        <div class="category-form-grid">
            <aside class="category-form-sidebar">
                <x-form.section title="Thiết lập danh mục" description="Thông tin quản trị và trạng thái" icon="settings">
                    <div class="form-stack">
                        <x-form.input name="code" label="Mã danh mục" wire:model.blur="code" placeholder="VD: CA-FILLET" />
                        <x-form.select name="parent_id" label="Danh mục cha" :options="$parentOptions" wire:model="parent_id" placeholder="Không có danh mục cha" />
                        <x-form.input name="sort_order" label="Thứ tự hiển thị" type="number" wire:model="sort_order" min="0" max="999999" required />
                        <x-form.switch name="is_active" label="Hiển thị danh mục" wire:model="is_active" />
                    </div>
                </x-form.section>
            </aside>
            <div class="category-form-content">
                <x-form.language-tabs :locales="$locales">
                    @foreach($locales as $locale => $label)
                        <section id="panel-{{ $locale }}" class="localized-fields form-section card" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                            <header class="form-section-header"><span><x-ui.icon name="languages" /></span><div><h2>Nội dung {{ $label }}</h2><p>Tên, đường dẫn và mô tả cho ngôn ngữ này.</p></div></header>
                            <div class="form-section-body form-stack">
                                <x-form.input name="name[{{ $locale }}]" label="Tên danh mục" wire:model.blur="name.{{ $locale }}" :required="$locale === 'vi'" />
                                <div class="form-field">
                                    <label for="slug-{{ $locale }}">Đường dẫn</label>
                                    <div class="slug-input"><span>/{{ $locale }}/</span><input id="slug-{{ $locale }}" class="input" wire:model.blur="slug.{{ $locale }}"><button type="button" wire:click="generateSlug('{{ $locale }}')">Tạo lại</button></div>
                                    <x-form.field-error name="slug.{{ $locale }}" />
                                </div>
                                <x-form.textarea name="description[{{ $locale }}]" label="Mô tả" wire:model.blur="description.{{ $locale }}" rows="6" />
                            </div>
                        </section>
                    @endforeach
                </x-form.language-tabs>
            </div>
        </div>
        <div class="mobile-form-actions">
            <a class="button button-secondary" href="{{ route('admin.product-categories.index') }}" wire:navigate>Hủy</a>
            <x-ui.button type="submit" icon="save">Lưu danh mục</x-ui.button>
        </div>
    </form>
</div>
