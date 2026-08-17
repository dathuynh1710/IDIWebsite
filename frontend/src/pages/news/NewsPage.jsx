import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router'
import PageHead from '@components/common/PageHead'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { useDebounce } from '@hooks/useDebounce'
import { useLanguage } from '@hooks/useLanguage'
import {
  DEFAULT_NEWS_PAGE_CONFIG,
  newsService,
} from '@services/news.service'
import { SITE_URL } from '@utils/constants'

const CATEGORY_STYLES = [
  'bg-[#EBF4FF] text-ocean-deep',
  'bg-seafoam-pale text-seafoam',
  'bg-coral-pale text-[#9A6517]',
  'bg-[#F1EEFF] text-[#6246A8]',
  'bg-[#E9F7FB] text-[#176B87]',
  'bg-[#FFF1EA] text-[#A5542D]',
]

const DATE_LOCALES = { vi: 'vi-VN', en: 'en-US', zh: 'zh-CN' }

function categoryStyle(category) {
  const token = `${category?.code ?? ''}${category?.slug ?? ''}${category?.name ?? ''}`
  const score = Array.from(token).reduce((total, character) => total + character.charCodeAt(0), 0)
  return CATEGORY_STYLES[score % CATEGORY_STYLES.length]
}

function formatDate(date, language) {
  if (!date) return ''
  const value = new Date(date)
  if (Number.isNaN(value.getTime())) return ''
  return value.toLocaleDateString(DATE_LOCALES[language] ?? 'vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

function ArticleImage({ article, eager = false, showPlaceholder = true }) {
  const [failed, setFailed] = useState(false)
  const visible = article.imageUrl && !failed

  return (
    <div className="absolute inset-0 bg-gradient-to-br from-ocean-deep to-seafoam">
      {visible && (
        <img
          src={article.imageUrl}
          alt={article.imageAlt || article.title}
          className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
          loading={eager ? 'eager' : 'lazy'}
          fetchPriority={eager ? 'high' : undefined}
          onError={() => setFailed(true)}
        />
      )}
      {!visible && showPlaceholder && (
        <span className="absolute inset-0 grid place-items-center text-3xl font-black tracking-[0.2em] text-white/45">
          IDI
        </span>
      )}
      <div className="absolute inset-0 bg-gradient-to-t from-ocean-deep/30 to-transparent" />
    </div>
  )
}

function ArticleMeta({ article, pageConfig, language, featured = false }) {
  const date = formatDate(article.publishedAt, language)
  return (
    <div className={`flex flex-wrap items-center gap-3 text-xs ${featured ? 'text-storm-grey' : ''}`}>
      {article.category && (
        <span className={`rounded-full px-3 py-1 font-bold ${categoryStyle(article.category)}`}>
          {article.category.name}
        </span>
      )}
      {pageConfig.showPublishedDate && date && (
        <time dateTime={article.publishedAt} className="text-storm-grey">{date}</time>
      )}
      {pageConfig.showReadingTime && article.readTime > 0 && (
        <span className="text-storm-grey" aria-label={`${article.readTime} phút đọc`}>
          · {article.readTime} phút đọc
        </span>
      )}
      {pageConfig.showViewCount && article.viewCount > 0 && (
        <span className="text-storm-grey">· {article.viewCount.toLocaleString(DATE_LOCALES[language])} lượt xem</span>
      )}
    </div>
  )
}

function NewsCard({ article, pageConfig, language }) {
  return (
    <article className="group flex h-full flex-col overflow-hidden rounded-2xl border border-light-mist bg-white transition-all duration-300 hover:-translate-y-1 hover:border-transparent hover:shadow-xl">
      <Link
        to={`/news/${article.slug}`}
        className="relative block aspect-[16/10] overflow-hidden bg-light-mist"
        aria-label={`Đọc bài: ${article.title}`}
      >
        <ArticleImage article={article} showPlaceholder={pageConfig.showPlaceholderImage} />
      </Link>

      <div className="flex flex-1 flex-col p-6">
        <div className="mb-4">
          <ArticleMeta article={article} pageConfig={pageConfig} language={language} />
        </div>
        <h2 className="mb-3 text-lg font-bold leading-snug text-ocean-deep transition-colors group-hover:text-seafoam line-clamp-2">
          <Link to={`/news/${article.slug}`}>{article.title}</Link>
        </h2>
        {article.excerpt && (
          <p className="mb-5 flex-1 text-sm leading-relaxed text-storm-grey line-clamp-3">
            {article.excerpt}
          </p>
        )}
        <Link
          to={`/news/${article.slug}`}
          className="inline-flex items-center gap-2 text-sm font-semibold text-seafoam transition-all group-hover:gap-3"
        >
          Đọc bài viết <span aria-hidden="true">→</span>
        </Link>
      </div>
    </article>
  )
}

function LoadingGrid() {
  return (
    <div className="grid animate-pulse gap-6 sm:grid-cols-2 lg:grid-cols-3" aria-label="Đang tải tin tức">
      {Array.from({ length: 6 }, (_, index) => (
        <div key={index} className="overflow-hidden rounded-2xl border border-light-mist bg-white">
          <div className="aspect-[16/10] bg-light-mist" />
          <div className="space-y-4 p-6">
            <div className="h-4 w-1/3 rounded bg-light-mist" />
            <div className="h-6 rounded bg-light-mist" />
            <div className="h-16 rounded bg-light-mist" />
          </div>
        </div>
      ))}
    </div>
  )
}

function paginationItems(currentPage, lastPage) {
  if (lastPage <= 7) return Array.from({ length: lastPage }, (_, index) => index + 1)
  const pages = new Set([1, lastPage, currentPage - 1, currentPage, currentPage + 1])
  const valid = [...pages].filter(page => page >= 1 && page <= lastPage).sort((a, b) => a - b)
  return valid.flatMap((page, index) => (
    index > 0 && page - valid[index - 1] > 1 ? ['…', page] : [page]
  ))
}

export default function NewsPage() {
  const { language } = useLanguage()
  const [query, setQuery] = useState('')
  const debouncedQuery = useDebounce(query.trim(), 350)
  const [category, setCategory] = useState('')
  const [sortOrder, setSortOrder] = useState('newest')
  const [currentPage, setCurrentPage] = useState(1)
  const [requestKey, setRequestKey] = useState(0)
  const [status, setStatus] = useState('loading')
  const [allTotal, setAllTotal] = useState(0)
  const [result, setResult] = useState({
    items: [],
    featured: [],
    categories: [],
    pageConfig: DEFAULT_NEWS_PAGE_CONFIG,
    total: 0,
    page: 1,
    limit: DEFAULT_NEWS_PAGE_CONFIG.itemsPerPage,
    lastPage: 1,
  })

  useEffect(() => {
    setCurrentPage(1)
    setCategory('')
  }, [language])

  useEffect(() => {
    const controller = new AbortController()
    setStatus(previous => (previous === 'success' || previous === 'refreshing' ? 'refreshing' : 'loading'))

    newsService.getAll({
      locale: language,
      page: currentPage,
      category,
      search: debouncedQuery,
      sort: sortOrder,
    }, { signal: controller.signal })
      .then((data) => {
        setResult(data)
        if (!category && !debouncedQuery) setAllTotal(data.total)
        setStatus('success')
      })
      .catch((error) => {
        if (error?.code !== 'ERR_CANCELED') setStatus('error')
      })

    return () => controller.abort()
  }, [category, currentPage, debouncedQuery, language, requestKey, sortOrder])

  const pageConfig = result.pageConfig
  const featuredArticle = result.featured[0] ?? null
  const allCategoryCount = useMemo(
    () => Math.max(allTotal, result.categories.reduce((total, item) => total + item.count, 0)),
    [allTotal, result.categories],
  )
  const hasFilters = Boolean(query || category || sortOrder !== 'newest')
  const showFeatured = Boolean(
    pageConfig.showFeaturedSection && featuredArticle && !hasFilters && currentPage === 1,
  )

  const clearFilters = () => {
    setQuery('')
    setCategory('')
    setSortOrder('newest')
    setCurrentPage(1)
  }

  const selectCategory = (slug) => {
    setCategory(slug)
    setCurrentPage(1)
  }

  const selectSort = (value) => {
    setSortOrder(value)
    setCurrentPage(1)
  }

  return (
    <>
      <PageHead
        title={pageConfig.seo.title || `${pageConfig.title} | IDI Seafood`}
        description={pageConfig.seo.description || pageConfig.description}
        keywords={pageConfig.seo.keywords}
        canonical={`${SITE_URL}/news`}
        ogTitle={pageConfig.seo.ogTitle}
        ogDescription={pageConfig.seo.ogDescription}
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
            <h1 className="mb-5 text-h1 font-black text-white text-balance">{pageConfig.title}</h1>
            {pageConfig.description && (
              <p className="max-w-2xl text-body-lg leading-relaxed text-white/65">
                {pageConfig.description}
              </p>
            )}
          </div>
        </div>
      </header>

      {showFeatured && status !== 'loading' && (
        <section className="relative z-10 -mt-14 pb-20 lg:-mt-20 lg:pb-28">
          <div className="container">
            <RevealOnScroll>
              <article className="grid overflow-hidden rounded-3xl border border-light-mist bg-white shadow-2xl lg:grid-cols-[1.15fr_0.85fr]">
                <Link
                  to={`/news/${featuredArticle.slug}`}
                  className="group relative block min-h-[18rem] overflow-hidden lg:min-h-[29rem]"
                  aria-label={`Đọc bài nổi bật: ${featuredArticle.title}`}
                >
                  <ArticleImage
                    article={featuredArticle}
                    eager
                    showPlaceholder={pageConfig.showPlaceholderImage}
                  />
                </Link>
                <div className="flex flex-col justify-center p-7 sm:p-10 lg:p-12">
                  <span className="mb-5 text-xs font-bold uppercase tracking-[0.16em] text-seafoam">
                    Bài viết nổi bật
                  </span>
                  <div className="mb-5">
                    <ArticleMeta article={featuredArticle} pageConfig={pageConfig} language={language} featured />
                  </div>
                  <h2 className="mb-5 text-2xl font-black leading-tight text-ocean-deep text-balance sm:text-3xl">
                    {featuredArticle.title}
                  </h2>
                  {featuredArticle.excerpt && (
                    <p className="mb-8 leading-relaxed text-storm-grey">{featuredArticle.excerpt}</p>
                  )}
                  <Link to={`/news/${featuredArticle.slug}`} className="btn btn-primary self-start">
                    Đọc bài nổi bật <span aria-hidden="true">→</span>
                  </Link>
                </div>
              </article>
            </RevealOnScroll>
          </div>
        </section>
      )}

      <main className={`bg-arctic-white pb-24 lg:pb-32 ${!showFeatured ? 'pt-20' : ''}`}>
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
                  <svg className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-storm-grey" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <circle cx="9" cy="9" r="5.75" stroke="currentColor" strokeWidth="1.5" />
                    <path d="m13.5 13.5 4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                  </svg>
                  <input
                    type="search"
                    value={query}
                    onChange={(event) => {
                      setQuery(event.target.value)
                      setCurrentPage(1)
                    }}
                    placeholder="Tìm theo tiêu đề, nội dung..."
                    className="h-12 w-full rounded-xl border border-light-mist bg-white pl-11 pr-4 text-sm text-ink outline-none transition focus:border-seafoam focus:ring-2 focus:ring-seafoam/15"
                  />
                </label>
                <label>
                  <span className="sr-only">Sắp xếp bài viết</span>
                  <select
                    value={sortOrder}
                    onChange={event => selectSort(event.target.value)}
                    className="h-12 w-full rounded-xl border border-light-mist bg-white px-4 text-sm font-medium text-ink outline-none transition focus:border-seafoam focus:ring-2 focus:ring-seafoam/15 sm:w-auto"
                  >
                    <option value="newest">Mới nhất</option>
                    <option value="oldest">Cũ nhất</option>
                  </select>
                </label>
              </div>
            </div>

            {pageConfig.showCategoryNavigation && result.categories.length > 0 && (
              <div className="flex flex-wrap gap-2" aria-label="Lọc theo chuyên mục">
                <button
                  type="button"
                  onClick={() => selectCategory('')}
                  className={`rounded-full border px-4 py-2 text-sm font-semibold transition-colors ${
                    !category
                      ? 'border-ocean-deep bg-ocean-deep text-white'
                      : 'border-light-mist bg-white text-storm-grey hover:border-seafoam hover:text-seafoam'
                  }`}
                  aria-pressed={!category}
                >
                  Tất cả <span className={!category ? 'text-white/55' : 'text-storm-grey/60'}>{allCategoryCount}</span>
                </button>
                {result.categories.map(item => {
                  const isActive = category === item.slug
                  return (
                    <button
                      key={item.id}
                      type="button"
                      onClick={() => selectCategory(item.slug)}
                      className={`rounded-full border px-4 py-2 text-sm font-semibold transition-colors ${
                        isActive
                          ? 'border-ocean-deep bg-ocean-deep text-white'
                          : 'border-light-mist bg-white text-storm-grey hover:border-seafoam hover:text-seafoam'
                      }`}
                      aria-pressed={isActive}
                    >
                      {item.name} <span className={isActive ? 'text-white/55' : 'text-storm-grey/60'}>{item.count}</span>
                    </button>
                  )
                })}
              </div>
            )}
          </div>

          <div className="mb-6 flex items-center justify-between gap-4 text-sm text-storm-grey" aria-live="polite">
            <p>Hiển thị <strong className="text-ink">{result.total}</strong> bài viết</p>
            {hasFilters && (
              <button type="button" onClick={clearFilters} className="font-semibold text-seafoam hover:text-seafoam-light">
                Xóa bộ lọc
              </button>
            )}
          </div>

          {status === 'loading' ? (
            <LoadingGrid />
          ) : status === 'error' ? (
            <div className="rounded-2xl border border-dashed border-mist-mid bg-white px-6 py-16 text-center" role="alert">
              <h3 className="mb-2 text-xl font-bold text-ocean-deep">Không thể tải tin tức</h3>
              <p className="mb-6 text-sm text-storm-grey">Vui lòng kiểm tra kết nối và thử lại.</p>
              <button type="button" onClick={() => setRequestKey(key => key + 1)} className="btn btn-primary">
                Thử tải lại
              </button>
            </div>
          ) : result.items.length > 0 ? (
            <div className={`grid gap-6 transition-opacity sm:grid-cols-2 lg:grid-cols-3 ${status === 'refreshing' ? 'opacity-55' : ''}`} aria-busy={status === 'refreshing'}>
              {result.items.map((article, index) => (
                <RevealOnScroll key={article.id} delay={(index % 3) * 80}>
                  <NewsCard article={article} pageConfig={pageConfig} language={language} />
                </RevealOnScroll>
              ))}
            </div>
          ) : (
            <div className="rounded-2xl border border-dashed border-mist-mid bg-white px-6 py-16 text-center">
              <div className="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-seafoam-pale text-2xl">🔎</div>
              <h3 className="mb-2 text-xl font-bold text-ocean-deep">Không tìm thấy bài viết</h3>
              <p className="mb-6 text-sm text-storm-grey">Hãy thử từ khóa khác hoặc xóa bộ lọc hiện tại.</p>
              <button type="button" onClick={clearFilters} className="btn btn-secondary">Xem tất cả tin tức</button>
            </div>
          )}

          {status !== 'error' && result.lastPage > 1 && (
            <nav className="mt-12 flex items-center justify-center gap-2" aria-label="Phân trang tin tức">
              <button
                type="button"
                onClick={() => setCurrentPage(page => Math.max(1, page - 1))}
                disabled={currentPage === 1 || status === 'refreshing'}
                className="h-10 rounded-lg border border-light-mist bg-white px-4 text-sm font-semibold text-ocean-deep transition hover:border-seafoam disabled:cursor-not-allowed disabled:opacity-40"
              >
                Trước
              </button>
              {paginationItems(currentPage, result.lastPage).map((item, index) => (
                item === '…' ? (
                  <span key={`ellipsis-${index}`} className="px-1 text-storm-grey" aria-hidden="true">…</span>
                ) : (
                  <button
                    key={item}
                    type="button"
                    onClick={() => setCurrentPage(item)}
                    disabled={status === 'refreshing'}
                    className={`h-10 min-w-10 rounded-lg border px-3 text-sm font-bold transition ${
                      currentPage === item
                        ? 'border-ocean-deep bg-ocean-deep text-white'
                        : 'border-light-mist bg-white text-ocean-deep hover:border-seafoam'
                    }`}
                    aria-current={currentPage === item ? 'page' : undefined}
                    aria-label={`Trang ${item}`}
                  >
                    {item}
                  </button>
                )
              ))}
              <button
                type="button"
                onClick={() => setCurrentPage(page => Math.min(result.lastPage, page + 1))}
                disabled={currentPage === result.lastPage || status === 'refreshing'}
                className="h-10 rounded-lg border border-light-mist bg-white px-4 text-sm font-semibold text-ocean-deep transition hover:border-seafoam disabled:cursor-not-allowed disabled:opacity-40"
              >
                Sau
              </button>
            </nav>
          )}
        </div>
      </main>
    </>
  )
}
