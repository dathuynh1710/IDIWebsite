import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'

const PILLARS = [
  {
    icon: (
      <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M12 3L2 7v10l10 4 10-4V7L12 3z" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M12 3v18M2 7l10 4 10-4" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    label: 'Vertically Integrated',
    text: 'Farm → Processing → Export under one roof',
  },
  {
    icon: (
      <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    label: 'ASC Certified',
    text: 'Highest international sustainability certification',
  },
  {
    icon: (
      <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    label: '50+ Countries',
    text: 'Established export network across 5 continents',
  },
]

export default function AboutSection() {
  return (
    <section className="py-24 lg:py-36 bg-white overflow-hidden">
      <div className="container">
        <div className="grid lg:grid-cols-2 gap-16 xl:gap-24 items-center">

          {/* ── Left: Text ──────────────────────────────────────── */}
          <div>
            <RevealOnScroll>
              <span className="section-eyebrow">About IDI</span>
            </RevealOnScroll>

            <RevealOnScroll delay={80}>
              <h2 className="text-h2 font-bold text-ocean-deep mt-3 mb-6 text-balance">
                25+ Years of Sustainable
                Pangasius Excellence
              </h2>
            </RevealOnScroll>

            <RevealOnScroll delay={160}>
              <p className="text-body-lg text-storm-grey leading-relaxed mb-6">
                Founded in 1997, IDI (I.D.I Multipurpose Investment & Development Corporation)
                is one of Vietnam's largest pangasius seafood producers and exporters. Operating
                in the heart of the Mekong Delta, we control every stage of the supply chain —
                from our own ASC-certified farms to fully automated processing and global logistics.
              </p>
              <p className="text-storm-grey leading-relaxed mb-8">
                Listed on the Ho Chi Minh Stock Exchange (ticker: IDI), we are the
                first company in Asia-Pacific's seafood industry to issue a Green Bond,
                reaffirming our commitment to sustainable aquaculture and environmental responsibility.
              </p>
            </RevealOnScroll>

            {/* Pillars */}
            <div className="flex flex-col gap-4 mb-10">
              {PILLARS.map((p, i) => (
                <RevealOnScroll key={p.label} delay={240 + i * 80}>
                  <div className="flex items-center gap-4">
                    <div className="flex-shrink-0 w-11 h-11 rounded-xl bg-seafoam-pale text-seafoam flex items-center justify-center">
                      {p.icon}
                    </div>
                    <div>
                      <div className="font-semibold text-ink text-sm">{p.label}</div>
                      <div className="text-storm-grey text-sm">{p.text}</div>
                    </div>
                  </div>
                </RevealOnScroll>
              ))}
            </div>

            <RevealOnScroll delay={480}>
              <Link to="/about/story" className="btn btn-primary">
                Our Story
                <svg className="w-4 h-4" viewBox="0 0 16 16" fill="none">
                  <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </Link>
            </RevealOnScroll>
          </div>

          {/* ── Right: Image ─────────────────────────────────────── */}
          <RevealOnScroll direction="right" delay={100}>
            <div className="relative">
              {/* Main image */}
              <div className="relative rounded-2xl overflow-hidden aspect-[4/3] shadow-2xl">
                <img
                  src="https://idiseafood.com/vnt_upload/weblink/MAP_vn_1.jpg"
                  alt="IDI Seafood — Export Markets Map"
                  className="w-full h-full object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-ocean-deep/40 to-transparent" />
              </div>

              {/* Floating badge: Year Founded */}
              <div className="absolute -bottom-6 -left-6 bg-white rounded-2xl shadow-xl px-6 py-4 border border-light-mist">
                <div className="text-3xl font-black text-ocean-deep">1997</div>
                <div className="text-xs text-storm-grey font-medium mt-0.5">Year Founded</div>
              </div>

              {/* Floating badge: Green Bond */}
              <div className="absolute -top-5 -right-5 bg-seafoam text-white rounded-2xl shadow-xl px-5 py-3 max-w-[160px]">
                <div className="text-xs font-bold tracking-wide mb-1">🌿 GREEN BOND</div>
                <div className="text-[11px] leading-snug opacity-90">Asia-Pacific's First in Seafood</div>
              </div>

              {/* Decorative dots grid */}
              <div
                className="absolute -z-10 -bottom-8 -right-8 w-48 h-48 opacity-20"
                style={{
                  backgroundImage: 'radial-gradient(circle, #0B2545 1px, transparent 1px)',
                  backgroundSize: '16px 16px',
                }}
              />
            </div>
          </RevealOnScroll>

        </div>
      </div>
    </section>
  )
}
