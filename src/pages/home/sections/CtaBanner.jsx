import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'

/**
 * CtaBanner — Bottom-of-page call to action.
 * Encourages buyers to request a quote or start a conversation.
 */
export default function CtaBanner() {
  return (
    <section
      className="relative py-20 lg:py-28 overflow-hidden"
      style={{ background: 'linear-gradient(135deg, #0B2545 0%, #1A936F 100%)' }}
    >
      {/* Decorative wave-like blobs */}
      <div className="absolute inset-0 pointer-events-none overflow-hidden">
        <div
          className="absolute -top-32 -right-32 w-96 h-96 rounded-full opacity-10"
          style={{ background: 'radial-gradient(circle, #E8A045 0%, transparent 70%)' }}
        />
        <div
          className="absolute -bottom-24 -left-24 w-80 h-80 rounded-full opacity-10"
          style={{ background: 'radial-gradient(circle, #22B88A 0%, transparent 70%)' }}
        />
        {/* Grid pattern */}
        <div
          className="absolute inset-0 opacity-[0.04]"
          style={{
            backgroundImage: 'linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px)',
            backgroundSize: '60px 60px',
          }}
        />
      </div>

      <div className="container relative z-10">
        <div className="max-w-3xl mx-auto text-center">

          <RevealOnScroll>
            <span className="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white/80 text-xs font-semibold tracking-widest uppercase px-4 py-2 rounded-full mb-8">
              <span className="w-1.5 h-1.5 rounded-full bg-coral-gold animate-pulse" />
              Start a Conversation
            </span>
          </RevealOnScroll>

          <RevealOnScroll delay={80}>
            <h2 className="text-h1 font-black text-white mb-6 leading-tight text-balance">
              Ready to Source Premium{' '}
              <span className="text-coral-gold">Pangasius</span>?
            </h2>
          </RevealOnScroll>

          <RevealOnScroll delay={160}>
            <p className="text-white/70 text-body-lg leading-relaxed mb-10 max-w-xl mx-auto">
              Whether you need a full container or a custom product formulation,
              our export team responds within 24 hours. Get specifications, pricing,
              and samples tailored to your market.
            </p>
          </RevealOnScroll>

          <RevealOnScroll delay={240}>
            <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
              <Link
                to="/contact"
                className="btn btn-gold text-base px-8 py-4 w-full sm:w-auto"
              >
                Request a Quote
                <svg className="w-4 h-4" viewBox="0 0 16 16" fill="none">
                  <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </Link>
              <Link
                to="/products"
                className="btn btn-ghost text-base px-8 py-4 w-full sm:w-auto"
              >
                Browse Product Catalog
              </Link>
            </div>
          </RevealOnScroll>

          {/* Trust badges */}
          <RevealOnScroll delay={360}>
            <div className="flex flex-wrap items-center justify-center gap-6 mt-12 text-white/40 text-xs font-medium tracking-wide">
              <span>✓ ASC Certified Farms</span>
              <span className="hidden sm:block">·</span>
              <span>✓ 24h Response SLA</span>
              <span className="hidden sm:block">·</span>
              <span>✓ 50+ Countries Served</span>
              <span className="hidden sm:block">·</span>
              <span>✓ Custom Specifications</span>
            </div>
          </RevealOnScroll>

        </div>
      </div>
    </section>
  )
}
