import { Link } from 'react-router'
import { FOOTER_LINKS } from '@data/navigation'

const CONTACT_INFO = [
  {
    icon: (
      <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    label: 'Headquarters',
    value: '51 Bis, Võ Thị Sáu, Phường 6, TP. Sa Đéc, Đồng Tháp, Vietnam',
  },
  {
    icon: (
      <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    label: 'Export Enquiries',
    value: 'export@idiseafood.com',
    href: 'mailto:export@idiseafood.com',
  },
  {
    icon: (
      <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    label: 'Phone',
    value: '+84 277 3955 888',
    href: 'tel:+842773955888',
  },
]

const CERTIFICATIONS = ['ASC', 'BRC AA', 'GlobalGAP', 'IFS Higher', 'HACCP', 'HALAL']

const currentYear = new Date().getFullYear()

export default function Footer() {
  return (
    <footer style={{ background: '#081C38' }} className="text-white/80">

      {/* ── Main footer content ─────────────────────────────────── */}
      <div className="container pt-16 pb-12">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10">

          {/* Brand column — wider */}
          <div className="lg:col-span-4">
            {/* Logo area */}
            <Link to="/" className="inline-flex items-center gap-3 mb-6 group">
              <div className="w-10 h-10 rounded-xl bg-seafoam flex items-center justify-center flex-shrink-0">
                <span className="text-white font-black text-sm tracking-tight">IDI</span>
              </div>
              <div>
                <div className="text-white font-bold text-base leading-tight">IDI Seafood</div>
                <div className="text-white/40 text-[11px] tracking-wide">Since 1997 · Vietnam</div>
              </div>
            </Link>

            <p className="text-white/55 text-sm leading-relaxed mb-6 max-w-xs">
              I.D.I Multipurpose Investment & Development Corporation — Asia-Pacific's
              leading pangasius exporter, ASC-certified, serving 50+ countries since 1997.
            </p>

            {/* Stock ticker */}
            <div className="inline-flex items-center gap-2 bg-white/6 border border-white/10 rounded-lg px-3 py-2 text-xs mb-8">
              <span className="text-coral-gold font-bold">HOSE: IDI</span>
              <span className="text-white/30">·</span>
              <span className="text-white/50">Ho Chi Minh Stock Exchange</span>
            </div>

            {/* Certifications mini badges */}
            <div className="flex flex-wrap gap-2">
              {CERTIFICATIONS.map(cert => (
                <span
                  key={cert}
                  className="text-[10px] font-bold tracking-wide border border-white/15 text-white/50 px-2.5 py-1 rounded-full"
                >
                  {cert}
                </span>
              ))}
            </div>
          </div>

          {/* Nav link columns */}
          {Object.values(FOOTER_LINKS).map((col) => (
            <div key={col.title} className="lg:col-span-2">
              <h4 className="text-white text-sm font-bold mb-5 tracking-wide">
                {col.title}
              </h4>
              <ul className="flex flex-col gap-2.5">
                {col.links.map((link) => (
                  <li key={link.href}>
                    <Link
                      to={link.href}
                      className="text-white/50 hover:text-white text-sm transition-colors duration-200"
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}

          {/* Contact column */}
          <div className="sm:col-span-2 lg:col-span-3">
            <h4 className="text-white text-sm font-bold mb-5 tracking-wide">Contact</h4>
            <ul className="flex flex-col gap-4">
              {CONTACT_INFO.map((item) => (
                <li key={item.label} className="flex gap-3">
                  <div className="flex-shrink-0 mt-0.5 text-seafoam">{item.icon}</div>
                  <div>
                    <div className="text-white/30 text-[10px] font-semibold tracking-wider uppercase mb-0.5">
                      {item.label}
                    </div>
                    {item.href ? (
                      <a
                        href={item.href}
                        className="text-white/60 hover:text-white text-xs transition-colors duration-200"
                      >
                        {item.value}
                      </a>
                    ) : (
                      <span className="text-white/60 text-xs">{item.value}</span>
                    )}
                  </div>
                </li>
              ))}
            </ul>

            {/* CTA */}
            <Link
              to="/contact"
              className="inline-flex items-center gap-2 mt-6 btn btn-gold text-sm py-2.5 px-5"
            >
              Request a Quote
              <svg className="w-3.5 h-3.5" viewBox="0 0 14 14" fill="none">
                <path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            </Link>
          </div>

        </div>
      </div>

      {/* ── Divider ─────────────────────────────────────────────── */}
      <div className="border-t border-white/8" />

      {/* ── Bottom bar ──────────────────────────────────────────── */}
      <div className="container py-6">
        <div className="flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-white/30">

          <p>
            © {currentYear} I.D.I Multipurpose Investment & Development Corporation.
            All rights reserved.
          </p>

          <div className="flex flex-wrap items-center justify-center gap-4">
            <Link to="/privacy" className="hover:text-white/60 transition-colors">Privacy Policy</Link>
            <span>·</span>
            <Link to="/terms" className="hover:text-white/60 transition-colors">Terms of Use</Link>
            <span>·</span>
            <Link to="/sitemap" className="hover:text-white/60 transition-colors">Sitemap</Link>
          </div>

        </div>
      </div>

    </footer>
  )
}
