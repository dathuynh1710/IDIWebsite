/**
 * navigation.js — Complete navigation menu structure.
 * Single source of truth for desktop mega-menu and mobile menu.
 * Imported by Navbar and MobileMenu components.
 *
 * Structure: Array of top-level nav items.
 * Each item can have children[] for dropdown/mega-menu panels.
 */

export const NAV_ITEMS = [
  {
    id: 'products',
    label: 'Products',
    href: '/products',
    isMega: true, // Renders as full mega-menu panel
    children: [
      {
        id: 'pangasius-fillet',
        label: 'Pangasius Fillet',
        href: '/products?category=pangasius-fillet',
        description: 'Skinless, skin-on, trimmed varieties',
        icon: 'fillet',
      },
      {
        id: 'pangasius-portions',
        label: 'Pangasius Portions',
        href: '/products?category=pangasius-portions',
        description: 'Portion-controlled cuts for food service',
        icon: 'portions',
      },
      {
        id: 'whole-fish',
        label: 'Whole Fish',
        href: '/products?category=whole-fish',
        description: 'Butterfly cut, HGT varieties',
        icon: 'whole',
      },
      {
        id: 'value-added',
        label: 'Value Added',
        href: '/products?category=value-added',
        description: 'Breaded, marinated, specialty cuts',
        icon: 'value-added',
      },
    ],
    // Mega menu featured callout (right panel)
    featured: {
      label: 'Download Product Catalog',
      href: '/products#catalog',
      cta: 'Get PDF →',
    },
  },

  {
    id: 'manufacturing',
    label: 'Manufacturing',
    href: '/manufacturing',
    children: null, // No dropdown — direct link
  },

  {
    id: 'quality',
    label: 'Quality',
    href: '/quality',
    children: null,
  },

  {
    id: 'sustainability',
    label: 'Sustainability',
    href: '/sustainability',
    children: [
      { id: 'environment', label: 'Environment',       href: '/sustainability/environment' },
      { id: 'social',      label: 'Social Impact',     href: '/sustainability/social' },
      { id: 'reports',     label: 'ESG Reports',       href: '/sustainability/reports' },
      { id: 'green-bond',  label: 'Green Bond',        href: '/investors/green-bond' },
    ],
  },

  {
    id: 'about',
    label: 'About',
    href: '/about',
    children: [
      { id: 'story',      label: 'Our Story',        href: '/about/story' },
      { id: 'leadership', label: 'Leadership',        href: '/about/leadership' },
      { id: 'values',     label: 'Mission & Values',  href: '/about/values' },
    ],
  },

  {
    id: 'investors',
    label: 'Investors',
    href: '/investors',
    children: [
      { id: 'financials',     label: 'Financial Reports',  href: '/investors/financials' },
      { id: 'annual-reports', label: 'Annual Reports',     href: '/investors/annual-reports' },
      { id: 'agm',            label: 'AGM Documents',      href: '/investors/agm' },
      { id: 'green-bond',     label: 'Green Bond',         href: '/investors/green-bond' },
    ],
  },

  {
    id: 'news',
    label: 'News',
    href: '/news',
    children: null,
  },
]

// Investor sidebar navigation (used in InvestorLayout)
export const INVESTOR_NAV = [
  { id: 'overview',        label: 'Overview',          href: '/investors' },
  { id: 'financials',      label: 'Financial Reports', href: '/investors/financials' },
  { id: 'annual-reports',  label: 'Annual Reports',    href: '/investors/annual-reports' },
  { id: 'agm',             label: 'AGM Documents',     href: '/investors/agm' },
  { id: 'green-bond',      label: 'Green Bond',        href: '/investors/green-bond' },
]

// Footer quick links (columns)
export const FOOTER_LINKS = {
  products: {
    title: 'Products',
    links: [
      { label: 'Pangasius Fillet',   href: '/products?category=pangasius-fillet' },
      { label: 'Pangasius Portions', href: '/products?category=pangasius-portions' },
      { label: 'Whole Fish',         href: '/products?category=whole-fish' },
      { label: 'Value Added',        href: '/products?category=value-added' },
    ],
  },
  company: {
    title: 'Company',
    links: [
      { label: 'Our Story',     href: '/about/story' },
      { label: 'Leadership',    href: '/about/leadership' },
      { label: 'Manufacturing', href: '/manufacturing' },
      { label: 'Careers',       href: '/careers' },
    ],
  },
  quality: {
    title: 'Quality',
    links: [
      { label: 'Certifications', href: '/quality' },
      { label: 'Standards',      href: '/quality#standards' },
      { label: 'Traceability',   href: '/quality#traceability' },
    ],
  },
  investors: {
    title: 'Investors',
    links: [
      { label: 'Financial Reports', href: '/investors/financials' },
      { label: 'Annual Reports',    href: '/investors/annual-reports' },
      { label: 'Green Bond',        href: '/investors/green-bond' },
    ],
  },
}
