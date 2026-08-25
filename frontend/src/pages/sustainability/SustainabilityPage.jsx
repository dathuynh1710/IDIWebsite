import PageHead from '@components/common/PageHead'
import { useLanguage } from '@hooks/useLanguage'

const APPROACHES = [
  { number: '01', key: 'listen' },
  { number: '02', key: 'standards' },
  { number: '03', key: 'innovate', hasExtra: true },
]

const SOURCE_IMAGES = {
  aquaculture: 'https://idiseafood.com/vnt_upload/File/10_2020/about1.png',
  commitment: 'https://idiseafood.com/vnt_upload/File/10_2020/banner.png',
  greenFinance:
    'https://idiseafood.com/vnt_upload/File/12_2024/z6157041673068_a3e1b616e3505bb73eaa3d41c9de8d09.jpg',
}

export default function SustainabilityPage() {
  const { t } = useLanguage()

  return (
    <>
      <PageHead
        title={t('sustainability.seoTitle')}
        description={t('sustainability.seoDescription')}
      />

      <main className="overflow-hidden bg-white">
        <section className="relative flex min-h-[620px] items-end overflow-hidden bg-ocean-deep pt-[72px]">
          <img
            src={SOURCE_IMAGES.aquaculture}
            alt={t('sustainability.hero.imageAlt')}
            className="absolute inset-0 h-full w-full object-cover"
          />
          <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(5,29,54,0.92)_0%,rgba(5,29,54,0.68)_52%,rgba(5,29,54,0.2)_100%)]" />
          <div className="absolute -right-24 top-20 h-80 w-80 rounded-full border border-white/15" />
          <div className="absolute -right-4 top-40 h-44 w-44 rounded-full border border-white/15" />

          <div className="container relative z-10 pb-16 pt-28 sm:pb-20 lg:pb-24">
            <div className="max-w-3xl">
              <p className="mb-5 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.24em] text-coral-light">
                <span className="h-px w-10 bg-coral-light" />
                {t('sustainability.hero.eyebrow')}
              </p>
              <h1 className="max-w-3xl text-balance text-[clamp(2.75rem,7vw,5.75rem)] font-extrabold leading-[0.98] tracking-[-0.045em] text-white">
                {t('sustainability.hero.titleLine1')}
                <span className="block text-seafoam-light">{t('sustainability.hero.titleLine2')}</span>
              </h1>
              <p className="mt-7 max-w-xl text-lg leading-8 text-white/78 sm:text-xl">
                {t('sustainability.hero.description')}
              </p>
            </div>
          </div>
        </section>

        <section className="section-padding">
          <div className="container grid items-start gap-12 lg:grid-cols-[0.82fr_1.18fr] lg:gap-20">
            <div className="lg:sticky lg:top-28">
              <span className="section-eyebrow">{t('sustainability.vision.eyebrow')}</span>
              <h2 className="max-w-lg text-balance text-ocean-deep">
                {t('sustainability.vision.title')}
              </h2>
              <div className="mt-6 h-1 w-20 rounded-full bg-coral-gold" />
            </div>

            <div className="space-y-6 text-lg leading-9">
              <p className="text-slate">
                {t('sustainability.vision.paragraph1')}
              </p>
              <p className="text-slate">
                {t('sustainability.vision.paragraph2')}
              </p>
              <div className="mt-8 grid grid-cols-2 gap-3 border-t border-light-mist pt-7 sm:grid-cols-3">
                {['ecosystems', 'community', 'future'].map((item) => (
                  <div key={item} className="flex items-center gap-2 text-sm font-bold text-ocean-deep">
                    <span className="grid h-6 w-6 place-items-center rounded-full bg-seafoam-pale text-seafoam">✓</span>
                    {t(`sustainability.vision.${item}`)}
                  </div>
                ))}
              </div>
            </div>
          </div>
        </section>

        <section className="bg-arctic-white section-padding">
          <div className="container">
            <div className="mb-12 max-w-2xl">
              <span className="section-eyebrow">{t('sustainability.approach.eyebrow')}</span>
              <h2 className="text-balance text-ocean-deep">{t('sustainability.approach.title')}</h2>
            </div>

            <div className="grid gap-5 lg:grid-cols-3">
              {APPROACHES.map((approach) => (
                <article
                  key={approach.number}
                  className="group relative overflow-hidden border border-light-mist bg-white p-7 shadow-[0_20px_60px_-45px_rgba(11,37,69,0.55)] transition duration-300 hover:-translate-y-1 hover:border-seafoam/40 sm:p-8"
                >
                  <div className="mb-10 flex items-start justify-between">
                    <span className="text-xs font-extrabold uppercase tracking-[0.18em] text-seafoam">
                      {t('sustainability.approach.action', { number: approach.number })}
                    </span>
                    <span className="text-5xl font-extrabold leading-none text-light-mist transition-colors group-hover:text-seafoam-pale">
                      {approach.number}
                    </span>
                  </div>
                  <h3 className="mb-5 text-2xl text-ocean-deep">{t(`sustainability.approach.items.${approach.key}.title`)}</h3>
                  <p className="text-sm leading-7 text-storm-grey">{t(`sustainability.approach.items.${approach.key}.description`)}</p>
                  {approach.hasExtra && (
                    <p className="mt-4 border-t border-light-mist pt-4 text-sm leading-7 text-storm-grey">
                      {t(`sustainability.approach.items.${approach.key}.extra`)}
                    </p>
                  )}
                  <div className="absolute inset-x-0 bottom-0 h-1 origin-left scale-x-0 bg-seafoam transition-transform duration-300 group-hover:scale-x-100" />
                </article>
              ))}
            </div>
          </div>
        </section>

        <section className="relative isolate min-h-[440px] overflow-hidden">
          <img
            src={SOURCE_IMAGES.commitment}
            alt={t('sustainability.commitment.imageAlt')}
            className="absolute inset-0 h-full w-full object-cover"
          />
          <div className="absolute inset-0 bg-ocean-deep/72" />
          <div className="container relative z-10 flex min-h-[440px] items-center py-20">
            <div className="max-w-4xl border-l-4 border-coral-gold pl-6 sm:pl-9">
              <span className="mb-4 block text-xs font-bold uppercase tracking-[0.25em] text-coral-light">
                {t('sustainability.commitment.eyebrow')}
              </span>
              <h2 className="text-balance text-[clamp(2rem,5vw,4.25rem)] font-extrabold uppercase leading-[1.08] tracking-[-0.035em] text-white">
                {t('sustainability.commitment.title')}
              </h2>
            </div>
          </div>
        </section>

        <section className="section-padding-lg bg-seafoam-pale/45">
          <div className="container grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div className="relative">
              <div className="absolute -bottom-5 -left-5 h-full w-full border border-seafoam/35" />
              <img
                src={SOURCE_IMAGES.greenFinance}
                alt={t('sustainability.finance.imageAlt')}
                className="relative aspect-[4/3] w-full object-cover shadow-[0_30px_70px_-35px_rgba(11,37,69,0.55)]"
              />
              <div className="absolute -right-4 -top-4 bg-ocean-deep px-5 py-4 text-white shadow-lg sm:-right-6 sm:-top-6">
                <span className="block text-xs font-bold uppercase tracking-[0.16em] text-coral-light">{t('sustainability.finance.badge')}</span>
                <strong className="mt-1 block text-lg">{t('sustainability.finance.badgeTitle')}</strong>
              </div>
            </div>

            <div>
              <span className="section-eyebrow">{t('sustainability.finance.eyebrow')}</span>
              <h2 className="text-ocean-deep">{t('sustainability.finance.title')}</h2>
              <div className="mt-7 space-y-5">
                <p className="leading-8 text-slate">
                  {t('sustainability.finance.paragraph1')}
                </p>
                <p className="leading-8 text-slate">
                  {t('sustainability.finance.paragraph2')}
                </p>
                <p className="leading-8 text-slate">
                  {t('sustainability.finance.paragraph3')}
                </p>
              </div>

              <div className="mt-8 flex flex-wrap gap-3">
                {['ASC', 'BAP', t('sustainability.finance.greenBond')].map((standard) => (
                  <span
                    key={standard}
                    className="rounded-full border border-seafoam/25 bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.1em] text-seafoam shadow-sm"
                  >
                    {standard}
                  </span>
                ))}
              </div>
            </div>
          </div>
        </section>
      </main>
    </>
  )
}
