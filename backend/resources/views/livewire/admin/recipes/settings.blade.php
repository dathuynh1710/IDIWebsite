<div>
    <x-admin.page-header title="Cấu hình Recipes" description="Thiết lập nội dung, SEO và cách hiển thị trang công thức với 3 ngôn ngữ" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.recipes.index') }}" wire:navigate><x-ui.icon name="arrow-left" size="18" /> Danh sách</a>
            <x-ui.button type="submit" form="recipe-settings-form" icon="save">Lưu cấu hình</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="recipe-settings-form" wire:submit="save">
        @if($errors->any())<div class="validation-summary" role="alert"><x-ui.icon name="alert" /><div><strong>Vui lòng kiểm tra lại thông tin.</strong><p>Có {{ $errors->count() }} trường cần chỉnh sửa.</p></div></div>@endif
        <div class="about-settings-grid">
            <div>
                <x-form.language-tabs :locales="$locales">
                    @foreach($locales as $locale => $label)
                        <section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" x-show="active === '{{ $locale }}'" x-cloak>
                            <x-form.section title="Nội dung {{ $label }}" description="Cấu hình riêng cho phiên bản {{ $label }}" icon="languages">
                                <div class="localized-fields">
                                    <x-form.input name="page_title[{{ $locale }}]" label="Tiêu đề trang" wire:model.blur="page_title.{{ $locale }}" :required="$locale === 'vi'" />
                                    <x-form.textarea name="description[{{ $locale }}]" label="Mô tả trang Recipes" wire:model.blur="description.{{ $locale }}" rows="5" maxlength="2000" />
                                    <details class="seo-panel" open><summary>Search Engine Optimization (SEO)</summary><div class="seo-grid">
                                        <x-form.input name="seo_title[{{ $locale }}]" label="Friendly title" wire:model.blur="seo_title.{{ $locale }}" maxlength="255" />
                                        <x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model.blur="meta_description.{{ $locale }}" rows="4" maxlength="500" />
                                        <div class="snippet-preview"><small>Xem trước kết quả tìm kiếm</small><strong>{{ $seo_title[$locale] ?: ($page_title[$locale] ?: 'Công thức bạn có thể thử') }}</strong><span>idiseafood.com/{{ $locale }}/{{ $locale === 'vi' ? 'cong-thuc' : ($locale === 'en' ? 'recipes' : 'shipu') }}</span><p>{{ $meta_description[$locale] ?: ($description[$locale] ?: 'Khám phá những công thức ngon từ IDI Seafood.') }}</p></div>
                                    </div></details>
                                </div>
                            </x-form.section>
                        </section>
                    @endforeach
                </x-form.language-tabs>
            </div>
            <aside>
                <x-form.section title="Cấu hình chung" description="Áp dụng cho toàn bộ module Recipes" icon="settings">
                    <div class="form-stack">
                        <x-form.input name="items_per_page" label="Số công thức mỗi trang" type="number" wire:model="items_per_page" min="1" max="100" required />
                        <x-form.input name="thumbnail_size" label="Kích thước ảnh thu nhỏ (px)" type="number" wire:model="thumbnail_size" min="50" max="1000" required />
                        <x-form.input name="max_upload_width" label="Chiều rộng tối đa ảnh tải lên (px)" type="number" wire:model="max_upload_width" min="320" max="5000" required />
                        <x-form.switch name="show_placeholder_image" label="Hiện ảnh mặc định khi chưa có ảnh" wire:model="show_placeholder_image" />
                        <x-form.switch name="is_active" label="Kích hoạt module Recipes" wire:model="is_active" />
                    </div>
                </x-form.section>
            </aside>
        </div>
        <div class="mobile-form-actions"><a class="button button-secondary" href="{{ route('admin.recipes.index') }}" wire:navigate>Hủy</a><x-ui.button type="submit" icon="save">Lưu cấu hình</x-ui.button></div>
    </form>
</div>
