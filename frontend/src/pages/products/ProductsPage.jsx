import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router'
import PageHead from '@components/common/PageHead'
import { productsService } from '@services/products.service'

function ProductCard({ product, category, onOpen }) {
  return (
    <div className="colsl">
      <article className="itproducthb">
        <div className="thumb">
          <button
            type="button"
            className="nonepointe"
            onClick={onOpen}
            aria-label={`Xem chi tiết ${product.name}`}
            aria-haspopup="dialog"
          >
            <img src={product.image} alt={product.name} loading="lazy" />
          </button>
        </div>

        <div className="decss">
          <div className="dsmeta">
            <span>{category}</span>
            <span>{product.freezingMethod}</span>
          </div>

          <div className="dstitle">
            <h3>
              <button type="button" className="chitietpopup" onClick={onOpen} aria-haspopup="dialog">
                {product.name}
              </button>
            </h3>
          </div>

          <div className="dsconts" aria-label="Quy cách sản phẩm">
            <span className="size-label">Size</span>
            {product.sizes.map(size => (
              <span key={size} className="size-chip">{size}</span>
            ))}
          </div>

          <div className="dsviews">
            <button type="button" className="chitietpopup" onClick={onOpen} aria-haspopup="dialog">
              <span>Xem chi tiết</span>
            </button>
          </div>
        </div>
      </article>
    </div>
  )
}

function ProductModal({ product, isClosing, onClose, closeButtonRef, dialogRef }) {
  if (!product) return null

  return (
    <div
      className={`product-modal${isClosing ? ' is-closing' : ''}`}
      role="presentation"
      onMouseDown={(event) => {
        if (event.target === event.currentTarget) onClose()
      }}
    >
      <section
        ref={dialogRef}
        className="product-modal__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="product-modal-title"
        aria-describedby="product-modal-description"
      >
        <button
          ref={closeButtonRef}
          type="button"
          className="product-modal__close"
          onClick={onClose}
          aria-label="Đóng thông tin sản phẩm"
        >
          <span aria-hidden="true">×</span>
        </button>

        <div className="product-modal__media">
          <img src={product.image} alt={product.name} />
          <div className="product-modal__media-label">
            <span>IDI Seafood</span>
            <strong>Chất lượng xuất khẩu</strong>
          </div>
        </div>

        <div className="product-modal__content">
          <div className="product-modal__eyebrow">
            <span>{product.category}</span>
            <span>{product.freezingMethod}</span>
          </div>

          <h2 id="product-modal-title">{product.name}</h2>
          <p id="product-modal-description" className="product-modal__description">
            {product.description}
          </p>

          <div className="product-modal__sizes">
            <span>Kích cỡ hiện có</span>
            <div>
              {product.sizes.map(size => (
                <span key={size}>{size}</span>
              ))}
            </div>
          </div>

          <dl className="product-modal__specs">
            <div>
              <dt>Phương pháp cấp đông</dt>
              <dd>{product.freezingMethod}</dd>
            </div>
            <div>
              <dt>Nhiệt độ bảo quản</dt>
              <dd>{product.storageTemperature}</dd>
            </div>
            <div>
              <dt>Đóng gói</dt>
              <dd>{product.packaging}</dd>
            </div>
            <div>
              <dt>Chứng nhận</dt>
              <dd>{product.certifications.join(' · ')}</dd>
            </div>
            <div>
              <dt>Xuất xứ</dt>
              <dd>{product.origin}</dd>
            </div>
            <div>
              <dt>Thời hạn sử dụng</dt>
              <dd>{product.shelfLife}</dd>
            </div>
          </dl>

          <div className="product-modal__actions">
            <Link to="/contact" className="btn btn-primary">
              Yêu cầu tư vấn
              <span aria-hidden="true">→</span>
            </Link>
            <button type="button" className="btn btn-secondary" onClick={onClose}>
              Tiếp tục xem sản phẩm
            </button>
          </div>
        </div>
      </section>
    </div>
  )
}

