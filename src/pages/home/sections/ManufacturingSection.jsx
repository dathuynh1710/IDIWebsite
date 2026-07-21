import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'

const CAPABILITIES = [
  { label: 'Annual Capacity',    value: '100,000 MT', icon: '⚡' },
  { label: 'Processing Lines',   value: '12 Lines',   icon: '🏭' },
  { label: 'Cold Storage',       value: '15,000 MT',  icon: '❄️' },
  { label: 'Farm Area',          value: '280 Ha',     icon: '🌊' },
  { label: 'IQF Tunnels',        value: '8 Units',    icon: '🔧' },
  { label: 'Quality Labs',       value: '2 In-house', icon: '🔬' },
]

export default function ManufacturingSection() {
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
            <RevealOnScroll>
              <span className="section-eyebrow text-seafoam-light">Facilities</span>
            </RevealOnScroll>
            <RevealOnScroll delay={80}>
              <h2 className="text-h2 font-bold text-white mt-3 mb-6 text-balance">
                State-of-the-Art Processing at Scale
              </h2>
            </RevealOnScroll>
            <RevealOnScroll delay={160}>
              <p className="text-white/65 leading-relaxed mb-8 text-body-lg">
                Our Đồng Tháp processing complex spans 28 hectares along the Mekong River.
                Fully automated IQF processing lines, in-house quality laboratories, and
                real-time HACCP monitoring ensure every product meets the strictest global standards.
              </p>
            </RevealOnScroll>

            {/* Capabilities list */}
            <div className="grid grid-cols-2 gap-3 mb-10">
              {CAPABILITIES.map((cap, i) => (
                <RevealOnScroll key={cap.label} delay={240 + i * 60}>
                  <div className="flex items-center gap-3 bg-white/6 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/8 hover:bg-white/10 transition-colors duration-200">
                    <span className="text-xl">{cap.icon}</span>
                    <div>
                      <div className="text-white font-bold text-sm">{cap.value}</div>
                      <div className="text-white/50 text-xs">{cap.label}</div>
                    </div>
                  </div>
                </RevealOnScroll>
              ))}
            </div>

            <RevealOnScroll delay={600}>
              <Link to="/manufacturing" className="btn btn-ghost">
                Explore Facilities →
              </Link>
            </RevealOnScroll>
          </div>

          {/* ── Right: Image ─────────────────────────────────────── */}
          <RevealOnScroll direction="right" delay={120}>
            <div className="relative">
              <div className="rounded-2xl overflow-hidden aspect-[4/3] shadow-2xl ring-1 ring-white/10">
                <img
                  src="https://idiseafood.com/vnt_upload/weblink/dichvu.jpg"
                  alt="IDI Seafood Processing Facility"
                  className="w-full h-full object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-ocean-deep/60 to-transparent" />
              </div>

              {/* Process steps badge */}
              <div className="absolute -bottom-5 -right-5 bg-coral-gold text-white rounded-2xl p-5 shadow-xl max-w-[180px]">
                <div className="text-2xl font-black">Farm → Fork</div>
                <div className="text-xs mt-1 opacity-90 font-medium">Full vertical integration</div>
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
