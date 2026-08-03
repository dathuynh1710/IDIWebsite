<div>
    <x-admin.page-header title="Cấu hình giới thiệu" description="Thiết lập tiêu đề và SEO cho từng ngôn ngữ" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button type="submit" form="about-settings-form" icon="save">Lưu cấu hình</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="about-settings-form" wire:submit="save">
        @if($errors->any())<div class="validation-summary" role="alert"><x-ui.icon name="alert" /><div><strong>Vui lòng kiểm tra lại thông tin.</strong><p>Có {{ $errors->count() }} trường cần chỉnh sửa.</p></div></div>@endif

        <div class="about-settings-content">
            <x-form.language-tabs :locales="$locales">
                @foreach($locales as $locale => $label)
                    <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                        <x-form.section title="Nội dung {{ $label }}" description="Tiêu đề, mô tả và thông tin SEO cho phiên bản {{ $label }}" icon="languages">
                            <div class="localized-fields">
                                <x-form.input name="page_title[{{ $locale }}]" label="Tiêu đề trang" wire:model.blur="page_title.{{ $locale }}" :required="$locale === 'vi'" />
                                <x-form.textarea name="description[{{ $locale }}]" label="Mô tả trang giới thiệu" wire:model.blur="description.{{ $locale }}" rows="4" />
                                <details class="seo-panel" open><summary>Search Engine Optimization (SEO)</summary><div class="seo-grid">
                                    <x-form.input name="seo_title[{{ $locale }}]" label="Tiêu đề SEO" wire:model.blur="seo_title.{{ $locale }}" maxlength="255" />
                                    <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model.blur="meta_description.{{ $locale }}" rows="4" maxlength="500" />
                                    <div class="snippet-preview"><small>Xem trước kết quả tìm kiếm</small><strong>{{ $seo_title[$locale] ?: ($page_title[$locale] ?: 'Giới thiệu') }}</strong><span>idiseafood.com/{{ $locale }}/{{ $locale === 'vi' ? 'gioi-thieu' : ($locale === 'en' ? 'about' : 'guanyu') }}</span><p>{{ $meta_description[$locale] ?: ($description[$locale] ?: 'Mô tả trang giới thiệu sẽ hiển thị tại đây.') }}</p></div>
                                </div></details>
                            </div>
                        </x-form.section>
                    </section>
                @endforeach
            </x-form.language-tabs>
        </div>

        <div class="mobile-form-actions"><a class="button button-secondary" href="{{ route('admin.about-pages.index') }}" wire:navigate>Hủy</a><x-ui.button type="submit" icon="save">Lưu cấu hình</x-ui.button></div>
    </form>
</div>
