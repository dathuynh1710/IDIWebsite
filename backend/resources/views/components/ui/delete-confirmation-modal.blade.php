@props([
    'wireKey' => 'delete-confirmation',
    'title' => 'Xác nhận xóa?',
    'confirmLabel' => 'Có, xóa',
    'warning' => null,
])

<div
    class="modal-backdrop contact-delete-modal"
    wire:key="{{ $wireKey }}"
    wire:click.self="cancelDelete"
    x-data
    x-init="$nextTick(() => $refs.cancelButton.focus())"
    x-on:keydown.escape.window="$wire.cancelDelete()"
>
    <section class="modal-card contact-delete-card" role="alertdialog" aria-modal="true" aria-labelledby="{{ $wireKey }}-title" aria-describedby="{{ $wireKey }}-description">
        <div class="modal-icon contact-delete-icon"><x-ui.icon name="alert" size="30" /></div>
        <h2 id="{{ $wireKey }}-title">{{ $title }}</h2>
        <p id="{{ $wireKey }}-description">{{ $slot }}</p>
        @if($warning)<p class="contact-delete-warning">{{ $warning }}</p>@endif
        <div class="modal-actions contact-delete-actions">
            <button class="button button-secondary" type="button" wire:click="cancelDelete" x-ref="cancelButton">Không, giữ lại</button>
            <button class="button button-danger" type="button" wire:click="confirmDelete" wire:loading.attr="disabled" wire:target="confirmDelete">
                <x-ui.icon name="trash" size="17" />
                <span wire:loading.remove wire:target="confirmDelete">{{ $confirmLabel }}</span>
                <span wire:loading wire:target="confirmDelete">Đang xóa...</span>
            </button>
        </div>
    </section>
</div>
