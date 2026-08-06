const TYPES = ['success', 'error', 'warning', 'info'];

const toastrInstance = () => window.toastr;

const configure = () => {
    const instance = toastrInstance();
    if (!instance) return false;

    instance.options = {
        closeButton: true,
        debug: false,
        newestOnTop: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        preventDuplicates: true,
        onclick: null,
        showDuration: 300,
        hideDuration: 300,
        timeOut: 4000,
        extendedTimeOut: 1000,
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut',
        tapToDismiss: false,
        escapeHtml: true,
    };

    return true;
};

export const notify = (message, type = 'info', title = '') => {
    if (!message || !configure()) return;
    const normalizedType = TYPES.includes(type) ? type : 'info';
    toastrInstance()[normalizedType](String(message), title);
};

export const notifyValidation = (errors) => {
    const messages = Object.values(errors || {})
        .flat()
        .filter(Boolean)
        .filter((message, index, values) => values.indexOf(message) === index);

    if (messages.length) notify(messages.join(' '), 'error', 'Dữ liệu chưa hợp lệ');
};

window.toast = {
    show: notify,
    success: (message, title = '') => notify(message, 'success', title),
    error: (message, title = '') => notify(message, 'error', title),
    warning: (message, title = '') => notify(message, 'warning', title),
    info: (message, title = '') => notify(message, 'info', title),
    validation: notifyValidation,
};

const showBootstrapToast = () => {
    const element = document.getElementById('toast-bootstrap');
    if (!element || element.dataset.consumed === 'true') return;
    element.dataset.consumed = 'true';

    try {
        const toast = JSON.parse(element.textContent || 'null');
        if (toast?.message) notify(toast.message, toast.type);
    } catch (error) {
        console.error('Không thể đọc thông báo Toast.', error);
    }
};

window.addEventListener('toast', ({ detail }) => notify(detail?.message, detail?.type, detail?.title));
document.addEventListener('DOMContentLoaded', showBootstrapToast);
document.addEventListener('livewire:navigated', showBootstrapToast);

document.addEventListener('livewire:init', () => {
    window.Livewire?.hook('commit', ({ succeed }) => {
        succeed(({ snapshot }) => notifyValidation(snapshot?.memo?.errors));
    });

    window.Livewire?.hook('request', ({ fail }) => {
        fail(({ status, content, preventDefault }) => {
            if (status === 422) return;
            preventDefault?.();
            notify(content?.message || 'Yêu cầu không thể xử lý. Vui lòng thử lại.', 'error');
        });
    });
});
