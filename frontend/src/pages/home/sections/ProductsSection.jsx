import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { productsService } from '@services/products.service'
import { useLanguage } from '@hooks/useLanguage'
import { getHomeTranslations } from '@/i18n/home'

export default function ProductsSection() {
  const { language, t } = useLanguage()
  const copy = getHomeTranslations(language).products
  const [categories, setCategories] = useState([])

  useEffect(() => {
    let isMounted = true
    setCategories([])

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
    <section className="bg-arctic-white py-16 lg:py-20">
      <div className="container">
        <div className="mb-10 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
          <div className="max-w-xl">
            <RevealOnScroll>
              <span className="section-eyebrow">{copy.eyebrow}</span>
            </RevealOnScroll>
            <RevealOnScroll delay={80}>
              <h2 className="mt-3 text-h2 font-bold text-ocean-deep">
                {copy.title}
              </h2>
            </RevealOnScroll>
          </div>

          <RevealOnScroll direction="right">
            <Link to="/products" className="btn btn-secondary whitespace-nowrap">
              {t('actions.viewCatalog')}
            </Link>
          </RevealOnScroll>
        </div>

        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          {categories.map((category, index) => (
            <RevealOnScroll key={category.id} delay={index * 80}>
              <Link
                to={`/products?category=${category.slug}`}
                className="group relative block overflow-hidden rounded-xl bg-ocean-deep shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                aria-label={`${t('actions.viewDetails')}: ${category.name}`}
              >
                <div className="aspect-[4/3] overflow-hidden">
                  <img
                    src={category.image}
                    alt={category.name}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                    onError={(event) => {
                      event.currentTarget.hidden = true
                      event.currentTarget.parentElement.style.background = 'linear-gradient(135deg, #163D6B, #0B2545)'
                    }}
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-ocean-deep/90 via-ocean-deep/10 to-transparent" />
                </div>

                <div className="absolute inset-x-0 bottom-0 flex items-center justify-between gap-4 p-5">
                  <h3 className="text-lg font-bold leading-tight text-white">
                    {category.name}
                  </h3>
                  <span className="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-white/50 text-white transition-colors duration-300 group-hover:border-white group-hover:bg-white group-hover:text-ocean-deep" aria-hidden="true">
                    <svg className="h-4 w-4" viewBox="0 0 20 20" fill="none">
                      <path d="M4 10h12m-4-4 4 4-4 4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                  </span>
                </div>
              </Link>
            </RevealOnScroll>
          ))}
        </div>
      </div>
    </section>
  )
}
