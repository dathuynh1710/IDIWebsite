import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router'
import PageHead from '@components/common/PageHead'

const PRODUCT_TABS = [
  {
    label: 'Cá Fillet',
    target: 'vtab5',
    slug: 'pangasius-fillet',
    summary: 'Dòng sản phẩm chủ lực với nhiều tiêu chuẩn tạo hình cho thị trường xuất khẩu.',
  },
  {
    label: 'Cá cắt khúc',
    target: 'vtab6',
    slug: 'pangasius-portions',
    summary: 'Các quy cách cắt khúc tiện dụng cho bán lẻ, food service và chế biến sâu.',
  },
  {
    label: 'Cá Nguyên Con',
    target: 'vtab8',
    slug: 'whole-fish',
    summary: 'Sản phẩm cá nguyên con được sơ chế, cấp đông và đóng gói theo chuẩn quốc tế.',
  },
  {
    label: 'Các sản phẩm khác',
    target: 'vtab7',
    slug: 'value-added',
    summary: 'Danh mục giá trị gia tăng, đáp ứng nhu cầu trình bày và chế biến đa dạng.',
  },
]

const PRODUCT_GROUPS = {
  vtab5: [
    {
      name: 'Cá Fillet, Tạo Hình Sạch',
      image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm2.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=15',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
    {
      name: 'Cá Fillet, Còn Da, Còn Dè',
      image: 'https://idiseafood.com/vnt_upload/product/03_2021/Con_da_con_de_min.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=12',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
    {
      name: 'Cá Fillet, Tạo Hình Sạch, Xông CO',
      image: 'https://idiseafood.com/vnt_upload/product/03_2021/Xong_CO_min.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=14',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
    {
      name: 'Cá Fillet, Bỏ Da, Còn Thịt Đỏ, Vanh Dè Sát',
      image: 'https://idiseafood.com/vnt_upload/product/03_2021/VDS_con_thit_do_min.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=9',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
    {
      name: 'Cá Fillet, Bỏ Da, Còn Dè',
      image: 'https://idiseafood.com/vnt_upload/product/03_2021/Bo_da_de_EU_min.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=10',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
    {
      name: 'Cá Fillet, Còn Da, Vanh Dè Sát',
      image: 'https://idiseafood.com/vnt_upload/product/03_2021/VDS_con_da_min_1.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=1',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
  ],
  vtab6: [
    {
      name: 'Cá Cắt Khúc Từ Cá Fillet, Còn Da, Còn Dè',
      image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm3.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=6',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
    {
      name: 'Cá Cắt Khúc Từ Cá Nguyên Con Cắt Đầu Bằng',
      image: 'https://idiseafood.com/vnt_upload/product/03_2021/Cat_khuc_min.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=13',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
    {
      name: 'Cá Cắt Khúc Từ Cá Fillet, Tạo Hình Sạch',
      image: 'https://idiseafood.com/vnt_upload/product/03_2021/Cat_mieng_vuong_min.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=7',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
  ],
  vtab8: [
    {
      name: 'Cá Nguyên Con Cắt Đầu Bằng',
      image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm4.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=5',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
    {
      name: 'Cá Nguyên Con Xẻ Bướm Lưng',
      image: 'https://idiseafood.com/vnt_upload/product/03_2021/Nguyen_con_xe_buom_min.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=2',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
  ],
  vtab7: [
    {
      name: 'Ức Cá Tra',
      image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm6.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=3',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
    {
      name: 'Cá Fillet, Tạo Hình Sạch, Cuộn Hoa Hồng',
      image: 'https://idiseafood.com/vnt_upload/product/03_2021/Hoa_hong_min.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=11',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
    {
      name: 'Cá Cắt Khúc Từ Cá Fillet, Tạo Hình Sạch, Xiên Que',
      image: 'https://idiseafood.com/vnt_upload/product/10_2020/dm5.jpg',
      href: 'https://idiseafood.com/vn/san-pham/popup.html/?do=detail_product&p_id=4',
      sizes: ['60g-120g', '120g-170g', '170g-220g', '220g-up'],
    },
  ],
}

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
            <span>IQF / Block Frozen</span>
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
            <span>IQF / Block Frozen</span>
          </div>

          <h2 id="product-modal-title">{product.name}</h2>
          <p id="product-modal-description" className="product-modal__description">
            Sản phẩm được chế biến từ cá tra chọn lọc tại Đồng bằng sông Cửu Long,
            kiểm soát theo chuỗi khép kín và đáp ứng linh hoạt quy cách của từng thị trường.
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
              <dd>IQF / Block Frozen</dd>
            </div>
            <div>
              <dt>Nhiệt độ bảo quản</dt>
              <dd>≤ -18°C</dd>
            </div>
            <div>
              <dt>Đóng gói</dt>
              <dd>Theo yêu cầu khách hàng</dd>
            </div>
            <div>
              <dt>Chứng nhận</dt>
              <dd>ASC · BRC AA · HACCP</dd>
            </div>
            <div>
              <dt>Xuất xứ</dt>
              <dd>Đồng Tháp, Việt Nam</dd>
            </div>
            <div>
              <dt>Thời hạn sử dụng</dt>
              <dd>24 tháng</dd>
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
  const categoryParam = searchParams.get('category')
  const tabFromUrl = PRODUCT_TABS.find(tab => tab.slug === categoryParam)?.target ?? PRODUCT_TABS[0].target
  const [activeTab, setActiveTab] = useState(tabFromUrl)
  const [selectedProduct, setSelectedProduct] = useState(null)
  const [isModalClosing, setIsModalClosing] = useState(false)
  const closeTimerRef = useRef(null)
  const closeButtonRef = useRef(null)
  const dialogRef = useRef(null)
  const activeCategory = useMemo(
    () => PRODUCT_TABS.find(tab => tab.target === activeTab) ?? PRODUCT_TABS[0],
    [activeTab],
  )
  const totalProducts = Object.values(PRODUCT_GROUPS).reduce((count, items) => count + items.length, 0)

  useEffect(() => {
    setActiveTab(tabFromUrl)
  }, [tabFromUrl])

  const selectCategory = (tab) => {
    setActiveTab(tab.target)
    setSearchParams({ category: tab.slug })
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
                    <strong>{totalProducts} quy cách sản phẩm</strong>
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
                {PRODUCT_TABS.map(tab => (
                  <li
                    key={tab.target}
                    data-target={tab.target}
                    className={activeTab === tab.target ? 'active' : ''}
                  >
                    <button type="button" onClick={() => selectCategory(tab)}>
                      <span>{tab.label}</span>
                    </button>
                  </li>
                ))}
              </ul>
            </nav>

            <div className="products-catalog__summary">
              <div>
                <span>Đang xem</span>
                <h3>{activeCategory.label}</h3>
              </div>
              <p>{activeCategory.summary}</p>
            </div>

            <div className="tpproducthb">
              {PRODUCT_TABS.map(tab => (
                <div
                  key={tab.target}
                  className={`hbconts${activeTab === tab.target ? ' active' : ''}`}
                  data-target={tab.target}
                >
                  <div className="slproducthb vhslickload">
                    {PRODUCT_GROUPS[tab.target].map(product => (
                      <ProductCard
                        key={product.href}
                        product={product}
                        category={tab.label}
                        onOpen={() => openProduct(product, tab.label)}
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
