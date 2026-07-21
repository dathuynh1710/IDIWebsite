import PageHead from '@components/common/PageHead'
import HeroSection from './sections/HeroSection'
import AboutSection from './sections/AboutSection'
import ProductsSection from './sections/ProductsSection'
import StatisticsSection from './sections/StatisticsSection'
import WhyChooseUsSection from './sections/WhyChooseUsSection'
import ManufacturingSection from './sections/ManufacturingSection'
import CertificationsSection from './sections/CertificationsSection'
import NewsSection from './sections/NewsSection'
import CtaBanner from './sections/CtaBanner'

/**
 * HomePage
 * Route: /
 * Full-page landing for IDI Seafood — Vietnam's leading pangasius exporter.
 */
export default function HomePage() {
  return (
    <>
      <PageHead
        title="IDI Seafood — Vietnam's Leading Pangasius Exporter"
        description="I.D.I Multipurpose Investment & Development Corporation — ASC-certified pangasius from Mekong Delta farms to 50+ countries worldwide. Premium seafood since 1997."
      />

      {/* 1. Hero — full-screen video with headline + stats bar */}
      <HeroSection />

      {/* 2. About — who we are, 25+ years, green bond */}
      <AboutSection />

      {/* 3. Statistics — animated counters */}
      <StatisticsSection />

      {/* 4. Products — 4 main categories */}
      <ProductsSection />

      {/* 5. Why Choose Us — 4 differentiators */}
      <WhyChooseUsSection />

      {/* 6. Manufacturing — facility capabilities */}
      <ManufacturingSection />

      {/* 7. Certifications — trust signals */}
      <CertificationsSection />

      {/* 8. News — latest 3 articles */}
      <NewsSection />

      {/* 9. CTA Banner — close with a quote request */}
      <CtaBanner />
    </>
  )
}
