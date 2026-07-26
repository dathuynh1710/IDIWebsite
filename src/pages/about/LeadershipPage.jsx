import AboutPageHeader from '@components/about/AboutPageHeader'
import { GOVERNANCE_LAYERS, LEADERSHIP_TEAM } from '@data/about'

export default function LeadershipPage() {
  return (
    <>
      <AboutPageHeader
        eyebrow="Về IDI"
        title="Ban lãnh đạo"
        description="Đội ngũ định hướng chiến lược, tổ chức điều hành và giám sát hoạt động của IDI."
      />

      <main className="bg-white py-14 sm:py-18">
        <article className="container max-w-4xl">
          <p className="text-base leading-8 text-slate sm:text-lg sm:leading-9">
            Cơ cấu lãnh đạo của IDI được tổ chức rõ ràng giữa nhiệm vụ định hướng chiến lược, điều
            hành hoạt động và giám sát tuân thủ. Mỗi thành viên đóng góp kinh nghiệm chuyên môn và
            tinh thần trách nhiệm vào mục tiêu phát triển ổn định, bền vững của công ty.
          </p>

          <section className="mt-10" aria-labelledby="leadership-team">
            <h2 id="leadership-team" className="text-2xl font-bold text-ocean-deep sm:text-3xl">
              Đội ngũ lãnh đạo
            </h2>
            <div className="mt-6 divide-y divide-light-mist border-y border-light-mist">
              {LEADERSHIP_TEAM.map((person) => (
                <div key={person.id} className="py-6">
                  <p className="text-sm font-semibold uppercase tracking-wide text-seafoam">{person.group}</p>
                  <h3 className="mt-2 text-xl font-bold text-ocean-deep">{person.name}</h3>
                  <p className="mt-1 font-semibold text-slate">{person.title}</p>
                  <p className="mt-3 text-base leading-8 text-slate">{person.message}</p>
                </div>
              ))}
            </div>
          </section>

          <section className="mt-10" aria-labelledby="governance-model">
            <h2 id="governance-model" className="text-2xl font-bold text-ocean-deep sm:text-3xl">
              Mô hình quản trị
            </h2>
            <div className="mt-6 space-y-6">
              {GOVERNANCE_LAYERS.map((item) => (
                <div key={item.title}>
                  <h3 className="text-lg font-bold text-ocean-deep">{item.title}</h3>
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
