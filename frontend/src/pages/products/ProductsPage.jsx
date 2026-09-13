import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router'
import PageHead from '@components/common/PageHead'
import { productsService } from '@services/products.service'
import { useLanguage } from '@hooks/useLanguage'
import { publicAsset } from '@utils/publicAsset'

const PRODUCT_DETAIL_LABELS = {
  vi: {
    specification: 'Quy cách sản phẩm',
    size: 'Kích cỡ',
    presentation: 'Hình thức cấp đông',
    packaging: 'Đóng gói',
    nutrition: 'Giá trị dinh dưỡng',
    per100g: 'trên 100g',
    calories: 'Năng lượng',
    protein: 'Chất đạm',
    fat: 'Chất béo',
    saturatedFat: 'Chất béo bão hòa',
  },
  en: {
    specification: 'Product Specification',
    size: 'Size',
    presentation: 'Presentation',
    packaging: 'Packaging',
    nutrition: 'Nutrition Fact',
    per100g: 'per 100g',
    calories: 'Calories',
    protein: 'Protein',
    fat: 'Fat',
    saturatedFat: 'Saturated fat',
  },
  'zh-CN': {
    specification: '产品规格',
    size: '规格',
    presentation: '冷冻方式',
    packaging: '包装',
    nutrition: '营养成分',
    per100g: '每100克',
    calories: '热量',
    protein: '蛋白质',
    fat: '脂肪',
    saturatedFat: '饱和脂肪',
  },
}

function ProductCard({ product, category, onOpen }) {
  const { t } = useLanguage()

  return (
    <article className="itproducthb">
      <div className="thumb">
        <button type="button" onClick={onOpen} aria-label={`${t('actions.viewDetails')} ${product.name}`} aria-haspopup="dialog">
          <img src={product.image} alt={product.name} loading="lazy" />
        </button>
      </div>
      <div className="decss">
        <div className="dsmeta">
          <span>{category}</span>
        </div>
        <div className="dstitle">
          <h3><button type="button" onClick={onOpen} aria-haspopup="dialog">{product.name}</button></h3>
        </div>
        <div className="dsconts" aria-label={t('products.specificationLabel')}>
          <span className="size-label">{t('products.sizeLabel')}</span>
          {product.sizes.map(size => <span key={size} className="size-chip">{size}</span>)}
        </div>
        <div className="dsviews">
          <button type="button" onClick={onOpen} aria-haspopup="dialog">
            <span>{t('actions.viewDetails')}</span><span aria-hidden="true">→</span>
          </button>
        </div>
      </div>
    </article>
  )
}

function ProductModal({ product, isClosing, onClose, closeButtonRef, dialogRef }) {
  const { language, t } = useLanguage()
  if (!product) return null

  const labels = PRODUCT_DETAIL_LABELS[language] ?? PRODUCT_DETAIL_LABELS.vi
  const presentation = Array.isArray(product.presentation) ? product.presentation.filter(Boolean) : []
  const nutrition = [
    [labels.calories, product.nutrition?.calories],
    [labels.protein, product.nutrition?.protein],
    [labels.fat, product.nutrition?.fat],
    [labels.saturatedFat, product.nutrition?.saturated_fat],
  ].filter(([, value]) => value)

  return (
    <div
      className={`product-modal${isClosing ? ' is-closing' : ''}`}
      role="presentation"
      onMouseDown={(event) => { if (event.target === event.currentTarget) onClose() }}
    >
      <section ref={dialogRef} className="product-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="product-modal-title" aria-describedby="product-modal-description">
        <button ref={closeButtonRef} type="button" className="product-modal__close" onClick={onClose} aria-label={t('common.close')}>
          <span aria-hidden="true">×</span>
        </button>
        <div className="product-modal__media">
          <img src={product.image} alt={product.name} />
          <div className="product-modal__media-label"><span>IDI Seafood</span><strong>{t('products.exportQuality')}</strong></div>
        </div>
        <div className="product-modal__content">
          {product.category && <div className="product-modal__eyebrow"><span>{product.category}</span></div>}
          <h2 id="product-modal-title">{product.name}</h2>
          <div id="product-modal-description" className="product-modal__details">
            {product.productSpecification && (
              <section className="product-modal__detail-block">
                <h3>{labels.specification}</h3>
                <p>{product.productSpecification}</p>
              </section>
            )}

            {product.sizes?.length > 0 && (
              <section className="product-modal__detail-block product-modal__sizes">
                <h3>{labels.size}</h3>
                <div>{product.sizes.map(size => <span key={size}>{size}</span>)}</div>
              </section>
            )}

            {presentation.length > 0 && (
              <section className="product-modal__detail-block product-modal__detail-block--wide">
                <h3>{labels.presentation}</h3>
                <p>{presentation.join(', ')}</p>
              </section>
            )}

            {product.packaging && (
              <section className="product-modal__detail-block product-modal__detail-block--wide">
                <h3>{labels.packaging}</h3>
                <p>{product.packaging}</p>
              </section>
            )}

            {nutrition.length > 0 && (
              <section className="product-modal__detail-block product-modal__detail-block--wide product-modal__nutrition">
                <h3>{labels.nutrition} <small>{labels.per100g}</small></h3>
                <dl>
                  {nutrition.map(([label, value]) => <div key={label}><dt>{label}</dt><dd>{value}</dd></div>)}
                </dl>
              </section>
            )}
          </div>
          <div className="product-modal__actions">
            <Link to="/contact" className="btn btn-primary">{t('actions.requestAdvice')}<span aria-hidden="true">→</span></Link>
            <button type="button" className="btn btn-secondary" onClick={onClose}>{t('actions.continueProducts')}</button>
          </div>
        </div>
      </section>
    </div>
  )
}

