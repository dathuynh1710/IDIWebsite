import PageHead from '@components/common/PageHead'
import HeroSection from './sections/HeroSection'
import AboutSection from './sections/AboutSection'
import ProductsSection from './sections/ProductsSection'
import WhyChooseUsSection from './sections/WhyChooseUsSection'
import ManufacturingSection from './sections/ManufacturingSection'
import NewsSection from './sections/NewsSection'
import PartnersCarousel from '@components/sections/PartnersCarousel'
import { useLanguage } from '@hooks/useLanguage'
import { getHomeTranslations } from '@/i18n/home'

/**
 * HomePage
 * Route: /
 * Full-page landing for IDI Seafood — Vietnam's leading pangasius exporter.
 */
export default function HomePage() {
  const { language } = useLanguage()
  const copy = getHomeTranslations(language)

  return (
    <>
      <PageHead
        title={copy.seo.title}
        description={copy.seo.description}
      />

      {/* 1. Hero — full-screen video with headline + stats bar */}
      <HeroSection />

      {/* 2. About — who we are, 25+ years, green bond */}
      <AboutSection />

      {/* 3. Products — 4 main categories */}
      <ProductsSection />

      {/* 4. Why Choose Us — 4 differentiators */}
      <WhyChooseUsSection />

      {/* 5. Manufacturing — facility capabilities */}
      <ManufacturingSection />

      {/* 6. News — latest 3 articles */}
      <NewsSection />

      {/* 7. Partner ecosystem carousel */}
      <PartnersCarousel />
    </>
  )
}
