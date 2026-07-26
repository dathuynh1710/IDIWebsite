import { useCallback, useEffect, useMemo, useState } from 'react'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { CERTIFICATIONS_DATA } from '@data/certifications'

const AUTOPLAY_DELAY = 4000

export default function CertificationsSection() {
  const certifications = CERTIFICATIONS_DATA
  const carouselItems = useMemo(
    () => [...certifications, ...certifications],
    [certifications],
  )
  const [currentIndex, setCurrentIndex] = useState(0)
  const [isPaused, setIsPaused] = useState(false)
  const [activeCertId, setActiveCertId] = useState(null)

  const activeCert = useMemo(
    () => certifications.find(cert => cert.id === activeCertId),
    [activeCertId, certifications],
  )

  const nextSlide = useCallback(() => {
    setCurrentIndex(index => (index + 1) % certifications.length)
  }, [certifications.length])

  const prevSlide = useCallback(() => {
    setCurrentIndex(index => (index - 1 + certifications.length) % certifications.length)
  }, [certifications.length])

  useEffect(() => {
    if (isPaused || certifications.length < 2) return undefined

    const autoplay = window.setInterval(nextSlide, AUTOPLAY_DELAY)
    return () => window.clearInterval(autoplay)
  }, [certifications.length, isPaused, nextSlide])

  return (
    <section className="py-20 lg:py-28 bg-white border-t border-light-mist overflow-hidden">
      <div className="container">
        <div className="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8 mb-12">
          <div className="max-w-2xl">
            <RevealOnScroll>
              <span className="section-eyebrow">Niềm tin &amp; Tuân thủ</span>
            </RevealOnScroll>
            <RevealOnScroll delay={80}>
              <h2 className="text-h2 font-bold text-ocean-deep mt-3 mb-4">
                Chất lượng đạt chuẩn quốc tế
              </h2>
            </RevealOnScroll>
            <RevealOnScroll delay={160}>
              <p className="text-storm-grey text-body-lg">
                Hệ thống chứng nhận toàn diện mở cánh cửa đến những thị trường
                khắt khe nhất thế giới.
              </p>
            </RevealOnScroll>
          </div>

          <RevealOnScroll delay={220}>
            <div className="flex items-center gap-3">
              <button
                type="button"
                onClick={prevSlide}
                className="w-11 h-11 rounded-btn border border-light-mist bg-white text-ocean-deep shadow-card hover:bg-ocean-deep hover:text-white hover:border-ocean-deep transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-seafoam"
                aria-label="Chứng nhận trước"
              >
                <svg className="w-5 h-5 mx-auto" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                  <path d="M12.5 4.5L7 10l5.5 5.5" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </button>
              <button
                type="button"
                onClick={nextSlide}
                className="w-11 h-11 rounded-btn border border-light-mist bg-white text-ocean-deep shadow-card hover:bg-ocean-deep hover:text-white hover:border-ocean-deep transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-seafoam"
                aria-label="Chứng nhận tiếp theo"
              >
                <svg className="w-5 h-5 mx-auto" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                  <path d="M7.5 4.5L13 10l-5.5 5.5" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </button>
            </div>
          </RevealOnScroll>
        </div>

        <RevealOnScroll>
          <div
            className="relative"
            onMouseEnter={() => setIsPaused(true)}
            onMouseLeave={() => setIsPaused(false)}
            onFocus={() => setIsPaused(true)}
            onBlur={() => setIsPaused(false)}
          >
            <div className="overflow-hidden -mx-2 px-2">
              <div
                className="certifications-track flex transition-transform duration-500 ease-out"
                style={{
                  '--cert-offset-mobile': `${currentIndex * -100}%`,
                  '--cert-offset-sm': `${currentIndex * -50}%`,
                  '--cert-offset-md': `${currentIndex * -(100 / 3)}%`,
                  '--cert-offset-lg': `${currentIndex * -25}%`,
                  '--cert-offset-xl': `${currentIndex * -20}%`,
                }}
              >
                {carouselItems.map((cert, itemIndex) => {
                  const isActive = activeCertId === cert.id
                  const displayName = cert.grade ? `${cert.name} ${cert.grade}` : cert.name
                  const marketLabel = 'thị trường'

                  return (
                    <div
                      key={`${cert.id}-${itemIndex}`}
                      className="basis-full sm:basis-1/2 md:basis-1/3 lg:basis-1/4 xl:basis-1/5 shrink-0 px-2"
                    >
                      <button
                        type="button"
                        onClick={() => setActiveCertId(isActive ? null : cert.id)}
                        className={[
                          'card h-full w-full min-h-[13.5rem] flex flex-col items-start text-left p-5 border transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-seafoam',
                          isActive
                            ? 'border-seafoam shadow-card-hover -translate-y-1'
                            : 'border-light-mist hover:border-seafoam/50',
                        ].join(' ')}
                        aria-pressed={isActive}
                        aria-label={`Xem chi tiết ${displayName}`}
                      >
                        <span className="badge badge-green mb-4">
                          {cert.markets.length} {marketLabel}
                        </span>

                        <span className="text-2xl font-black text-ocean-deep leading-none mb-3">
                          {displayName}
                        </span>
                        <span className="text-sm font-semibold text-ink leading-snug line-clamp-2 mb-3">
                          {cert.fullName}
                        </span>
                        <span className="text-xs font-semibold uppercase tracking-[0.12em] text-seafoam line-clamp-2">
                          {cert.scope}
                        </span>
                        <span className="mt-auto pt-4 text-sm font-semibold text-seafoam">
                          Xem chi tiết
                        </span>
                      </button>
                    </div>
                  )
                })}
              </div>
            </div>
          </div>
        </RevealOnScroll>

        <div className="flex justify-center gap-2 mt-6" aria-label="Vị trí carousel chứng nhận">
          {certifications.map((cert, index) => (
            <button
              key={cert.id}
              type="button"
              onClick={() => setCurrentIndex(index)}
              className={[
                'h-2 rounded-full transition-all duration-300',
                currentIndex === index ? 'w-8 bg-seafoam' : 'w-2 bg-mist-mid hover:bg-seafoam/60',
              ].join(' ')}
              aria-label={`Đi đến ${cert.name}`}
              aria-current={currentIndex === index ? 'true' : undefined}
            />
          ))}
        </div>

        <div
          className="mt-8 overflow-hidden transition-all duration-500 ease-out"
          style={{
            maxHeight: activeCert ? '15rem' : '0',
            opacity: activeCert ? 1 : 0,
          }}
        >
          {activeCert && (
            <div className="rounded-card border border-light-mist bg-seafoam-pale p-5 md:p-6">
              <div className="flex flex-col md:flex-row md:items-start gap-5">
                <div className="flex-1 min-w-0">
                  <div className="flex flex-wrap items-center gap-3 mb-2">
                    <span className="text-xl font-black text-ocean-deep">
                      {activeCert.grade ? `${activeCert.name} ${activeCert.grade}` : activeCert.name}
                    </span>
                    <span className="badge badge-blue">Chứng nhận hiện hành</span>
                  </div>
                  <p className="font-semibold text-ink text-sm md:text-base mb-2">
                    {activeCert.fullName}
                  </p>
                  <p className="text-storm-grey text-sm">
                    {activeCert.description}
                  </p>
                </div>

                <div className="flex items-center gap-3 shrink-0">
                  {activeCert.verifyUrl && (
                    <a
                      href={activeCert.verifyUrl}
                      target="_blank"
                      rel="noreferrer"
                      className="btn btn-primary py-2 px-4"
                    >
                      Xác minh
                    </a>
                  )}
                  <button
                    type="button"
                    onClick={() => setActiveCertId(null)}
                    className="w-10 h-10 rounded-btn bg-white text-storm-grey hover:text-ink border border-light-mist transition-colors"
                    aria-label="Đóng chi tiết chứng nhận"
                  >
                    <svg className="w-4 h-4 mx-auto" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                      <path d="M2 2l10 10M12 2L2 12" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          )}
        </div>

        <RevealOnScroll delay={400}>
          <p className="text-center text-storm-grey text-sm mt-8">
            Tất cả chứng nhận đều còn hiệu lực và được xác minh độc lập ·{' '}
            <a
              href="/quality"
              className="text-seafoam hover:text-seafoam-light font-semibold transition-colors"
            >
              Xem đầy đủ hồ sơ chất lượng →
            </a>
          </p>
        </RevealOnScroll>
      </div>
    </section>
  )
}
