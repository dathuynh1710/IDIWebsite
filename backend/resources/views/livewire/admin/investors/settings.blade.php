<div>
    <x-admin.page-header title="Cấu hình quan hệ cổ đông" description="Thiết lập nội dung giới thiệu, SEO và cách hiển thị thư viện tài liệu." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><x-ui.button type="submit" form="investor-settings" icon="save">Lưu cấu hình</x-ui.button></x-slot:actions>
    </x-admin.page-header>
    <form id="investor-settings" wire:submit="save" data-dirty-form>
        <x-form.language-tabs :locales="$locales">
            @foreach($locales as $locale => $label)
                <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                    <x-form.section title="Thông tin trang — {{ $label }}" description="Nội dung độc lập cho phiên bản {{ $label }}" icon="languages">
                        <div class="form-stack">
                            <x-form.input name="page_title[{{ $locale }}]" label="Tiêu đề trang" wire:model="page_title.{{ $locale }}" :required="$locale === 'vi'" />
                            <x-form.textarea name="description[{{ $locale }}]" label="Mô tả giới thiệu" wire:model="description.{{ $locale }}" rows="5" />
                            <x-form.input name="seo_title[{{ $locale }}]" label="Tiêu đề SEO" wire:model="seo_title.{{ $locale }}" maxlength="255" />
                            <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model="meta_description.{{ $locale }}" rows="3" maxlength="500" />
                        </div>
                    </x-form.section>
                </section>
            @endforeach
        </x-form.language-tabs>
        <x-form.section title="Quản lý tài liệu" description="Thiết lập mặc định cho danh sách quản trị và tệp tải lên." icon="settings">
            <div class="news-number-grid">
                <x-form.input name="items_per_page" label="Số dòng mặc định" type="number" wire:model="items_per_page" min="5" max="100" helper="Số tài liệu hiển thị ban đầu trên mỗi trang trong màn hình quản lý." />
                <x-form.input name="max_upload_size" label="Giới hạn mỗi tệp (MB)" type="number" wire:model="max_upload_size" min="1" max="100" helper="Áp dụng cho từng tệp tài liệu tải lên; tối đa 100 MB." />
            </div>
        </x-form.section>
    </form>
</div>
