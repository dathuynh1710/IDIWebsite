import { useMemo, useState } from 'react'
import PageHead from '@components/common/PageHead'

const PRODUCT_TABS = [
  {
    label: 'Cá Fillet',
    target: 'vtab5',
    summary: 'Dòng sản phẩm chủ lực với nhiều tiêu chuẩn tạo hình cho thị trường xuất khẩu.',
  },
  {
    label: 'Cá cắt khúc',
    target: 'vtab6',
    summary: 'Các quy cách cắt khúc tiện dụng cho bán lẻ, food service và chế biến sâu.',
  },
  {
    label: 'Cá Nguyên Con',
    target: 'vtab8',
    summary: 'Sản phẩm cá nguyên con được sơ chế, cấp đông và đóng gói theo chuẩn quốc tế.',
  },
  {
    label: 'Các sản phẩm khác',
    target: 'vtab7',
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

const HERO_IMAGE = 'https://idiseafood.com/vnt_upload/weblink/dichvu.jpg'

function ProductCard({ product, category }) {
  return (
    <div className="colsl">
      <article className="itproducthb">
        <div className="thumb">
          <a className="nonepointe" href={product.href} rel="nofollow" aria-label={product.name}>
            <img src={product.image} alt={product.name} loading="lazy" />
          </a>
        </div>

        <div className="decss">
          <div className="dsmeta">
            <span>{category}</span>
            <span>IQF / Block Frozen</span>
          </div>

          <div className="dstitle">
            <h3>
              <a className="chitietpopup" href={product.href} rel="nofollow">
                {product.name}
              </a>
            </h3>
          </div>

          <div className="dsconts" aria-label="Quy cách sản phẩm">
            <span className="size-label">Size</span>
            {product.sizes.map(size => (
              <span key={size} className="size-chip">{size}</span>
            ))}
          </div>

          <div className="dsviews">
            <a className="chitietpopup" href={product.href} rel="nofollow">
              <span>Xem chi tiết</span>
            </a>
          </div>
        </div>
      </article>
    </div>
  )
}

export default function ProductsPage() {
  const [activeTab, setActiveTab] = useState('vtab5')
  const activeCategory = useMemo(
    () => PRODUCT_TABS.find(tab => tab.target === activeTab) ?? PRODUCT_TABS[0],
    [activeTab],
  )
  const totalProducts = Object.values(PRODUCT_GROUPS).reduce((count, items) => count + items.length, 0)

  return (
    <>
      <PageHead
        title="Sản phẩm | IDI Seafood"
        description="Danh mục sản phẩm cá tra IDI với quy trình khép kín, công nghệ hiện đại và tiêu chuẩn chất lượng quốc tế."
      />

      <main className="products-page">
        <section className="products-hero">
          <img src={HERO_IMAGE} alt="IDI Seafood production and aquaculture" />
          <div className="products-hero__overlay" />

          <div className="container products-hero__content">
            <span className="products-eyebrow">IDI Seafood Product Portfolio</span>
            <h1>SẢN PHẨM</h1>
            <p>
              Với quy trình sản xuất khép kín từ vườn ươm, nuôi trồng đến nhà máy chế biến
              cùng công nghệ hiện đại, IDI cung cấp cho khách hàng toàn cầu những sản phẩm
              cá tra an toàn, ổn định và đạt chuẩn quốc tế.
            </p>

            <div className="products-hero__stats" aria-label="IDI product strengths">
              <div>
                <strong>{totalProducts}</strong>
                <span>Sản phẩm mẫu</span>
              </div>
              <div>
                <strong>4</strong>
                <span>Danh mục chính</span>
              </div>
              <div>
                <strong>-18°C</strong>
                <span>Chuỗi lạnh xuất khẩu</span>
              </div>
            </div>
          </div>
        </section>

        <section className="products-catalog" id="products-catalog">
          <div className="container">
            <div className="products-catalog__intro">
              <span className="section-eyebrow">Product Categories</span>
              <h2>Danh mục sản phẩm xuất khẩu</h2>
              <p>
                Chọn nhóm sản phẩm để xem các quy cách hiện có. Mỗi sản phẩm vẫn giữ liên kết
                popup chi tiết theo cấu trúc gốc của hệ thống IDI.
              </p>
            </div>

            <nav className="tpproductha" aria-label="Product categories">
              <ul>
                {PRODUCT_TABS.map(tab => (
                  <li
                    key={tab.target}
                    data-target={tab.target}
                    className={activeTab === tab.target ? 'active' : ''}
                  >
                    <button type="button" onClick={() => setActiveTab(tab.target)}>
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
                      <ProductCard key={product.href} product={product} category={tab.label} />
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>
      </main>
    </>
  )
}
