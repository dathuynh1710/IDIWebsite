/* ═══════════════════════════════════════════════════════════════
   Application-wide constants
   Import named exports to avoid importing the entire module.
   Usage: import { SITE_NAME, COMPANY_INFO } from '@utils/constants'
   ═══════════════════════════════════════════════════════════════ */

// ── Site Identity
export const SITE_NAME     = 'IDI Seafood'
export const SITE_TAGLINE  = 'Nhà xuất khẩu cá tra hàng đầu Việt Nam'
export const SITE_URL      = import.meta.env.VITE_SITE_URL ?? 'https://idiseafood.com'
export const SITE_FOUNDED  = 1997

// ── Supported Languages
export const LANGUAGES = Object.freeze({
  EN: 'en',
  VI: 'vi',
  ZH: 'zh',
})

export const DEFAULT_LANGUAGE = LANGUAGES.VI

export const LANGUAGE_LABELS = Object.freeze({
  [LANGUAGES.EN]: 'English',
  [LANGUAGES.VI]: 'Tiếng Việt',
  [LANGUAGES.ZH]: '中文',
})

// ── Responsive Breakpoints (must mirror Tailwind config)
export const BREAKPOINTS = Object.freeze({
  SM:  640,
  MD:  768,
  LG:  1024,
  XL:  1280,
  '2XL': 1536,
})

// ── Product Categories (used in routing + filtering)
export const PRODUCT_CATEGORIES = Object.freeze({
  FILLET:      { id: 'pangasius-fillet',   label: 'Pangasius Fillet'   },
  PORTIONS:    { id: 'pangasius-portions', label: 'Pangasius Portions'  },
  WHOLE:       { id: 'whole-fish',         label: 'Whole Fish'          },
  VALUE_ADDED: { id: 'value-added',        label: 'Value Added'         },
})

// ── Certifications
export const CERT_IDS = Object.freeze({
  ASC:        'asc',
  BRC:        'brc',
  GLOBAL_GAP: 'global-gap',
  HACCP:      'haccp',
  ISO:        'iso',
  IFS:        'ifs',
  HALAL:      'halal',
  KOSHER:     'kosher',
})

// ── Export Markets
export const MARKETS = Object.freeze({
  EU:    { id: 'eu',    label: 'European Union'  },
  US:    { id: 'us',    label: 'United States'   },
  JP:    { id: 'jp',    label: 'Japan'            },
  CN:    { id: 'cn',    label: 'China'            },
  KR:    { id: 'kr',    label: 'Korea'            },
  ME:    { id: 'me',    label: 'Middle East'      },
  OTHER: { id: 'other', label: 'Other Markets'    },
})

// ── Contact / Inquiry Types
export const INQUIRY_TYPES = Object.freeze({
  TRADE:   'trade',
  MEDIA:   'media',
  GENERAL: 'general',
})

// ── Company Details (single source of truth — used in Footer, Contact, Schema.org)
export const COMPANY_INFO = Object.freeze({
  legalName:    'CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ PHÁT TRIỂN ĐA QUỐC GIA I.D.I',
  displayName:  'IDI Seafood',
  ticker:       'IDI',
  exchange:     'HOSE',
  founded:      SITE_FOUNDED,
  businessReg:  '0303141296',
  exportCountries: 50,
  staffCount:   5000,
  capacityMT:   100000, // MT per year

  headOffice: {
    label:    'Trụ sở chính',
    address:  'Quốc lộ 80, Cụm công nghiệp Vàm Cống, ấp An Thạnh, xã Lấp Vò, Tỉnh Đồng Tháp, Việt Nam',
    phone:    '+84 2773 680 383',
    phone2:   '+84 2777 300 468',
    fax:      '+84 2773 680 382',
    email:    'info@idiseafood.com',
    lat: 10.448, // Approx coordinates for map
    lng: 105.617,
  },

  hcmOffice: {
    label:   'Văn phòng đại diện Hồ Chí Minh',
    address: '9 Nguyễn Kim, phường 12, quận 5, Thành phố Hồ Chí Minh, Việt Nam',
    phone:   '+84 932 824 888',
    lat: 10.758,
    lng: 106.666,
  },

  social: {
    facebook:  'https://www.facebook.com/idicorporationofficial',
    youtube:   'https://www.youtube.com/@saomaigroup6051/videos',
    linkedin:  '', // To be filled in
  },
})

// ── Nav Routes (for active link detection in Navbar)
export const NAV_PATHS = Object.freeze({
  HOME:           '/',
  PRODUCTS:       '/products',
  ABOUT:          '/about',
  QUALITY:        '/quality',
  SUSTAINABILITY: '/sustainability',
  INVESTORS:      '/investors',
  NEWS:           '/news',
  RECIPES:        '/recipes',
  CAREERS:        '/careers',
  CONTACT:        '/contact',
})

// ── Pagination defaults
export const DEFAULT_PAGE_SIZE = 12
