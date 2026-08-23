export const NAV_ITEMS = [
  { id: 'about', labelKey: 'nav.about', href: '/about', children: [
    { id: 'story', labelKey: 'nav.story', href: '/about/story' },
    { id: 'values', labelKey: 'nav.values', href: '/about/values' },
  ] },
  { id: 'products', labelKey: 'nav.products', href: '/products', isMega: true, children: [
    { id: 'pangasius-fillet', labelKey: 'productNav.fillet', href: '/products?category=pangasius-fillet', descriptionKey: 'productNav.filletDescription', icon: 'fillet' },
    { id: 'pangasius-portions', labelKey: 'productNav.portions', href: '/products?category=pangasius-portions', descriptionKey: 'productNav.portionsDescription', icon: 'portions' },
    { id: 'whole-fish', labelKey: 'productNav.whole', href: '/products?category=whole-fish', descriptionKey: 'productNav.wholeDescription', icon: 'whole' },
    { id: 'value-added', labelKey: 'productNav.valueAdded', href: '/products?category=value-added', descriptionKey: 'productNav.valueAddedDescription', icon: 'value-added' },
  ], featured: { labelKey: 'productNav.catalog', href: '/products#catalog', ctaKey: 'productNav.catalogCta' } },
  { id: 'sustainability', labelKey: 'nav.sustainability', href: '/sustainability', children: null },
  { id: 'investors', labelKey: 'nav.investors', href: '/investors', children: [
    { id: 'investor-overview', labelKey: 'nav.overview', href: '/investors' },
    { id: 'announcements', labelKey: 'nav.announcements', href: '/investors/announcements' },
    { id: 'financials', labelKey: 'nav.financials', href: '/investors/financials' },
    { id: 'annual-reports', labelKey: 'nav.annualReports', href: '/investors/annual-reports' },
    { id: 'agm', labelKey: 'nav.agm', href: '/investors/agm' },
    { id: 'green-bond', labelKey: 'nav.greenBond', href: '/investors/green-bond' },
  ] },
  { id: 'news', labelKey: 'nav.news', href: '/news', children: null },
  { id: 'recipes', labelKey: 'nav.recipes', href: '/recipes', children: null },
  { id: 'careers', labelKey: 'nav.careers', href: '/careers', children: null },
]

export const INVESTOR_NAV = NAV_ITEMS.find(item => item.id === 'investors').children

export const FOOTER_LINKS = {
  products: { titleKey: 'nav.products', links: NAV_ITEMS.find(item => item.id === 'products').children },
  company: { titleKey: 'footer.company', links: [
    { labelKey: 'nav.story', href: '/about/story' }, { labelKey: 'nav.sustainability', href: '/sustainability' },
    { labelKey: 'nav.recipes', href: '/recipes' }, { labelKey: 'nav.careers', href: '/careers' },
  ] },
  investors: { titleKey: 'nav.investors', links: INVESTOR_NAV },
}

export function localizedNavItems(items, t) {
  return items.map(item => ({
    ...item,
    label: t(item.labelKey),
    description: item.descriptionKey ? t(item.descriptionKey) : undefined,
    children: item.children ? localizedNavItems(item.children, t) : item.children,
    featured: item.featured ? { ...item.featured, label: t(item.featured.labelKey), cta: t(item.featured.ctaKey) } : undefined,
  }))
}
