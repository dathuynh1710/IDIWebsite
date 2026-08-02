@props(['formId', 'title' => 'Xác nhận xóa', 'message', 'confirmLabel' => 'Xóa sản phẩm'])
<div x-data="confirmDialog()" @keydown.escape.window="close()" class="confirm-wrap">
    <span @click="openDialog($event)">{{ $trigger }}</span>
    <template x-teleport="body">
        <div class="modal-backdrop" x-show="open" x-transition.opacity x-cloak @click.self="close()">
            <div class="modal-card" role="alertdialog" aria-modal="true" aria-labelledby="{{ $formId }}-title" x-ref="dialog">
                <div class="modal-icon"><x-ui.icon name="alert" /></div>
                <h2 id="{{ $formId }}-title">{{ $title }}</h2>
                <p>{{ $message }}</p>
                <div class="modal-actions">
                    <x-ui.button variant="secondary" @click="close()">Hủy</x-ui.button>
                    <x-ui.button variant="danger" type="submit" :form="$formId">{{ $confirmLabel }}</x-ui.button>
                </div>
            </div>
        </div>
    </template>
</div>
