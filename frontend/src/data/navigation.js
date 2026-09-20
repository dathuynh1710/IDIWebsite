export const NAV_ITEMS = [
  { id: 'about', labelKey: 'nav.about', href: '/about', children: [
    { id: 'story', labelKey: 'nav.story', href: '/about/story' },
    { id: 'values', labelKey: 'nav.values', href: '/about/values' },
  ] },
  { id: 'products', labelKey: 'nav.products', href: '/products', isMega: true, children: [], featured: { labelKey: 'productNav.catalog', href: '/products#catalog', ctaKey: 'productNav.catalogCta' } },
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
    label: item.label ?? t(item.labelKey),
    description: item.description ?? (item.descriptionKey ? t(item.descriptionKey) : undefined),
    children: item.children ? localizedNavItems(item.children, t) : item.children,
    featured: item.featured ? { ...item.featured, label: t(item.featured.labelKey), cta: t(item.featured.ctaKey) } : undefined,
  }))
}

export function withProductCategories(items, categories) {
  if (!categories.length) return items

  return items.map(item => item.id === 'products'
    ? {
        ...item,
        children: categories.map(category => ({
          id: `product-category-${category.id}`,
          label: category.name,
          description: category.description,
          href: `/products?category=${encodeURIComponent(category.slug)}`,
        })),
      }
    : item)
}
