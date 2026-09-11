import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import NewsImage from '@components/common/NewsImage'
import PageHead from '@components/common/PageHead'
import { useDebounce } from '@hooks/useDebounce'
import { useLanguage } from '@hooks/useLanguage'
import { DEFAULT_NEWS_PAGE_CONFIG, newsService } from '@services/news.service'
import { SITE_URL } from '@utils/constants'

const DATE_LOCALES = { vi: 'vi-VN', en: 'en-US', 'zh-CN': 'zh-CN' }

const COPY = {
  vi: {
    eyebrow: 'Thông tin doanh nghiệp', featured: 'Tiêu điểm', latest: 'Tin tức mới nhất',
    search: 'Tìm kiếm bài viết', searchPlaceholder: 'Tìm theo tiêu đề...', sort: 'Sắp xếp bài viết',
    newest: 'Mới nhất', oldest: 'Cũ nhất', all: 'Tất cả', readFeatured: 'Đọc bài viết',
    clear: 'Xóa bộ lọc',
    loading: 'Đang tải tin tức', errorTitle: 'Không thể tải tin tức',
    errorDescription: 'Vui lòng kiểm tra kết nối và thử lại.', retry: 'Thử lại',
    emptyTitle: 'Không tìm thấy bài viết', emptyDescription: 'Hãy thử từ khóa hoặc chuyên mục khác.',
    showAll: 'Xem tất cả tin tức', pagination: 'Phân trang tin tức', page: 'Trang',
    previous: 'Trước', next: 'Sau', readArticle: 'Đọc bài',
  },
  en: {
    eyebrow: 'Corporate updates', featured: 'Featured', latest: 'Latest news',
    search: 'Search articles', searchPlaceholder: 'Search by title...', sort: 'Sort articles',
    newest: 'Newest', oldest: 'Oldest', all: 'All', readFeatured: 'Read article',
    clear: 'Clear filters', loading: 'Loading news',
    errorTitle: 'Unable to load news', errorDescription: 'Please check your connection and try again.',
    retry: 'Try again', emptyTitle: 'No articles found', emptyDescription: 'Try another keyword or category.',
    showAll: 'View all news', pagination: 'News pagination', page: 'Page',
    previous: 'Previous', next: 'Next', readArticle: 'Read article',
  },
  'zh-CN': {
    eyebrow: '企业动态', featured: '重点关注', latest: '最新资讯',
    search: '搜索文章', searchPlaceholder: '按标题搜索...', sort: '文章排序', newest: '最新',
    oldest: '最早', all: '全部', readFeatured: '阅读文章',
    clear: '清除筛选', loading: '正在加载新闻', errorTitle: '无法加载新闻',
    errorDescription: '请检查网络连接后重试。', retry: '重试', emptyTitle: '未找到文章',
    emptyDescription: '请尝试其他关键词或分类。', showAll: '查看全部新闻', pagination: '新闻分页',
    page: '第', previous: '上一页', next: '下一页', readArticle: '阅读文章',
  },
}

function formatDate(date, language) {
  if (!date) return ''
  const value = new Date(date)
  if (Number.isNaN(value.getTime())) return ''

  return value.toLocaleDateString(DATE_LOCALES[language] ?? 'vi-VN', {
    day: '2-digit', month: '2-digit', year: 'numeric',
  })
}

function ArrowIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" className="h-4 w-4" aria-hidden="true">
      <path d="M4 10h11M11 6l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  )
}

function SearchIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" className="h-4 w-4" aria-hidden="true">
      <circle cx="9" cy="9" r="5.75" stroke="currentColor" strokeWidth="1.5" />
      <path d="m13.5 13.5 4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  )
}

function ArticleImage({ article, eager = false }) {
  return (
    <div className="absolute inset-0 bg-light-mist">
      <NewsImage
        article={article}
        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.025]"
        loading={eager ? 'eager' : 'lazy'}
        fetchPriority={eager ? 'high' : undefined}
      />
    </div>
  )
}

