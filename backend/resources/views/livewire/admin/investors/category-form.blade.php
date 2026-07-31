<div>
    <x-admin.page-header :title="$category ? 'Cập nhật danh mục' : 'Thêm danh mục quan hệ cổ đông'" description="Khai báo tên, đường dẫn và mô tả cho Việt, Anh, Trung." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><a class="button button-secondary" href="{{ route('admin.investors.categories.index') }}" wire:navigate>Quay lại</a><x-ui.button type="submit" form="investor-category-form" icon="save">Lưu danh mục</x-ui.button></x-slot:actions>
    </x-admin.page-header>
    <form id="investor-category-form" wire:submit="save" data-dirty-form><div class="product-form-grid">
        <aside><x-form.section title="Thông tin chung" description="Áp dụng cho mọi ngôn ngữ" icon="folder"><div class="form-stack">
            <div class="form-field"><label>Danh mục cha</label><select class="select" wire:model="parent_id"><option value="">— Gốc —</option>@foreach($parents as $parent)<option value="{{ $parent->id }}">{{ $parent->getTranslation('name', 'vi', false) }}</option>@endforeach</select><x-form.field-error name="parent_id" /></div>
            <x-form.input name="sort_order" label="Thứ tự hiển thị" type="number" wire:model="sort_order" min="0" />
            <x-form.switch name="is_active" label="Đang hiển thị" wire:model="is_active" />
        </div></x-form.section></aside>
        <div><x-form.language-tabs :locales="$locales">@foreach($locales as $locale => $label)
            <section id="panel-{{ $locale }}" class="tab-panel" x-show="active === '{{ $locale }}'" x-cloak>
                <x-form.section title="Nội dung {{ $label }}" description="Bản dịch độc lập" icon="languages"><div class="localized-fields">
                    <x-form.input name="name[{{ $locale }}]" label="Tiêu đề" wire:model.blur="name.{{ $locale }}" :required="$locale === 'vi'" />
                    <div class="form-field"><label>Friendly URL</label><div class="slug-input"><span>/{{ $locale }}/investors/</span><input class="input" wire:model="slug.{{ $locale }}"><button type="button" wire:click="generateSlug('{{ $locale }}')">Tạo lại</button></div><x-form.field-error name="slug.{{ $locale }}" /></div>
                    <x-form.textarea name="description[{{ $locale }}]" label="Mô tả" wire:model="description.{{ $locale }}" rows="6" />
                </div></x-form.section>
            </section>
        @endforeach</x-form.language-tabs></div>
    </div></form>
</div>
