import InvestorDocumentLibrary from '@components/investors/InvestorDocumentLibrary'
import InvestorPageHeader from '@components/investors/InvestorPageHeader'
import InvestorStatGrid from '@components/investors/InvestorStatGrid'
import { agmDocuments, investorPageData } from '@data/investors'

export default function AGMPage() {
  const page = investorPageData.agm

  return (
    <>
      <InvestorPageHeader title={page.title} description={page.description} updated="25/04/2026" />
      <InvestorStatGrid items={page.stats} />

      <section className="mb-8 rounded-2xl border border-light-mist bg-white p-6 sm:p-8">
        <div className="mb-6">
          <span className="section-eyebrow">Hành trình thông tin</span>
          <h2 className="text-2xl font-extrabold text-ocean-deep">Từ thư mời đến nghị quyết</h2>
        </div>
        <div className="grid gap-4 md:grid-cols-3">
          {page.process.map((item, index) => (
            <article key={item.step} className="relative rounded-xl bg-arctic-white p-5">
              <span className="inline-grid h-9 w-9 place-items-center rounded-full bg-ocean-deep text-xs font-black text-white">{item.step}</span>
              <h3 className="mt-4 text-base font-extrabold text-ocean-deep">{item.title}</h3>
              <p className="mt-2 text-sm leading-6">{item.body}</p>
              {index < page.process.length - 1 && <span aria-hidden="true" className="absolute -right-3 top-8 z-10 hidden text-lg font-black text-seafoam md:block">→</span>}
            </article>
          ))}
        </div>
      </section>

      <InvestorDocumentLibrary
        documents={agmDocuments}
        title="Hồ sơ Đại hội đồng cổ đông"
        description="Tìm thư mời, tài liệu họp, biểu mẫu ủy quyền, biên bản và nghị quyết."
      />
    </>
  )
}
