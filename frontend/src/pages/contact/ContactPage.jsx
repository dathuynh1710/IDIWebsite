import { useState } from 'react'
import { Link } from 'react-router'
import PageHead from '@components/common/PageHead'
import { inquiryService } from '@services/inquiry.service'
import toast from '@/utils/toast'

const INITIAL_FORM = {
  inquiryType: 'Báo giá xuất khẩu',
  fullName: '',
  phone: '',
  email: '',
  address: '',
  subject: '',
  message: '',
  consent: false,
  companyWebsite: '',
}

const CONTACT_CHANNELS = [
  {
    label: 'Trụ sở chính',
    value: 'Quốc lộ 80, Cụm công nghiệp Vàm Cống, ấp An Thạnh, xã Lấp Vò, Tỉnh Đồng Tháp, Việt Nam',
    href: null,
  },
  {
    label: 'Điện thoại trụ sở',
    value: '+84 2773 680 383 / +84 2777 300 468',
    href: 'tel:+842773680383',
  },
  {
    label: 'Fax',
    value: '+84 2773 680 382',
    href: 'tel:+842773680382',
  },
  {
    label: 'Email',
    value: 'info@idiseafood.com',
    href: 'mailto:info@idiseafood.com',
  },
  {
    label: 'Văn phòng đại diện Hồ Chí Minh',
    value: '9 Nguyễn Kim, phường 12, quận 5, Thành phố Hồ Chí Minh, Việt Nam',
    href: null,
  },
  {
    label: 'Điện thoại văn phòng Hồ Chí Minh',
    value: '+84 932 824 888',
    href: 'tel:+84932824888',
  },
]

const REQUIRED_FIELDS = ['fullName', 'phone', 'email', 'address', 'subject', 'message', 'consent']

function validateField(name, value) {
  const text = typeof value === 'string' ? value.trim() : value

  if (name === 'consent') {
    return value ? '' : 'Vui lòng đồng ý với chính sách bảo mật.'
  }
  if (!text) return 'Thông tin này là bắt buộc.'
  if (name === 'fullName' && text.length < 2) return 'Họ tên cần có ít nhất 2 ký tự.'
  if (name === 'phone' && !/^\+?[\d\s\-().]{7,20}$/.test(text)) {
    return 'Vui lòng nhập số điện thoại hợp lệ.'
  }
  if (name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text)) {
    return 'Vui lòng nhập địa chỉ email hợp lệ.'
  }
  if (name === 'subject' && text.length < 3) return 'Tiêu đề cần có ít nhất 3 ký tự.'
  if (name === 'message' && text.length < 10) return 'Nội dung cần có ít nhất 10 ký tự.'
  if (name === 'message' && text.length > 1000) return 'Nội dung không được vượt quá 1.000 ký tự.'
  return ''
}

function FormField({ label, name, error, required = true, children }) {
  return (
    <label className="block" htmlFor={name}>
      <span className="mb-2 block text-sm font-bold text-ocean-deep">
        {label}
        {required && <span className="ml-1 text-[#C04B38]" aria-hidden="true">*</span>}
      </span>
      {children}
      {error && (
        <span id={`${name}-error`} className="mt-1.5 block text-xs font-medium text-[#B93B2B]">
          {error}
        </span>
      )}
    </label>
  )
}

