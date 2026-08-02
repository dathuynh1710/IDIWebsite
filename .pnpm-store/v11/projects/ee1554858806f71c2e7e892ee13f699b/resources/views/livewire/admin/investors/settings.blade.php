<div>
    <x-admin.page-header title="Cấu hình quan hệ cổ đông" description="Thiết lập nội dung giới thiệu, SEO và cách hiển thị thư viện tài liệu." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><x-ui.button type="submit" form="investor-settings" icon="save">Lưu cấu hình</x-ui.button></x-slot:actions>
    </x-admin.page-header>
    <form id="investor-settings" wire:submit="save" data-dirty-form>
        <x-form.language-tabs :locales="$locales">
            @foreach($locales as $locale => $label)
                <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                    <x-form.section title="Thông tin trang — {{ $label }}" description="Nội dung độc lập cho phiên bản {{ $label }}" icon="languages">
                        <div class="news-settings-grid">
                            <x-form.input name="page_title[{{ $locale }}]" label="Tiêu đề trang" wire:model="page_title.{{ $locale }}" :required="$locale === 'vi'" />
                            <x-form.textarea name="description[{{ $locale }}]" label="Mô tả giới thiệu" wire:model="description.{{ $locale }}" rows="5" />
                            <x-form.input name="seo_title[{{ $locale }}]" label="Tiêu đề SEO" wire:model="seo_title.{{ $locale }}" maxlength="255" />
                            <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model="meta_description.{{ $locale }}" rows="3" maxlength="500" />
                        </div>
                    </x-form.section>
                </section>
            @endforeach
        </x-form.language-tabs>
        <x-form.section title="Hiển thị & tải lên" description="Áp dụng chung cho toàn bộ thư viện tài liệu" icon="settings">
            <div class="news-number-grid">
                <x-form.input name="items_per_page" label="Số tài liệu mỗi trang" type="number" wire:model="items_per_page" min="5" max="100" />
                <x-form.input name="default_year" label="Năm mặc định" type="number" wire:model="default_year" min="2000" max="2100" />
                <x-form.input name="max_upload_size" label="Dung lượng tệp tối đa (MB)" type="number" wire:model="max_upload_size" min="1" max="100" />
            </div>
        </x-form.section>
    </form>
</div>