function ArticleMeta({ article, pageConfig, language }) {
  const date = formatDate(article.publishedAt, language)

  return (
    <div className="flex flex-wrap items-center gap-2 text-xs">
      {article.category && (
        <span className="font-bold uppercase tracking-[0.1em] text-seafoam">{article.category.name}</span>
      )}
      {article.category && pageConfig.showPublishedDate && date && (
        <span className="text-mist-mid" aria-hidden="true">•</span>
      )}
      {pageConfig.showPublishedDate && date && (
        <time dateTime={article.publishedAt} className="text-storm-grey">{date}</time>
      )}
    </div>
  )
}

function NewsCard({ article, pageConfig, language, labels }) {
  const href = `/news/${article.slug}`

  return (
    <article className="group flex h-full flex-col border-t border-mist-mid pt-4">
      <Link
        to={href}
        className="relative block aspect-[16/10] overflow-hidden bg-light-mist focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-seafoam"
        aria-label={`${labels.readArticle}: ${article.title}`}
      >
        <ArticleImage
          article={article}
        />
      </Link>

      <div className="flex flex-1 flex-col pt-5">
        <ArticleMeta article={article} pageConfig={pageConfig} language={language} />
        <h2 className="mt-3 text-xl font-bold leading-snug tracking-[-0.015em] text-ocean-deep line-clamp-2">
          <Link
            to={href}
            className="transition-colors hover:text-seafoam focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-seafoam"
          >
            {article.title}
          </Link>
        </h2>
      </div>
    </article>
  )
}

