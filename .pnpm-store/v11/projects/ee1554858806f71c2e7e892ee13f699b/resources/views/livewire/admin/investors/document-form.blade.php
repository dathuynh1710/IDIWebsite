<div>
    <x-admin.page-header :title="$document ? 'Cập nhật QHCĐ' : 'Thêm QHCĐ'" description="Đăng tài liệu và tệp tải xuống riêng cho Việt, Anh, Trung." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><a class="button button-secondary" href="{{ route('admin.investors.documents.index') }}" wire:navigate>Quay lại</a><x-ui.button type="submit" form="investor-document-form" icon="save">Lưu tài liệu</x-ui.button></x-slot:actions>
    </x-admin.page-header>
    <form id="investor-document-form" wire:submit="save" data-dirty-form><div class="product-form-grid">
        <aside><x-form.section title="Thông tin chung" description="Áp dụng cho mọi ngôn ngữ" icon="info"><div class="form-stack">
            <div class="form-field"><label>Danh mục <span>*</span></label><select class="select" wire:model="document_category_id"><option value="">Chọn danh mục</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->getTranslation('name', 'vi', false) }}</option>@endforeach</select><x-form.field-error name="document_category_id" /></div>
            <x-form.input name="document_number" label="Số / mã văn bản" wire:model="document_number" />
            <div class="news-number-grid">
                <x-form.input name="year" label="Năm" type="number" wire:model="year" min="2000" max="2100" />
                <x-form.select name="quarter" label="Quý" :options="['' => 'Không chọn', 1 => 'Quý 1', 2 => 'Quý 2', 3 => 'Quý 3', 4 => 'Quý 4']" wire:model="quarter" />
            </div>
            <x-form.input name="published_on" label="Ngày đăng" type="date" wire:model="published_on" />
            <x-form.input name="sort_order" label="Thứ tự hiển thị" type="number" wire:model="sort_order" min="0" />
            <div class="switch-group"><x-form.switch name="is_featured" label="Tài liệu nổi bật" wire:model="is_featured" /><x-form.switch name="is_active" label="Đang hiển thị" wire:model="is_active" /></div>
        </div></x-form.section></aside>
        <div><x-form.language-tabs :locales="$locales">@foreach($locales as $locale => $label)
            @php($currentFile = $document?->files?->firstWhere('locale', $locale))
            <section id="panel-{{ $locale }}" class="tab-panel" x-show="active === '{{ $locale }}'" x-cloak>
                <x-form.section title="Nội dung {{ $label }}" description="Tiêu đề, mô tả và tệp tải xuống của bản {{ strtoupper($locale) }}" icon="languages"><div class="localized-fields">
                    <x-form.input name="title[{{ $locale }}]" label="Tiêu đề" wire:model="title.{{ $locale }}" :required="$locale === 'vi'" />
                    <x-form.textarea name="summary[{{ $locale }}]" label="Mô tả ngắn" wire:model="summary.{{ $locale }}" rows="7" />
                    <div class="form-field">
                        <label>Tệp tải xuống {{ strtoupper($locale) }} @if($locale === 'vi' && !$document)<span>*</span>@endif</label>
                        @if(isset($uploads[$locale]) && $uploads[$locale])
                            <div class="media-preview"><strong>{{ $uploads[$locale]->getClientOriginalName() }}</strong><small>{{ number_format($uploads[$locale]->getSize() / 1024 / 1024, 2) }} MB</small></div>
                        @elseif($currentFile && !($removeFiles[$locale] ?? false))
                            <div class="media-preview"><a href="{{ $currentFile->media->url }}" target="_blank"><x-ui.icon name="file" size="20" /> {{ $currentFile->media->original_name }}</a><small>{{ number_format($currentFile->media->file_size / 1024 / 1024, 2) }} MB</small></div>
                        @else
                            <div class="media-preview"><span class="media-placeholder"><x-ui.icon name="upload" size="28" /> Chọn PDF, Word, Excel, PowerPoint hoặc ZIP</span></div>
                        @endif
                        <input class="input" type="file" wire:model="uploads.{{ $locale }}" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip">
                        <small>Dung lượng tối đa {{ $maxUploadMegabytes }} MB.</small>
                        @if((isset($uploads[$locale]) && $uploads[$locale]) || ($currentFile && !($removeFiles[$locale] ?? false)))<button class="button button-ghost" type="button" wire:click="removeFile('{{ $locale }}')">Xóa tệp</button>@endif
                        <x-form.field-error name="uploads.{{ $locale }}" />
                    </div>
                    <x-form.input name="display_name[{{ $locale }}]" label="Tên hiển thị của tệp" wire:model="display_name.{{ $locale }}" placeholder="Để trống để dùng tên tệp gốc" />
                </div></x-form.section>
            </section>
        @endforeach</x-form.language-tabs></div>
    </div></form>
</div>
