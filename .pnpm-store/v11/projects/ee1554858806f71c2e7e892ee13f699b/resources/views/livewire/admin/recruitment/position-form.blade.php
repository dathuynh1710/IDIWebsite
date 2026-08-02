<div>
    <x-admin.page-header :title="$position ? 'Cập nhật tuyển dụng' : 'Thêm tuyển dụng'" description="Biên tập thông tin công việc, lịch xuất bản và SEO cho Việt, Anh, Trung." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><a class="button button-secondary" href="{{ route('admin.recruitment.positions.index') }}" wire:navigate>Quay lại</a>@if($position)<a class="button button-ghost" href="{{ route('admin.recruitment.positions.preview', $position) }}" target="_blank"><x-ui.icon name="eye" size="18" /> Xem trước</a>@endif<x-ui.button type="submit" form="position-form" icon="save">Lưu tuyển dụng</x-ui.button></x-slot:actions>
    </x-admin.page-header>
    <form id="position-form" wire:submit="save" data-dirty-form><div class="product-form-grid">
        <aside><x-form.section title="Thông tin chung" description="Áp dụng cho cả ba ngôn ngữ" icon="briefcase"><div class="form-stack">
            <x-form.input name="code" label="Mã tuyển dụng" wire:model="code" />
            <x-form.input name="department" label="Phòng ban" wire:model="department" />
            <div class="recruitment-common-grid"><x-form.input name="quantity" label="Số lượng" type="number" wire:model="quantity" min="1" /><x-form.input name="sort_order" label="Thứ tự" type="number" wire:model="sort_order" min="0" /></div>
            <x-form.input name="expires_at" label="Hạn nộp hồ sơ" type="date" wire:model="expires_at" />
            <x-form.switch name="is_active" label="Đang hiển thị" wire:model="is_active" />
        </div></x-form.section></aside>
        <div><x-form.language-tabs :locales="$locales">@foreach($locales as $locale => $label)<section id="panel-{{ $locale }}" class="tab-panel" x-show="active === '{{ $locale }}'" x-cloak>
            <x-form.section title="Nội dung {{ $label }}" description="Bản dịch và lịch xuất bản độc lập" icon="languages"><div class="localized-fields">
                <x-form.input name="title[{{ $locale }}]" label="Tiêu đề tuyển dụng" wire:model.blur="title.{{ $locale }}" :required="$locale === 'vi'" />
                <div class="form-field"><label>Friendly URL</label><div class="slug-input"><span>/{{ $locale }}/careers/</span><input class="input" wire:model="slug.{{ $locale }}"><button type="button" wire:click="generateSlug('{{ $locale }}')">Tạo lại</button></div><x-form.field-error name="slug.{{ $locale }}" /></div>
                <x-form.input name="location[{{ $locale }}]" label="Nơi làm việc" wire:model="location.{{ $locale }}" />
                <x-form.textarea name="summary[{{ $locale }}]" label="Mô tả ngắn" wire:model="summary.{{ $locale }}" rows="3" maxlength="2000" />
                @foreach(['description' => 'Mô tả công việc', 'requirements' => 'Yêu cầu công việc', 'benefits' => 'Phúc lợi'] as $field => $caption)
                    <div class="form-field rich-editor"><label>{{ $caption }}</label><textarea id="position-{{ $field }}-{{ $locale }}" class="textarea rich-text-textarea" rows="9" wire:model="{{ $field }}.{{ $locale }}"></textarea><x-form.field-error name="{{ $field }}.{{ $locale }}" /></div>
                @endforeach
                <details class="seo-panel" open><summary>Search Engine Optimization (SEO)</summary><div class="seo-grid"><x-form.input name="seo_title[{{ $locale }}]" label="Tiêu đề SEO" wire:model="seo_title.{{ $locale }}" maxlength="255" /><x-form.textarea name="meta_description[{{ $locale }}]" label="Meta description" wire:model="meta_description.{{ $locale }}" rows="3" maxlength="500" /></div></details>
                <div class="publication-grid"><x-form.select name="translation_status[{{ $locale }}]" label="Trạng thái bản dịch" :options="$statuses" wire:model.live="translation_status.{{ $locale }}" />@if(($translation_status[$locale] ?? '') === 'scheduled')<x-form.input name="locale_published_at[{{ $locale }}]" label="Ngày xuất bản" type="datetime-local" wire:model="locale_published_at.{{ $locale }}" required />@endif</div>
            </div></x-form.section>
        </section>@endforeach</x-form.language-tabs></div>
    </div></form>
</div>
