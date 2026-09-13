import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { useLanguage } from '@hooks/useLanguage'
import { getHomeTranslations } from '@/i18n/home'
import { publicAsset } from '@utils/publicAsset'

export default function ManufacturingSection() {
  const { language, t } = useLanguage()
  const copy = getHomeTranslations(language).manufacturing

  return (
    <section className="bg-arctic-white py-16 lg:py-20">
      <div className="container">
        <div className="relative min-h-[36rem] overflow-hidden sm:min-h-[40rem] lg:aspect-[15/8] lg:min-h-0">
          <img
            src={publicAsset('assets/images/home/manufacturing.jpg')}
            alt={copy.imageAlt}
            className="absolute inset-0 h-full w-full object-cover object-center"
            loading="lazy"
          />

          <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-black/10 lg:bg-gradient-to-r lg:from-black/25 lg:via-black/20 lg:to-black/35" />

          <div className="relative z-10 flex min-h-[36rem] items-end px-6 py-10 sm:min-h-[40rem] sm:px-10 sm:py-12 lg:absolute lg:inset-0 lg:min-h-0 lg:items-center lg:justify-end lg:px-16 xl:px-24">
            <div className="w-full max-w-xl">
              <RevealOnScroll>
                <span className="mb-4 inline-flex items-center gap-3 text-xs font-bold uppercase tracking-[0.18em] text-white/75">
                  <span className="h-px w-8 bg-coral-gold" aria-hidden="true" />
                  {copy.badgeText}
                </span>
              </RevealOnScroll>

              <RevealOnScroll delay={80}>
                <h2 className="max-w-lg text-balance text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-[2.75rem]">
                  {copy.title}
                </h2>
              </RevealOnScroll>

              <RevealOnScroll delay={160}>
                <p className="mt-5 max-w-lg text-justify text-base leading-7 text-white/80 sm:text-lg sm:leading-8">
                  {copy.description}
                </p>
              </RevealOnScroll>

              <RevealOnScroll delay={240}>
                <Link
                  to="/sustainability"
                  className="mt-8 inline-flex min-h-12 items-center gap-3 rounded-md border border-white/70 px-5 text-sm font-bold text-white transition-colors duration-200 hover:border-white hover:bg-white hover:text-ocean-deep"
                >
                  {t('actions.learnMore')}
                </Link>
              </RevealOnScroll>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