export default function ProductsPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const [catalog, setCatalog] = useState({ categories: [], total: 0 })
  const [loadError, setLoadError] = useState(false)
  const [selectedProduct, setSelectedProduct] = useState(null)
  const [isModalClosing, setIsModalClosing] = useState(false)
  const closeTimerRef = useRef(null)
  const closeButtonRef = useRef(null)
  const dialogRef = useRef(null)
  const categoryParam = searchParams.get('category')
  const activeCategory = useMemo(
    () => categoryParam
      ? catalog.categories.find(category => category.slug === categoryParam) ?? null
      : null,
    [catalog.categories, categoryParam],
  )
  const allProducts = useMemo(
    () => catalog.categories
      .flatMap(category => category.products.map(product => ({
        ...product,
        categoryName: category.name,
      })))
      .sort((first, second) => second.sortOrder - first.sortOrder),
    [catalog.categories],
  )

  useEffect(() => {
    let isMounted = true

    productsService.getCatalog({ locale: 'vi' })
      .then((data) => {
        if (!isMounted) return
        setCatalog(data)
        setLoadError(false)
      })
      .catch(() => {
        if (isMounted) setLoadError(true)
      })

    return () => {
      isMounted = false
    }
  }, [])

  const selectCategory = (category) => {
    setSearchParams({ category: category.slug })
  }

  const selectAllProducts = () => {
    setSearchParams({})
  }

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
      if (event.key === 'Tab') {
        const focusableElements = dialogRef.current?.querySelectorAll(
          'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])',
        )
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
    }
    window.addEventListener('keydown', handleKeyDown)

    return () => {
      document.body.style.overflow = previousOverflow
      window.removeEventListener('keydown', handleKeyDown)
    }
  }, [closeProduct, selectedProduct])

  useEffect(
    () => () => {
      if (closeTimerRef.current) window.clearTimeout(closeTimerRef.current)
    },
    [],
  )

  return (
    <>
      <PageHead
        title="Sản phẩm | IDI Seafood"
        description="Danh mục sản phẩm cá tra IDI với quy trình khép kín, công nghệ hiện đại và tiêu chuẩn chất lượng quốc tế."
      />

      <main className="products-page">
        <section className="products-catalog" id="products-catalog">
          <div className="container">
            <div className="products-page__header">
              <nav className="products-breadcrumb" aria-label="Đường dẫn trang">
                <Link to="/">Trang chủ</Link>
                <span aria-hidden="true">/</span>
                <span aria-current="page">Sản phẩm</span>
              </nav>

              <div className="products-page__heading">
                <div className="products-catalog__intro">
                  <span className="section-eyebrow">Danh mục sản phẩm</span>
                  <h1>Cá tra chất lượng cao cho thị trường toàn cầu</h1>
                  <p>
                    Khám phá các dòng cá tra được sản xuất theo chuỗi khép kín,
                    cấp đông hiện đại và linh hoạt quy cách cho từng thị trường.
                  </p>
                </div>

                <aside className="products-page__assurance" aria-label="Tiêu chuẩn sản phẩm">
                  <div>
                    <span className="products-page__assurance-label">Tiêu chuẩn xuất khẩu</span>
                    <strong>{catalog.total} quy cách sản phẩm</strong>
                    <p>ASC · BRC AA · HACCP · HALAL</p>
                  </div>
                  <Link to="/contact">
                    Nhận tư vấn quy cách
                    <span aria-hidden="true">→</span>
                  </Link>
                </aside>
              </div>
            </div>

            <nav className="tpproductha" aria-label="Danh mục sản phẩm">
              <ul>
                <li
                  data-target="category-all"
                  className={activeCategory === null ? 'active' : ''}
                >
                  <button type="button" onClick={selectAllProducts}>
                    <span>Tất cả</span>
                  </button>
                </li>
                {catalog.categories.map(category => (
                  <li
                    key={category.id}
                    data-target={`category-${category.id}`}
                    className={activeCategory?.id === category.id ? 'active' : ''}
                  >
                    <button type="button" onClick={() => selectCategory(category)}>
                      <span>{category.name}</span>
                    </button>
                  </li>
                ))}
              </ul>
            </nav>

            <div className="products-catalog__summary">
              <div>
                <span>Đang xem</span>
                <h3>
                  {loadError
                    ? 'Không thể tải dữ liệu'
                    : activeCategory?.name ?? (catalog.categories.length ? 'Tất cả sản phẩm' : 'Đang tải...')}
                </h3>
              </div>
              <p>
                {loadError
                  ? 'Vui lòng thử tải lại trang.'
                  : activeCategory?.description ?? (catalog.categories.length
                    ? `Khám phá toàn bộ ${catalog.total} quy cách sản phẩm cá tra của IDI Seafood.`
                    : '')}
              </p>
            </div>

            <div className="tpproducthb">
              <div
                className={`hbconts${activeCategory === null ? ' active' : ''}`}
                data-target="category-all"
              >
                <div className="slproducthb vhslickload">
                  {allProducts.map(product => (
                    <ProductCard
                      key={product.id}
                      product={product}
                      category={product.categoryName}
                      onOpen={() => openProduct(product, product.categoryName)}
                    />
                  ))}
                </div>
              </div>
              {catalog.categories.map(category => (
                <div
                  key={category.id}
                  className={`hbconts${activeCategory?.id === category.id ? ' active' : ''}`}
                  data-target={`category-${category.id}`}
                >
                  <div className="slproducthb vhslickload">
                    {category.products.map(product => (
                      <ProductCard
                        key={product.id}
                        product={product}
                        category={category.name}
                        onOpen={() => openProduct(product, category.name)}
                      />
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>
      </main>

      <ProductModal
        product={selectedProduct}
        isClosing={isModalClosing}
        onClose={closeProduct}
        closeButtonRef={closeButtonRef}
        dialogRef={dialogRef}
      />
    </>
  )
}
