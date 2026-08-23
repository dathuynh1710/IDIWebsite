import { NavLink, Outlet } from 'react-router'
import { INVESTOR_NAV, localizedNavItems } from '@data/navigation'
import { useLanguage } from '@hooks/useLanguage'

export default function InvestorLayout() {
  const { t } = useLanguage()
  const investorNav = localizedNavItems(INVESTOR_NAV, t)
  return (
    <div className="bg-[radial-gradient(circle_at_100%_0%,rgba(26,147,111,0.11),transparent_30rem)]">
      <div className="container pb-20 pt-28 sm:pt-32">
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-[245px_minmax(0,1fr)] lg:gap-12">
          <aside className="lg:sticky lg:top-28 lg:self-start">
            <div className="overflow-hidden rounded-2xl border border-light-mist bg-white shadow-[0_20px_50px_-42px_rgba(11,37,69,0.75)]">
              <div className="bg-ocean-deep px-5 py-5 text-white">
                <span className="text-[10px] font-extrabold uppercase tracking-[0.16em] text-coral-light">
                  IDI · HOSE
                </span>
                <strong className="mt-1 block text-lg">{t('nav.investors')}</strong>
              </div>
              <nav aria-label={t('nav.investorMenu')} className="flex gap-2 overflow-x-auto p-2 lg:block">
                {investorNav.map((item) => (
                  <NavLink
                    key={item.id}
                    to={item.href}
                    end={item.href === '/investors'}
                    className={({ isActive }) => [
                      'flex min-h-11 shrink-0 items-center justify-between gap-4 rounded-xl px-3.5 py-3 text-sm font-bold transition',
                      isActive
                        ? 'bg-seafoam-pale text-seafoam'
                        : 'text-slate hover:bg-arctic-white hover:text-ocean-deep',
                    ].join(' ')}
                  >
                    <span>{item.label}</span>
                    <span aria-hidden="true" className="hidden text-base lg:inline">›</span>
                  </NavLink>
                ))}
              </nav>
              <div className="hidden border-t border-light-mist p-5 lg:block">
                <span className="text-[10px] font-extrabold uppercase tracking-[0.12em] text-storm-grey">{t('nav.irContact')}</span>
                <a href="mailto:info@idiseafood.com" className="mt-2 block break-all text-sm font-bold text-ocean-deep hover:text-seafoam">
                  info@idiseafood.com
                </a>
              </div>
            </div>
          </aside>
          <main className="min-w-0">
            <Outlet />
          </main>
        </div>
      </div>
    </div>
  )
}