export default function ContactPage() {
  const [form, setForm] = useState(INITIAL_FORM)
  const [errors, setErrors] = useState({})
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [referenceId, setReferenceId] = useState('')

  const fieldClass = (name) => [
    'h-12 w-full rounded-xl border bg-white px-4 text-sm text-ink outline-none transition',
    'placeholder:text-storm-grey/55 focus:ring-2',
    errors[name]
      ? 'border-[#D46A5A] focus:border-[#D46A5A] focus:ring-[#D46A5A]/15'
      : 'border-light-mist focus:border-seafoam focus:ring-seafoam/15',
  ].join(' ')

  const handleChange = (event) => {
    const { name, value, type, checked } = event.target
    const nextValue = type === 'checkbox' ? checked : value
    setForm(current => ({ ...current, [name]: nextValue }))
    if (errors[name]) {
      setErrors(current => ({ ...current, [name]: validateField(name, nextValue) }))
    }
  }

  const handleBlur = (event) => {
    const { name, value, type, checked } = event.target
    setErrors(current => ({
      ...current,
      [name]: validateField(name, type === 'checkbox' ? checked : value),
    }))
  }

  const handleSubmit = async (event) => {
    event.preventDefault()
    if (form.companyWebsite) return

    const nextErrors = REQUIRED_FIELDS.reduce((result, field) => {
      const error = validateField(field, form[field])
      if (error) result[field] = error
      return result
    }, {})

    if (Object.keys(nextErrors).length) {
      setErrors(nextErrors)
      toast.validation(nextErrors)
      const firstInvalidField = document.querySelector(`[name="${Object.keys(nextErrors)[0]}"]`)
      firstInvalidField?.focus()
      return
    }

    setIsSubmitting(true)
    try {
      const result = await inquiryService.submitTrade(form)
      setReferenceId(result.referenceId)
      setForm(INITIAL_FORM)
      setErrors({})
    } catch {
      // The shared Axios interceptor displays the server or network error.
    } finally {
      setIsSubmitting(false)
    }
  }

  const startNewInquiry = () => {
    setReferenceId('')
    setForm(INITIAL_FORM)
  }

  return (
    <>
      <PageHead
        title="Liên hệ | IDI Seafood"
        description="Liên hệ IDI Seafood để nhận báo giá, tư vấn sản phẩm cá tra, thông tin hợp tác và hỗ trợ xuất khẩu."
      />

      <main className="bg-arctic-white pb-24 pt-28 lg:pb-32 lg:pt-32">
        <div className="container">
          <nav className="mb-8 flex items-center gap-2 text-sm text-storm-grey" aria-label="Đường dẫn trang">
            <Link to="/" className="transition hover:text-seafoam">Trang chủ</Link>
            <span aria-hidden="true">/</span>
            <span className="font-semibold text-ocean-deep" aria-current="page">Liên hệ</span>
          </nav>

          <div className="grid overflow-hidden rounded-3xl border border-light-mist bg-white shadow-2xl lg:grid-cols-[0.72fr_1.28fr]">
            <aside className="relative overflow-hidden bg-[#102B4D] p-7 text-white sm:p-10 lg:p-12">
              <div
                className="absolute inset-0 opacity-20"
                style={{ background: 'radial-gradient(circle at 0% 100%, #1A936F, transparent 42%)' }}
              />
              <div className="relative z-10">
                <span className="mb-3 block text-xs font-bold uppercase tracking-[0.16em] text-coral-gold">
                  Thông tin liên hệ
                </span>
                <h2 className="mb-10 text-2xl font-black text-white">IDI Seafood</h2>

                <div className="space-y-7">
                  {CONTACT_CHANNELS.map((channel, index) => (
                    <div key={channel.label} className="grid grid-cols-[2.25rem_1fr] gap-3">
                      <span className="flex h-9 w-9 items-center justify-center rounded-full border border-white/15 bg-white/5 text-xs font-black text-seafoam-light">
                        {String(index + 1).padStart(2, '0')}
                      </span>
                      <div className="min-w-0">
                        <span className="mb-1 block text-[10px] font-bold uppercase tracking-[0.14em] text-white/35">
                          {channel.label}
                        </span>
                        {channel.href ? (
                          <a
                            href={channel.href}
                            className="break-words text-sm font-bold text-white transition hover:text-coral-light"
                          >
                            {channel.value}
                          </a>
                        ) : (
                          <p className="text-sm font-bold leading-relaxed text-white">{channel.value}</p>
                        )}
                      </div>
                    </div>
                  ))}
                </div>

              </div>
            </aside>

            <section className="p-6 sm:p-10 lg:p-12">
              {referenceId ? (
                <div className="flex min-h-[38rem] flex-col items-center justify-center text-center" role="status">
                  <div className="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-seafoam-pale text-3xl text-seafoam">
                    ✓
                  </div>
                  <span className="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-seafoam">
                    Gửi liên hệ thành công
                  </span>
                  <h2 className="mb-4 text-3xl font-black text-ocean-deep">Cảm ơn bạn đã liên hệ IDI</h2>
                  <p className="max-w-lg text-sm leading-relaxed text-storm-grey">
                    Chúng tôi đã tiếp nhận thông tin và sẽ phản hồi trong thời gian sớm nhất.
                    Mã tham chiếu của bạn là <strong className="text-ocean-deep">{referenceId}</strong>.
                  </p>
                  <button type="button" onClick={startNewInquiry} className="btn btn-secondary mt-8">
                    Gửi yêu cầu khác
                  </button>
                </div>
              ) : (
                <>
                  <div className="mb-8">
                    <span className="section-eyebrow">Biểu mẫu liên hệ</span>
                    <h2 className="mt-2 text-2xl font-black text-ocean-deep sm:text-3xl">
                      Bạn đang cần IDI hỗ trợ điều gì?
                    </h2>
                    <p className="mt-3 text-sm text-storm-grey">
                      Các trường có dấu <span className="text-[#C04B38]">*</span> là bắt buộc.
                    </p>
                  </div>

                  <form onSubmit={handleSubmit} noValidate>
                    <div className="mb-7">
                      <label htmlFor="inquiryType" className="mb-2 block text-sm font-bold text-ocean-deep">
                        Loại yêu cầu
                      </label>
                      <select
                        id="inquiryType"
                        name="inquiryType"
                        value={form.inquiryType}
                        onChange={handleChange}
                        className="h-12 w-full rounded-xl border border-light-mist bg-white px-4 text-sm text-ink outline-none transition focus:border-seafoam focus:ring-2 focus:ring-seafoam/15"
                      >
                        <option>Báo giá xuất khẩu</option>
                        <option>Tư vấn sản phẩm</option>
                        <option>Hợp tác kinh doanh</option>
                        <option>Quan hệ nhà đầu tư</option>
                        <option>Tuyển dụng</option>
                        <option>Yêu cầu khác</option>
                      </select>
                    </div>

                    <div className="grid gap-6 sm:grid-cols-2">
                      <FormField label="Họ và tên" name="fullName" error={errors.fullName}>
                        <input
                          id="fullName"
                          name="fullName"
                          type="text"
                          autoComplete="name"
                          value={form.fullName}
                          onChange={handleChange}
                          onBlur={handleBlur}
                          placeholder="Nguyễn Văn A"
                          className={fieldClass('fullName')}
                          aria-invalid={Boolean(errors.fullName)}
                          aria-describedby={errors.fullName ? 'fullName-error' : undefined}
                        />
                      </FormField>

                      <FormField label="Điện thoại" name="phone" error={errors.phone}>
                        <input
                          id="phone"
                          name="phone"
                          type="tel"
                          autoComplete="tel"
                          value={form.phone}
                          onChange={handleChange}
                          onBlur={handleBlur}
                          placeholder="+84 000 000 000"
                          className={fieldClass('phone')}
                          aria-invalid={Boolean(errors.phone)}
                          aria-describedby={errors.phone ? 'phone-error' : undefined}
                        />
                      </FormField>

                      <FormField label="Email" name="email" error={errors.email}>
                        <input
                          id="email"
                          name="email"
                          type="email"
                          autoComplete="email"
                          value={form.email}
                          onChange={handleChange}
                          onBlur={handleBlur}
                          placeholder="email@congty.com"
                          className={fieldClass('email')}
                          aria-invalid={Boolean(errors.email)}
                          aria-describedby={errors.email ? 'email-error' : undefined}
                        />
                      </FormField>

                      <FormField label="Địa chỉ" name="address" error={errors.address}>
                        <input
                          id="address"
                          name="address"
                          type="text"
                          autoComplete="street-address"
                          value={form.address}
                          onChange={handleChange}
                          onBlur={handleBlur}
                          placeholder="Thành phố, Quốc gia"
                          className={fieldClass('address')}
                          aria-invalid={Boolean(errors.address)}
                          aria-describedby={errors.address ? 'address-error' : undefined}
                        />
                      </FormField>
                    </div>

                    <div className="mt-6">
                      <FormField label="Tiêu đề" name="subject" error={errors.subject}>
                        <input
                          id="subject"
                          name="subject"
                          type="text"
                          value={form.subject}
                          onChange={handleChange}
                          onBlur={handleBlur}
                          placeholder="Nội dung bạn cần được hỗ trợ"
                          className={fieldClass('subject')}
                          aria-invalid={Boolean(errors.subject)}
                          aria-describedby={errors.subject ? 'subject-error' : undefined}
                        />
                      </FormField>
                    </div>

                    <div className="mt-6">
                      <FormField label="Nội dung liên hệ" name="message" error={errors.message}>
                        <textarea
                          id="message"
                          name="message"
                          rows="7"
                          maxLength="1000"
                          value={form.message}
                          onChange={handleChange}
                          onBlur={handleBlur}
                          placeholder="Hãy mô tả nhu cầu, sản phẩm quan tâm, thị trường và sản lượng dự kiến..."
                          className={[
                            'w-full resize-y rounded-xl border bg-white px-4 py-3 text-sm leading-relaxed text-ink outline-none transition',
                            'placeholder:text-storm-grey/55 focus:ring-2',
                            errors.message
                              ? 'border-[#D46A5A] focus:border-[#D46A5A] focus:ring-[#D46A5A]/15'
                              : 'border-light-mist focus:border-seafoam focus:ring-seafoam/15',
                          ].join(' ')}
                          aria-invalid={Boolean(errors.message)}
                          aria-describedby={errors.message ? 'message-error message-count' : 'message-count'}
                        />
                      </FormField>
                      <span id="message-count" className="mt-1 block text-right text-xs text-storm-grey">
                        {form.message.length}/1.000
                      </span>
                    </div>

                    <input
                      type="text"
                      name="companyWebsite"
                      value={form.companyWebsite}
                      onChange={handleChange}
                      tabIndex="-1"
                      autoComplete="off"
                      className="hidden"
                      aria-hidden="true"
                    />

                    <div className="mt-6">
                      <label className="flex cursor-pointer items-start gap-3">
                        <input
                          type="checkbox"
                          name="consent"
                          checked={form.consent}
                          onChange={handleChange}
                          onBlur={handleBlur}
                          className="mt-1 h-4 w-4 rounded border-light-mist text-seafoam accent-seafoam"
                          aria-invalid={Boolean(errors.consent)}
                          aria-describedby={errors.consent ? 'consent-error' : undefined}
                        />
                        <span className="text-xs leading-relaxed text-storm-grey">
                          Tôi đồng ý để IDI sử dụng thông tin trên nhằm phản hồi yêu cầu và
                          cam kết tuân thủ <Link to="/privacy" className="font-semibold text-seafoam">chính sách bảo mật</Link>.
                        </span>
                      </label>
                      {errors.consent && (
                        <span id="consent-error" className="mt-1.5 block text-xs font-medium text-[#B93B2B]">
                          {errors.consent}
                        </span>
                      )}
                    </div>

                    <div className="mt-8 flex flex-col gap-4 border-t border-light-mist pt-7 sm:flex-row sm:items-center sm:justify-between">
                      <p className="text-xs text-storm-grey">
                        Thông tin của bạn được bảo mật và chỉ dùng để phản hồi liên hệ.
                      </p>
                      <button
                        type="submit"
                        disabled={isSubmitting}
                        className="btn btn-primary min-w-40 disabled:cursor-wait disabled:opacity-65"
                      >
                        {isSubmitting ? 'Đang gửi...' : 'Gửi liên hệ'}
                        {!isSubmitting && <span aria-hidden="true">→</span>}
                      </button>
                    </div>
                  </form>
                </>
              )}
            </section>
          </div>
        </div>
      </main>
    </>
  )
}
