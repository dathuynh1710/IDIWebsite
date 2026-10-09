import { usePublicRouting } from '@context/PublicRoutingContext'
import { Outlet, useLocation, useNavigate } from 'react-router'
import { useMenu } from '@context/MenuContext'
import MenuTree from '@components/navigation/MenuTree'
import { useLanguage } from '@hooks/useLanguage'

export default function InvestorLayout() {
  const { t } = useLanguage()
  const routing = usePublicRouting()
  const investorPath = routing?.resolveLink('/investors') || '/investors'
  const location = useLocation()
  const navigate = useNavigate()
  const { items } = useMenu('main')
  const investorNav = items.find(item => item.href === investorPath)?.children || []
  const flatten = nodes => nodes.flatMap(node => [node, ...flatten(node.children || [])])
  const options = flatten(investorNav).filter(item => item.href)
  const activePath = options.find(item => (
    item.href === investorPath
      ? location.pathname === item.href
      : location.pathname.startsWith(item.href)
  ))?.href ?? investorPath

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
            onChange={event => /^https?:\/\//.test(event.target.value) ? window.location.assign(event.target.value) : navigate(event.target.value)}
            className="min-h-12 w-full rounded-lg border border-mist-mid bg-white px-4 text-sm font-semibold text-ocean-deep outline-none transition focus:border-seafoam"
          >
            {options.map(item => <option key={item.id} value={item.href}>{item.label}</option>)}
          </select>
        </div>

        <div className="grid grid-cols-1 gap-8 lg:grid-cols-[210px_minmax(0,1fr)] lg:gap-8 xl:grid-cols-[220px_minmax(0,1fr)] xl:gap-10">
          <aside className="hidden lg:sticky lg:top-24 lg:block lg:self-start">
            <div className="border-l border-light-mist pl-3">
              <nav aria-label={t('nav.investorMenu')} className="space-y-1">
                <MenuTree items={investorNav} mode="sidebar" />
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
