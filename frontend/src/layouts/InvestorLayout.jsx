import { NavLink, Outlet, useLocation, useNavigate } from 'react-router'
import { INVESTOR_NAV, localizedNavItems } from '@data/navigation'
import { useLanguage } from '@hooks/useLanguage'

export default function InvestorLayout() {
  const { t } = useLanguage()
  const location = useLocation()
  const navigate = useNavigate()
  const investorNav = localizedNavItems(INVESTOR_NAV, t)
  const activePath = investorNav.find(item => (
    item.href === '/investors'
      ? location.pathname === item.href
      : location.pathname.startsWith(item.href)
  ))?.href ?? '/investors'

  return (
    <div className="min-h-[70vh] bg-arctic-white">
      <div className="container pb-16 pt-24 sm:pt-28 lg:pb-20">
        <div className="mb-5 lg:hidden">
          <label htmlFor="investor-section" className="mb-2 block text-[11px] font-bold uppercase tracking-[0.12em] text-storm-grey">
            {t('nav.investorMenu')}
          </label>
          <select
            id="investor-section"
            value={activePath}
            onChange={event => navigate(event.target.value)}
            className="min-h-12 w-full rounded-lg border border-mist-mid bg-white px-4 text-sm font-semibold text-ocean-deep outline-none transition focus:border-seafoam"
          >
            {investorNav.map(item => <option key={item.id} value={item.href}>{item.label}</option>)}
          </select>
        </div>

        <div className="grid grid-cols-1 gap-8 lg:grid-cols-[210px_minmax(0,1fr)] lg:gap-8 xl:grid-cols-[220px_minmax(0,1fr)] xl:gap-10">
          <aside className="hidden lg:sticky lg:top-24 lg:block lg:self-start">
            <div className="border-l border-light-mist pl-3">
              <nav aria-label={t('nav.investorMenu')} className="space-y-1">
                {investorNav.map((item) => (
                  <NavLink
                    key={item.id}
                    to={item.href}
                    end={item.href === '/investors'}
                    className={({ isActive }) => [
                      'relative flex min-h-10 items-center rounded-lg px-3 py-2.5 text-sm font-semibold transition-colors',
                      isActive
                        ? 'bg-seafoam-pale text-[#14785b] before:absolute before:-left-[13px] before:h-6 before:w-0.5 before:rounded-full before:bg-seafoam'
                        : 'text-slate hover:bg-white hover:text-ocean-deep',
                    ].join(' ')}
                  >
                    <span>{item.label}</span>
                  </NavLink>
                ))}
              </nav>
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
