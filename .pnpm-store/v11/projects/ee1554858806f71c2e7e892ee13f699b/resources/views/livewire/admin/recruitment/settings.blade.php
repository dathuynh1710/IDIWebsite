<div>
    <x-admin.page-header title="Cấu hình tuyển dụng" description="Thiết lập nội dung, SEO và cách tiếp nhận hồ sơ theo từng ngôn ngữ." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><x-ui.button type="submit" form="recruitment-settings" icon="save">Lưu cấu hình</x-ui.button></x-slot:actions>
    </x-admin.page-header>
    <form id="recruitment-settings" wire:submit="save" data-dirty-form>
        <x-form.language-tabs :locales="$locales">
            @foreach($locales as $locale => $label)
                <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                    <x-form.section title="Nội dung {{ $label }}" description="Tiêu đề, giới thiệu và SEO độc lập cho bản {{ $label }}" icon="languages">
                        <div class="seo-grid">
                            <x-form.input name="page_title[{{ $locale }}]" label="Tiêu đề trang" wire:model="page_title.{{ $locale }}" :required="$locale === 'vi'" />
                            <div class="form-field rich-editor"><label>Nội dung giới thiệu</label><textarea id="recruitment-description-{{ $locale }}" class="textarea rich-text-textarea" rows="8" wire:model="description.{{ $locale }}"></textarea><x-form.field-error name="description.{{ $locale }}" /></div>
                            <x-form.input name="seo_title[{{ $locale }}]" label="Tiêu đề SEO" wire:model="seo_title.{{ $locale }}" maxlength="255" />
                            <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model="meta_description.{{ $locale }}" rows="3" maxlength="500" />
                        </div>
                    </x-form.section>
                </section>
            @endforeach
        </x-form.language-tabs>
        <x-form.section title="Cấu hình chung" description="Áp dụng cho toàn bộ module tuyển dụng" icon="settings">
            <div class="recruitment-settings-grid">
                <x-form.input name="items_per_page" label="Số tin mỗi trang" type="number" wire:model="items_per_page" min="1" />
                <x-form.input name="notification_email" label="Email nhận thông báo" type="email" wire:model="notification_email" />
                <x-form.switch name="application_enabled" label="Cho phép gửi hồ sơ trực tuyến" wire:model="application_enabled" />
            </div>
        </x-form.section>
    </form>
</div>
