import { useEffect, useRef, useState } from 'react'

const PARTNERS = [
  {
    name: 'Công ty CP Đầu tư và Phát triển Thủy sản Trisedco',
    logo: '/images/partners/trisedco.png',
    href: 'https://trisedco.com/',
  },
  {
    name: 'Công ty CP Tư vấn và Đầu tư Tài chính ASTAR',
    logo: '/images/partners/astar.png',
    href: 'https://www.saomainews.com.vn/',
  },
  {
    name: 'Công ty CP Tài chính và Truyền thông Quốc tế MIF',
    logo: '/images/partners/mif.png',
    href: 'https://mifvn.com/',
  },
  {
    name: 'Công ty CP Tư vấn Xây dựng và Đầu tư Tài chính AFC',
    logo: '/images/partners/afc.jpg',
  },
  {
    name: 'Công ty CP Du lịch An Giang',
    logo: '/images/partners/du-lich-an-giang.jpg',
    href: 'https://www.angiangtourimex.vn/',
  },
  {
    name: 'Công ty CP Du lịch Đồng Tháp',
    logo: '/images/partners/du-lich-dong-thap.png',
    href: 'https://dongthaptourist.com/',
  },
  {
    name: 'Tập đoàn Sao Mai',
    logo: '/images/partners/sao-mai-group.jpg',
    href: 'https://www.saomainews.com.vn/',
  },
  {
    name: 'Sao Mai Super Feed',
    logo: '/images/partners/sao-mai-superfeed.png',
    href: 'https://www.saomaisuperfeed.com/',
  },
  {
    name: 'Sao Mai Solar',
    logo: '/images/partners/sao-mai-solar.png',
    href: 'https://saomaisolar.vn/',
  },
]

function getVisibleCount() {
  if (window.innerWidth >= 1280) return 5
  if (window.innerWidth >= 640) return 3
  return 1
}

function PartnerLogo({ partner }) {
  const content = (
    <img
      src={partner.logo}
      alt={partner.name}
      className="max-h-20 w-full max-w-[180px] object-contain transition-transform duration-300 group-hover:scale-105"
      loading="lazy"
    />
  )

  if (!partner.href) {
    return (
      <div className="group flex h-36 items-center justify-center rounded-2xl border border-light-mist bg-white px-5 shadow-[0_8px_24px_-18px_rgba(11,37,69,0.45)]">
        {content}
      </div>
    )
  }

  return (
    <a
      href={partner.href}
      target="_blank"
      rel="noreferrer"
      className="group flex h-36 items-center justify-center rounded-2xl border border-light-mist bg-white px-5 shadow-[0_8px_24px_-18px_rgba(11,37,69,0.45)] transition-all duration-300 hover:-translate-y-1 hover:border-seafoam/35 hover:shadow-[0_14px_30px_-18px_rgba(26,147,111,0.5)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-seafoam"
      aria-label={`${partner.name} — mở website`}
    >
      {content}
    </a>
  )
}

export default function PartnersCarousel() {
  const [visibleCount, setVisibleCount] = useState(getVisibleCount)
  const maxIndex = Math.max(0, PARTNERS.length - visibleCount)
  const [current, setCurrent] = useState(0)
  const [paused, setPaused] = useState(false)
  const touchStartX = useRef(null)

  useEffect(() => {
    const handleResize = () => setVisibleCount(getVisibleCount())
    window.addEventListener('resize', handleResize, { passive: true })
    return () => window.removeEventListener('resize', handleResize)
  }, [])

  useEffect(() => {
    setCurrent((index) => Math.min(index, maxIndex))
  }, [maxIndex])

  useEffect(() => {
    if (paused || maxIndex === 0 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return undefined
    }

    const timer = window.setInterval(() => {
      setCurrent((index) => (index >= maxIndex ? 0 : index + 1))
    }, 3500)

    return () => window.clearInterval(timer)
  }, [maxIndex, paused])

  const goPrevious = () => {
    setCurrent((index) => (index <= 0 ? maxIndex : index - 1))
  }

  const goNext = () => {
    setCurrent((index) => (index >= maxIndex ? 0 : index + 1))
  }

  const handleTouchEnd = (event) => {
    if (touchStartX.current === null) return

    const distance = event.changedTouches[0].clientX - touchStartX.current
    if (Math.abs(distance) > 45) {
      distance > 0 ? goPrevious() : goNext()
    }
    touchStartX.current = null
  }

  return (
    <section
      className="relative overflow-hidden border-y border-light-mist py-12 sm:py-16"
      style={{ background: 'linear-gradient(135deg, #F8FAFB 0%, #EEF7F5 100%)' }}
      aria-label="Đối tác và thành viên trong hệ sinh thái Sao Mai"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
      onFocusCapture={() => setPaused(true)}
      onBlurCapture={() => setPaused(false)}
      onKeyDown={(event) => {
        if (event.key === 'ArrowLeft') goPrevious()
        if (event.key === 'ArrowRight') goNext()
      }}
    >
      <div className="relative z-10">
        <div className="container">
          <div
            className="relative mx-auto max-w-[1240px] px-11 sm:px-14"
            onTouchStart={(event) => {
              touchStartX.current = event.touches[0].clientX
            }}
            onTouchEnd={handleTouchEnd}
          >
            <div className="overflow-hidden">
              <div
                className="flex transition-transform duration-500 ease-out"
                style={{ transform: `translateX(-${current * (100 / visibleCount)}%)` }}
                aria-live="polite"
              >
                {PARTNERS.map((partner) => (
                  <div
                    key={partner.name}
                    className="shrink-0 px-3 sm:px-4"
                    style={{ width: `${100 / visibleCount}%` }}
                  >
                    <PartnerLogo partner={partner} />
                  </div>
                ))}
              </div>
            </div>

            <button
              type="button"
              onClick={goPrevious}
              className="absolute left-0 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-light-mist bg-white text-ocean-deep shadow-md transition-all hover:border-seafoam/30 hover:bg-seafoam hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-seafoam"
              aria-label="Xem đối tác trước"
            >
              <svg className="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m15 5-7 7 7 7" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            </button>

            <button
              type="button"
              onClick={goNext}
              className="absolute right-0 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-light-mist bg-white text-ocean-deep shadow-md transition-all hover:border-seafoam/30 hover:bg-seafoam hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-seafoam"
              aria-label="Xem đối tác tiếp theo"
            >
              <svg className="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m9 5 7 7-7 7" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </section>
  )
}
