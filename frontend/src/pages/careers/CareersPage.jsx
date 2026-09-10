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
  { number: '01', key: 'health' },
  { number: '02', key: 'reviews' },
  { number: '03', key: 'locations' },
]

const JOBS_COPY = {
  vi: {
    eyebrow: 'Cơ hội tại IDI', title: 'Vị trí đang tuyển dụng',
    role: 'Vị trí tuyển dụng', quantity: 'Số lượng', location: 'Nơi làm việc', apply: 'Ứng tuyển',
    openings: 'vị trí', fullTime: 'Toàn thời gian',
  },
  en: {
    eyebrow: 'Opportunities at IDI', title: 'Current openings',
    role: 'Position', quantity: 'Openings', location: 'Location', apply: 'Apply now',
    openings: 'positions', fullTime: 'Full-time',
  },
  'zh-CN': {
    eyebrow: 'IDI 职业机会', title: '正在招聘的职位',
    role: '招聘职位', quantity: '人数', location: '工作地点', apply: '立即申请',
    openings: '个职位', fullTime: '全职',
  },
}

const MOCK_JOB_OPENINGS = {
  vi: [
    { id: 'mock-sales', code: 'SALES_EXPORT_01', title: 'Chuyên viên kinh doanh xuất khẩu', department: 'Kinh doanh quốc tế', quantity: 2, location: 'Thành phố Hồ Chí Minh' },
    { id: 'mock-qa', code: 'QA_SUPERVISOR_01', title: 'Giám sát đảm bảo chất lượng', department: 'Đảm bảo chất lượng', quantity: 1, location: 'Nhà máy Vàm Cống, Đồng Tháp' },
    { id: 'mock-it', code: 'IT_SYSTEM_01', title: 'Nhân viên hệ thống công nghệ thông tin', department: 'Công nghệ thông tin', quantity: 2, location: 'Lấp Vò, Đồng Tháp' },
    { id: 'mock-hr', code: 'HR_RECRUITMENT_01', title: 'Chuyên viên tuyển dụng và đào tạo', department: 'Nhân sự', quantity: 1, location: 'Lấp Vò, Đồng Tháp' },
  ],
  en: [
    { id: 'mock-sales', code: 'SALES_EXPORT_01', title: 'Export sales executive', department: 'International Sales', quantity: 2, location: 'Ho Chi Minh City' },
    { id: 'mock-qa', code: 'QA_SUPERVISOR_01', title: 'Quality assurance supervisor', department: 'Quality Assurance', quantity: 1, location: 'Vam Cong Factory, Dong Thap' },
    { id: 'mock-it', code: 'IT_SYSTEM_01', title: 'IT systems specialist', department: 'Information Technology', quantity: 2, location: 'Lap Vo, Dong Thap' },
    { id: 'mock-hr', code: 'HR_RECRUITMENT_01', title: 'Recruitment and training specialist', department: 'Human Resources', quantity: 1, location: 'Lap Vo, Dong Thap' },
  ],
  'zh-CN': [
    { id: 'mock-sales', code: 'SALES_EXPORT_01', title: '出口销售专员', department: '国际销售部', quantity: 2, location: '胡志明市' },
    { id: 'mock-qa', code: 'QA_SUPERVISOR_01', title: '质量保证主管', department: '质量保证部', quantity: 1, location: '同塔省 Vam Cong 工厂' },
    { id: 'mock-it', code: 'IT_SYSTEM_01', title: '信息技术系统专员', department: '信息技术部', quantity: 2, location: '同塔省立武县' },
    { id: 'mock-hr', code: 'HR_RECRUITMENT_01', title: '招聘与培训专员', department: '人力资源部', quantity: 1, location: '同塔省立武县' },
  ],
}

function LocationIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" className="h-4 w-4 shrink-0" aria-hidden="true">
      <path d="M15.5 8.2c0 4.1-5.5 8.3-5.5 8.3S4.5 12.3 4.5 8.2a5.5 5.5 0 1 1 11 0Z" stroke="currentColor" strokeWidth="1.4" />
      <circle cx="10" cy="8" r="1.8" stroke="currentColor" strokeWidth="1.4" />
    </svg>
  )
}

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
    if (!value) return 'careers.validation.cvRequired'
    if (!ACCEPTED_FILE_TYPES.includes(value.type)) {
      return 'careers.validation.cvType'
    }
    if (value.size > MAX_FILE_SIZE) return 'careers.validation.cvSize'
    return ''
  }

  const text = value.trim()
  if (!text) return 'careers.validation.required'
  if (name === 'fullName' && text.length < 2) return 'careers.validation.fullName'
  if (name === 'phone' && !/^\+?[\d\s\-().]{7,20}$/.test(text)) {
    return 'careers.validation.phone'
  }
  if (name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text)) {
    return 'careers.validation.email'
  }
  return ''
}

function FormField({ label, name, error, children }) {
  const { t } = useLanguage()

  return (
    <label htmlFor={name} className="block">
      <span className="mb-2 block text-sm font-bold text-ocean-deep">
        {label}
        <span className="ml-1 text-[#C04B38]" aria-hidden="true">*</span>
      </span>
      {children}
      {error && (
        <span id={`${name}-error`} className="mt-1.5 block text-xs font-semibold text-[#B93B2B]">
          {t(error)}
        </span>
      )}
    </label>
  )
}

