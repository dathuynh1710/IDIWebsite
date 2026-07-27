import PageHead from '@components/common/PageHead'
import HeroSection from './sections/HeroSection'
import AboutSection from './sections/AboutSection'
import ProductsSection from './sections/ProductsSection'
import WhyChooseUsSection from './sections/WhyChooseUsSection'
import ManufacturingSection from './sections/ManufacturingSection'
import NewsSection from './sections/NewsSection'
import PartnersCarousel from '@components/sections/PartnersCarousel'

/**
 * HomePage
 * Route: /
 * Full-page landing for IDI Seafood — Vietnam's leading pangasius exporter.
 */
export default function HomePage() {
  return (
    <>
      <PageHead
        title="IDI Seafood — Nhà xuất khẩu cá tra hàng đầu Việt Nam"
        description="Công ty Cổ phần Đầu tư và Phát triển Đa Quốc Gia I.D.I — cá tra đạt chuẩn ASC từ Đồng bằng sông Cửu Long, xuất khẩu đến hơn 50 quốc gia."
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
