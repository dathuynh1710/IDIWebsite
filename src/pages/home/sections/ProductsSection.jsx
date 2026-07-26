import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'

const CATEGORIES = [
  {
    id: 'pangasius-fillet',
    name: 'Cá tra phi lê',
    desc: 'Không da / Có da · Chỉnh hình D · Đông IQF & Block',
    specs: 'Kích cỡ: 60–220g+ · Mạ băng: 5–20%',
    image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm2.jpg',
    badge: 'Bán chạy',
    badgeColor: 'badge-gold',
    href: '/products?category=pangasius-fillet',
  },
  {
    id: 'pangasius-portions',
    name: 'Cá tra cắt khúc',
    desc: 'Cắt theo khẩu phần cho nhà hàng và bán lẻ',
    specs: 'Kích cỡ: 80–200g · Nhận cắt theo yêu cầu',
    image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm3.jpg',
    badge: 'Nhà hàng',
    badgeColor: 'badge-blue',
    href: '/products?category=pangasius-portions',
  },
  {
    id: 'whole-fish',
    name: 'Cá nguyên con',
    desc: 'Dạng bướm · HGT · Làm sạch',
    specs: 'Kích cỡ: 300g–1,5kg · Thị trường Châu Á',
    image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm4.jpg',
    badge: 'Sẵn sàng xuất khẩu',
    badgeColor: 'badge-green',
    href: '/products?category=whole-fish',
  },
  {
    id: 'value-added',
    name: 'Sản phẩm chế biến',
    desc: 'Tẩm bột · Bao bột · Tẩm ướp theo công thức',
    specs: 'Phát triển công thức theo yêu cầu',
    image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm6.jpg',
    badge: 'Dòng sản phẩm mới',
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
              <span className="section-eyebrow">Danh mục sản phẩm</span>
            </RevealOnScroll>
            <RevealOnScroll delay={80}>
              <h2 className="text-h2 font-bold text-ocean-deep mt-3">
                Cá tra chất lượng cao cho mọi thị trường
              </h2>
            </RevealOnScroll>
          </div>
          <RevealOnScroll direction="right">
            <Link to="/products" className="btn btn-secondary whitespace-nowrap">
              Xem toàn bộ danh mục →
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
                    Xem thông số
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
            Tất cả sản phẩm đều có định dạng đông IQF và Block ·{' '}
            <Link to="/contact" className="text-seafoam hover:text-seafoam-light font-semibold transition-colors">
              Nhận sản xuất theo quy cách riêng
            </Link>
          </p>
        </RevealOnScroll>

      </div>
    </section>
  )
}
