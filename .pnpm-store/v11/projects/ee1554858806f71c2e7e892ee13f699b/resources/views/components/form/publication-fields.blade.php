@props(['locale', 'product' => null, 'statuses'])
@php
    $status = old("translation_status.{$locale}", $product?->getTranslation('translation_status', $locale, false) ?: 'draft');
    $publishedAt = old("locale_published_at.{$locale}", optional(
        $product?->getTranslation('locale_published_at', $locale, false)
            ? \Illuminate\Support\Carbon::parse($product->getTranslation('locale_published_at', $locale, false))
            : null
    )->format('Y-m-d\TH:i'));
@endphp
<div class="publication-grid" x-data="{ status: @js($status) }">
    <x-form.select name="translation_status[{{ $locale }}]" label="Trạng thái bản dịch" :options="$statuses" :selected="$status" x-model="status" required />
    <div x-show="status === 'scheduled'" x-transition>
        <x-form.input name="locale_published_at[{{ $locale }}]" label="Ngày xuất bản" type="datetime-local" :value="$publishedAt" x-bind:required="status === 'scheduled'" />
    </div>
</div>
