import { useEffect, useState } from 'react'
import { Link, useLocation } from 'react-router'
import { cn } from '@utils/cn'
import NavbarBrand from './NavbarBrand'
import NavbarDesktop from './NavbarDesktop'

const MOBILE_MENU_LINKS = [
  {
    label: 'Về IDI',
    href: '/about',
    children: [
      { label: 'Lịch sử phát triển', href: '/about/story' },
      { label: 'Giá trị cốt lõi', href: '/about/values' },
    ],
  },
  { label: 'Sản phẩm', href: '/products' },
  { label: 'Phát triển bền vững', href: '/sustainability' },
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
  { label: 'Tin tức',    href: '/news' },
  { label: 'Tuyển dụng', href: '/careers' },
  { label: 'Liên hệ',    href: '/contact' },
]

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false)
  const [mobileOpen, setMobileOpen] = useState(false)
  const location = useLocation()

  // Detect scroll to switch from transparent → solid
  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 60)
    window.addEventListener('scroll', onScroll, { passive: true })
    onScroll() // Run on mount
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  // Close mobile menu on route change
  useEffect(() => {
    setMobileOpen(false)
  }, [location.pathname])

  // Lock body scroll when mobile menu is open
  useEffect(() => {
    document.body.style.overflow = mobileOpen ? 'hidden' : ''
    return () => { document.body.style.overflow = '' }
  }, [mobileOpen])

  const isHeroPage = location.pathname === '/'

  return (
    <>
      <header
        className={cn(
          'fixed top-0 inset-x-0 z-[100] transition-all duration-300',
          scrolled || !isHeroPage
            ? 'bg-white/95 backdrop-blur-md shadow-[0_1px_0_0_rgba(0,0,0,0.08)]'
            : 'bg-transparent',
        )}
      >
        <div className="container">
          <div className="flex items-center justify-between h-[72px]">
            {/* Brand */}
            <NavbarBrand />

            {/* Desktop Nav */}
            <NavbarDesktop scrolled={scrolled || !isHeroPage} />

            {/* Right: Language + CTA + Hamburger */}
            <div className="flex items-center gap-3">
              {/* Language switcher */}
              <div className={cn(
                'hidden xl:flex items-center gap-1 text-xs font-semibold',
                scrolled || !isHeroPage ? 'text-storm-grey' : 'text-white/80',
              )}>
                <button className={cn(
                  'px-2 py-1 rounded transition-colors',
                  scrolled || !isHeroPage
                    ? 'text-ocean-deep'
                    : 'text-white',
                )}>VI</button>
                <span className="opacity-30">|</span>
                <button className="px-2 py-1 rounded hover:opacity-100 opacity-60 transition-opacity">EN</button>
                <span className="opacity-30">|</span>
                <button className="px-2 py-1 rounded hover:opacity-100 opacity-60 transition-opacity">中文</button>
              </div>

              {/* Contact CTA */}
              <Link
                to="/contact"
                className={cn(
                  'hidden xl:inline-flex btn px-4 py-2 text-sm uppercase tracking-[0.035em]',
                  scrolled || !isHeroPage ? 'btn-primary' : 'btn-gold',
                )}
              >
                Liên hệ
              </Link>

              {/* Mobile hamburger */}
              <button
                onClick={() => setMobileOpen(prev => !prev)}
                className={cn(
                  'xl:hidden flex flex-col justify-center items-center w-10 h-10 gap-1.5',
                  scrolled || !isHeroPage ? 'text-ocean-deep' : 'text-white',
                )}
                aria-label={mobileOpen ? 'Đóng menu' : 'Mở menu'}
                aria-expanded={mobileOpen}
              >
                <span className={cn('block w-6 h-0.5 bg-current transition-all duration-300', mobileOpen && 'rotate-45 translate-y-2')} />
                <span className={cn('block w-6 h-0.5 bg-current transition-all duration-300', mobileOpen && 'opacity-0')} />
                <span className={cn('block w-6 h-0.5 bg-current transition-all duration-300', mobileOpen && '-rotate-45 -translate-y-2')} />
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Mobile Menu Overlay */}
      {mobileOpen && (
        <div className="fixed inset-0 z-[99] flex flex-col xl:hidden animate-fade-in">
          {/* Backdrop */}
          <div
            className="absolute inset-0 bg-ocean-deep/95 backdrop-blur-lg"
            onClick={() => setMobileOpen(false)}
          />

          {/* Drawer content */}
          <div className="relative z-10 flex flex-col h-full pt-[72px] overflow-y-auto animate-slide-in-left">
            <nav className="container py-8 flex flex-col gap-1">
              {MOBILE_MENU_LINKS.map((link) => (
                <div key={link.href} className="border-b border-white/10">
                  <Link
                    to={link.href}
                    className={cn(
                      'block py-3 text-lg font-semibold uppercase tracking-[0.035em] text-white/80 transition-all duration-200 hover:pl-2 hover:text-white',
                      location.pathname.startsWith(link.href) && link.href !== '/' && 'text-coral-gold',
                    )}
                  >
                    {link.label}
                  </Link>
                  {link.children && (
                    <div className="grid grid-cols-1 gap-1 pb-3 pl-4">
                      {link.children.map((child) => (
                        <Link
                          key={`${child.href}-${child.label}`}
                          to={child.href}
                          className="py-1.5 text-sm font-medium text-white/55 hover:text-white transition-colors"
                        >
                          {child.label}
                        </Link>
                      ))}
                    </div>
                  )}
                </div>
              ))}
            </nav>

            {/* Mobile CTA */}
            <div className="container pb-8 mt-auto flex flex-col gap-3">
              <Link to="/contact" className="btn btn-gold w-full text-center uppercase tracking-[0.035em]">
                Yêu cầu báo giá
              </Link>
              <div className="flex justify-center gap-4 text-sm text-white/50">
                <button className="hover:text-white transition-colors">VI</button>
                <span>|</span>
                <button className="hover:text-white transition-colors">English</button>
                <span>|</span>
                <button className="hover:text-white transition-colors">中文</button>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  )
}
