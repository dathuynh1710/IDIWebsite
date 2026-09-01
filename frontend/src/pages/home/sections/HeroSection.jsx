import { Link } from 'react-router'
import { useLanguage } from '@hooks/useLanguage'
import { getHomeTranslations } from '@/i18n/home'

const STAT_VALUES = ['50+', '200+', '80+', 'ASC']
const VIDEO_BY_LANGUAGE = {
  vi: '/videos/idi-food-30s-vi.mp4',
  en: '/videos/idi-food-30s-en.mp4',
  'zh-CN': '/videos/idi-food-30s-zh-cn.mp4',
}

export default function HeroSection() {
  const { language, t } = useLanguage()
  const copy = getHomeTranslations(language).hero
  const stats = STAT_VALUES.map((value, index) => ({ value, label: copy.stats[index] }))
  const videoSrc = VIDEO_BY_LANGUAGE[language] ?? VIDEO_BY_LANGUAGE.vi
  return (
    <section className="relative min-h-screen flex flex-col overflow-hidden">

      {/* ── Video Background ──────────────────────────────────── */}
      <div className="absolute inset-0">
        <video
          key={language}
          className="w-full h-full object-cover"
          autoPlay
          muted
          loop
          playsInline
          poster="https://idiseafood.com/vnt_upload/weblink/MAP_vn_1.jpg"
        >
          <source
            src={videoSrc}
            type="video/mp4"
          />
        </video>
        {/* Cinematic dark gradient */}
        <div className="absolute inset-0 bg-gradient-to-t from-[#0B2545] via-[#0B2545]/55 to-[#0B2545]/10" />
        {/* Subtle horizontal vignette */}
        <div className="absolute inset-0 bg-gradient-to-r from-[#0B2545]/60 via-transparent to-transparent" />
      </div>

      {/* ── Content ───────────────────────────────────────────── */}
      <div className="relative z-10 flex-1 flex items-center">
        <div className="container py-24 sm:py-32">
          <div className="max-w-3xl">

            {/* Eyebrow badge */}
            <div
              className="inline-flex max-w-full items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 text-white/90 text-xs font-semibold tracking-widest uppercase px-3 sm:px-4 py-2 rounded-full mb-6 sm:mb-8"
              style={{ animation: 'fadeInUp 0.6s ease forwards', animationDelay: '0ms' }}
            >
              <span className="w-2 h-2 rounded-full bg-coral-gold animate-pulse" />
              <span className="hidden xs:inline">{copy.eyebrow}</span>
              <span className="xs:hidden">{copy.eyebrowShort}</span>
            </div>

            {/* Main headline */}
            <h1
              className="text-display text-white mb-5 sm:mb-6 text-balance"
              style={{ animation: 'fadeInUp 0.7s ease forwards', animationDelay: '100ms', opacity: 0 }}
            >
              {copy.titleBefore}{' '}
              <span className="text-coral-gold">{copy.titleAccent}</span>{' '}
              {copy.titleAfter}
            </h1>

            {/* Sub-headline */}
            <p
              className="text-lg sm:text-xl text-white/75 mb-8 sm:mb-10 max-w-xl leading-relaxed"
              style={{ animation: 'fadeInUp 0.7s ease forwards', animationDelay: '200ms', opacity: 0 }}
            >
              {copy.description}
            </p>

            {/* CTAs */}
            <div
              className="flex flex-col xs:flex-row flex-wrap gap-3 sm:gap-4"
              style={{ animation: 'fadeInUp 0.7s ease forwards', animationDelay: '300ms', opacity: 0 }}
            >
              <Link to="/products" className="btn btn-gold text-base px-7 py-3.5 w-full xs:w-auto justify-center">
                {t('actions.exploreProducts')}
                <svg className="w-4 h-4" viewBox="0 0 16 16" fill="none">
                  <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </Link>
              <Link to="/contact" className="btn btn-ghost text-base px-7 py-3.5 w-full xs:w-auto justify-center">
                {t('nav.requestQuote')}
              </Link>
            </div>

          </div>
        </div>
      </div>

      {/* ── Stats Bar ─────────────────────────────────────────── */}
      <div className="relative z-10 bg-white/8 backdrop-blur-md border-t border-white/10">
        <div className="container">
          <div className="grid grid-cols-2 md:grid-cols-4">
            {stats.map((stat, i) => (
              <div
                key={stat.label}
                className="py-5 px-4 text-center border-r border-white/10 last:border-0 md:border-r first:border-l-0"
                style={{ animation: `fadeInUp 0.5s ease forwards`, animationDelay: `${400 + i * 80}ms`, opacity: 0 }}
              >
                <div className="text-2xl lg:text-3xl font-black text-coral-gold tracking-tight">
                  {stat.value}
                </div>
                <div className="text-xs text-white/60 mt-1 tracking-wide">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </div>

    </section>
  )
}
