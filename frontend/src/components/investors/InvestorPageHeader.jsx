import PageHead from '@components/common/PageHead'

export default function InvestorPageHeader({ title, description, eyebrow = 'Quan hệ cổ đông', updated = '18/05/2026' }) {
  return (
    <>
      <PageHead
        title={`${title} | Nhà đầu tư IDI`}
        description={description}
      />
      <header className="mb-8 border-b border-light-mist pb-7">
        <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
          <div className="max-w-3xl">
            <span className="section-eyebrow">{eyebrow}</span>
            <h1 className="text-[clamp(2rem,4vw,3.15rem)] font-extrabold leading-[1.08] tracking-[-0.035em] text-ocean-deep">
              {title}
            </h1>
            <p className="mt-3 max-w-2xl text-[15px] leading-7 text-storm-grey">{description}</p>
          </div>
          <div className="shrink-0 rounded-lg border border-light-mist bg-white px-4 py-3 text-xs text-storm-grey shadow-sm">
            <span className="block font-semibold uppercase tracking-[0.1em] text-seafoam">Cập nhật gần nhất</span>
            <strong className="mt-1 block text-sm text-ocean-deep">{updated}</strong>
          </div>
        </div>
      </header>
    </>
  )
}
