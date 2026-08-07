import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router'
import PageHead from '@components/common/PageHead'
import { careersService } from '@services/careers.service'
import toast from '@/utils/toast'
import { useLanguage } from '@hooks/useLanguage'

const DEFAULT_HERO_IMAGE =
  'https://www.idiseafood.com/vnt_upload/recruitment/gt2.jpg'
const DEFAULT_TEAM_IMAGE =
  'https://www.idiseafood.com/vnt_upload/recruitment/gt3.jpg'

const BENEFITS = [
  {
    number: '01',
    title: 'Bảo hiểm y tế',
    description:
      'Sức khỏe của bạn luôn được quan tâm. Bạn sẽ nhận được bảo hiểm y tế đáp ứng hầu hết các nhu cầu thiết yếu.',
  },
  {
    number: '02',
    title: 'Đánh giá thường xuyên',
    description:
      'Đánh giá hiệu suất hai chiều giúp tạo sự cân bằng, ghi nhận đóng góp và đo lường sự phát triển cùng nhau.',
  },
  {
    number: '03',
    title: 'Vị trí làm việc thuận tiện',
    description:
      'Làm việc tại trụ sở Đồng Tháp hoặc văn phòng đại diện ngay trung tâm Quận 5, Thành phố Hồ Chí Minh.',
  },
]

const INITIAL_FORM = {
  jobPositionId: '',
  fullName: '',
  phone: '',
  email: '',
  address: '',
  cv: null,
}

const ACCEPTED_FILE_TYPES = [
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
]
const MAX_FILE_SIZE = 10 * 1024 * 1024

function validateField(name, value) {
  if (name === 'jobPositionId') return ''
  if (name === 'cv') {
    if (!value) return 'Vui lòng chọn CV của bạn.'
    if (!ACCEPTED_FILE_TYPES.includes(value.type)) {
      return 'CV chỉ chấp nhận định dạng PDF, DOC hoặc DOCX.'
    }
    if (value.size > MAX_FILE_SIZE) return 'Dung lượng CV không được vượt quá 10MB.'
    return ''
  }

  const text = value.trim()
  if (!text) return 'Thông tin này là bắt buộc.'
  if (name === 'fullName' && text.length < 2) return 'Họ tên cần có ít nhất 2 ký tự.'
  if (name === 'phone' && !/^\+?[\d\s\-().]{7,20}$/.test(text)) {
    return 'Vui lòng nhập số điện thoại hợp lệ.'
  }
  if (name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text)) {
    return 'Vui lòng nhập địa chỉ email hợp lệ.'
  }
  return ''
}

function FormField({ label, name, error, children }) {
  return (
    <label htmlFor={name} className="block">
      <span className="mb-2 block text-sm font-bold text-ocean-deep">
        {label}
        <span className="ml-1 text-[#C04B38]" aria-hidden="true">*</span>
      </span>
      {children}
      {error && (
        <span id={`${name}-error`} className="mt-1.5 block text-xs font-semibold text-[#B93B2B]">
          {error}
        </span>
      )}
    </label>
  )
}

