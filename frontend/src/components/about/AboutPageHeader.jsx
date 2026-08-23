import { NavLink } from 'react-router'
import PageHead from '@components/common/PageHead'
import { useLanguage } from '@hooks/useLanguage'

const ABOUT_NAV = [
  { labelKey: 'nav.story', href: '/about/story' },
  { labelKey: 'nav.values', href: '/about/values' },
]

export default function AboutPageHeader({ eyebrow, title, description }) {
  const { t } = useLanguage()
  return (
    <>
      <PageHead title={`${title} | IDI Seafood`} description={description} />
      <section className="relative overflow-hidden bg-ocean-deep pt-28 text-white sm:pt-32">
        <div className="absolute -right-32 top-0 h-96 w-96 rounded-full border border-white/8" />
        <div className="absolute right-8 top-20 h-48 w-48 rounded-full border border-white/8" />
        <div className="container relative pb-14 pt-10 sm:pb-16 sm:pt-14">
          <span className="text-xs font-extrabold uppercase tracking-[0.18em] text-coral-light">{eyebrow ?? t('nav.about')}</span>
          <h1 className="mt-4 max-w-4xl text-[clamp(2.4rem,5.5vw,4.8rem)] font-black leading-[1.02] tracking-[-0.05em] text-white">
            {title}
          </h1>
          <p className="mt-5 max-w-2xl text-base leading-8 text-white/68 sm:text-lg">{description}</p>
        </div>
        <div className="border-t border-white/10 bg-white/5">
          <nav aria-label={t('nav.about')} className="container flex gap-2 overflow-x-auto py-3">
            {ABOUT_NAV.map((item) => (
              <NavLink
                key={item.href}
                to={item.href}
                end={item.end}
                className={({ isActive }) => [
                  'shrink-0 rounded-lg px-4 py-2.5 text-sm font-bold transition',
                  isActive ? 'bg-white text-ocean-deep' : 'text-white/65 hover:bg-white/10 hover:text-white',
                ].join(' ')}
              >
                {t(item.labelKey)}
              </NavLink>
            ))}
          </nav>
        </div>
      </section>
    </>
  )
}