export default function CareersPage() {
  const { language, t } = useLanguage()
  const jobsCopy = JOBS_COPY[language] ?? JOBS_COPY.vi
  const mockJobs = MOCK_JOB_OPENINGS[language] ?? MOCK_JOB_OPENINGS.vi
  const [form, setForm] = useState(INITIAL_FORM)
  const [openings, setOpenings] = useState([])
  const [pageConfig, setPageConfig] = useState(null)
  const [isLoadingOpenings, setIsLoadingOpenings] = useState(true)
  const [errors, setErrors] = useState({})
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [referenceId, setReferenceId] = useState('')
  const [pendingJobCode, setPendingJobCode] = useState('')
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

  useEffect(() => {
    if (!pendingJobCode) return

    const selectedOpening = openings.find(opening => opening.code === pendingJobCode)
    if (!selectedOpening) return

    setForm(current => ({ ...current, jobPositionId: String(selectedOpening.id) }))
    setErrors(current => ({ ...current, jobPositionId: '' }))
    setPendingJobCode('')
  }, [openings, pendingJobCode])

  const inputClass = (name) => [
    'h-12 w-full rounded-lg border bg-white px-4 text-sm text-ink outline-none transition',
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
      toast.validation(Object.fromEntries(
        Object.entries(nextErrors).map(([field, error]) => [field, t(error)]),
      ))
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

  const selectJobForApplication = (job) => {
    const selectedOpening = openings.find(opening => opening.code === job.code)

    setReferenceId('')
    setPendingJobCode(selectedOpening ? '' : job.code)
    if (selectedOpening) {
      setForm(current => ({ ...current, jobPositionId: String(selectedOpening.id) }))
      setErrors(current => ({ ...current, jobPositionId: '' }))
    }

    document.getElementById('ung-tuyen')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  return (
    <>
      <PageHead
        title={pageConfig?.seoTitle || t('careers.seoTitle')}
        description={pageConfig?.metaDescription || t('careers.seoDescription')}
      />

      <main className="overflow-hidden bg-white">
        <section className="relative flex min-h-[590px] items-end overflow-hidden bg-ocean-deep pt-[72px]">
          <picture>
            {pageConfig?.heroMobile && <source media="(max-width: 639px)" srcSet={pageConfig.heroMobile} />}
            <img
              src={pageConfig?.heroDesktop || DEFAULT_HERO_IMAGE}
              alt={t('careers.hero.imageAlt')}
              className="absolute inset-0 h-full w-full object-cover"
            />
          </picture>
          <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(5,29,54,0.94)_0%,rgba(5,29,54,0.72)_52%,rgba(5,29,54,0.28)_100%)]" />
          <div className="container relative z-10 pb-16 pt-28 sm:pb-20 lg:pb-24">
            <div className="max-w-3xl">
              <p className="mb-5 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.24em] text-coral-light">
                <span className="h-px w-10 bg-coral-light" />
                {pageConfig?.title || t('careers.hero.eyebrow')}
              </p>
              <h1 className="text-balance text-[clamp(2.75rem,6.5vw,5.6rem)] font-extrabold leading-[0.98] tracking-[-0.045em] text-white">
                {t('careers.hero.titleLine1')}
                <span className="block text-seafoam-light">{t('careers.hero.titleLine2')}</span>
              </h1>
              <p className="mt-7 max-w-2xl text-lg leading-8 text-white/78 sm:text-xl">
                {t('careers.hero.description')}
              </p>
              <a href="#ung-tuyen" className="btn btn-gold mt-8">
                {t('actions.submitCv')}
                <span aria-hidden="true">↓</span>
              </a>
            </div>
          </div>
        </section>

        <section className="section-padding">
          <div className="container grid items-center gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:gap-20">
            <div>
              <span className="section-eyebrow">{t('careers.workplace.eyebrow')}</span>
              <h2 className="max-w-2xl text-balance text-ocean-deep">
                {t('careers.workplace.title')}
              </h2>
              <div className="mt-6 h-1 w-20 rounded-full bg-coral-gold" />
              <div className="careers-workplace-copy mt-7 max-w-2xl">
                {pageConfig?.description ? (
                  <div dangerouslySetInnerHTML={{ __html: pageConfig.description }} />
                ) : (
                  <>
                    <p>{t('careers.workplace.paragraph1')}</p>
                    <p>{t('careers.workplace.paragraph2')}</p>
                  </>
                )}
              </div>
            </div>

            <div className="relative">
              <div className="absolute -bottom-5 -right-5 h-full w-full border border-seafoam/35" />
              <img
                src={pageConfig?.gallery?.[0] || DEFAULT_TEAM_IMAGE}
                alt={t('careers.workplace.imageAlt')}
                className="relative aspect-[4/3] w-full object-cover shadow-[0_30px_70px_-35px_rgba(11,37,69,0.55)]"
              />
              <div className="absolute -left-4 -top-5 bg-ocean-deep px-6 py-5 text-white shadow-xl sm:-left-6">
                <span className="block text-xs font-bold uppercase tracking-[0.16em] text-coral-light">
                  {t('careers.workplace.badge')}
                </span>
                <strong className="mt-1 block text-xl">{t('careers.workplace.badgeTitle')}</strong>
              </div>
            </div>
          </div>
        </section>

        <section className="section-padding bg-white">
          <div className="container">
            <div className="mb-12 max-w-2xl">
              <span className="section-eyebrow">{t('careers.benefits.eyebrow')}</span>
              <h2 className="text-balance text-ocean-deep">{t('careers.benefits.title')}</h2>
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
                  <h3 className="mb-4 text-2xl text-ocean-deep">{t(`careers.benefits.items.${benefit.key}.title`)}</h3>
                  <p className="text-sm leading-7 text-storm-grey">{t(`careers.benefits.items.${benefit.key}.description`)}</p>
                </article>
              ))}</div>
            )}

            <div className="mt-8 grid overflow-hidden border border-light-mist bg-white lg:grid-cols-2">
              <div className="border-b border-light-mist p-7 sm:p-8 lg:border-b-0 lg:border-r">
                <span className="text-xs font-extrabold uppercase tracking-[0.16em] text-seafoam">
                  {t('careers.benefits.headOffice')}
                </span>
                <p className="mt-3 font-semibold leading-7 text-ocean-deep">
                  {t('careers.benefits.headOfficeAddress')}
                </p>
              </div>
              <div className="p-7 sm:p-8">
                <span className="text-xs font-extrabold uppercase tracking-[0.16em] text-seafoam">
                  {t('careers.benefits.representativeOffice')}
                </span>
                <p className="mt-3 font-semibold leading-7 text-ocean-deep">
                  {t('careers.benefits.representativeOfficeAddress')}
                </p>
                <a
                  href="tel:+84932824888"
                  className="mt-2 inline-block text-sm font-bold text-seafoam transition hover:text-ocean-deep"
                >
                  {t('careers.benefits.phone')}: +84 932 824 888
                </a>
                <p className="mt-2 text-sm leading-6 text-storm-grey">
                  {t('careers.benefits.officeDescription')}
                </p>
              </div>
            </div>
          </div>
        </section>

        <section className="section-padding bg-arctic-white" aria-labelledby="career-openings-title">
          <div className="container">
            <div className="flex flex-col gap-6 border-b border-mist-mid pb-7 sm:flex-row sm:items-end sm:justify-between">
              <div>
                <span className="section-eyebrow">{jobsCopy.eyebrow}</span>
                <h2 id="career-openings-title" className="max-w-2xl text-balance text-ocean-deep">
                  {jobsCopy.title}
                </h2>
              </div>
              <div className="flex shrink-0 items-baseline gap-2 border-l-2 border-seafoam py-1 pl-4">
                <strong className="text-3xl font-bold leading-none tabular-nums text-ocean-deep">{mockJobs.length}</strong>
                <span className="text-xs font-bold uppercase tracking-[0.13em] text-seafoam">{jobsCopy.openings}</span>
              </div>
            </div>

            <div className="mt-8">
              <div className="hidden grid-cols-[minmax(0,1.45fr)_minmax(7rem,0.35fr)_minmax(14rem,0.65fr)_8.5rem] gap-6 border-b border-mist-mid px-5 pb-4 text-xs font-bold uppercase tracking-[0.1em] text-storm-grey lg:grid">
                <span>{jobsCopy.role}</span>
                <span>{jobsCopy.quantity}</span>
                <span>{jobsCopy.location}</span>
                <span className="sr-only">{jobsCopy.apply}</span>
              </div>

              <div className="grid gap-4 lg:gap-0">
                {mockJobs.map((job, index) => (
                  <article
                    key={job.id}
                    className="group grid gap-6 border border-light-mist bg-white p-5 transition-colors hover:border-seafoam/45 sm:p-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(7rem,0.35fr)_minmax(14rem,0.65fr)_8.5rem] lg:items-center lg:border-x-0 lg:border-t-0 lg:px-5 lg:py-7 lg:hover:bg-seafoam-pale/35"
                  >
                    <div className="min-w-0">
                      <div className="flex items-start gap-4">
                        <span className="mt-1 text-xs font-bold tabular-nums text-seafoam" aria-hidden="true">
                          {String(index + 1).padStart(2, '0')}
                        </span>
                        <div>
                          <h3 className="text-lg font-bold leading-snug text-ocean-deep transition-colors group-hover:text-seafoam">
                            {job.title}
                          </h3>
                          <div className="mt-2 flex flex-wrap items-center gap-2 text-xs">
                            <span className="font-semibold text-storm-grey">{job.department}</span>
                            <span className="h-1 w-1 rounded-full bg-mist-mid" aria-hidden="true" />
                            <span className="text-storm-grey">{jobsCopy.fullTime}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div>
                      <span className="mb-1 block text-xs font-semibold uppercase tracking-wide text-storm-grey lg:hidden">{jobsCopy.quantity}</span>
                      <strong className="text-lg font-bold tabular-nums text-ocean-deep">{job.quantity}</strong>
                    </div>

                    <div>
                      <span className="mb-1 block text-xs font-semibold uppercase tracking-wide text-storm-grey lg:hidden">{jobsCopy.location}</span>
                      <p className="flex items-start gap-2 text-sm leading-6 text-slate"><LocationIcon /> {job.location}</p>
                    </div>

                    <button
                      type="button"
                      onClick={() => selectJobForApplication(job)}
                      className="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 border border-ocean-deep px-4 text-sm font-semibold text-ocean-deep transition-colors hover:bg-ocean-deep hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-seafoam"
                      aria-label={`${jobsCopy.apply}: ${job.title}`}
                    >
                      {jobsCopy.apply} <span aria-hidden="true">→</span>
                    </button>
                  </article>
                ))}
              </div>
            </div>
          </div>
        </section>

        <section id="ung-tuyen" className="scroll-mt-20 bg-[#F2F7F5] section-padding-lg">
          <div className="container">
            <div className="grid overflow-hidden rounded-[1.5rem] border border-[#D8E5E0] bg-white shadow-[0_30px_80px_-52px_rgba(11,37,69,0.55)] lg:grid-cols-[0.72fr_1.28fr]">
              <aside className="relative overflow-hidden bg-ocean-deep p-7 text-white sm:p-10 lg:p-12">
                <div className="relative z-10">
                  <span className="text-xs font-bold uppercase tracking-[0.2em] text-coral-light">
                    {t('careers.application.eyebrow')}
                  </span>
                  <h2 className="mt-4 text-3xl font-extrabold text-white sm:text-4xl">
                    {t('careers.application.title')}
                  </h2>
                  {pageConfig?.contactContent ? (
                    <div className="careers-application-copy mt-5" dangerouslySetInnerHTML={{ __html: pageConfig.contactContent }} />
                  ) : (
                    <p className="mt-5 leading-8 text-white/72">{t('careers.application.description')}</p>
                  )}

                <div className="mt-10 space-y-5 border-t border-white/12 pt-8">
                  {['privacy', 'proactive', 'response'].map(item => (
                    <div key={item} className="flex items-center gap-3 text-sm font-semibold text-white/80">
                      <span className="grid h-6 w-6 place-items-center rounded-full bg-seafoam text-xs text-white">✓</span>
                      {t(`careers.application.${item}`)}
                    </div>
                  ))}
                </div>

                <div className="mt-10 rounded-xl border border-white/12 bg-white/[0.06] p-5">
                  <span className="block text-xs font-bold uppercase tracking-[0.14em] text-seafoam-light">
                    {t('careers.application.support')}
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

              <section className="bg-white p-6 sm:p-10 lg:p-12">
              {referenceId ? (
                <div className="flex min-h-[36rem] flex-col items-center justify-center text-center" role="status">
                  <div className="mb-6 grid h-20 w-20 place-items-center rounded-full bg-seafoam-pale text-3xl text-seafoam">
                    ✓
                  </div>
                  <span className="text-xs font-bold uppercase tracking-[0.16em] text-seafoam">
                    {t('careers.application.successEyebrow')}
                  </span>
                  <h2 className="mt-3 text-3xl font-black text-ocean-deep">{t('careers.application.successTitle')}</h2>
                  <p className="mt-4 max-w-lg text-sm leading-7 text-storm-grey">
                    {t('careers.application.successDescription')}{' '}
                    <strong className="text-ocean-deep">{referenceId}</strong>.
                  </p>
                  <button type="button" onClick={startNewApplication} className="btn btn-secondary mt-8">
                    {t('actions.submitAnotherApplication')}
                  </button>
                </div>
              ) : pageConfig?.applicationEnabled === false ? (
                <div className="flex min-h-[36rem] flex-col items-center justify-center text-center" role="status">
                  <div className="mb-6 grid h-20 w-20 place-items-center rounded-full bg-coral-pale text-3xl text-coral">
                    !
                  </div>
                  <span className="text-xs font-bold uppercase tracking-[0.16em] text-coral">
                    {t('careers.application.closedEyebrow')}
                  </span>
                  <h2 className="mt-3 text-3xl font-black text-ocean-deep">{t('careers.application.closedTitle')}</h2>
                  <p className="mt-4 max-w-lg text-sm leading-7 text-storm-grey">
                    {t('careers.application.closedDescription')}
                  </p>
                </div>
              ) : (
                <>
                  <div className="mb-8">
                    <span className="section-eyebrow">{t('careers.application.formEyebrow')}</span>
                    <h2 className="mt-2 text-2xl font-black text-ocean-deep sm:text-3xl">
                      {t('careers.application.formTitle')}
                    </h2>
                    <p className="mt-3 text-sm text-storm-grey">
                      {t('careers.application.requiredNote')}
                    </p>
                  </div>

                  <form onSubmit={handleSubmit} noValidate>
                    <div className="mb-6">
                      <label htmlFor="jobPositionId" className="block">
                        <span className="mb-2 block text-sm font-bold text-ocean-deep">{t('careers.application.positionLabel')}</span>
                        <select
                          id="jobPositionId"
                          name="jobPositionId"
                          value={form.jobPositionId}
                          onChange={handleChange}
                          className={inputClass('jobPositionId')}
                        >
                          <option value="">{isLoadingOpenings ? t('careers.application.loadingPositions') : t('careers.application.openApplication')}</option>
                          {openings.map(opening => (
                            <option key={opening.id} value={opening.id}>
                              {opening.title}{opening.location ? ` — ${opening.location}` : ''}
                            </option>
                          ))}
                        </select>
                      </label>
                    </div>
                    <div className="grid gap-6 sm:grid-cols-2">
                      <FormField label={t('careers.application.fullName')} name="fullName" error={errors.fullName}>
                        <input
                          id="fullName"
                          name="fullName"
                          type="text"
                          autoComplete="name"
                          value={form.fullName}
                          onChange={handleChange}
                          onBlur={handleBlur}
                          placeholder={t('careers.application.fullNamePlaceholder')}
                          className={inputClass('fullName')}
                          aria-invalid={Boolean(errors.fullName)}
                          aria-describedby={errors.fullName ? 'fullName-error' : undefined}
                        />
                      </FormField>

                      <FormField label={t('careers.application.phone')} name="phone" error={errors.phone}>
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

                      <FormField label={t('careers.application.email')} name="email" error={errors.email}>
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

                      <FormField label={t('careers.application.address')} name="address" error={errors.address}>
                        <input
                          id="address"
                          name="address"
                          type="text"
                          autoComplete="street-address"
                          value={form.address}
                          onChange={handleChange}
                          onBlur={handleBlur}
                          placeholder={t('careers.application.addressPlaceholder')}
                          className={inputClass('address')}
                          aria-invalid={Boolean(errors.address)}
                          aria-describedby={errors.address ? 'address-error' : undefined}
                        />
                      </FormField>
                    </div>

                    <div className="mt-6">
                      <FormField label={t('careers.application.cv')} name="cv" error={errors.cv}>
                        <div
                          className={[
                            'rounded-lg border border-dashed bg-arctic-white p-5 transition',
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
                            {t('careers.application.fileNote')}
                          </span>
                        </div>
                      </FormField>
                    </div>

                    <div className="mt-8 flex flex-col gap-4 border-t border-light-mist pt-7 sm:flex-row sm:items-center sm:justify-between">
                      <p className="text-xs leading-6 text-storm-grey">
                        {t('careers.application.consent')}
                      </p>
                      <button
                        type="submit"
                        disabled={isSubmitting}
                        className="btn btn-primary min-w-44 disabled:cursor-wait disabled:opacity-65"
                      >
                        {isSubmitting ? t('actions.sending') : t('actions.submitApplication')}
                        {!isSubmitting && <span aria-hidden="true">→</span>}
                      </button>
                    </div>
                  </form>
                </>
              )}
              </section>
            </div>
          </div>
        </section>

        <section className="bg-ocean-deep py-14">
          <div className="container flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
            <div>
              <span className="text-xs font-bold uppercase tracking-[0.18em] text-coral-light">
                {t('careers.bottom.eyebrow')}
              </span>
              <h2 className="mt-2 text-2xl text-white sm:text-3xl">{t('careers.bottom.title')}</h2>
            </div>
            <Link to="/about" className="btn btn-ghost">
              {t('actions.exploreIdi')}
              <span aria-hidden="true">→</span>
            </Link>
          </div>
        </section>
      </main>
    </>
  )
}
