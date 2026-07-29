import PageHead from '@components/common/PageHead'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { CORE_VALUES, MISSION_PILLARS } from '@data/about'

function CoreValueCard({ value, index }) {
  const imageOnRight = index < 2

  return (
    <article className="group grid h-full min-w-0 overflow-hidden rounded-2xl border border-light-mist bg-white shadow-[0_20px_55px_-42px_rgba(11,37,69,0.45)] sm:min-h-72 sm:grid-cols-2">
      <figure
        className={`relative min-h-56 overflow-hidden bg-ocean-deep ${
          imageOnRight ? 'sm:order-2' : 'sm:order-1'
        }`}
      >
        <div
          className="absolute -inset-8 scale-110 bg-cover bg-center opacity-40 blur-2xl"
          style={{ backgroundImage: `url("${value.image}")` }}
          aria-hidden="true"
        />
        <div className="absolute inset-0 bg-ocean-deep/20" aria-hidden="true" />
        <img
          src={value.image}
          alt={value.imageAlt}
          className="absolute inset-0 z-10 h-full w-full object-contain"
          loading={index > 1 ? 'lazy' : 'eager'}
          onError={event => {
            event.currentTarget.style.display = 'none'
            event.currentTarget.parentElement.style.background =
              'linear-gradient(135deg, #0B2545, #1A936F)'
          }}
        />
        <div className="pointer-events-none absolute inset-0 z-20 bg-gradient-to-t from-ocean-deep/45 via-transparent to-transparent" />
        <span className="absolute bottom-4 left-4 z-30 text-4xl font-black leading-none text-white/95">
          {value.number}
        </span>
      </figure>

      <div
        className={`flex min-w-0 flex-col justify-center p-6 sm:p-7 ${
          imageOnRight ? 'sm:order-1' : 'sm:order-2'
        }`}
      >
        <span className="mb-4 h-1 w-10 rounded-full bg-coral-gold" aria-hidden="true" />
        <h3 className="text-xl font-black text-ocean-deep sm:text-2xl">{value.title}</h3>
        <p className="mt-4 text-sm leading-7 text-slate">{value.body}</p>
      </div>
    </article>
  )
}

export default function ValuesPage() {
  return (
    <>
      <PageHead
        title="Giá trị cốt lõi | IDI Seafood"
        description="Niềm đam mê, sự đổi mới, sẻ chia và tinh thần trách nhiệm định hình văn hóa doanh nghiệp IDI."
      />

      <main className="overflow-hidden bg-arctic-white">
        <header className="relative overflow-hidden bg-ocean-deep pb-24 pt-32 text-white sm:pb-28 sm:pt-40">
          <div
            className="absolute inset-0 opacity-25"
            style={{
              background:
                'radial-gradient(circle at 82% 15%, #1A936F 0%, transparent 34%), radial-gradient(circle at 12% 92%, #E8A045 0%, transparent 25%)',
            }}
          />
          <div className="container relative z-10">
            <span className="inline-flex items-center gap-2 text-xs font-black uppercase tracking-[0.2em] text-seafoam-light">
              <span className="h-2 w-2 rounded-full bg-coral-gold" aria-hidden="true" />
              Văn hóa IDI
            </span>
            <div className="mt-6 grid max-w-6xl gap-7 lg:grid-cols-[1fr_0.7fr] lg:items-end lg:gap-20">
              <h1 className="text-h1 font-black leading-[1.05] text-white text-balance">
                Giá trị cốt lõi tạo nên bản sắc IDI
              </h1>
              <p className="max-w-xl text-lg leading-8 text-white/65">
                Bốn giá trị dẫn dắt cách chúng tôi làm việc, đổi mới và đồng hành cùng con người,
                cộng đồng và thiên nhiên.
              </p>
            </div>
          </div>
        </header>

        <section className="relative py-16 lg:py-20" aria-labelledby="core-values">
          <div
            className="pointer-events-none absolute -right-40 top-24 h-96 w-96 rounded-full bg-seafoam-pale blur-3xl"
            aria-hidden="true"
          />
          <div className="container relative">
            <div className="mb-12 max-w-2xl">
              <span className="section-eyebrow">Điều chúng tôi tin tưởng</span>
              <h2 id="core-values" className="text-h2 font-black text-ocean-deep">
                Bốn giá trị, một hành trình chung
              </h2>
              <p className="mt-4 text-lg leading-8">
                Mỗi giá trị không chỉ là một lời cam kết, mà được thể hiện trong từng quyết định và
                hoạt động hằng ngày tại IDI.
              </p>
            </div>

            <div className="mx-auto grid max-w-6xl gap-5 xl:grid-cols-2">
              {CORE_VALUES.map((value, index) => (
                <RevealOnScroll key={value.number} delay={(index % 2) * 100}>
                  <CoreValueCard value={value} index={index} />
                </RevealOnScroll>
              ))}
            </div>
          </div>
        </section>

        <section className="relative overflow-hidden bg-ocean-deep py-20 text-white lg:py-28" aria-labelledby="mission">
          <div
            className="absolute inset-0 opacity-15"
            style={{
              background:
                'linear-gradient(125deg, transparent 35%, #1A936F 100%), radial-gradient(circle at 8% 10%, #E8A045 0%, transparent 24%)',
            }}
          />
          <div className="container relative z-10">
            <div className="max-w-3xl">
              <span className="text-xs font-black uppercase tracking-[0.2em] text-coral-light">
                Thế mạnh &amp; nhiệm vụ
              </span>
              <h2 id="mission" className="mt-4 text-h2 font-black text-white">
                Từ tình yêu cá tra đến trách nhiệm dài hạn
              </h2>
              <p className="mt-6 text-lg leading-8 text-white/65">
                IDI yêu mến và trân trọng giá trị của loài cá tra. Chúng tôi không ngừng tìm hiểu để
                mang đến hương vị tốt nhất, đồng thời sản xuất minh bạch và có trách nhiệm.
              </p>
              <div className="mt-8 h-1 w-20 rounded-full bg-coral-gold" aria-hidden="true" />
            </div>

            <div className="mt-12 grid min-w-0 gap-4 lg:grid-cols-2">
              {MISSION_PILLARS.map((item, index) => (
                <RevealOnScroll
                  key={item.title}
                  delay={(index % 2) * 80}
                  className={`min-w-0 ${index === MISSION_PILLARS.length - 1 ? 'lg:col-span-2' : ''}`}
                >
                  <article className="h-full min-w-0 overflow-hidden rounded-2xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur-sm transition hover:-translate-y-1 hover:border-seafoam-light/50 hover:bg-white/[0.1] sm:p-7">
                    <span className="text-sm font-black text-coral-light">
                      {String(index + 1).padStart(2, '0')}
                    </span>
                    <h3 className="mt-4 break-words text-lg font-black uppercase leading-snug text-white">
                      {item.title}
                    </h3>
                    <p className="mt-4 text-sm leading-7 text-white/60">{item.body}</p>
                  </article>
                </RevealOnScroll>
              ))}
            </div>
          </div>
        </section>
      </main>
    </>
  )
}