export default function ProductsPage() {
  const { language, t } = useLanguage()
  const [searchParams, setSearchParams] = useSearchParams()
  const [catalog, setCatalog] = useState({ categories: [], total: 0 })
  const [selectedProduct, setSelectedProduct] = useState(null)
  const [isModalClosing, setIsModalClosing] = useState(false)
  const closeTimerRef = useRef(null)
  const closeButtonRef = useRef(null)
  const dialogRef = useRef(null)
  const categoryParam = searchParams.get('category')
  const activeCategory = useMemo(
    () => categoryParam ? catalog.categories.find(category => category.slug === categoryParam) ?? null : null,
    [catalog.categories, categoryParam],
  )
  const allProducts = useMemo(
    () => catalog.categories
      .flatMap(category => category.products.map(product => ({ ...product, categoryName: category.name })))
      .sort((first, second) => second.sortOrder - first.sortOrder),
    [catalog.categories],
  )
  const visibleProducts = activeCategory?.products ?? allProducts

  useEffect(() => {
    let isMounted = true
    productsService.getCatalog({ locale: language })
      .then((data) => {
        if (!isMounted) return
        setCatalog(data)
      })
      .catch(() => {})
    return () => { isMounted = false }
  }, [language])

  const openProduct = (product, category) => {
    if (closeTimerRef.current) window.clearTimeout(closeTimerRef.current)
    setIsModalClosing(false)
    setSelectedProduct({ ...product, category })
  }
  const closeProduct = useCallback(() => {
    if (!selectedProduct || isModalClosing) return
    setIsModalClosing(true)
    closeTimerRef.current = window.setTimeout(() => {
      setSelectedProduct(null)
      setIsModalClosing(false)
    }, 220)
  }, [isModalClosing, selectedProduct])

  useEffect(() => {
    if (!selectedProduct) return undefined
    const previousOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    closeButtonRef.current?.focus()
    const handleKeyDown = (event) => {
      if (event.key === 'Escape') closeProduct()
      if (event.key !== 'Tab') return
      const focusableElements = dialogRef.current?.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])')
      if (!focusableElements?.length) return
      const firstElement = focusableElements[0]
      const lastElement = focusableElements[focusableElements.length - 1]
      if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault()
        lastElement.focus()
      } else if (!event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault()
        firstElement.focus()
      }
    }
    window.addEventListener('keydown', handleKeyDown)
    return () => {
      document.body.style.overflow = previousOverflow
      window.removeEventListener('keydown', handleKeyDown)
    }
  }, [closeProduct, selectedProduct])

  useEffect(() => () => {
    if (closeTimerRef.current) window.clearTimeout(closeTimerRef.current)
  }, [])

  return (
    <>
      <PageHead title={t('products.seoTitle')} description={t('products.seoDescription')} />
      <main className="products-page">
        <section className="products-catalog" id="products-catalog">
          <div className="container">
            <header className="products-page__header">
              <h1>{t('products.title')}</h1>
            </header>

            <section className="products-introduction" aria-labelledby="products-introduction-title">
              <p className="products-introduction__lead">{t('products.lead')}</p>

              <div className="products-introduction__feature">
                <figure className="products-introduction__media">
                  <img
                    src={publicAsset('assets/images/products/category-fillets.jpg')}
                    alt={t('products.imageAlt')}
                    loading="eager"
                  />
                </figure>
                <div className="products-introduction__content">
                  <h2 id="products-introduction-title">{t('products.introductionTitle')}</h2>
                  <p>{t('products.introductionDescription')}</p>
                </div>
              </div>
            </section>

            <nav className="tpproductha" aria-label={t('products.catalogLabel')}>
              <ul>
                <li className={activeCategory === null ? 'active' : ''}>
                  <button type="button" onClick={() => setSearchParams({}, { preventScrollReset: true })} aria-pressed={activeCategory === null}><span>{t('actions.all')}</span></button>
                </li>
                {catalog.categories.map(category => (
                  <li key={category.id} className={activeCategory?.id === category.id ? 'active' : ''}>
                    <button type="button" onClick={() => setSearchParams({ category: category.slug }, { preventScrollReset: true })} aria-pressed={activeCategory?.id === category.id}>
                      <span>{category.name}</span>
                    </button>
                  </li>
                ))}
              </ul>
            </nav>

            <div className="slproducthb vhslickload">
              {visibleProducts.map(product => (
                <ProductCard
                  key={product.id}
                  product={product}
                  category={product.categoryName ?? activeCategory?.name}
                  onOpen={() => openProduct(product, product.categoryName ?? activeCategory?.name)}
                />
              ))}
            </div>
          </div>
        </section>
      </main>
      <ProductModal product={selectedProduct} isClosing={isModalClosing} onClose={closeProduct} closeButtonRef={closeButtonRef} dialogRef={dialogRef} />
    </>
  )
}
