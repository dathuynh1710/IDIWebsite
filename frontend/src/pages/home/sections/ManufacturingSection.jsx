import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { useLanguage } from '@hooks/useLanguage'
import { getHomeTranslations } from '@/i18n/home'

const CAPABILITY_ICONS = ['⚡', '🏭', '❄️', '🌊', '🔧', '🔬']

export default function ManufacturingSection() {
  const { language, t } = useLanguage()
  const copy = getHomeTranslations(language).manufacturing
  return (
    <section
      className="py-24 lg:py-36 relative overflow-hidden"
      style={{ background: 'linear-gradient(135deg, #0f1f36 0%, #0B2545 60%, #163D6B 100%)' }}
    >
      {/* Background factory image */}
      <div className="absolute inset-0">
        <img
          src="https://idiseafood.com/vnt_upload/weblink/dichvu.jpg"
          alt=""
          className="w-full h-full object-cover opacity-15"
          aria-hidden="true"
        />
        {/* Grid pattern overlay */}
        <div
          className="absolute inset-0 opacity-[0.03]"
          style={{
            backgroundImage: 'linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px)',
            backgroundSize: '64px 64px',
          }}
        />
      </div>

      <div className="container relative z-10">
        <div className="grid lg:grid-cols-2 gap-16 items-center">

          {/* ── Left: Content ──────────────────────────────────── */}
          <div>

            <RevealOnScroll delay={80}>
              <h2 className="text-h2 font-bold text-white mt-3 mb-6 text-balance">
                {copy.title}
              </h2>
            </RevealOnScroll>
            <RevealOnScroll delay={160}>
              <p className="text-white/65 leading-relaxed mb-8 text-body-lg">
                {copy.description}
              </p>
            </RevealOnScroll>

            {/* Capabilities list */}
            <div className="grid grid-cols-2 gap-3 mb-10">
              {copy.capabilities.map((cap, i) => (
                <RevealOnScroll key={cap[0]} delay={240 + i * 60}>
                  <div className="flex items-center gap-3 bg-white/6 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/8 hover:bg-white/10 transition-colors duration-200">
                    <span className="text-xl">{CAPABILITY_ICONS[i]}</span>
                    <div>
                      <div className="text-white font-bold text-sm">{cap[1]}</div>
                      <div className="text-white/50 text-xs">{cap[0]}</div>
                    </div>
                  </div>
                </RevealOnScroll>
              ))}
            </div>

            <RevealOnScroll delay={600}>
              <Link to="/sustainability" className="btn btn-ghost">
                {t('actions.learnMore')}
              </Link>
            </RevealOnScroll>
          </div>

          {/* ── Right: Image ─────────────────────────────────────── */}
          <RevealOnScroll direction="right" delay={120}>
            <div className="relative">
              <div className="rounded-2xl overflow-hidden aspect-[4/3] shadow-2xl ring-1 ring-white/10">
                <img
                  src="https://idiseafood.com/vnt_upload/weblink/dichvu.jpg"
                  alt={copy.imageAlt}
                  className="w-full h-full object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-ocean-deep/60 to-transparent" />
              </div>

              {/* Process steps badge */}
              <div className="absolute -bottom-5 -right-5 bg-coral-gold text-white rounded-2xl p-5 shadow-xl max-w-[180px]">
                <div className="text-2xl font-black">{copy.badge}</div>
                <div className="text-xs mt-1 opacity-90 font-medium">{copy.badgeText}</div>
              </div>

              {/* Decorative ring */}
              <div className="absolute -top-4 -left-4 w-20 h-20 rounded-full border-2 border-white/10" />
              <div className="absolute -top-8 -left-8 w-32 h-32 rounded-full border border-white/5" />
            </div>
          </RevealOnScroll>

        </div>
      </div>
    </section>
  )
}
