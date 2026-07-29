import InvestorDocumentLibrary from '@components/investors/InvestorDocumentLibrary'
import InvestorPageHeader from '@components/investors/InvestorPageHeader'
import InvestorStatGrid from '@components/investors/InvestorStatGrid'
import { financialReports, investorPageData } from '@data/investors'

export default function FinancialsPage() {
  const page = investorPageData.financials

  return (
    <>
      <InvestorPageHeader title={page.title} description={page.description} updated="28/04/2026" />
      <InvestorStatGrid items={page.stats} />

      <section className="mb-8 rounded-2xl bg-ocean-deep p-6 text-white sm:p-8">
        <div className="grid gap-6 lg:grid-cols-[0.65fr_1fr]">
          <div>
            <span className="text-[10px] font-extrabold uppercase tracking-[0.14em] text-coral-light">Cách đọc thư viện</span>
            <h2 className="mt-2 text-2xl font-extrabold text-white">Ba lớp thông tin tài chính</h2>
            <p className="mt-3 text-sm leading-6 text-white/70">Chọn loại tài liệu phù hợp với nhu cầu phân tích và đối chiếu của bạn.</p>
          </div>
          <div className="grid gap-3 sm:grid-cols-3">
            {page.guide.map((item) => (
              <article key={item.title} className="rounded-xl border border-white/10 bg-white/7 p-4">
                <h3 className="text-sm font-extrabold text-white">{item.title}</h3>
                <p className="mt-2 text-xs leading-5 text-white/65">{item.body}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <InvestorDocumentLibrary
        documents={financialReports}
        title="Thư viện báo cáo tài chính"
        description="Lọc nhanh theo năm, loại báo cáo hoặc tìm kiếm theo kỳ công bố."
      />
    </>
  )
}
