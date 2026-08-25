import { Link } from 'react-router'
import { FOOTER_LINKS } from '@data/navigation'
import { useLanguage } from '@hooks/useLanguage'

const CONTACT_INFO = [
  {
    icon: (
      <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    labelKey: 'footer.headOffice',
    valueKey: 'footer.headOfficeAddress',
  },
  {
    icon: (
      <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    labelKey: 'footer.hcmOffice',
    valueKey: 'footer.hcmAddress',
  },
  {
    icon: (
      <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    labelKey: 'footer.email',
    value: 'info@idiseafood.com',
    href: 'mailto:info@idiseafood.com',
  },
  {
    icon: (
      <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    labelKey: 'footer.phone',
    value: '+84 2773 680 383 / +84 2777 300 468',
    href: 'tel:+842773680383',
  },
  {
    icon: (
      <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    labelKey: 'footer.hcmPhone',
    value: '+84 932 824 888',
    href: 'tel:+84932824888',
  },
]

const CERTIFICATIONS = [
  'BSCI',
  'ASC',
  'ISO 9001:2015',
  'IFS Food',
  'BRCGS',
  'DIPOA Brazil',
  'HACCP',
  'JAKIM Halal',
  'GCC Halal',
]

const currentYear = new Date().getFullYear()

export default function Footer() {
  const { t } = useLanguage()
  return (
    <footer style={{ background: '#081C38' }} className="text-white/80">

      {/* ── Main footer content ─────────────────────────────────── */}
      <div className="container pt-16 pb-12">
        <div className="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-8 xl:gap-12">

          {/* Brand column — wider */}
          <div className="text-center md:col-span-12 lg:col-span-3 xl:col-span-4">
            {/* Logo area */}
            <Link to="/" className="group mb-6 inline-flex flex-col items-center gap-4">
              <img
                src="/images/brand/idi-logo.png"
                alt="IDI Seafood"
                className="h-14 w-24 flex-shrink-0 rounded-md bg-white object-contain p-1"
              />
              <span className="max-w-xs text-sm font-bold leading-relaxed tracking-wide text-white">
                {t('footer.legalName')}
              </span>
            </Link>

            {/* Certifications mini badges */}
            <div className="flex flex-wrap justify-center gap-2">
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
          <nav
            aria-label={t('nav.footer')}
            className="md:col-span-8 lg:col-span-6 xl:col-span-5 grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-10"
          >
            {Object.values(FOOTER_LINKS).map((col) => (
              <div key={col.titleKey} className="min-w-0">
                <h4 className="text-white text-sm font-bold mb-5 tracking-wide">
                  {t(col.titleKey)}
                </h4>
                <ul className="flex flex-col gap-2.5">
                  {col.links.map((link) => (
                    <li key={link.href}>
                      <Link
                        to={link.href}
                        className="text-white/50 hover:text-white text-sm leading-snug transition-colors duration-200"
                      >
                        {t(link.labelKey)}
                      </Link>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </nav>

          {/* Contact column */}
          <div className="md:col-span-4 lg:col-span-3 min-w-0">
            <h4 className="text-white text-sm font-bold mb-5 tracking-wide">{t('footer.contact')}</h4>
            <ul className="flex flex-col gap-4">
              {CONTACT_INFO.map((item) => (
                <li key={item.labelKey} className="flex gap-3">
                  <div className="flex-shrink-0 mt-0.5 text-seafoam">{item.icon}</div>
                  <div className="min-w-0">
                    <div className="text-white/30 text-[10px] font-semibold tracking-wider uppercase mb-0.5">
                      {item.labelKey === 'footer.email' ? 'Email' : t(item.labelKey)}
                    </div>
                    {item.href ? (
                      <a
                        href={item.href}
                        className="text-white/60 hover:text-white text-xs transition-colors duration-200"
                      >
                        {item.valueKey ? t(item.valueKey) : item.value}
                      </a>
                    ) : (
                      <span className="text-white/60 text-xs leading-relaxed">{item.valueKey ? t(item.valueKey) : item.value}</span>
                    )}
                  </div>
                </li>
              ))}
            </ul>


          </div>

        </div>
      </div>

      {/* ── Divider ─────────────────────────────────────────────── */}
      <div className="border-t border-white/8" />

      {/* ── Bottom bar ──────────────────────────────────────────── */}
      <div className="container py-6">
        <div className="flex flex-col items-center justify-between gap-2 text-center text-[11px] text-white/35 sm:flex-row sm:text-left">
          <p>
            {t('footer.license')}
          </p>
          <p className="shrink-0">Copyright © {currentYear} idiseafood.com</p>
        </div>
      </div>

    </footer>
  )
}
