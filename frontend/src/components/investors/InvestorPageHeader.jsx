import PageHead from '@components/common/PageHead'
import { useLanguage } from '@hooks/useLanguage'

function StructuredDescription({ description }) {
  const sentenceEnd = description.indexOf('.')
  const lead = sentenceEnd >= 0 ? description.slice(0, sentenceEnd + 1) : description
  const detail = sentenceEnd >= 0 ? description.slice(sentenceEnd + 1).trim() : ''

  return (
    <div className="mt-4 max-w-3xl border-l-2 border-seafoam pl-4 sm:pl-5">
      <p className="text-[15px] font-semibold leading-6 text-ocean-deep">{lead}</p>
      {detail && <p className="mt-1.5 text-sm leading-6 text-storm-grey">{detail}</p>}
    </div>
  )
}

export default function InvestorPageHeader({ title, description, updated, seo, uppercaseTitle = false, structuredDescription = false }) {
  const { t } = useLanguage()
  return (
    <>
      <PageHead
        title={seo?.title || `${title} | ${t('nav.investors')} IDI`}
        description={seo?.description || description}
      />
      <header className="mb-5 border-b border-light-mist pb-5 sm:mb-6 sm:pb-6">
        <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
          <div className="min-w-0 max-w-3xl">
            <h1 className={`text-[clamp(1.75rem,3.5vw,2.5rem)] font-extrabold leading-[1.12] tracking-[-0.03em] text-ocean-deep ${uppercaseTitle ? 'uppercase' : ''}`}>
              {title}
            </h1>
            {description && (structuredDescription
              ? <StructuredDescription description={description} />
              : <p className="mt-2 max-w-3xl text-sm leading-6 text-storm-grey">{description}</p>)}
          </div>
          {updated && (
            <div className="shrink-0 rounded-lg border border-light-mist bg-white px-4 py-3 text-xs text-storm-grey shadow-sm">
              <span className="block font-semibold uppercase tracking-[0.1em] text-seafoam">Cập nhật gần nhất</span>
              <strong className="mt-1 block text-sm text-ocean-deep">{updated}</strong>
            </div>
          )}
        </div>
      </header>
    </>
  )
}
