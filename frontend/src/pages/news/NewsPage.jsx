import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router'
import PageHead from '@components/common/PageHead'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { NEWS_DATA } from '@data/news'

const PAGE_SIZE = 6

const CATEGORY_STYLES = {
  'Giải thưởng': 'bg-coral-pale text-[#9A6517]',
  'Tin doanh nghiệp': 'bg-[#EBF4FF] text-ocean-deep',
  ESG: 'bg-seafoam-pale text-seafoam',
  'Thị trường': 'bg-[#F1EEFF] text-[#6246A8]',
  'Công nghệ': 'bg-[#E9F7FB] text-[#176B87]',
  'Sản phẩm': 'bg-[#FFF1EA] text-[#A5542D]',
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

function NewsCard({ article }) {
  return (
    <article className="group flex h-full flex-col overflow-hidden rounded-2xl border border-light-mist bg-white transition-all duration-300 hover:-translate-y-1 hover:border-transparent hover:shadow-xl">
      <Link
        to={`/news/${article.slug}`}
        className="relative block aspect-[16/10] overflow-hidden bg-light-mist"
        aria-label={`Đọc bài: ${article.title}`}
      >
        <img
          src={article.image}
          alt=""
          className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
          loading="lazy"
          onError={(event) => {
            event.currentTarget.style.display = 'none'
            event.currentTarget.parentElement.style.background =
              'linear-gradient(135deg, #0B2545, #1A936F)'
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-t from-ocean-deep/25 to-transparent" />
      </Link>

      <div className="flex flex-1 flex-col p-6">
        <div className="mb-4 flex flex-wrap items-center gap-3">
          <span
            className={`rounded-full px-3 py-1 text-[11px] font-bold ${
              CATEGORY_STYLES[article.category] ?? CATEGORY_STYLES['Tin doanh nghiệp']
            }`}
          >
            {article.category}
          </span>
          <time dateTime={article.date} className="text-xs text-storm-grey">
            {formatDate(article.date)}
          </time>
          <span className="text-xs text-storm-grey" aria-label={`${article.readTime} phút đọc`}>
            · {article.readTime} phút đọc
          </span>
        </div>

        <h2 className="mb-3 text-lg font-bold leading-snug text-ocean-deep transition-colors group-hover:text-seafoam line-clamp-2">
          <Link to={`/news/${article.slug}`}>{article.title}</Link>
        </h2>
        <p className="mb-5 flex-1 text-sm leading-relaxed text-storm-grey line-clamp-3">
          {article.excerpt}
        </p>
        <Link
          to={`/news/${article.slug}`}
          className="inline-flex items-center gap-2 text-sm font-semibold text-seafoam transition-all group-hover:gap-3"
        >
          Đọc bài viết
          <span aria-hidden="true">→</span>
        </Link>
      </div>
    </article>
  )
}

export default function NewsPage() {
  const [query, setQuery] = useState('')
  const [category, setCategory] = useState('Tất cả')
  const [sortOrder, setSortOrder] = useState('newest')
  const [currentPage, setCurrentPage] = useState(1)
  const [email, setEmail] = useState('')
  const [subscribed, setSubscribed] = useState(false)

  const featuredArticle = NEWS_DATA.find(article => article.featured) ?? NEWS_DATA[0]

  const categories = useMemo(
    () => ['Tất cả', ...new Set(NEWS_DATA.map(article => article.category))],
    [],
  )

  const filteredArticles = useMemo(() => {
    const normalizedQuery = query.trim().toLocaleLowerCase('vi')
    const articles = NEWS_DATA.filter((article) => {
      const matchesCategory = category === 'Tất cả' || article.category === category
      const searchableText = `${article.title} ${article.excerpt} ${article.category}`.toLocaleLowerCase('vi')
      return matchesCategory && (!normalizedQuery || searchableText.includes(normalizedQuery))
    })

    return articles.sort((a, b) => {
      const comparison = new Date(b.date) - new Date(a.date)
      return sortOrder === 'newest' ? comparison : -comparison
    })
  }, [category, query, sortOrder])

  const totalPages = Math.max(1, Math.ceil(filteredArticles.length / PAGE_SIZE))
  const visibleArticles = filteredArticles.slice(
    (currentPage - 1) * PAGE_SIZE,
    currentPage * PAGE_SIZE,
  )

  useEffect(() => {
    setCurrentPage(1)
  }, [category, query, sortOrder])

  const clearFilters = () => {
    setQuery('')
    setCategory('Tất cả')
    setSortOrder('newest')
  }

  const handleSubscribe = (event) => {
    event.preventDefault()
    if (!email.trim()) return
    setSubscribed(true)
    setEmail('')
  }

  return (
    <>
      <PageHead
        title="Tin tức & Sự kiện | IDI Seafood"
        description="Cập nhật tin tức mới nhất về IDI Seafood, thị trường cá tra, phát triển bền vững, công nghệ chế biến và hoạt động doanh nghiệp."
      />

      <header className="relative overflow-hidden bg-ocean-deep pb-24 pt-32 text-white lg:pb-32 lg:pt-40">
        <div
          className="absolute inset-0 opacity-20"
          style={{
            background:
              'radial-gradient(circle at 80% 10%, #1A936F 0%, transparent 38%), radial-gradient(circle at 15% 90%, #E8A045 0%, transparent 30%)',
          }}
        />
        <div className="container relative z-10">
          <div className="max-w-3xl">
            <span className="mb-5 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-seafoam-light">
              <span className="h-2 w-2 rounded-full bg-coral-gold" />
              Trung tâm truyền thông
            </span>
            <h1 className="mb-5 text-h1 font-black text-white text-balance">
              Tin tức &amp; Góc nhìn từ IDI
            </h1>
            <p className="max-w-2xl text-body-lg leading-relaxed text-white/65">
              Cập nhật hoạt động doanh nghiệp, xu hướng thị trường, đổi mới công nghệ
              và những bước tiến bền vững của ngành cá tra Việt Nam.
            </p>
          </div>
        </div>
      </header>

      {featuredArticle && (
        <section className="relative z-10 -mt-14 pb-20 lg:-mt-20 lg:pb-28">
          <div className="container">
            <RevealOnScroll>
              <article className="grid overflow-hidden rounded-3xl border border-light-mist bg-white shadow-2xl lg:grid-cols-[1.15fr_0.85fr]">
                <Link
                  to={`/news/${featuredArticle.slug}`}
                  className="relative block min-h-[18rem] overflow-hidden lg:min-h-[29rem]"
                  aria-label={`Đọc bài nổi bật: ${featuredArticle.title}`}
                >
                  <img
                    src={featuredArticle.image}
                    alt=""
                    className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 hover:scale-105"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-ocean-deep/45 to-transparent lg:bg-gradient-to-r" />
                </Link>

                <div className="flex flex-col justify-center p-7 sm:p-10 lg:p-12">
                  <span className="mb-5 text-xs font-bold uppercase tracking-[0.16em] text-seafoam">
                    Bài viết nổi bật
                  </span>
                  <div className="mb-5 flex flex-wrap items-center gap-3 text-xs text-storm-grey">
                    <span
                      className={`rounded-full px-3 py-1 font-bold ${
                        CATEGORY_STYLES[featuredArticle.category] ??
                        CATEGORY_STYLES['Tin doanh nghiệp']
                      }`}
                    >
                      {featuredArticle.category}
                    </span>
                    <time dateTime={featuredArticle.date}>{formatDate(featuredArticle.date)}</time>
                    <span>· {featuredArticle.readTime} phút đọc</span>
                  </div>
                  <h2 className="mb-5 text-2xl font-black leading-tight text-ocean-deep text-balance sm:text-3xl">
                    {featuredArticle.title}
                  </h2>
                  <p className="mb-8 leading-relaxed text-storm-grey">
                    {featuredArticle.excerpt}
                  </p>
                  <Link
                    to={`/news/${featuredArticle.slug}`}
                    className="btn btn-primary self-start"
                  >
                    Đọc bài nổi bật <span aria-hidden="true">→</span>
                  </Link>
                </div>
              </article>
            </RevealOnScroll>
          </div>
        </section>
      )}

      <main className="bg-arctic-white pb-24 lg:pb-32">
        <div className="container">
          <div className="mb-10 flex flex-col gap-6">
            <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <span className="section-eyebrow">Kho tin tức</span>
                <h2 className="mt-2 text-h2 font-bold text-ocean-deep">Tất cả bài viết</h2>
              </div>

              <div className="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                <label className="relative min-w-0 flex-1 lg:w-80">
                  <span className="sr-only">Tìm kiếm bài viết</span>
                  <svg
                    className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-storm-grey"
                    viewBox="0 0 20 20"
                    fill="none"
                    aria-hidden="true"
                  >
                    <circle cx="9" cy="9" r="5.75" stroke="currentColor" strokeWidth="1.5" />
                    <path d="m13.5 13.5 4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                  </svg>
                  <input
                    type="search"
                    value={query}
                    onChange={event => setQuery(event.target.value)}
                    placeholder="Tìm theo tiêu đề, nội dung..."
                    className="h-12 w-full rounded-xl border border-light-mist bg-white pl-11 pr-4 text-sm text-ink outline-none transition focus:border-seafoam focus:ring-2 focus:ring-seafoam/15"
                  />
                </label>

                <label>
                  <span className="sr-only">Sắp xếp bài viết</span>
                  <select
                    value={sortOrder}
                    onChange={event => setSortOrder(event.target.value)}
                    className="h-12 w-full rounded-xl border border-light-mist bg-white px-4 text-sm font-medium text-ink outline-none transition focus:border-seafoam focus:ring-2 focus:ring-seafoam/15 sm:w-auto"
                  >
                    <option value="newest">Mới nhất</option>
                    <option value="oldest">Cũ nhất</option>
                  </select>
                </label>
              </div>
            </div>

            <div className="flex flex-wrap gap-2" aria-label="Lọc theo chuyên mục">
              {categories.map(item => {
                const count =
                  item === 'Tất cả'
                    ? NEWS_DATA.length
                    : NEWS_DATA.filter(article => article.category === item).length
                const isActive = category === item
                return (
                  <button
                    key={item}
                    type="button"
                    onClick={() => setCategory(item)}
                    className={[
                      'rounded-full border px-4 py-2 text-sm font-semibold transition-colors',
                      isActive
                        ? 'border-ocean-deep bg-ocean-deep text-white'
                        : 'border-light-mist bg-white text-storm-grey hover:border-seafoam hover:text-seafoam',
                    ].join(' ')}
                    aria-pressed={isActive}
                  >
                    {item} <span className={isActive ? 'text-white/55' : 'text-storm-grey/60'}>{count}</span>
                  </button>
                )
              })}
            </div>
          </div>

          <div className="mb-6 flex items-center justify-between gap-4 text-sm text-storm-grey" aria-live="polite">
            <p>
              Hiển thị <strong className="text-ink">{filteredArticles.length}</strong> bài viết
            </p>
            {(query || category !== 'Tất cả' || sortOrder !== 'newest') && (
              <button
                type="button"
                onClick={clearFilters}
                className="font-semibold text-seafoam hover:text-seafoam-light"
              >
                Xóa bộ lọc
              </button>
            )}
          </div>

          {visibleArticles.length > 0 ? (
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {visibleArticles.map((article, index) => (
                <RevealOnScroll key={article.id} delay={(index % 3) * 80}>
                  <NewsCard article={article} />
                </RevealOnScroll>
              ))}
            </div>
          ) : (
            <div className="rounded-2xl border border-dashed border-mist-mid bg-white px-6 py-16 text-center">
              <div className="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-seafoam-pale text-2xl">
                🔎
              </div>
              <h3 className="mb-2 text-xl font-bold text-ocean-deep">Không tìm thấy bài viết</h3>
              <p className="mb-6 text-sm text-storm-grey">
                Hãy thử từ khóa khác hoặc xóa bộ lọc hiện tại.
              </p>
              <button type="button" onClick={clearFilters} className="btn btn-secondary">
                Xem tất cả tin tức
              </button>
            </div>
          )}

          {totalPages > 1 && (
            <nav className="mt-12 flex items-center justify-center gap-2" aria-label="Phân trang tin tức">
              <button
                type="button"
                onClick={() => setCurrentPage(page => Math.max(1, page - 1))}
                disabled={currentPage === 1}
                className="h-10 rounded-lg border border-light-mist bg-white px-4 text-sm font-semibold text-ocean-deep transition hover:border-seafoam disabled:cursor-not-allowed disabled:opacity-40"
              >
                Trước
              </button>
              {Array.from({ length: totalPages }, (_, index) => index + 1).map(page => (
                <button
                  key={page}
                  type="button"
                  onClick={() => setCurrentPage(page)}
                  className={[
                    'h-10 min-w-10 rounded-lg border px-3 text-sm font-bold transition',
                    currentPage === page
                      ? 'border-ocean-deep bg-ocean-deep text-white'
                      : 'border-light-mist bg-white text-ocean-deep hover:border-seafoam',
                  ].join(' ')}
                  aria-current={currentPage === page ? 'page' : undefined}
                  aria-label={`Trang ${page}`}
                >
                  {page}
                </button>
              ))}
              <button
                type="button"
                onClick={() => setCurrentPage(page => Math.min(totalPages, page + 1))}
                disabled={currentPage === totalPages}
                className="h-10 rounded-lg border border-light-mist bg-white px-4 text-sm font-semibold text-ocean-deep transition hover:border-seafoam disabled:cursor-not-allowed disabled:opacity-40"
              >
                Sau
              </button>
            </nav>
          )}
        </div>
      </main>

      <section className="bg-white py-20">
        <div className="container">
          <div className="relative overflow-hidden rounded-3xl bg-ocean-deep px-6 py-12 sm:px-10 lg:px-16">
            <div
              className="absolute inset-0 opacity-20"
              style={{ background: 'radial-gradient(circle at 90% 20%, #1A936F, transparent 40%)' }}
            />
            <div className="relative z-10 grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
              <div className="max-w-2xl">
                <span className="mb-3 block text-xs font-bold uppercase tracking-[0.16em] text-coral-gold">
                  Bản tin IDI
                </span>
                <h2 className="mb-3 text-2xl font-black text-white sm:text-3xl">
                  Nhận tin mới trực tiếp qua email
                </h2>
                <p className="text-sm leading-relaxed text-white/60">
                  Cập nhật hoạt động doanh nghiệp, báo cáo thị trường và câu chuyện phát triển
                  bền vững. Không gửi thư rác.
                </p>
              </div>

              {subscribed ? (
                <div className="rounded-xl border border-seafoam-light/30 bg-seafoam/20 px-6 py-4 font-semibold text-white" role="status">
                  ✓ Cảm ơn bạn đã đăng ký nhận tin.
                </div>
              ) : (
                <form onSubmit={handleSubscribe} className="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                  <label className="sr-only" htmlFor="news-email">Địa chỉ email</label>
                  <input
                    id="news-email"
                    type="email"
                    required
                    value={email}
                    onChange={event => setEmail(event.target.value)}
                    placeholder="email@congty.com"
                    className="h-12 min-w-0 rounded-xl border border-white/15 bg-white/10 px-4 text-sm text-white outline-none placeholder:text-white/35 focus:border-seafoam-light sm:w-72"
                  />
                  <button type="submit" className="btn btn-gold h-12 whitespace-nowrap px-6">
                    Đăng ký nhận tin
                  </button>
                </form>
              )}
            </div>
          </div>
        </div>
      </section>
    </>
  )
}
