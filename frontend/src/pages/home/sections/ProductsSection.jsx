import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { productsService } from '@services/products.service'
import { useLanguage } from '@hooks/useLanguage'

const CATEGORY_PRESENTATION = {
  'pangasius-fillet': { badge: 'Bán chạy', badgeColor: 'badge-gold' },
  'pangasius-portions': { badge: 'Nhà hàng', badgeColor: 'badge-blue' },
  'whole-fish': { badge: 'Sẵn sàng xuất khẩu', badgeColor: 'badge-green' },
  'value-added': { badge: 'Dòng sản phẩm mới', badgeColor: 'badge-gold' },
}

export default function ProductsSection() {
  const { language, t } = useLanguage()
  const [categories, setCategories] = useState([])

  useEffect(() => {
    let isMounted = true

    productsService.getCatalog({ locale: language })
      .then((catalog) => {
        if (isMounted) setCategories(catalog.categories)
      })
      .catch(() => {})

    return () => {
      isMounted = false
    }
  }, [language])

  return (
    <section className="py-24 lg:py-36 bg-white">
      <div className="container">
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
              {t('actions.viewCatalog')}
            </Link>
          </RevealOnScroll>
        </div>

        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
          {categories.map((category, index) => {
            const presentation = CATEGORY_PRESENTATION[category.slug] ?? {
              badge: 'Sản phẩm IDI',
              badgeColor: 'badge-blue',
            }
            const sizes = category.products[0]?.sizes ?? []

            return (
              <RevealOnScroll key={category.id} delay={index * 80}>
                <Link
                  to={`/products?category=${category.slug}`}
                  className="group relative block rounded-2xl overflow-hidden bg-ocean-deep shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-1"
                >
                  <div className="aspect-[3/4] overflow-hidden">
                    <img
                      src={category.image}
                      alt={category.name}
                      className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                      loading="lazy"
                      onError={(event) => {
                        event.currentTarget.hidden = true
                        event.currentTarget.parentElement.style.background = 'linear-gradient(135deg, #163D6B, #0B2545)'
                      }}
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-ocean-deep via-ocean-deep/40 to-transparent" />
                  </div>

                  <div className={`absolute top-4 left-4 badge ${presentation.badgeColor} text-[10px]`}>
                    {presentation.badge}
                  </div>

                  <div className="absolute bottom-0 left-0 right-0 p-5">
                    <h3 className="font-bold text-white text-lg leading-tight mb-1">
                      {category.name}
                    </h3>
                    <p className="text-white/70 text-xs mb-2">{category.description}</p>
                    <p className="text-white/50 text-[11px] font-mono">
                      Kích cỡ: {sizes.join(' · ')}
                    </p>

                    <div className="flex items-center gap-1.5 text-coral-gold text-xs font-semibold mt-3 translate-y-1 opacity-0 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                      {t('actions.viewDetails')}
                      <svg className="w-3.5 h-3.5" viewBox="0 0 14 14" fill="none">
                        <path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                    </div>
                  </div>
                </Link>
              </RevealOnScroll>
            )
          })}
        </div>

        <RevealOnScroll delay={400}>
          <p className="text-center text-storm-grey text-sm mt-10">
            Tất cả sản phẩm đều có định dạng đông IQF và Block ·{' '}
            <Link to="/contact" className="text-seafoam hover:text-seafoam-light font-semibold transition-colors">
              {t('actions.customProduction')}
            </Link>
          </p>
        </RevealOnScroll>
      </div>
    </section>
  )
}
