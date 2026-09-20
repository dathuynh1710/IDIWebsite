import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import PageHead from '@components/common/PageHead'
import { inquiryService } from '@services/inquiry.service'
import { useLanguage } from '@hooks/useLanguage'
import toast from '@/utils/toast'

const INITIAL_FORM = {
  inquiryType: 'export_quote',
  fullName: '',
  phone: '',
  email: '',
  address: '',
  subject: '',
  message: '',
  consent: false,
  companyWebsite: '',
}

const INQUIRY_OPTIONS = ['exportQuote', 'productAdvice', 'businessCooperation', 'investorRelations', 'careers', 'other']
const INQUIRY_VALUES = {
  exportQuote: 'export_quote',
  productAdvice: 'product_advice',
  businessCooperation: 'business_cooperation',
  investorRelations: 'investor_relations',
  careers: 'careers',
  other: 'other',
}

const FALLBACK_OFFICES = [
  {
    id: 'head-office', code: 'HEAD_OFFICE', nameKey: 'headOffice',
    phone: '+84 2773 680 383 · +84 2777 300 468', fax: '+84 2773 680 382', email: 'info@idiseafood.com',
    map: { type: 'embed', embedUrl: 'https://www.google.com/maps?q=IDI+Seafood+Vam+Cong+Dong+Thap&output=embed' },
  },
  {
    id: 'hcm-office', code: 'HCMC_OFFICE', nameKey: 'hcmOffice',
    phone: '+84 932 824 888',
    map: { type: 'embed', embedUrl: 'https://www.google.com/maps?q=9+Nguyen+Kim+Ward+12+District+5+Ho+Chi+Minh+City&output=embed' },
  },
]

const REQUIRED_FIELDS = ['fullName', 'phone', 'email', 'address', 'subject', 'message', 'consent']

function validateField(name, value) {
  const text = typeof value === 'string' ? value.trim() : value
  if (name === 'consent') return value ? '' : 'contact.validation.consent'
  if (!text) return 'contact.validation.required'
  if (name === 'fullName' && text.length < 2) return 'contact.validation.fullName'
  if (name === 'phone' && !/^\+?[\d\s\-().]{7,20}$/.test(text)) return 'contact.validation.phone'
  if (name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text)) return 'contact.validation.email'
  if (name === 'subject' && text.length < 3) return 'contact.validation.subject'
  if (name === 'message' && text.length < 10) return 'contact.validation.messageMin'
  if (name === 'message' && text.length > 1000) return 'contact.validation.messageMax'
  return ''
}

function FormField({ label, name, error, children }) {
  return (
    <label className="block" htmlFor={name}>
      <span className="mb-2 block text-sm font-bold text-ocean-deep">
        {label}<span className="ml-1 text-[#B54735]" aria-hidden="true">*</span>
      </span>
      {children}
      <span id={`${name}-error`} className="mt-1.5 block min-h-4 text-xs font-medium text-[#B23A2B]">
        {error || ''}
      </span>
    </label>
  )
}

function phoneHref(phone) {
  return `tel:${phone.replace(/[^+\d]/g, '')}`
}

