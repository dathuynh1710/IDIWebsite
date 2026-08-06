const TYPES = ['success', 'error', 'warning', 'info']

const configure = () => {
  if (!window.toastr) return false

  window.toastr.options = {
    closeButton: true,
    debug: false,
    newestOnTop: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    preventDuplicates: true,
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
  }

  return true
}

const show = (message, type = 'info', title = '') => {
  if (!message || !configure()) return
  const normalizedType = TYPES.includes(type) ? type : 'info'
  window.toastr[normalizedType](String(message), title)
}

const validation = (errors) => {
  const messages = Object.values(errors || {})
    .flat()
    .filter(Boolean)
    .filter((message, index, values) => values.indexOf(message) === index)

  if (messages.length) show(messages.join(' '), 'error', 'Dữ liệu chưa hợp lệ')
}

export const toast = {
  show,
  validation,
  success: (message, title = '') => show(message, 'success', title),
  error: (message, title = '') => show(message, 'error', title),
  warning: (message, title = '') => show(message, 'warning', title),
  info: (message, title = '') => show(message, 'info', title),
}

export default toast
