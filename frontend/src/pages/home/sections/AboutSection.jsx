import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { useLanguage } from '@hooks/useLanguage'
import { getHomeTranslations } from '@/i18n/home'
import { publicAsset } from '@utils/publicAsset'

export default function AboutSection() {
  const { language, t } = useLanguage()
  const copy = getHomeTranslations(language).about

  return (
    <section className="overflow-hidden bg-arctic-white py-16 lg:py-20">
      <div className="container">
        <div className="grid items-center gap-10 lg:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)] lg:gap-16">
          <div className="max-w-xl">
            <RevealOnScroll>
              <span className="section-eyebrow">{copy.eyebrow}</span>
            </RevealOnScroll>

            <RevealOnScroll delay={80}>
              <h2 className="mt-3 text-balance text-h2 font-bold text-ocean-deep">
                {copy.title}
              </h2>
            </RevealOnScroll>

            <RevealOnScroll delay={160}>
              <p className="mt-6 text-justify text-base leading-7 text-storm-grey sm:text-lg sm:leading-8">
                {copy.paragraph1}
              </p>
            </RevealOnScroll>

            <RevealOnScroll delay={240}>
              <Link to="/about" className="btn btn-secondary mt-8">
                {t('actions.exploreIdi')}
                <svg className="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                  <path d="M4 10h12m-4-4 4 4-4 4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </Link>
            </RevealOnScroll>
          </div>

          <RevealOnScroll direction="right" delay={100}>
            <img
              src={publicAsset('assets/images/home/company-map.jpg')}
              alt={copy.imageAlt}
              className="h-auto w-full object-contain"
              loading="lazy"
            />
          </RevealOnScroll>
        </div>
      </div>
    </section>
  )
}
