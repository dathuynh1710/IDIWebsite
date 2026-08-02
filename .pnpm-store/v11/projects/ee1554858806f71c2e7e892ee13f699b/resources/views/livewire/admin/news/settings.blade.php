<div>
    <x-admin.page-header title="Cấu hình tin tức" description="Thiết lập SEO, hiển thị và giới hạn nội dung của module tin tức." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><x-ui.button type="submit" form="news-settings" icon="save">Lưu cấu hình</x-ui.button></x-slot:actions>
    </x-admin.page-header>
    <form id="news-settings" wire:submit="save" data-dirty-form class="news-settings-form">
        <x-form.language-tabs :locales="$locales">
            @foreach($locales as $locale => $label)
                <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                    <x-form.section title="SEO & tiêu đề — {{ $label }}" description="Nội dung độc lập cho phiên bản {{ $label }}" icon="search">
                        <div class="news-settings-grid">
                            <x-form.input name="page_title[{{ $locale }}]" label="Tiêu đề trang" wire:model="page_title.{{ $locale }}" :required="$locale === 'vi'" />
                            <x-form.input name="seo_title[{{ $locale }}]" label="Tiêu đề SEO" wire:model="seo_title.{{ $locale }}" maxlength="255" />
                            <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model="meta_description.{{ $locale }}" rows="3" maxlength="500" />
                        </div>
                    </x-form.section>
                </section>
            @endforeach
        </x-form.language-tabs>
        <div class="news-settings-columns">
            <x-form.section title="Chức năng chung" description="Các tiện ích áp dụng cho toàn module" icon="settings">
                <div class="switch-group">
                    <x-form.switch name="allow_print" label="Cho phép in bài viết" wire:model="allow_print" />
                    <x-form.switch name="allow_comments" label="Cho phép bình luận" wire:model="allow_comments" />
                    <x-form.switch name="fetch_remote_images" label="Lấy ảnh từ trang nguồn" wire:model="fetch_remote_images" />
                    <x-form.switch name="show_placeholder_image" label="Hiện ảnh mặc định khi tin không có ảnh" wire:model="show_placeholder_image" />
                </div>
            </x-form.section>
            <x-form.section title="Hiển thị & hình ảnh" description="Giới hạn số lượng và kích thước" icon="image">
                <div class="news-number-grid">
                    <x-form.input name="items_per_page" label="Số tin mỗi trang" type="number" wire:model="items_per_page" min="1" />
                    <x-form.input name="category_items_per_page" label="Số tin trang danh mục" type="number" wire:model="category_items_per_page" min="1" />
                    <x-form.input name="featured_limit" label="Số tin tiêu điểm" type="number" wire:model="featured_limit" min="1" />
                    <x-form.input name="related_limit" label="Số tin liên quan" type="number" wire:model="related_limit" min="0" />
                    <x-form.input name="thumbnail_size" label="Cỡ ảnh thu nhỏ (px)" type="number" wire:model="thumbnail_size" min="100" />
                    <x-form.input name="max_upload_width" label="Chiều ngang ảnh tối đa (px)" type="number" wire:model="max_upload_width" min="320" />
                </div>
            </x-form.section>
        </div>
    </form>
</div>