function LoadingGrid({ label }) {
  return (
    <div className="grid animate-pulse gap-x-7 gap-y-14 sm:grid-cols-2 lg:grid-cols-3" aria-busy="true" aria-label={label}>
      {Array.from({ length: 6 }, (_, index) => (
        <div key={index} className="border-t border-mist-mid pt-4">
          <div className="aspect-[16/10] bg-light-mist" />
          <div className="mt-5 h-3 w-1/3 bg-light-mist" />
          <div className="mt-4 h-6 w-full bg-light-mist" />
          <div className="mt-2 h-6 w-3/4 bg-light-mist" />
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
  const labels = COPY[language] ?? COPY.vi
  const [query, setQuery] = useState('')
  const debouncedQuery = useDebounce(query.trim(), 350)
  const [category, setCategory] = useState('')
  const [sortOrder, setSortOrder] = useState('newest')
  const [currentPage, setCurrentPage] = useState(1)
  const [requestKey, setRequestKey] = useState(0)
  const [status, setStatus] = useState('loading')
  const [result, setResult] = useState({
    items: [], featured: [], categories: [], pageConfig: DEFAULT_NEWS_PAGE_CONFIG,
    total: 0, page: 1, limit: DEFAULT_NEWS_PAGE_CONFIG.itemsPerPage, lastPage: 1,
  })

  useEffect(() => {
    setCurrentPage(1)
    setCategory('')
    setQuery('')
    setSortOrder('newest')
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
      .then((data) => { setResult(data); setStatus('success') })
      .catch((error) => { if (error?.code !== 'ERR_CANCELED') setStatus('error') })

    return () => controller.abort()
  }, [category, currentPage, debouncedQuery, language, requestKey, sortOrder])

  const pageConfig = result.pageConfig
  const featuredArticle = result.featured[0] ?? null
  const hasFilters = Boolean(query || category || sortOrder !== 'newest')
  const showFeatured = Boolean(
    pageConfig.showFeaturedSection && featuredArticle && !hasFilters && currentPage === 1,
  )
  const articles = showFeatured
    ? result.items.filter(article => article.id !== featuredArticle.id)
    : result.items

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

  const changePage = (page) => {
    setCurrentPage(page)
    document.getElementById('news-library')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
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

      <header className="border-b border-light-mist bg-white pb-12 pt-28 sm:pb-14 sm:pt-32 lg:pb-16 lg:pt-36">
        <div className="container">
          <div className="grid gap-7 lg:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.65fr)] lg:items-end lg:gap-16">
            <div>
              <span className="mb-5 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.16em] text-seafoam">
                <span className="h-px w-8 bg-seafoam" />
                {labels.eyebrow}
              </span>
              <h1 className="max-w-4xl text-[clamp(2.5rem,5.5vw,4.75rem)] font-bold leading-[1.04] tracking-[-0.045em] text-ocean-deep text-balance">
                {pageConfig.title}
              </h1>
            </div>
            {pageConfig.description && (
              <p className="border-l-2 border-coral-gold pl-5 text-base leading-7 text-slate">
                {pageConfig.description}
              </p>
            )}
          </div>
        </div>
      </header>

      {showFeatured && status !== 'loading' && (
        <section className="bg-arctic-white py-14 lg:py-20" aria-labelledby="featured-news-title">
          <div className="container">
            <div className="mb-5 border-b border-mist-mid pb-4">
              <p className="text-xs font-semibold uppercase tracking-[0.15em] text-seafoam">{labels.featured}</p>
            </div>
            <article className="grid border border-light-mist bg-white lg:grid-cols-[1.2fr_0.8fr]">
              <Link
                to={`/news/${featuredArticle.slug}`}
                className="group relative block min-h-72 overflow-hidden bg-light-mist focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-seafoam lg:min-h-[28rem]"
                aria-label={`${labels.readFeatured}: ${featuredArticle.title}`}
              >
                <ArticleImage
                  article={featuredArticle}
                  eager
                />
              </Link>
              <div className="flex flex-col justify-center border-t border-light-mist p-7 sm:p-10 lg:border-l lg:border-t-0 lg:p-12">
                <ArticleMeta article={featuredArticle} pageConfig={pageConfig} language={language} />
                <h2 id="featured-news-title" className="mt-5 text-[clamp(1.75rem,3vw,2.5rem)] font-bold leading-tight tracking-[-0.03em] text-ocean-deep text-balance">
                  {featuredArticle.title}
                </h2>
                {featuredArticle.excerpt && <p className="mt-5 leading-7 text-slate line-clamp-3">{featuredArticle.excerpt}</p>}
                <Link
                  to={`/news/${featuredArticle.slug}`}
                  className="mt-8 inline-flex w-fit items-center gap-2 border-b border-ocean-deep/25 pb-1 text-sm font-semibold text-ocean-deep transition-colors hover:border-seafoam hover:text-seafoam focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-seafoam"
                >
                  {labels.readFeatured} <ArrowIcon />
                </Link>
              </div>
            </article>
          </div>
        </section>
      )}

      <main id="news-library" className="scroll-mt-24 bg-white py-16 lg:py-24">
        <div className="container">
          <div className="flex flex-col gap-7 border-b border-mist-mid pb-7">
            <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
              <h2 className="text-[clamp(1.75rem,3vw,2.5rem)] font-bold leading-tight tracking-[-0.025em] text-ocean-deep">{labels.latest}</h2>
              <div className="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                <label className="relative min-w-0 flex-1 lg:w-72">
                  <span className="sr-only">{labels.search}</span>
                  <span className="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-storm-grey"><SearchIcon /></span>
                  <input
                    type="search"
                    value={query}
                    onChange={(event) => { setQuery(event.target.value); setCurrentPage(1) }}
                    placeholder={labels.searchPlaceholder}
                    className="h-11 w-full border border-mist-mid bg-white pl-11 pr-4 text-sm text-ink outline-none transition focus:border-seafoam focus:ring-2 focus:ring-seafoam/10"
                  />
                </label>
                <label>
                  <span className="sr-only">{labels.sort}</span>
                  <select
                    value={sortOrder}
                    onChange={(event) => { setSortOrder(event.target.value); setCurrentPage(1) }}
                    className="h-11 w-full border border-mist-mid bg-white px-4 text-sm font-medium text-ink outline-none transition focus:border-seafoam focus:ring-2 focus:ring-seafoam/10 sm:w-auto"
                  >
                    <option value="newest">{labels.newest}</option>
                    <option value="oldest">{labels.oldest}</option>
                  </select>
                </label>
              </div>
            </div>

            <div className="flex items-center justify-between gap-5">
              {pageConfig.showCategoryNavigation && result.categories.length > 0 ? (
                <div className="flex gap-6 overflow-x-auto" aria-label={labels.sort}>
                  <button
                    type="button"
                    onClick={() => selectCategory('')}
                    className={`shrink-0 border-b-2 pb-2 text-sm font-semibold transition-colors ${!category ? 'border-ocean-deep text-ocean-deep' : 'border-transparent text-storm-grey hover:text-seafoam'}`}
                    aria-pressed={!category}
                  >
                    {labels.all}
                  </button>
                  {result.categories.map(item => (
                    <button
                      key={item.id}
                      type="button"
                      onClick={() => selectCategory(item.slug)}
                      className={`shrink-0 border-b-2 pb-2 text-sm font-semibold transition-colors ${category === item.slug ? 'border-ocean-deep text-ocean-deep' : 'border-transparent text-storm-grey hover:text-seafoam'}`}
                      aria-pressed={category === item.slug}
                    >
                      {item.name}
                    </button>
                  ))}
                </div>
              ) : <span />}
              {hasFilters && (
                <button type="button" onClick={clearFilters} className="shrink-0 text-sm font-semibold text-seafoam hover:text-ocean-deep">
                  {labels.clear}
                </button>
              )}
            </div>
          </div>

          <div className="mt-10">
            {status === 'loading' ? (
              <LoadingGrid label={labels.loading} />
            ) : status === 'error' ? (
              <div className="border border-dashed border-mist-mid bg-arctic-white px-6 py-16 text-center" role="alert">
                <h3 className="text-xl font-bold text-ocean-deep">{labels.errorTitle}</h3>
                <p className="mt-2 text-sm text-storm-grey">{labels.errorDescription}</p>
                <button type="button" onClick={() => setRequestKey(key => key + 1)} className="btn btn-primary mt-6">{labels.retry}</button>
              </div>
            ) : articles.length > 0 ? (
              <div className={`grid gap-x-7 gap-y-14 transition-opacity sm:grid-cols-2 lg:grid-cols-3 ${status === 'refreshing' ? 'opacity-50' : ''}`} aria-busy={status === 'refreshing'}>
                {articles.map(article => (
                  <NewsCard key={article.id} article={article} pageConfig={pageConfig} language={language} labels={labels} />
                ))}
              </div>
            ) : showFeatured && result.items.length > 0 ? null : (
              <div className="border border-dashed border-mist-mid bg-arctic-white px-6 py-16 text-center">
                <h3 className="text-xl font-bold text-ocean-deep">{labels.emptyTitle}</h3>
                <p className="mt-2 text-sm text-storm-grey">{labels.emptyDescription}</p>
                <button type="button" onClick={clearFilters} className="btn btn-secondary mt-6">{labels.showAll}</button>
              </div>
            )}
          </div>

          {status !== 'error' && result.lastPage > 1 && (
            <nav className="mt-16 flex items-center justify-between border-t border-mist-mid pt-6" aria-label={labels.pagination}>
              <button
                type="button"
                onClick={() => changePage(Math.max(1, currentPage - 1))}
                disabled={currentPage === 1 || status === 'refreshing'}
                className="text-sm font-semibold text-ocean-deep transition-colors hover:text-seafoam disabled:cursor-not-allowed disabled:opacity-35"
              >
                ← {labels.previous}
              </button>
              <div className="flex items-center gap-1">
                {paginationItems(currentPage, result.lastPage).map((item, index) => (
                  item === '…' ? (
                    <span key={`ellipsis-${index}`} className="px-2 text-storm-grey" aria-hidden="true">…</span>
                  ) : (
                    <button
                      key={item}
                      type="button"
                      onClick={() => changePage(item)}
                      disabled={status === 'refreshing'}
                      className={`h-9 min-w-9 px-2 text-sm font-semibold transition-colors ${currentPage === item ? 'bg-ocean-deep text-white' : 'text-storm-grey hover:bg-arctic-white hover:text-ocean-deep'}`}
                      aria-current={currentPage === item ? 'page' : undefined}
                      aria-label={`${labels.page} ${item}`}
                    >
                      {item}
                    </button>
                  )
                ))}
              </div>
              <button
                type="button"
                onClick={() => changePage(Math.min(result.lastPage, currentPage + 1))}
                disabled={currentPage === result.lastPage || status === 'refreshing'}
                className="text-sm font-semibold text-ocean-deep transition-colors hover:text-seafoam disabled:cursor-not-allowed disabled:opacity-35"
              >
                {labels.next} →
              </button>
            </nav>
          )}
        </div>
      </main>
    </>
  )
}
