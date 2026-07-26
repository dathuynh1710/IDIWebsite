import InvestorDocumentLibrary from '@components/investors/InvestorDocumentLibrary'
import InvestorPageHeader from '@components/investors/InvestorPageHeader'
import InvestorStatGrid from '@components/investors/InvestorStatGrid'
import { announcements, investorPageData } from '@data/investors'

export default function InvestorsPage() {
  const page = investorPageData.overview

  return (
    <>
      <InvestorPageHeader title={page.title} description={page.description} />
      <InvestorStatGrid items={page.stats} />

      <section className="mb-8 grid gap-3 md:grid-cols-3">
        {page.principles.map((item, index) => (
          <article key={item.title} className="rounded-xl border border-light-mist bg-white p-5">
            <span className="text-xs font-black text-coral-gold">0{index + 1}</span>
            <h2 className="mt-4 text-lg font-extrabold text-ocean-deep">{item.title}</h2>
            <p className="mt-2 text-sm leading-6">{item.body}</p>
          </article>
        ))}
      </section>

      <InvestorDocumentLibrary
        documents={announcements}
        title="Công bố & thông báo mới nhất"
        description="Tra cứu các thông báo quản trị, nghị quyết và thông tin dành cho cổ đông."
      />
    </>
  )
}
