/**
 * products.js — Product catalog data.
 * Phase 1: Static data source for productsService.
 * Phase 2: Replace with CMS API — this file becomes the type/schema reference.
 *
 * Each product object shape:
 * {
 *   id, slug, name, nameVi, category, featured,
 *   imagePrimary, imageGallery[],
 *   certifications[], markets[],
 *   sizeRange[], glazing[], packaging[],
 *   species, origin, freezingMethod, shelfLife,
 *   techSheetUrl, specSheetUrl,
 *   description, descriptionVi
 * }
 */
export const PRODUCTS_DATA = [
  {
    id: 'pf-trim-d',
    slug: 'pangasius-fillet-trim-d',
    name: 'Pangasius Fillet Trim D',
    nameVi: 'Cá Fillet Tạo Hình Sạch',
    category: 'pangasius-fillet',
    featured: true,
    imagePrimary: '/assets/images/products/fillet-trim-d.jpg',
    imageGallery: [],
    certifications: ['asc', 'brc', 'haccp', 'ifs'],
    markets: ['eu', 'us', 'jp'],
    sizeRange: ['60g-120g', '120g-170g', '170g-220g', '220g+'],
    glazing: ['5%', '10%', '20%'],
    packaging: ['IWP', 'Block Frozen', 'Bulk'],
    species: 'Pangasianodon hypophthalmus',
    origin: 'Mekong Delta, Vietnam',
    freezingMethod: 'IQF / Block Frozen',
    coreTemperature: '-18°C',
    shelfLife: '24 months from production date',
    moistureRetention: '≤ 86%',
    weightTolerance: '±3%',
    color: 'White to light pink',
    techSheetUrl: null, // TODO: Add PDF path
    specSheetUrl: null,
    description: 'Premium skinless pangasius fillet, trimmed to Trim D specification. No skin, no bone, no red meat. Ideal for European and North American retail markets.',
    descriptionVi: 'Cá fillet cá tra cao cấp, tạo hình sạch đạt tiêu chuẩn Trim D.',
  },
  {
    id: 'pf-skin-on',
    slug: 'pangasius-fillet-skin-on',
    name: 'Pangasius Fillet Skin-On',
    nameVi: 'Cá Fillet Còn Da, Còn Dè',
    category: 'pangasius-fillet',
    featured: false,
    imagePrimary: '/assets/images/products/fillet-skin-on.jpg',
    imageGallery: [],
    certifications: ['asc', 'brc', 'haccp'],
    markets: ['us', 'cn', 'other'],
    sizeRange: ['60g-120g', '120g-170g', '170g-220g', '220g+'],
    glazing: ['5%', '10%'],
    packaging: ['IWP', 'Block Frozen'],
    species: 'Pangasianodon hypophthalmus',
    origin: 'Mekong Delta, Vietnam',
    freezingMethod: 'IQF / Block Frozen',
    coreTemperature: '-18°C',
    shelfLife: '24 months from production date',
    moistureRetention: '≤ 86%',
    weightTolerance: '±3%',
    color: 'White',
    techSheetUrl: null,
    specSheetUrl: null,
    description: 'Pangasius fillet with skin-on and belly flap retained. Preferred in Asian and North American ethnic markets.',
    descriptionVi: 'Cá fillet cá tra còn da, còn dè.',
  },
  // Additional products to be added...
]

export const PRODUCT_CATEGORIES_META = {
  'pangasius-fillet': {
    id: 'pangasius-fillet',
    name: 'Pangasius Fillet',
    description: 'Our flagship product line. Multiple trim specifications to meet every market requirement.',
    imageCover: '/assets/images/products/category-fillet.jpg',
  },
  'pangasius-portions': {
    id: 'pangasius-portions',
    name: 'Pangasius Portions',
    description: 'Portion-controlled cuts from pangasius fillet. Ideal for food service and retail.',
    imageCover: '/assets/images/products/category-portions.jpg',
  },
  'whole-fish': {
    id: 'whole-fish',
    name: 'Whole Fish',
    description: 'Whole pangasius in various cut styles. Popular in Asian markets.',
    imageCover: '/assets/images/products/category-whole.jpg',
  },
  'value-added': {
    id: 'value-added',
    name: 'Value Added',
    description: 'Specialty products including breaded, marinated, and presentation cuts.',
    imageCover: '/assets/images/products/category-value-added.jpg',
  },
}
