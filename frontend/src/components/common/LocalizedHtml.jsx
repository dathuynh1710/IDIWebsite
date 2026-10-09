import { useMemo } from 'react'
import { usePublicRouting } from '@context/PublicRoutingContext'

// CMS sanitization remains at the existing content boundary; only rewrite URLs here.
export default function LocalizedHtml({ html, ...props }) {
  const routing = usePublicRouting()
  const localized = useMemo(() => {
    if (!routing || !html) return html
    const template = document.createElement('template')
    template.innerHTML = html
    template.content.querySelectorAll('a[href]').forEach(anchor => {
      const href = routing.resolveLink(anchor.getAttribute('href'))
      if (href === null) { anchor.removeAttribute('href'); anchor.setAttribute('aria-disabled', 'true') }
      else anchor.setAttribute('href', href)
    })
    return template.innerHTML
  }, [html, routing])
  return <div {...props} dangerouslySetInnerHTML={{ __html: localized }} />
}
