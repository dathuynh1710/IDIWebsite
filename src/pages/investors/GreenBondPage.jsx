import InvestorDocumentLibrary from '@components/investors/InvestorDocumentLibrary'
import InvestorPageHeader from '@components/investors/InvestorPageHeader'
import InvestorStatGrid from '@components/investors/InvestorStatGrid'
import { bondDocuments, investorPageData } from '@data/investors'

export default function GreenBondPage() {
  const page = investorPageData.bond

  return (
    <>
      <InvestorPageHeader title={page.title} description={page.description} eyebrow="Tài chính bền vững" updated="30/03/2026" />
      <InvestorStatGrid items={page.stats} />

      <section className="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-seafoam to-[#0d684f] p-6 text-white sm:p-8">
        <div className="grid gap-7 lg:grid-cols-[0.85fr_1.15fr]">
          <div>
            <span className="text-[10px] font-extrabold uppercase tracking-[0.14em] text-white/65">Tác động nguồn vốn</span>
            <h2 className="mt-2 text-3xl font-extrabold text-white">Đầu tư cho một chuỗi giá trị xanh hơn</h2>
            <p className="mt-3 text-sm leading-7 text-white/75">Nguồn vốn trái phiếu được định hướng vào các hạng mục giúp tăng năng lực và hiệu quả bền vững của chuỗi cá tra.</p>
          </div>
          <div className="grid gap-3 sm:grid-cols-3">
            {page.impact.map((item, index) => (
              <article key={item.title} className="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                <span className="text-xs font-black text-coral-light">0{index + 1}</span>
                <h3 className="mt-5 text-sm font-extrabold text-white">{item.title}</h3>
                <p className="mt-2 text-xs leading-5 text-white/70">{item.body}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <aside className="mb-8 flex flex-col gap-5 rounded-2xl border border-coral-gold/30 bg-coral-pale p-6 sm:flex-row sm:items-center sm:justify-between">
        <div className="max-w-2xl">
          <span className="text-[10px] font-extrabold uppercase tracking-[0.14em] text-[#a96b13]">{page.award.label}</span>
          <h2 className="mt-2 text-xl font-extrabold text-ocean-deep">{page.award.title}</h2>
          <p className="mt-2 text-sm leading-6">{page.award.body}</p>
        </div>
        <div aria-hidden="true" className="grid h-16 w-16 shrink-0 place-items-center rounded-full bg-coral-gold text-2xl">★</div>
      </aside>

      <InvestorDocumentLibrary
        documents={bondDocuments}
        title="Công bố thông tin trái phiếu"
        description="Tra cứu báo cáo sử dụng vốn, tình hình tài chính, cam kết và lịch thanh toán lãi."
      />
    </>
  )
}
