import { Outlet } from 'react-router'
import { INVESTOR_NAV } from '@data/navigation'
/**
 * InvestorLayout — Adds a sidebar nav for the /investors/* section.
 * Rendered as a nested route under RootLayout.
 * TODO: Implement sidebar UI
 */
export default function InvestorLayout() {
  return (
    <div className="container section-padding">
      <div className="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-12">
        <aside>
          {/* TODO: InvestorSidebar using INVESTOR_NAV */}
        </aside>
        <main>
          <Outlet />
        </main>
      </div>
    </div>
  )
}