function OfficeSection({ number, office, title, address, labels, accent = false }) {
  const phones = office.phone?.split(/\s*[·;|]\s*/).filter(Boolean) ?? []

  return (
    <article className="h-full rounded-xl bg-[#F4F7F7] p-6 sm:p-8">
      <div className="flex h-full items-start gap-4 sm:gap-5">
        <span className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-xs font-black text-white ${accent ? 'bg-seafoam' : 'bg-ocean-deep'}`} aria-hidden="true">
          {number}
        </span>
        <div className="min-w-0 flex-1">
          <h3 className="text-xl font-black leading-tight text-ocean-deep sm:text-[1.4rem]">{title}</h3>
          <p className="mt-2.5 max-w-2xl text-sm leading-6 text-storm-grey">{address}</p>
          <dl className="mt-5 space-y-2 text-sm leading-6">
            {phones.length > 0 && (
              <div className="flex flex-wrap gap-x-2">
                <dt className="font-bold text-ocean-deep">{labels.phone}:</dt>
                <dd>{phones.map((phone, index) => <span key={phone}><a className="text-seafoam hover:underline" href={phoneHref(phone)}>{phone}</a>{index < phones.length - 1 && <span className="mx-2 text-storm-grey/40">·</span>}</span>)}</dd>
              </div>
            )}
            {office.fax && <div className="flex gap-2"><dt className="font-bold text-ocean-deep">{labels.fax}:</dt><dd className="text-storm-grey">{office.fax}</dd></div>}
            {office.email && <div className="flex gap-2"><dt className="font-bold text-ocean-deep">{labels.email}:</dt><dd><a className="break-all text-seafoam hover:underline" href={`mailto:${office.email}`}>{office.email}</a></dd></div>}
          </dl>
        </div>
      </div>
    </article>
  )
}

export default function ContactPage() {
  const { t, language } = useLanguage()
  const [form, setForm] = useState(INITIAL_FORM)
  const [errors, setErrors] = useState({})
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [referenceId, setReferenceId] = useState('')
  const [submitError, setSubmitError] = useState(false)
  const [pageData, setPageData] = useState(null)
  const [activeMap, setActiveMap] = useState('HEAD_OFFICE')

  useEffect(() => {
    let active = true
    setPageData(null)
    inquiryService.getPage(language)
      .then(result => { if (active) setPageData(result) })
      .catch(() => { if (active) setPageData(false) })

    return () => { active = false }
  }, [language])

  const pageConfig = pageData?.pageConfig ?? null
  const offices = pageData && pageData !== false ? (pageData.locations ?? []) : FALLBACK_OFFICES
  const mapOffices = offices.filter(office => office.map?.type !== 'none' && (office.map?.embedUrl || office.map?.imageUrl || office.map?.url))
  const activeOffice = mapOffices.find(office => (office.code || String(office.id)) === activeMap) ?? mapOffices[0]
  const officeTitle = office => office.name ?? t(`contact.details.${office.nameKey}`)
  const officeAddress = office => office.address ?? t(`contact.details.${office.nameKey}Address`)

  const fieldClass = (name) => [
    'h-12 w-full rounded-lg border bg-white px-4 text-sm text-ink outline-none transition',
    'placeholder:text-storm-grey/55 focus:ring-2',
    errors[name]
      ? 'border-[#D46A5A] focus:border-[#D46A5A] focus:ring-[#D46A5A]/15'
      : 'border-light-mist focus:border-seafoam focus:ring-seafoam/15',
  ].join(' ')

  const handleChange = (event) => {
    const { name, value, type, checked } = event.target
    const nextValue = type === 'checkbox' ? checked : value
    setForm(current => ({ ...current, [name]: nextValue }))
    setSubmitError(false)
    if (errors[name]) setErrors(current => ({ ...current, [name]: validateField(name, nextValue) }))
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
      toast.validation(Object.fromEntries(Object.entries(nextErrors).map(([key, value]) => [key, t(value)])))
      document.querySelector(`[name="${Object.keys(nextErrors)[0]}"]`)?.focus()
      return
    }

    setIsSubmitting(true)
    setSubmitError(false)
    try {
      const result = await inquiryService.submitTrade(form, language)
      setReferenceId(result.referenceId)
      setForm(INITIAL_FORM)
      setErrors({})
    } catch {
      setSubmitError(true)
    } finally {
      setIsSubmitting(false)
    }
  }

  const startNewInquiry = () => {
    setReferenceId('')
    setSubmitError(false)
    setForm(INITIAL_FORM)
  }

  return (
    <>
      <PageHead title={pageConfig?.seo?.title || t('contact.seoTitle')} description={pageConfig?.seo?.description || t('contact.seoDescription')} />

      <main className="bg-white pt-20 sm:pt-24">
        <section className="border-b border-light-mist py-12 text-center sm:py-16 lg:py-20">
          <div className="container">
            <span className="section-eyebrow">{t('contact.hero.eyebrow')}</span>
            <h1 className="mx-auto mt-3 max-w-4xl text-3xl font-black tracking-tight text-ocean-deep sm:text-4xl lg:text-5xl">
              {pageConfig?.title || t('contact.hero.title')}
            </h1>
            {pageConfig?.description ? (
              <div className="mx-auto mt-5 max-w-2xl text-sm leading-7 text-storm-grey sm:text-base" dangerouslySetInnerHTML={{ __html: pageConfig.description }} />
            ) : (
              <p className="mx-auto mt-5 max-w-2xl text-sm leading-7 text-storm-grey sm:text-base">{t('contact.hero.description')}</p>
            )}
          </div>
        </section>

        <section className="bg-[#F3F6F6] py-12 sm:py-16 lg:py-20" aria-labelledby="contact-form-title">
          <div className="container">
            <div className="mx-auto max-w-5xl">
              {referenceId ? (
                <div className="flex min-h-[28rem] flex-col items-center justify-center text-center" role="status">
                  <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-seafoam-pale text-2xl font-black text-seafoam" aria-hidden="true">✓</div>
                  <span className="section-eyebrow">{t('contact.success.eyebrow')}</span>
                  <h2 id="contact-form-title" className="mt-2 text-3xl font-black text-ocean-deep">{t('contact.success.title')}</h2>
                  <p className="mt-4 max-w-lg text-sm leading-7 text-storm-grey">{pageConfig?.successMessage || t('contact.success.description')}</p>
                  <p className="mt-4 rounded-lg bg-white px-4 py-3 text-sm text-storm-grey">
                    {t('contact.success.referenceLabel')}: <strong className="text-ocean-deep">{referenceId}</strong>
                  </p>
                  <button type="button" onClick={startNewInquiry} className="btn btn-secondary mt-8">
                    {t('actions.submitAnotherInquiry')} <span aria-hidden="true">→</span>
                  </button>
                </div>
              ) : (
                <>
                  <header className="mb-9 text-center">
                    <span className="section-eyebrow">{t('contact.form.eyebrow')}</span>
                    <h2 id="contact-form-title" className="mt-2 text-2xl font-black text-ocean-deep sm:text-3xl">{t('contact.form.title')}</h2>
                    <p className="mt-3 text-sm text-storm-grey">{t('contact.form.requiredNote')}</p>
                  </header>

                  {submitError && (
                    <div className="mb-6 rounded-lg border border-[#D46A5A]/35 bg-[#FFF5F2] px-4 py-3 text-sm text-[#9F3427]" role="alert">
                      {t('contact.form.submitError')}
                    </div>
                  )}

                  {pageConfig?.formEnabled === false ? (
                    <div className="rounded-lg border border-light-mist bg-white px-5 py-8 text-center text-sm text-storm-grey" role="status">
                      {t('contact.form.unavailable')}
                    </div>
                  ) : <form onSubmit={handleSubmit} noValidate>
                    <div className="mb-2">
                      <label htmlFor="inquiryType" className="mb-2 block text-sm font-bold text-ocean-deep">
                        {t('contact.form.inquiryType')}<span className="ml-1 text-[#B54735]" aria-hidden="true">*</span>
                      </label>
                      <select id="inquiryType" name="inquiryType" required value={form.inquiryType} onChange={handleChange} className={fieldClass('inquiryType')}>
                        {INQUIRY_OPTIONS.map(option => (
                          <option key={option} value={INQUIRY_VALUES[option]}>{t(`contact.options.${option}`)}</option>
                        ))}
                      </select>
                    </div>

                    <div className="grid gap-x-6 sm:grid-cols-2">
                      <FormField label={t('contact.form.fullName')} name="fullName" error={errors.fullName ? t(errors.fullName) : ''}>
                        <input id="fullName" name="fullName" type="text" autoComplete="name" value={form.fullName} onChange={handleChange} onBlur={handleBlur} placeholder={t('contact.form.fullNamePlaceholder')} className={fieldClass('fullName')} aria-invalid={Boolean(errors.fullName)} aria-describedby={errors.fullName ? 'fullName-error' : undefined} />
                      </FormField>
                      <FormField label={t('contact.form.phone')} name="phone" error={errors.phone ? t(errors.phone) : ''}>
                        <input id="phone" name="phone" type="tel" autoComplete="tel" value={form.phone} onChange={handleChange} onBlur={handleBlur} placeholder={t('contact.form.phonePlaceholder')} className={fieldClass('phone')} aria-invalid={Boolean(errors.phone)} aria-describedby={errors.phone ? 'phone-error' : undefined} />
                      </FormField>
                      <FormField label={t('contact.form.email')} name="email" error={errors.email ? t(errors.email) : ''}>
                        <input id="email" name="email" type="email" autoComplete="email" value={form.email} onChange={handleChange} onBlur={handleBlur} placeholder={t('contact.form.emailPlaceholder')} className={fieldClass('email')} aria-invalid={Boolean(errors.email)} aria-describedby={errors.email ? 'email-error' : undefined} />
                      </FormField>
                      <FormField label={t('contact.form.address')} name="address" error={errors.address ? t(errors.address) : ''}>
                        <input id="address" name="address" type="text" autoComplete="street-address" value={form.address} onChange={handleChange} onBlur={handleBlur} placeholder={t('contact.form.addressPlaceholder')} className={fieldClass('address')} aria-invalid={Boolean(errors.address)} aria-describedby={errors.address ? 'address-error' : undefined} />
                      </FormField>
                    </div>

                    <FormField label={t('contact.form.subject')} name="subject" error={errors.subject ? t(errors.subject) : ''}>
                      <input id="subject" name="subject" type="text" value={form.subject} onChange={handleChange} onBlur={handleBlur} placeholder={t('contact.form.subjectPlaceholder')} className={fieldClass('subject')} aria-invalid={Boolean(errors.subject)} aria-describedby={errors.subject ? 'subject-error' : undefined} />
                    </FormField>

                    <div className="mt-1">
                      <FormField label={t('contact.form.message')} name="message" error={errors.message ? t(errors.message) : ''}>
                        <textarea id="message" name="message" rows="7" maxLength="1000" value={form.message} onChange={handleChange} onBlur={handleBlur} placeholder={t('contact.form.messagePlaceholder')} className={['w-full resize-y rounded-lg border bg-white px-4 py-3 text-sm leading-relaxed text-ink outline-none transition placeholder:text-storm-grey/55 focus:ring-2', errors.message ? 'border-[#D46A5A] focus:border-[#D46A5A] focus:ring-[#D46A5A]/15' : 'border-light-mist focus:border-seafoam focus:ring-seafoam/15'].join(' ')} aria-invalid={Boolean(errors.message)} aria-describedby="message-error message-count" />
                      </FormField>
                      <span id="message-count" className="-mt-5 block text-right text-xs text-storm-grey">
                        {t('contact.form.messageCount', { count: form.message.length, max: 1000 })}
                      </span>
                    </div>

                    <input type="text" name="companyWebsite" value={form.companyWebsite} onChange={handleChange} tabIndex="-1" autoComplete="off" className="hidden" aria-hidden="true" />

                    <div className="mt-7">
                      <label className="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" name="consent" checked={form.consent} onChange={handleChange} onBlur={handleBlur} className="mt-1 h-4 w-4 shrink-0 rounded border-light-mist text-seafoam accent-seafoam" aria-invalid={Boolean(errors.consent)} aria-describedby={errors.consent ? 'consent-error' : undefined} />
                        <span className="text-xs leading-6 text-storm-grey">
                          {t('contact.form.consentPrefix')} <Link to="/privacy" className="font-bold text-seafoam hover:underline">{t('contact.form.privacyLink')}</Link>.
                        </span>
                      </label>
                      <span id="consent-error" className="mt-1.5 block min-h-4 text-xs font-medium text-[#B23A2B]">{errors.consent ? t(errors.consent) : ''}</span>
                    </div>

                    <div className="mt-5 flex flex-col gap-4 border-t border-light-mist pt-6 sm:flex-row sm:items-center sm:justify-between">
                      <p className="max-w-md text-xs leading-5 text-storm-grey">{t('contact.form.privacyNote')}</p>
                      <button type="submit" disabled={isSubmitting} className="btn btn-primary min-w-44 shrink-0 disabled:cursor-wait disabled:opacity-65" aria-busy={isSubmitting}>
                        {isSubmitting ? t('actions.sending') : t('actions.submitContact')}
                        {!isSubmitting && <span aria-hidden="true">→</span>}
                      </button>
                    </div>
                  </form>}
                </>
              )}
            </div>
          </div>
        </section>

        <section className="py-10 sm:py-12 lg:py-14" aria-label={t('contact.details.eyebrow')}>
          <div className="container">
            <div className="grid items-stretch gap-5 lg:grid-cols-2 lg:gap-6">
              {offices.map((office, index) => (
                <OfficeSection
                  key={office.id || office.code}
                  number={String(index + 1).padStart(2, '0')}
                  office={office}
                  title={officeTitle(office)}
                  address={officeAddress(office)}
                  labels={{ phone: t('contact.details.phone'), fax: t('contact.details.fax'), email: t('contact.details.email') }}
                  accent={index % 2 === 1}
                />
              ))}
            </div>
          </div>
        </section>

        {activeOffice && <section className="bg-[#F3F6F6] py-12 sm:py-16 lg:py-20" aria-labelledby="map-title">
          <div className="container">
            <header className="mb-8 text-center">
              <span className="section-eyebrow">{t('contact.map.eyebrow')}</span>
              <h2 id="map-title" className="mt-2 text-2xl font-black text-ocean-deep sm:text-3xl">{t('contact.map.title')}</h2>
            </header>
            <div className="mb-4 flex flex-wrap justify-center gap-3" role="tablist" aria-label={t('contact.map.locationLabel')}>
              {mapOffices.map(office => {
                const key = office.code || String(office.id)
                return (
                <button
                  key={key}
                  type="button"
                  role="tab"
                  aria-selected={activeOffice === office}
                  onClick={() => setActiveMap(key)}
                  className={`rounded-lg border px-4 py-2.5 text-sm font-bold transition ${activeOffice === office ? 'border-ocean-deep bg-ocean-deep text-white' : 'border-light-mist bg-white text-ocean-deep hover:border-seafoam hover:text-seafoam'}`}
                >
                  {officeTitle(office)}
                </button>
                )
              })}
            </div>
            <div className="overflow-hidden rounded-xl border border-light-mist bg-white shadow-sm">
              {activeOffice.map.embedUrl && <iframe key={activeOffice.map.embedUrl} src={activeOffice.map.embedUrl} title={`${t('contact.map.iframeTitle')} — ${officeTitle(activeOffice)}`} className="h-[22rem] w-full border-0 sm:h-[28rem] lg:h-[32rem]" loading="lazy" referrerPolicy="no-referrer-when-downgrade" />}
              {activeOffice.map.imageUrl && <img src={activeOffice.map.imageUrl} alt={`${t('contact.map.iframeTitle')} — ${officeTitle(activeOffice)}`} className="h-auto min-h-[22rem] w-full object-cover sm:min-h-[28rem] lg:min-h-[32rem]" />}
              {!activeOffice.map.embedUrl && !activeOffice.map.imageUrl && activeOffice.map.url && <div className="flex min-h-[22rem] items-center justify-center"><a className="btn btn-primary" href={activeOffice.map.url} target="_blank" rel="noreferrer">{t('contact.details.viewMap')} <span aria-hidden="true">→</span></a></div>}
            </div>
          </div>
        </section>}
      </main>
    </>
  )
}