export default function CareersPage() {
  const { language } = useLanguage()
  const [form, setForm] = useState(INITIAL_FORM)
  const [openings, setOpenings] = useState([])
  const [pageConfig, setPageConfig] = useState(null)
  const [isLoadingOpenings, setIsLoadingOpenings] = useState(true)
  const [errors, setErrors] = useState({})
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [referenceId, setReferenceId] = useState('')
  const fileInputRef = useRef(null)

  useEffect(() => {
    let active = true
    setIsLoadingOpenings(true)
    careersService.getOpenings({ locale: language })
      .then(result => {
        if (active) {
          setOpenings(result.items ?? [])
          setPageConfig(result.pageConfig ?? null)
        }
      })
      .catch(() => {
        if (active) setOpenings([])
      })
      .finally(() => {
        if (active) setIsLoadingOpenings(false)
      })
    return () => { active = false }
  }, [language])

  const inputClass = (name) => [
    'h-12 w-full rounded-xl border bg-white px-4 text-sm text-ink outline-none transition',
    'placeholder:text-storm-grey/55 focus:ring-2',
    errors[name]
      ? 'border-[#D46A5A] focus:border-[#D46A5A] focus:ring-[#D46A5A]/15'
      : 'border-light-mist focus:border-seafoam focus:ring-seafoam/15',
  ].join(' ')

  const handleChange = (event) => {
    const { name, value, files } = event.target
    const nextValue = name === 'cv' ? files?.[0] ?? null : value
    setForm(current => ({ ...current, [name]: nextValue }))
    if (errors[name]) {
      setErrors(current => ({ ...current, [name]: validateField(name, nextValue) }))
    }
  }

  const handleBlur = (event) => {
    const { name, value } = event.target
    if (name !== 'cv') {
      setErrors(current => ({ ...current, [name]: validateField(name, value) }))
    }
  }

  const handleSubmit = async (event) => {
    event.preventDefault()

    const nextErrors = Object.keys(INITIAL_FORM).reduce((result, field) => {
      const error = validateField(field, form[field])
      if (error) result[field] = error
      return result
    }, {})

    if (Object.keys(nextErrors).length) {
      setErrors(nextErrors)
      toast.validation(nextErrors)
      document.querySelector(`[name="${Object.keys(nextErrors)[0]}"]`)?.focus()
      return
    }

    setIsSubmitting(true)
    try {
      const result = await careersService.submitApplication(form)
      setReferenceId(result.referenceId)
      setForm(INITIAL_FORM)
      setErrors({})
      if (fileInputRef.current) fileInputRef.current.value = ''
    } catch {
      // The shared Axios interceptor displays upload, validation, and server errors.
    } finally {
      setIsSubmitting(false)
    }
  }

  const startNewApplication = () => {
    setReferenceId('')
  }

  return (
    <>
      <PageHead
        title={pageConfig?.seoTitle || 'Tuyển dụng | IDI Seafood'}
        description={pageConfig?.metaDescription || 'Khám phá môi trường làm việc, phúc lợi và gửi CV ứng tuyển để đồng hành cùng IDI Seafood.'}
      />

      <main className="overflow-hidden bg-white">
        <section className="relative flex min-h-[590px] items-end overflow-hidden bg-ocean-deep pt-[72px]">
          <picture>
            {pageConfig?.heroMobile && <source media="(max-width: 639px)" srcSet={pageConfig.heroMobile} />}
            <img
              src={pageConfig?.heroDesktop || DEFAULT_HERO_IMAGE}
              alt="Đội ngũ IDI tại nhà máy chế biến cá tra"
              className="absolute inset-0 h-full w-full object-cover"
            />
          </picture>
          <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(5,29,54,0.94)_0%,rgba(5,29,54,0.72)_52%,rgba(5,29,54,0.28)_100%)]" />
          <div className="container relative z-10 pb-16 pt-28 sm:pb-20 lg:pb-24">
            <div className="max-w-3xl">
              <p className="mb-5 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.24em] text-coral-light">
                <span className="h-px w-10 bg-coral-light" />
                {pageConfig?.title || 'Cơ hội nghề nghiệp tại IDI'}
              </p>
              <h1 className="text-balance text-[clamp(2.75rem,6.5vw,5.6rem)] font-extrabold leading-[0.98] tracking-[-0.045em] text-white">
                Cùng chúng tôi
                <span className="block text-seafoam-light">tạo nên giá trị</span>
              </h1>
              <p className="mt-7 max-w-2xl text-lg leading-8 text-white/78 sm:text-xl">
                Gia nhập một đội ngũ cùng chung khát vọng phát triển ngành thủy sản Việt Nam bền vững và vươn xa trên thị trường quốc tế.
              </p>
              <a href="#ung-tuyen" className="btn btn-gold mt-8">
                Gửi CV ứng tuyển
                <span aria-hidden="true">↓</span>
              </a>
            </div>
          </div>
        </section>

        <section className="section-padding">
          <div className="container grid items-center gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:gap-20">
            <div>
              <span className="section-eyebrow">Làm việc có ý nghĩa</span>
              <h2 className="max-w-2xl text-balance text-ocean-deep">
                Phát triển sự nghiệp trong một hệ sinh thái bền vững
              </h2>
              <div className="mt-6 h-1 w-20 rounded-full bg-coral-gold" />
              {pageConfig?.description ? (
                <div className="mt-7 max-w-2xl space-y-5 text-lg leading-9 text-slate" dangerouslySetInnerHTML={{ __html: pageConfig.description }} />
              ) : (
                <>
                  <p className="mt-7 max-w-2xl text-lg leading-9 text-slate">Tại IDI, bảo vệ môi trường là một trong những mối quan tâm hàng đầu của chúng tôi vì chúng tôi hiểu rằng việc cải thiện chất lượng môi trường là điều thiết yếu để đạt được mức sống cao hơn cho cộng đồng thân yêu của chúng tôi.</p>
                  <p className="mt-5 max-w-2xl leading-8 text-storm-grey">Mỗi thành viên đều có cơ hội học hỏi, phát huy năng lực và cùng xây dựng chuỗi giá trị thủy sản có trách nhiệm từ vùng nuôi đến thị trường toàn cầu.</p>
                </>
              )}
            </div>

            <div className="relative">
              <div className="absolute -bottom-5 -right-5 h-full w-full border border-seafoam/35" />
              <img
                src={pageConfig?.gallery?.[0] || DEFAULT_TEAM_IMAGE}
                alt="Nhân viên IDI trong dây chuyền sản xuất"
                className="relative aspect-[4/3] w-full object-cover shadow-[0_30px_70px_-35px_rgba(11,37,69,0.55)]"
              />
              <div className="absolute -left-4 -top-5 bg-ocean-deep px-6 py-5 text-white shadow-xl sm:-left-6">
                <span className="block text-xs font-bold uppercase tracking-[0.16em] text-coral-light">
                  Đồng hành
                </span>
                <strong className="mt-1 block text-xl">Cùng phát triển</strong>
              </div>
            </div>
          </div>
        </section>

        <section className="section-padding bg-arctic-white">
          <div className="container">
            <div className="mb-12 max-w-2xl">
              <span className="section-eyebrow">Lợi ích</span>
              <h2 className="text-balance text-ocean-deep">Tại sao bạn nên gia nhập cùng chúng tôi?</h2>
            </div>

            {pageConfig?.benefitsContent ? (
              <div className="border border-light-mist bg-white p-7 leading-8 text-storm-grey shadow-[0_20px_60px_-45px_rgba(11,37,69,0.55)] sm:p-9" dangerouslySetInnerHTML={{ __html: pageConfig.benefitsContent }} />
            ) : (
              <div className="grid gap-5 lg:grid-cols-3">{BENEFITS.map(benefit => (
                <article
                  key={benefit.number}
                  className="group border border-light-mist bg-white p-7 shadow-[0_20px_60px_-45px_rgba(11,37,69,0.55)] transition duration-300 hover:-translate-y-1 hover:border-seafoam/45 sm:p-8"
                >
                  <div className="mb-10 flex items-center justify-between">
                    <span className="grid h-11 w-11 place-items-center rounded-full bg-seafoam-pale text-sm font-black text-seafoam">
                      {benefit.number}
                    </span>
                    <span className="h-px w-16 bg-light-mist transition-colors group-hover:bg-coral-gold" />
                  </div>
                  <h3 className="mb-4 text-2xl text-ocean-deep">{benefit.title}</h3>
                  <p className="text-sm leading-7 text-storm-grey">{benefit.description}</p>
                </article>
              ))}</div>
            )}

            <div className="mt-8 grid overflow-hidden border border-light-mist bg-white lg:grid-cols-2">
              <div className="border-b border-light-mist p-7 sm:p-8 lg:border-b-0 lg:border-r">
                <span className="text-xs font-extrabold uppercase tracking-[0.16em] text-seafoam">
                  Trụ sở chính
                </span>
                <p className="mt-3 font-semibold leading-7 text-ocean-deep">
                  Quốc lộ 80, Cụm công nghiệp Vàm Cống, ấp An Thạnh, xã Lấp Vò, Tỉnh Đồng Tháp, Việt Nam
                </p>
              </div>
              <div className="p-7 sm:p-8">
                <span className="text-xs font-extrabold uppercase tracking-[0.16em] text-seafoam">
                  Văn phòng đại diện
                </span>
                <p className="mt-3 font-semibold leading-7 text-ocean-deep">
                  9 Nguyễn Kim, phường 12, quận 5, Thành phố Hồ Chí Minh
                </p>
                <a
                  href="tel:+84932824888"
                  className="mt-2 inline-block text-sm font-bold text-seafoam transition hover:text-ocean-deep"
                >
                  Điện thoại: +84 932 824 888
                </a>
                <p className="mt-2 text-sm leading-6 text-storm-grey">
                  Ngay trung tâm Quận 5, thuận tiện tiếp cận nhà hàng, trung tâm chăm sóc sức khỏe, trường học và khu mua sắm.
                </p>
              </div>
            </div>
          </div>
        </section>

        <section id="ung-tuyen" className="scroll-mt-20 bg-seafoam-pale/45 section-padding-lg">
          <div className="container grid overflow-hidden rounded-3xl border border-seafoam/15 bg-white shadow-[0_35px_90px_-50px_rgba(11,37,69,0.6)] lg:grid-cols-[0.72fr_1.28fr]">
            <aside className="relative overflow-hidden bg-ocean-deep p-7 text-white sm:p-10 lg:p-12">
              <div
                className="absolute inset-0 opacity-30"
                style={{ background: 'radial-gradient(circle at 0% 100%, #1A936F, transparent 46%)' }}
              />
              <div className="relative z-10">
                <span className="text-xs font-bold uppercase tracking-[0.2em] text-coral-light">
                  Cơ hội nghề nghiệp
                </span>
                <h2 className="mt-4 text-3xl font-extrabold text-white sm:text-4xl">
                  Hãy để chúng tôi biết thêm về bạn
                </h2>
                {pageConfig?.contactContent ? (
                  <div className="mt-5 leading-8 text-white/70" dangerouslySetInnerHTML={{ __html: pageConfig.contactContent }} />
                ) : (
                  <p className="mt-5 leading-8 text-white/65">Đăng CV tại đây để bộ phận tuyển dụng IDI có thể liên hệ khi có vị trí phù hợp với kinh nghiệm và định hướng của bạn.</p>
                )}

                <div className="mt-10 space-y-5 border-t border-white/12 pt-8">
                  {[
                    'Hồ sơ được bảo mật',
                    'Tiếp nhận ứng tuyển chủ động',
                    'Phản hồi khi có vị trí phù hợp',
                  ].map(item => (
                    <div key={item} className="flex items-center gap-3 text-sm font-semibold text-white/80">
                      <span className="grid h-6 w-6 place-items-center rounded-full bg-seafoam text-xs text-white">✓</span>
                      {item}
                    </div>
                  ))}
                </div>

                <div className="mt-10 rounded-2xl border border-white/10 bg-white/5 p-5">
                  <span className="block text-xs font-bold uppercase tracking-[0.14em] text-seafoam-light">
                    Hỗ trợ ứng tuyển
                  </span>
                  <a
                    href="mailto:info@idiseafood.com"
                    className="mt-2 block font-bold text-white transition hover:text-coral-light"
                  >
                    info@idiseafood.com
                  </a>
                </div>
              </div>
            </aside>

            <section className="p-6 sm:p-10 lg:p-12">
              {referenceId ? (
                <div className="flex min-h-[36rem] flex-col items-center justify-center text-center" role="status">
                  <div className="mb-6 grid h-20 w-20 place-items-center rounded-full bg-seafoam-pale text-3xl text-seafoam">
                    ✓
                  </div>
                  <span className="text-xs font-bold uppercase tracking-[0.16em] text-seafoam">
                    Gửi hồ sơ thành công
                  </span>
                  <h2 className="mt-3 text-3xl font-black text-ocean-deep">Cảm ơn bạn đã chọn IDI</h2>
                  <p className="mt-4 max-w-lg text-sm leading-7 text-storm-grey">
                    Hồ sơ của bạn đã được tiếp nhận. Mã tham chiếu là{' '}
                    <strong className="text-ocean-deep">{referenceId}</strong>.
                  </p>
                  <button type="button" onClick={startNewApplication} className="btn btn-secondary mt-8">
                    Gửi hồ sơ khác
                  </button>
                </div>
              ) : pageConfig?.applicationEnabled === false ? (
                <div className="flex min-h-[36rem] flex-col items-center justify-center text-center" role="status">
                  <div className="mb-6 grid h-20 w-20 place-items-center rounded-full bg-coral-pale text-3xl text-coral">
                    !
                  </div>
                  <span className="text-xs font-bold uppercase tracking-[0.16em] text-coral">
                    Tạm ngưng nhận hồ sơ
                  </span>
                  <h2 className="mt-3 text-3xl font-black text-ocean-deep">Cổng ứng tuyển đang tạm đóng</h2>
                  <p className="mt-4 max-w-lg text-sm leading-7 text-storm-grey">
                    Hiện tại IDI chưa tiếp nhận hồ sơ trực tuyến. Bạn vẫn có thể theo dõi các vị trí đang tuyển và quay lại sau.
                  </p>
                </div>
              ) : (
                <>
                  <div className="mb-8">
                    <span className="section-eyebrow">Gửi ứng tuyển</span>
                    <h2 className="mt-2 text-2xl font-black text-ocean-deep sm:text-3xl">
                      Đăng CV của bạn tại đây
                    </h2>
                    <p className="mt-3 text-sm text-storm-grey">
                      Tất cả các trường bên dưới đều là bắt buộc.
                    </p>
                  </div>

                  <form onSubmit={handleSubmit} noValidate>
                    <div className="mb-6">
                      <label htmlFor="jobPositionId" className="block">
                        <span className="mb-2 block text-sm font-bold text-ocean-deep">Vị trí quan tâm</span>
                        <select
                          id="jobPositionId"
                          name="jobPositionId"
                          value={form.jobPositionId}
                          onChange={handleChange}
                          className={inputClass('jobPositionId')}
                        >
                          <option value="">{isLoadingOpenings ? 'Đang tải vị trí...' : 'Ứng tuyển tự do'}</option>
                          {openings.map(opening => (
                            <option key={opening.id} value={opening.id}>
                              {opening.title}{opening.location ? ` — ${opening.location}` : ''}
                            </option>
                          ))}
                        </select>
                      </label>
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
                          className={inputClass('fullName')}
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
                          className={inputClass('phone')}
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
                          placeholder="email@example.com"
                          className={inputClass('email')}
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
                          placeholder="Tỉnh/Thành phố"
                          className={inputClass('address')}
                          aria-invalid={Boolean(errors.address)}
                          aria-describedby={errors.address ? 'address-error' : undefined}
                        />
                      </FormField>
                    </div>

                    <div className="mt-6">
                      <FormField label="CV của bạn" name="cv" error={errors.cv}>
                        <div
                          className={[
                            'rounded-xl border border-dashed bg-arctic-white p-5 transition',
                            errors.cv ? 'border-[#D46A5A]' : 'border-mist-mid focus-within:border-seafoam',
                          ].join(' ')}
                        >
                          <input
                            ref={fileInputRef}
                            id="cv"
                            name="cv"
                            type="file"
                            accept=".pdf,.doc,.docx"
                            onChange={handleChange}
                            className="block w-full text-sm text-storm-grey file:mr-4 file:rounded-lg file:border-0 file:bg-ocean-deep file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-white hover:file:bg-ocean-mid"
                            aria-invalid={Boolean(errors.cv)}
                            aria-describedby={errors.cv ? 'cv-error cv-note' : 'cv-note'}
                          />
                          <span id="cv-note" className="mt-3 block text-xs text-storm-grey">
                            Định dạng DOC, DOCX hoặc PDF; dung lượng nhỏ hơn 10MB.
                          </span>
                        </div>
                      </FormField>
                    </div>

                    <div className="mt-8 flex flex-col gap-4 border-t border-light-mist pt-7 sm:flex-row sm:items-center sm:justify-between">
                      <p className="text-xs leading-6 text-storm-grey">
                        Bằng việc gửi hồ sơ, bạn đồng ý để IDI sử dụng thông tin cho mục đích tuyển dụng.
                      </p>
                      <button
                        type="submit"
                        disabled={isSubmitting}
                        className="btn btn-primary min-w-44 disabled:cursor-wait disabled:opacity-65"
                      >
                        {isSubmitting ? 'Đang gửi...' : 'Gửi ứng tuyển'}
                        {!isSubmitting && <span aria-hidden="true">→</span>}
                      </button>
                    </div>
                  </form>
                </>
              )}
            </section>
          </div>
        </section>

        <section className="bg-ocean-deep py-14">
          <div className="container flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
            <div>
              <span className="text-xs font-bold uppercase tracking-[0.18em] text-coral-light">
                Muốn biết thêm về đội ngũ?
              </span>
              <h2 className="mt-2 text-2xl text-white sm:text-3xl">Tìm hiểu thêm về I.D.I</h2>
            </div>
            <Link to="/about" className="btn btn-ghost">
              Khám phá IDI
              <span aria-hidden="true">→</span>
            </Link>
          </div>
        </section>
      </main>
    </>
  )
}
