import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { useLanguage } from '@hooks/useLanguage'
import { DEFAULT_NEWS_PAGE_CONFIG, newsService } from '@services/news.service'
import { getHomeTranslations } from '@/i18n/home'

const CATEGORY_COLORS = {
  gold: { bg: 'bg-coral-pale', text: 'text-[#B37518]' },
  blue: { bg: 'bg-[#EBF4FF]', text: 'text-ocean-deep' },
  green: { bg: 'bg-seafoam-pale', text: 'text-seafoam' },
}

const DATE_LOCALES = { vi: 'vi-VN', en: 'en-US', 'zh-CN': 'zh-CN' }

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

function LoadingCards({ label }) {
  return (
    <div className="grid animate-pulse gap-6 sm:grid-cols-2 lg:grid-cols-3" aria-label={label}>
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
  const { language, t } = useLanguage()
  const copy = getHomeTranslations(language).news
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
    <section className="bg-arctic-white py-16 lg:py-20">
      <div className="container">
        <div className="mb-12 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <RevealOnScroll>
              <span className="section-eyebrow">
                {pageConfig.showFeaturedSection && response.featured.length ? copy.featured : copy.latest}
              </span>
            </RevealOnScroll>
            <RevealOnScroll delay={80}>
              <h2 className="mt-3 text-h2 font-bold text-ocean-deep">{status === 'loading' ? copy.latest : pageConfig.title}</h2>
            </RevealOnScroll>
          </div>
          <RevealOnScroll direction="right">
            <Link to="/news" className="btn btn-secondary whitespace-nowrap">
              {t('common.viewAll')}
              <svg viewBox="0 0 20 20" fill="none" className="h-4 w-4" aria-hidden="true">
                <path d="M4 10h12m-4-4 4 4-4 4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            </Link>
          </RevealOnScroll>
        </div>

        {status === 'loading' ? (
          <LoadingCards label={copy.loading} />
        ) : status === 'error' ? (
          <div className="rounded-2xl border border-dashed border-mist-mid bg-white px-6 py-12 text-center" role="alert">
            <h3 className="text-xl font-bold text-ocean-deep">{copy.errorTitle}</h3>
            <p className="mt-2 text-sm">{copy.errorText}</p>
            <button type="button" onClick={() => setRequestKey(key => key + 1)} className="btn btn-primary mt-6">
              {t('actions.retryLoad')}
            </button>
          </div>
        ) : articles.length === 0 ? (
          <div className="rounded-2xl border border-dashed border-mist-mid bg-white px-6 py-12 text-center">
            <p>{copy.empty}</p>
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
                      <div className="mt-6 flex items-center justify-between gap-4 border-t border-light-mist pt-4">
                        {pageConfig.showReadingTime && article.readTime > 0 && (
                          <span className="inline-flex items-center gap-1.5 text-xs font-medium text-storm-grey">
                            <svg viewBox="0 0 20 20" fill="none" className="h-4 w-4 text-mist-mid" aria-hidden="true">
                              <circle cx="10" cy="10" r="7" stroke="currentColor" strokeWidth="1.5" />
                              <path d="M10 6.5V10l2.5 1.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            {t('common.minutes', { count: article.readTime })}
                          </span>
                        )}
                        <span className="ml-auto inline-flex items-center gap-2.5 text-sm font-bold text-seafoam">
                          {t('common.readMore')}
                          <span className="grid h-8 w-8 place-items-center rounded-full bg-seafoam-pale transition-all duration-300 group-hover:bg-seafoam group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="none" className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5">
                              <path d="M4 10h12m-4-4 4 4-4 4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                          </span>
                        </span>
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
