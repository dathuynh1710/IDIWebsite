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
    label: 'Sản phẩm',
    href: '/products',
    isMega: true, // Renders as full mega-menu panel
    children: [
      {
        id: 'pangasius-fillet',
        label: 'Cá tra phi lê',
        href: '/products?category=pangasius-fillet',
        description: 'Không da, có da và nhiều quy cách chỉnh hình',
        icon: 'fillet',
      },
      {
        id: 'pangasius-portions',
        label: 'Cá tra cắt khúc',
        href: '/products?category=pangasius-portions',
        description: 'Cắt theo khẩu phần cho dịch vụ thực phẩm',
        icon: 'portions',
      },
      {
        id: 'whole-fish',
        label: 'Cá nguyên con',
        href: '/products?category=whole-fish',
        description: 'Dạng bướm, HGT và làm sạch',
        icon: 'whole',
      },
      {
        id: 'value-added',
        label: 'Sản phẩm chế biến',
        href: '/products?category=value-added',
        description: 'Tẩm bột, tẩm ướp và cắt theo yêu cầu',
        icon: 'value-added',
      },
    ],
    // Mega menu featured callout (right panel)
    featured: {
      label: 'Tải danh mục sản phẩm',
      href: '/products#catalog',
      cta: 'Tải PDF →',
    },
  },

  {
    id: 'about',
    label: 'Về IDI',
    href: '/about',
    children: [
      { id: 'about-overview', label: 'Thông điệp công ty', href: '/about' },
      { id: 'story', label: 'Lịch sử phát triển', href: '/about/story' },
      { id: 'values', label: 'Giá trị cốt lõi', href: '/about/values' },
    ],
  },

  {
    id: 'investors',
    label: 'Nhà đầu tư',
    href: '/investors',
    children: [
      { id: 'announcements', label: 'Thông báo', href: '/investors' },
      { id: 'financials', label: 'Báo cáo tài chính', href: '/investors/financials' },
      { id: 'annual-reports', label: 'Báo cáo thường niên', href: '/investors/annual-reports' },
      { id: 'agm', label: 'Đại hội cổ đông', href: '/investors/agm' },
      { id: 'green-bond', label: 'Trái phiếu', href: '/investors/green-bond' },
    ],
  },

  {
    id: 'news',
    label: 'Tin tức',
    href: '/news',
    children: null,
  },
]

// Investor sidebar navigation (used in InvestorLayout)
export const INVESTOR_NAV = [
  { id: 'announcements', label: 'Thông báo', href: '/investors' },
  { id: 'financials', label: 'Báo cáo tài chính', href: '/investors/financials' },
  { id: 'annual-reports', label: 'Báo cáo thường niên', href: '/investors/annual-reports' },
  { id: 'agm', label: 'Đại hội cổ đông', href: '/investors/agm' },
  { id: 'green-bond', label: 'Trái phiếu', href: '/investors/green-bond' },
]

// Footer quick links (columns)
export const FOOTER_LINKS = {
  products: {
    title: 'Sản phẩm',
    links: [
      { label: 'Cá tra phi lê', href: '/products?category=pangasius-fillet' },
      { label: 'Cá tra cắt khúc', href: '/products?category=pangasius-portions' },
      { label: 'Cá nguyên con', href: '/products?category=whole-fish' },
      { label: 'Sản phẩm chế biến', href: '/products?category=value-added' },
    ],
  },
  company: {
    title: 'Công ty',
    links: [
      { label: 'Lịch sử phát triển', href: '/about/story' },
      { label: 'Năng lực sản xuất', href: '/manufacturing' },
      { label: 'Tuyển dụng', href: '/careers' },
    ],
  },
  quality: {
    title: 'Chất lượng',
    links: [
      { label: 'Chứng nhận', href: '/quality' },
      { label: 'Tiêu chuẩn', href: '/quality#standards' },
      { label: 'Truy xuất nguồn gốc', href: '/quality#traceability' },
    ],
  },
  investors: {
    title: 'Nhà đầu tư',
    links: [
      { label: 'Thông báo', href: '/investors' },
      { label: 'Báo cáo tài chính', href: '/investors/financials' },
      { label: 'Báo cáo thường niên', href: '/investors/annual-reports' },
      { label: 'Đại hội cổ đông', href: '/investors/agm' },
      { label: 'Trái phiếu', href: '/investors/green-bond' },
    ],
  },
}
