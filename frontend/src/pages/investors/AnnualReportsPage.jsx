import InvestorDocumentLibrary from '@components/investors/InvestorDocumentLibrary'
import InvestorPageHeader from '@components/investors/InvestorPageHeader'
import InvestorStatGrid from '@components/investors/InvestorStatGrid'
import { annualReports, investorPageData } from '@data/investors'

export default function AnnualReportsPage() {
  const page = investorPageData.annualReports
  const latest = page.latest

  return (
    <>
      <InvestorPageHeader title={page.title} description={page.description} updated="15/04/2026" />
      <InvestorStatGrid items={page.stats} />

      <section className="mb-8 overflow-hidden rounded-2xl bg-ocean-deep text-white">
        <div className="grid lg:grid-cols-[0.7fr_1.3fr]">
          <div className="relative grid min-h-72 place-items-center overflow-hidden bg-[radial-gradient(circle_at_30%_20%,rgba(232,160,69,0.36),transparent_13rem),linear-gradient(145deg,#163D6B,#0B2545)] p-8">
            <div className="absolute -right-16 -top-16 h-44 w-44 rounded-full border border-white/10" />
            <div className="absolute -bottom-20 -left-10 h-56 w-56 rounded-full border border-white/10" />
            <div className="relative w-44 border border-white/25 bg-white/8 p-6 shadow-2xl backdrop-blur-sm">
              <span className="text-[10px] font-black uppercase tracking-[0.18em] text-coral-light">IDI Seafood</span>
              <strong className="mt-14 block text-5xl font-black">{latest.year}</strong>
              <span className="mt-2 block text-xs font-bold uppercase tracking-[0.1em] text-white/70">Báo cáo thường niên</span>
            </div>
          </div>
          <div className="p-6 sm:p-9">
            <span className="text-[10px] font-extrabold uppercase tracking-[0.14em] text-coral-light">{latest.label}</span>
            <h2 className="mt-2 text-3xl font-extrabold text-white">{latest.title}</h2>
            <p className="mt-3 text-sm leading-7 text-white/70">{latest.body}</p>
            <div className="mt-6 grid gap-x-5 gap-y-2 sm:grid-cols-2">
              {latest.chapters.map((chapter) => (
                <span key={chapter} className="border-t border-white/10 py-2.5 text-xs font-bold text-white/85">✓ {chapter}</span>
              ))}
            </div>
            <a href={latest.href} target="_blank" rel="noreferrer" className="mt-6 inline-flex min-h-11 items-center rounded-lg bg-coral-gold px-5 text-sm font-extrabold text-ocean-deep transition hover:bg-coral-light">
              Xem báo cáo 2025 ↗
            </a>
          </div>
        </div>
      </section>

      <InvestorDocumentLibrary
        documents={annualReports}
        title="Kho báo cáo thường niên"
        description="Theo dõi hành trình phát triển và hiệu quả quản trị của IDI qua từng năm."
      />
    </>
  )
}
