import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'

const CATEGORIES = [
  {
    id: 'pangasius-fillet',
    name: 'Pangasius Fillet',
    desc: 'Skinless / Skin-on · Trim D · IQF & Block Frozen',
    specs: 'Size: 60–220g+ · Glazing: 5–20%',
    image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm2.jpg',
    badge: 'Best Seller',
    badgeColor: 'badge-gold',
    href: '/products?category=pangasius-fillet',
  },
  {
    id: 'pangasius-portions',
    name: 'Pangasius Portions',
    desc: 'Portion-controlled cuts for food service & retail',
    specs: 'Size: 80–200g · Custom cuts available',
    image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm3.jpg',
    badge: 'Food Service',
    badgeColor: 'badge-blue',
    href: '/products?category=pangasius-portions',
  },
  {
    id: 'whole-fish',
    name: 'Whole Fish',
    desc: 'Butterfly · HGT · Dress varieties',
    specs: 'Size: 300g–1.5kg · Asian markets',
    image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm4.jpg',
    badge: 'Export Ready',
    badgeColor: 'badge-green',
    href: '/products?category=whole-fish',
  },
  {
    id: 'value-added',
    name: 'Value Added',
    desc: 'Breaded · Battered · Marinated specialty cuts',
    specs: 'Custom recipe development available',
    image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm6.jpg',
    badge: 'New Range',
    badgeColor: 'badge-gold',
    href: '/products?category=value-added',
  },
]

export default function ProductsSection() {
  return (
    <section className="py-24 lg:py-36 bg-white">
      <div className="container">

        {/* Header row */}
        <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-14">
          <div className="max-w-xl">
            <RevealOnScroll>
              <span className="section-eyebrow">Product Range</span>
            </RevealOnScroll>
            <RevealOnScroll delay={80}>
              <h2 className="text-h2 font-bold text-ocean-deep mt-3">
                Premium Pangasius for Every Market
              </h2>
            </RevealOnScroll>
          </div>
          <RevealOnScroll direction="right">
            <Link to="/products" className="btn btn-secondary whitespace-nowrap">
              View Full Catalog →
            </Link>
          </RevealOnScroll>
        </div>

        {/* Product cards */}
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
          {CATEGORIES.map((cat, i) => (
            <RevealOnScroll key={cat.id} delay={i * 80}>
              <Link
                to={cat.href}
                className="group relative block rounded-2xl overflow-hidden bg-ocean-deep shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-1"
              >
                {/* Product image */}
                <div className="aspect-[3/4] overflow-hidden">
                  <img
                    src={cat.image}
                    alt={cat.name}
                    className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    loading="lazy"
                    onError={(e) => {
                      e.target.src = ''
                      e.target.parentElement.style.background = 'linear-gradient(135deg, #163D6B, #0B2545)'
                    }}
                  />
                  {/* Dark gradient overlay */}
                  <div className="absolute inset-0 bg-gradient-to-t from-ocean-deep via-ocean-deep/40 to-transparent" />
                </div>

                {/* Badge */}
                <div className={`absolute top-4 left-4 badge ${cat.badgeColor} text-[10px]`}>
                  {cat.badge}
                </div>

                {/* Content overlay */}
                <div className="absolute bottom-0 left-0 right-0 p-5">
                  <h3 className="font-bold text-white text-lg leading-tight mb-1">
                    {cat.name}
                  </h3>
                  <p className="text-white/70 text-xs mb-2">{cat.desc}</p>
                  <p className="text-white/50 text-[11px] font-mono">{cat.specs}</p>

                  {/* CTA arrow */}
                  <div className="flex items-center gap-1.5 text-coral-gold text-xs font-semibold mt-3 translate-y-1 opacity-0 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                    View Specifications
                    <svg className="w-3.5 h-3.5" viewBox="0 0 14 14" fill="none">
                      <path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                  </div>
                </div>
              </Link>
            </RevealOnScroll>
          ))}
        </div>

        {/* Bottom note */}
        <RevealOnScroll delay={400}>
          <p className="text-center text-storm-grey text-sm mt-10">
            All products available in IQF and Block Frozen format ·{' '}
            <Link to="/contact" className="text-seafoam hover:text-seafoam-light font-semibold transition-colors">
              Custom specifications welcome
            </Link>
          </p>
        </RevealOnScroll>

      </div>
    </section>
  )
}
