import { Link as RouterLink, NavLink as RouterNavLink } from 'react-router'
import { usePublicRouting } from '@context/PublicRoutingContext'

export function Link({ to, ...props }) {
  const routing = usePublicRouting()
  const target = routing?.resolveLink(to) ?? (routing ? null : to)
  return target === null ? <span {...props} /> : <RouterLink to={target} {...props} />
}
export function NavLink({ to, ...props }) {
  const routing = usePublicRouting()
  const target = routing?.resolveLink(to) ?? (routing ? null : to)
  return target === null ? <span {...props} /> : <RouterNavLink to={target} {...props} />
}

export function Anchor({ href, ...props }) {
  const routing = usePublicRouting()
  const target = routing ? routing.resolveLink(href) : href
  return <a href={target ?? undefined} aria-disabled={target === null || undefined} {...props} />
}
