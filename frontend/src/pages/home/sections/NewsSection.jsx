import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { useLanguage } from '@hooks/useLanguage'
import { DEFAULT_NEWS_PAGE_CONFIG, newsService } from '@services/news.service'

const CATEGORY_COLORS = {
  gold: { bg: 'bg-coral-pale', text: 'text-[#B37518]' },
  blue: { bg: 'bg-[#EBF4FF]', text: 'text-ocean-deep' },
  green: { bg: 'bg-seafoam-pale', text: 'text-seafoam' },
}

const DATE_LOCALES = { vi: 'vi-VN', en: 'en-US', zh: 'zh-CN' }

function formatDate(iso, language) {
  if (!iso) return ''
  const date = new Date(iso)
  if (Number.isNaN(date.getTime())) return ''
  return date.toLocaleDateString(DATE_LOCALES[language] ?? 'vi-VN', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function LoadingCards() {
  return (
    <div className="grid animate-pulse gap-6 sm:grid-cols-2 lg:grid-cols-3" aria-label="Đang tải tin mới">
      {Array.from({ length: 3 }, (_, index) => (
        <div key={index} className="overflow-hidden rounded-2xl border border-light-mist bg-white">
          <div className="aspect-[16/9] bg-light-mist" />
          <div className="space-y-4 p-6">
            <div className="h-4 w-1/3 rounded bg-light-mist" />
            <div className="h-12 rounded bg-light-mist" />
            <div className="h-16 rounded bg-light-mist" />
          </div>
        </div>
      ))}
    </div>
  )
}

export default function NewsSection() {
  const { language } = useLanguage()
  const [requestKey, setRequestKey] = useState(0)
  const [status, setStatus] = useState('loading')
  const [response, setResponse] = useState({
    items: [],
    featured: [],
    pageConfig: DEFAULT_NEWS_PAGE_CONFIG,
  })

  useEffect(() => {
    const controller = new AbortController()
    setStatus('loading')

    newsService.getAll({ locale: language, page: 1, sort: 'newest' }, { signal: controller.signal })
      .then((data) => {
        setResponse(data)
        setStatus('success')
      })
      .catch((error) => {
        if (error?.code !== 'ERR_CANCELED') setStatus('error')
      })

    return () => controller.abort()
  }, [language, requestKey])

  const pageConfig = response.pageConfig
  const articles = useMemo(() => {
    const preferred = pageConfig.showFeaturedSection && response.featured.length
      ? response.featured
      : response.items
    const limit = pageConfig.showFeaturedSection && response.featured.length
      ? pageConfig.featuredLimit
      : pageConfig.itemsPerPage
    return preferred.slice(0, Math.max(1, limit))
  }, [pageConfig, response.featured, response.items])

  return (
    <section className="bg-arctic-white py-20 lg:py-28">
      <div className="container">
        <div className="mb-12 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <RevealOnScroll>
              <span className="section-eyebrow">
                {pageConfig.showFeaturedSection && response.featured.length ? 'Tin nổi bật' : 'Tin mới nhất'}
              </span>
            </RevealOnScroll>
            <RevealOnScroll delay={80}>
              <h2 className="mt-3 text-h2 font-bold text-ocean-deep">{pageConfig.title}</h2>
            </RevealOnScroll>
          </div>
          <RevealOnScroll direction="right">
            <Link to="/news" className="btn btn-secondary whitespace-nowrap">Xem tất cả →</Link>
          </RevealOnScroll>
        </div>

        {status === 'loading' ? (
          <LoadingCards />
        ) : status === 'error' ? (
          <div className="rounded-2xl border border-dashed border-mist-mid bg-white px-6 py-12 text-center" role="alert">
            <h3 className="text-xl font-bold text-ocean-deep">Không thể tải tin mới</h3>
            <p className="mt-2 text-sm">Vui lòng kiểm tra kết nối và thử lại.</p>
            <button type="button" onClick={() => setRequestKey(key => key + 1)} className="btn btn-primary mt-6">
              Thử tải lại
            </button>
          </div>
        ) : articles.length === 0 ? (
          <div className="rounded-2xl border border-dashed border-mist-mid bg-white px-6 py-12 text-center">
            <p>Nội dung tin tức đang được cập nhật.</p>
          </div>
        ) : (
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {articles.map((article, index) => {
              const color = CATEGORY_COLORS[article.categoryColor] ?? CATEGORY_COLORS.blue
              const date = formatDate(article.publishedAt, language)
              return (
                <RevealOnScroll key={article.id} delay={(index % 3) * 100}>
                  <Link
                    to={`/news/${article.slug}`}
                    className="group flex h-full flex-col overflow-hidden rounded-2xl border border-light-mist bg-white transition-all duration-300 hover:-translate-y-1 hover:border-transparent hover:shadow-xl"
                  >
                    <div className="relative aspect-[16/9] flex-shrink-0 overflow-hidden bg-gradient-to-br from-ocean-deep to-seafoam">
                      {article.imageUrl ? (
                        <img
                          src={article.imageUrl}
                          alt={article.imageAlt || article.title}
                          className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                          loading={pageConfig.lazyLoadImages ? 'lazy' : 'eager'}
                          onError={(event) => {
                            event.currentTarget.style.display = 'none'
                          }}
                        />
                      ) : pageConfig.showPlaceholderImage ? (
                        <span className="absolute inset-0 grid place-items-center text-3xl font-black tracking-[0.2em] text-white/45">IDI</span>
                      ) : null}
                    </div>

                    <div className="flex flex-1 flex-col p-6">
                      <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        {article.category && (
                          <span className={`badge text-[11px] ${color.bg} ${color.text}`}>{article.category.name}</span>
                        )}
                        {pageConfig.showPublishedDate && date && (
                          <time dateTime={article.publishedAt} className="text-xs text-storm-grey">{date}</time>
                        )}
                      </div>
                      <h3 className="mb-3 text-base font-bold leading-snug text-ink transition-colors duration-200 group-hover:text-ocean-deep line-clamp-2">
                        {article.title}
                      </h3>
                      {article.excerpt && (
                        <p className="flex-1 text-sm leading-relaxed text-storm-grey line-clamp-3">{article.excerpt}</p>
                      )}
                      <div className="mt-5 flex items-center gap-3 text-xs font-semibold text-seafoam">
                        <span className="inline-flex items-center gap-1.5">
                          Đọc thêm <span aria-hidden="true">→</span>
                        </span>
                        {pageConfig.showReadingTime && article.readTime > 0 && (
                          <span className="font-medium text-storm-grey">{article.readTime} phút</span>
                        )}
                      </div>
                    </div>
                  </Link>
                </RevealOnScroll>
              )
            })}
          </div>
        )}
      </div>
    </section>
  )
}
