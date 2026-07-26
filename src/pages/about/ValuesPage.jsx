import AboutPageHeader from '@components/about/AboutPageHeader'
import { CORE_VALUES, MISSION_PILLARS } from '@data/about'

export default function ValuesPage() {
  return (
    <>
      <AboutPageHeader
        eyebrow="Về IDI"
        title="Giá trị cốt lõi"
        description="Những nguyên tắc định hình văn hóa doanh nghiệp và dẫn dắt mọi hoạt động của IDI."
      />

      <main className="bg-white py-14 sm:py-18">
        <article className="container max-w-4xl">
          <section aria-labelledby="core-values">
            <h2 id="core-values" className="sr-only">Giá trị cốt lõi của IDI</h2>
            <div className="space-y-8">
              {CORE_VALUES.map((value) => (
                <div key={value.number}>
                  <h3 className="text-xl font-bold text-ocean-deep sm:text-2xl">{value.title}</h3>
                  <p className="mt-3 text-base leading-8 text-slate sm:text-lg sm:leading-9">{value.body}</p>
                </div>
              ))}
            </div>
          </section>

          <hr className="my-10 border-light-mist" />

          <section aria-labelledby="mission">
            <h2 id="mission" className="text-2xl font-bold text-ocean-deep sm:text-3xl">Thế mạnh và nhiệm vụ</h2>
            <p className="mt-5 text-base leading-8 text-slate sm:text-lg sm:leading-9">
              IDI yêu mến và trân trọng giá trị của loài cá tra. Chúng tôi không ngừng tìm hiểu để
              mang đến cho khách hàng hương vị tốt nhất, đồng thời sản xuất một cách thuần túy, minh
              bạch và có trách nhiệm.
            </p>
            <div className="mt-8 space-y-7">
              {MISSION_PILLARS.map((item) => (
                <div key={item.title}>
                  <h3 className="text-lg font-bold uppercase text-ocean-deep">{item.title}</h3>
                  <p className="mt-2 text-base leading-8 text-slate">{item.body}</p>
                </div>
              ))}
            </div>
          </section>
        </article>
      </main>
    </>
  )
}
