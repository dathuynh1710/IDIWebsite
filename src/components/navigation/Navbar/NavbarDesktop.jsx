import { Link, useLocation } from 'react-router'
import { cn } from '@utils/cn'

const NAV_ITEMS = [
  {
    label: 'Sản phẩm',
    href: '/products',
    children: [
      { label: 'Cá tra phi lê',     href: '/products?category=pangasius-fillet' },
      { label: 'Cá tra cắt khúc',   href: '/products?category=pangasius-portions' },
      { label: 'Cá nguyên con',     href: '/products?category=whole-fish' },
      { label: 'Sản phẩm chế biến', href: '/products?category=value-added' },
    ],
  },
  {
    label: 'Về IDI',
    href: '/about',
    children: [
      { label: 'Lịch sử phát triển', href: '/about/story' },
      { label: 'Giá trị cốt lõi', href: '/about/values' },
    ],
  },
  {
    label: 'Nhà đầu tư',
    href: '/investors',
    children: [
      { label: 'Thông báo', href: '/investors' },
      { label: 'Báo cáo tài chính', href: '/investors/financials' },
      { label: 'Báo cáo thường niên', href: '/investors/annual-reports' },
      { label: 'Đại hội cổ đông', href: '/investors/agm' },
      { label: 'Trái phiếu', href: '/investors/green-bond' },
    ],
  },
  { label: 'Tin tức', href: '/news', children: null },
]

export default function NavbarDesktop({ scrolled }) {
  const location = useLocation()

  const isActive = (href) =>
    href === '/'
      ? location.pathname === '/'
      : location.pathname.startsWith(href)

  return (
    <nav className="hidden xl:flex items-center gap-1" aria-label="Điều hướng chính">
      {NAV_ITEMS.map((item) => (
        <div key={item.href} className="group relative">
          <Link
            to={item.href}
            className={cn(
              'flex items-center gap-1 px-3 py-2 text-[13px] font-semibold rounded-md transition-colors duration-150',
              scrolled
                ? isActive(item.href)
                  ? 'text-ocean-deep'
                  : 'text-slate hover:text-ocean-deep'
                : isActive(item.href)
                  ? 'text-coral-gold'
                  : 'text-white/90 hover:text-white',
            )}
          >
            {item.label}
            {item.children && (
              <svg className="w-3 h-3 opacity-60 transition-transform duration-200 group-hover:rotate-180" viewBox="0 0 12 12" fill="none">
                <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            )}
          </Link>

          {/* Dropdown */}
          {item.children && (
            <div className={cn(
              'absolute top-full left-1/2 -translate-x-1/2 pt-2',
              'invisible opacity-0 group-hover:visible group-hover:opacity-100',
              'transition-all duration-200 ease-out',
              'translate-y-1 group-hover:translate-y-0',
            )}>
              <div className="bg-white rounded-xl shadow-[0_10px_40px_-8px_rgba(0,0,0,0.15)] border border-light-mist p-2 min-w-[200px]">
                {item.children.map((child) => (
                  <Link
                    key={child.href}
                    to={child.href}
                    className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate hover:text-ocean-deep hover:bg-arctic-white rounded-lg transition-colors duration-150"
                  >
                    <span className="w-1.5 h-1.5 rounded-full bg-seafoam flex-shrink-0" />
                    {child.label}
                  </Link>
                ))}
              </div>
            </div>
          )}
        </div>
      ))}
    </nav>
  )
}
