import { useId, useRef, useState } from 'react'
import { useLocation } from 'react-router'
import { Link } from '@components/navigation/LocalizedLink'
import { useLanguage } from '@hooks/useLanguage'
import { cn } from '@utils/cn'

export function MenuLink({ item, children, ...props }) {
  const href = item.href
  const internal = typeof href === 'string' && /^\/(?!\/)/.test(href) && !/[\\\s]/.test(href)
  const external = typeof href === 'string' && /^https?:\/\//i.test(href)
  if (external) return <a href={href} {...props}>{children ?? item.label}</a>
  if (internal) return <Link to={href} {...props}>{children ?? item.label}</Link>
  return <span aria-disabled="true" {...props}>{children ?? item.label}</span>
}

function MenuNode({ item, mode, depth, scrolled }) {
  const [open, setOpen] = useState(false)
  const toggle = useRef(null)
  const id = useId()
  const location = useLocation()
  const { t } = useLanguage()
  const children = item.children || []
  const desktopRoot = mode === 'desktop' && depth === 0
  const dark = mode === 'mobile' || mode === 'footer' || (desktopRoot && !scrolled)
  const active = item.href && (location.pathname + location.search === item.href)
  return (
    <li className="relative min-w-0" onKeyDown={event => {
      if (event.key === 'Escape' && open) {
        event.stopPropagation()
        setOpen(false)
        toggle.current?.focus()
      }
    }} onBlur={event => {
      if (mode === 'desktop' && !event.currentTarget.contains(event.relatedTarget)) setOpen(false)
    }}>
      <div className={cn('flex items-center rounded-lg', dark ? 'text-white/80 hover:text-white' : 'text-slate hover:bg-arctic-white hover:text-ocean-deep', active && 'font-bold')}>
        <MenuLink item={item} aria-current={active ? 'page' : undefined} className={cn('min-w-0 flex-1 px-3 py-2 text-sm font-semibold', desktopRoot && 'text-[13px] uppercase tracking-[0.035em]')} />
        {children.length > 0 && <button ref={toggle} type="button" aria-expanded={open} aria-controls={id}
          aria-label={`${item.label}: ${open ? t('nav.closeMenu') : t('nav.openMenu')}`}
          className="shrink-0 rounded p-2 focus-visible:outline-2 focus-visible:outline-seafoam" onClick={() => setOpen(value => !value)}>
          <span aria-hidden="true">{open ? '−' : '+'}</span>
        </button>}
      </div>
      {children.length > 0 && <ul id={id} hidden={!open} className={cn(
        desktopRoot ? 'absolute left-0 top-full z-20 max-h-[70vh] w-72 max-w-[85vw] overflow-auto rounded-xl border border-light-mist bg-white p-2 shadow-xl' : 'ml-3 border-l border-current/15 pl-2',
      )}>
        {children.map(child => <MenuNode key={child.id ?? child.href} item={child} mode={mode} depth={depth + 1} scrolled={scrolled} />)}
      </ul>}
    </li>
  )
}

export default function MenuTree({ items, mode = 'mobile', scrolled = true }) {
  const location = useLocation()
  return <ul key={location.pathname + location.search} className={mode === 'desktop' ? 'flex items-center gap-1' : 'space-y-1'}>
    {items.map(item => <MenuNode key={item.id ?? item.href} item={item} mode={mode} depth={0} scrolled={scrolled} />)}
  </ul>
}
