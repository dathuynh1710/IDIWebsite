import { useId, useRef, useState } from 'react'
import { useLocation } from 'react-router'
import { cn } from '@utils/cn'
import { useLanguage } from '@hooks/useLanguage'
import { MenuLink } from '../MenuTree'

function DesktopItem({ item, scrolled, depth = 0 }) {
  const [open, setOpen] = useState(false)
  const toggleRef = useRef(null)
  const submenuId = useId()
  const location = useLocation()
  const { t } = useLanguage()
  const children = item.children || []
  const root = depth === 0
  const active = item.href && (item.href === '/' ? location.pathname === '/' : location.pathname.startsWith(item.href))
  return (
    <li className="relative" onMouseEnter={() => setOpen(true)} onMouseLeave={() => setOpen(false)}
      onBlur={event => { if (!event.currentTarget.contains(event.relatedTarget)) setOpen(false) }}
      onKeyDown={event => {
        if (event.key === 'Escape' && open) {
          event.stopPropagation()
          setOpen(false)
          toggleRef.current?.focus()
        }
      }}>
      <div className={cn('relative rounded-lg', !root && 'hover:bg-arctic-white')}>
        <MenuLink item={item} aria-current={active ? 'page' : undefined} className={cn(
          'flex items-center gap-2 px-3 py-2 transition-colors duration-150',
          children.length > 0 && 'pr-7',
          root ? 'gap-1 rounded-md text-[13px] font-semibold uppercase tracking-[0.035em]' : 'text-sm font-medium text-slate hover:text-ocean-deep',
          root && (scrolled ? (active ? 'text-ocean-deep' : 'text-slate hover:text-ocean-deep') : (active ? 'text-coral-gold' : 'text-white/90 hover:text-white')),
        )}>
          {!root && <span className="h-1.5 w-1.5 shrink-0 rounded-full bg-seafoam" />}
          {item.label}
        </MenuLink>
        {children.length > 0 && <button type="button" ref={toggleRef} aria-expanded={open} aria-controls={submenuId}
          aria-label={`${item.label}: ${open ? t('nav.closeMenu') : t('nav.openMenu')}`}
          onClick={() => setOpen(value => !value)}
          className={cn('absolute right-1 top-1/2 -translate-y-1/2 rounded p-1 focus-visible:outline-2 focus-visible:outline-seafoam', root && !scrolled ? 'text-white/90' : 'text-slate')}>
          <svg aria-hidden="true" className={cn('h-3 w-3 opacity-60 transition-transform duration-200', open && 'rotate-180')} viewBox="0 0 12 12" fill="none">
            <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>}
      </div>
      {children.length > 0 && <div id={submenuId} hidden={!open} className={root ? 'absolute top-full left-1/2 z-20 -translate-x-1/2 pt-2' : 'pl-4'}>
        <ul className={root ? 'isolate min-w-[200px] max-h-[70vh] max-w-[85vw] overflow-auto rounded-xl border border-light-mist bg-white p-2 shadow-[0_10px_40px_-8px_rgba(0,0,0,0.15)]' : ''}>
          {children.map(child => <DesktopItem key={child.id ?? child.href} item={child} scrolled depth={depth + 1} />)}
        </ul>
      </div>}
    </li>
  )
}

export function NavbarMobileItems({ items, depth = 0 }) {
  const location = useLocation()
  return <ul className={depth ? 'grid grid-cols-1 gap-1 pb-3 pl-4' : 'flex flex-col gap-1'}>
    {items.map(item => <li key={item.id ?? item.href} className={depth ? '' : 'border-b border-white/10'}>
      <MenuLink item={item} className={cn(
        depth ? 'block py-1.5 text-sm font-medium text-white/55 hover:text-white transition-colors' : 'block py-3 text-lg font-semibold uppercase tracking-[0.035em] text-white/80 transition-all duration-200 hover:pl-2 hover:text-white',
        item.href && item.href !== '/' && location.pathname.startsWith(item.href) && 'text-coral-gold',
      )} />
      {item.children?.length > 0 && <NavbarMobileItems items={item.children} depth={depth + 1} />}
    </li>)}
  </ul>
}

export default function NavbarDesktop({ scrolled, items, loading }) {
  const { t } = useLanguage()
  const location = useLocation()
  return <nav className="hidden xl:flex items-center gap-1" aria-label={t('nav.main')} aria-busy={loading}>
    <ul key={location.pathname + location.search} className="flex items-center gap-1">
      {items.map(item => <DesktopItem key={item.id ?? item.href} item={item} scrolled={scrolled} />)}
    </ul>
  </nav>
}
