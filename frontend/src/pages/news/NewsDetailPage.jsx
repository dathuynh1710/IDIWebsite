import { Fragment, useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router'
import PageHead from '@components/common/PageHead'
import { useLanguage } from '@hooks/useLanguage'
import { useScrollProgress } from '@hooks/useScrollProgress'
import {
  DEFAULT_NEWS_PAGE_CONFIG,
  getNewsErrorStatus,
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

const DATE_LOCALES = { vi: 'vi-VN', en: 'en-US', 'zh-CN': 'zh-CN' }

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
    month: 'long',
    year: 'numeric',
  })
}

function InlineContent({ nodes, fallback = '' }) {
  if (!Array.isArray(nodes) || !nodes.length) return fallback

  return nodes.map((node, index) => {
    const key = `${node.type}-${index}`
    const children = <InlineContent nodes={node.children} />

    switch (node.type) {
      case 'text': return <Fragment key={key}>{node.text}</Fragment>
      case 'break': return <br key={key} />
      case 'strong': return <strong key={key}>{children}</strong>
      case 'emphasis': return <em key={key}>{children}</em>
      case 'underline': return <u key={key}>{children}</u>
      case 'code': return <code key={key}>{children}</code>
      case 'subscript': return <sub key={key}>{children}</sub>
      case 'superscript': return <sup key={key}>{children}</sup>
      case 'link':
        return (
          <a
            key={key}
            href={node.href}
            className="font-semibold text-seafoam underline decoration-seafoam/30 underline-offset-4 hover:text-ocean-deep"
            target={node.external ? '_blank' : undefined}
            rel={node.external ? 'noreferrer noopener' : undefined}
          >
            {children}
          </a>
        )
      default: return <Fragment key={key}>{children}</Fragment>
    }
  })
}

function ArticleBlock({ block, index }) {
  const { t } = useLanguage()
  if (!block) return null

  switch (block.type) {
    case 'heading': {
      const Heading = block.level >= 3 ? 'h3' : 'h2'
      return (
        <Heading
          id={block.id ?? `noi-dung-${index}`}
          className={
            Heading === 'h2'
              ? 'scroll-mt-28 pt-5 text-2xl font-black leading-tight text-ocean-deep sm:text-3xl'
              : 'scroll-mt-28 pt-3 text-xl font-extrabold leading-tight text-ocean-deep sm:text-2xl'
          }
        >
          <InlineContent nodes={block.children} fallback={block.text} />
        </Heading>
      )
    }

    case 'quote':
      return (
        <blockquote className="my-9 rounded-r-2xl border-l-4 border-coral-gold bg-coral-pale px-6 py-7 sm:px-8">
          <p className="text-xl font-semibold italic leading-relaxed text-ocean-deep">
            “<InlineContent nodes={block.children} fallback={block.text} />”
          </p>
          {block.attribution && (
            <footer className="mt-4 text-sm font-bold text-[#9A6517]">— {block.attribution}</footer>
          )}
        </blockquote>
      )

    case 'list': {
      const List = block.ordered ? 'ol' : 'ul'
      return (
        <List className={`space-y-4 ${block.ordered ? 'list-decimal pl-6 marker:font-bold marker:text-seafoam' : ''}`}>
          {(block.items ?? []).map((item, itemIndex) => {
            const normalized = typeof item === 'string' ? { text: item } : item
            return (
              <li key={`${normalized.text}-${itemIndex}`} className={block.ordered ? 'pl-2 text-base leading-7 text-slate sm:text-lg' : 'flex gap-3 text-base leading-7 text-slate sm:text-lg'}>
                {!block.ordered && <span className="mt-2.5 h-2 w-2 shrink-0 rounded-full bg-seafoam" aria-hidden="true" />}
                <span><InlineContent nodes={normalized.children} fallback={normalized.text} /></span>
              </li>
            )
          })}
        </List>
      )
    }

    case 'image':
      return (
        <figure className="my-9 overflow-hidden rounded-2xl bg-light-mist">
          <img src={block.url ?? block.src} alt={block.alt ?? ''} className="w-full object-cover" loading="lazy" />
          {block.caption && <figcaption className="px-5 py-3 text-center text-sm text-storm-grey">{block.caption}</figcaption>}
        </figure>
      )

    case 'table':
      return (
        <div className="my-9 overflow-x-auto rounded-xl border border-light-mist">
          <table className="w-full min-w-[36rem] border-collapse text-left text-sm">
            {block.headers?.length > 0 && (
              <thead className="bg-ocean-deep text-white">
                <tr>{block.headers.map((cell, cellIndex) => <th key={cellIndex} className="px-4 py-3 font-bold">{cell}</th>)}</tr>
              </thead>
            )}
            <tbody>
              {(block.rows ?? []).map((row, rowIndex) => (
                <tr key={rowIndex} className="border-t border-light-mist even:bg-arctic-white">
                  {row.map((cell, cellIndex) => <td key={cellIndex} className="px-4 py-3 align-top text-slate">{cell}</td>)}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )

    case 'code':
      return <pre className="my-8 overflow-x-auto rounded-xl bg-ocean-deep p-5 text-sm leading-7 text-white"><code>{block.text}</code></pre>

    case 'separator':
      return <hr className="my-10 border-light-mist" />

    case 'video':
      return (
        <figure className="my-9 overflow-hidden rounded-2xl bg-ocean-deep">
          <video src={block.url} poster={block.poster || undefined} controls className="aspect-video w-full" preload="metadata" />
          {block.caption && <figcaption className="px-5 py-3 text-center text-sm text-white/70">{block.caption}</figcaption>}
        </figure>
      )

    case 'embed':
      return (
        <a href={block.url} target="_blank" rel="noreferrer noopener" className="my-8 flex items-center justify-between gap-4 rounded-xl border border-light-mist bg-arctic-white px-5 py-4 font-bold text-ocean-deep hover:border-seafoam hover:text-seafoam">
          <span>{block.title || 'Xem nội dung được nhúng'}</span><span aria-hidden="true">↗</span>
        </a>
      )

    case 'callout':
      return (
        <aside className="my-9 rounded-2xl bg-ocean-deep p-7 text-white sm:p-8">
          <h3 className="text-xl font-bold text-white">{block.title}</h3>
          <p className="mt-3 leading-relaxed text-white/70">{block.text}</p>
          {block.link && <Link to={block.link} className="mt-5 inline-flex items-center gap-2 font-bold text-coral-light hover:text-white">{block.linkLabel ?? t('actions.viewMore')} →</Link>}
        </aside>
      )

    default:
      return (
        <p className={block.lead ? 'text-xl font-medium leading-9 text-slate' : 'text-base leading-8 text-slate sm:text-lg sm:leading-9'}>
          <InlineContent nodes={block.children} fallback={block.text} />
        </p>
      )
  }
}

function RelatedCard({ article, pageConfig, language }) {
  const date = formatDate(article.publishedAt, language)
  return (
    <article className="group overflow-hidden rounded-2xl border border-light-mist bg-white">
      <Link to={`/news/${article.slug}`} className="relative block aspect-[16/10] overflow-hidden bg-gradient-to-br from-ocean-deep to-seafoam" aria-label={`Đọc bài: ${article.title}`}>
        {article.imageUrl ? (
          <img
            src={article.imageUrl}
            alt={article.imageAlt || article.title}
            className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            loading="lazy"
            onError={event => { event.currentTarget.style.display = 'none' }}
          />
        ) : pageConfig.showPlaceholderImage ? (
          <span className="absolute inset-0 grid place-items-center text-3xl font-black tracking-[0.2em] text-white/45">IDI</span>
        ) : null}
      </Link>
      <div className="p-5">
        <div className="mb-3 flex flex-wrap items-center gap-2 text-xs text-storm-grey">
          {article.category && <span className="font-bold text-seafoam">{article.category.name}</span>}
          {article.category && pageConfig.showPublishedDate && date && <span aria-hidden="true">·</span>}
          {pageConfig.showPublishedDate && date && <time dateTime={article.publishedAt}>{date}</time>}
        </div>
        <h3 className="text-lg font-bold leading-snug text-ocean-deep group-hover:text-seafoam">
          <Link to={`/news/${article.slug}`}>{article.title}</Link>
        </h3>
      </div>
    </article>
  )
}

function HeroMedia({ article, showPlaceholder }) {
  const [failed, setFailed] = useState(false)
  const showImage = article.imageUrl && !failed

  if (!showImage && !showPlaceholder) return null

  return (
    <div className="container relative z-10 -mt-20 lg:-mt-28">
      <figure className="relative min-h-[18rem] overflow-hidden rounded-3xl bg-gradient-to-br from-ocean-deep to-seafoam shadow-2xl lg:min-h-[34rem]">
        {showImage ? (
          <img
            src={article.imageUrl}
            alt={article.imageAlt || article.title}
            className="absolute inset-0 h-full w-full object-cover"
            fetchPriority="high"
            onError={() => setFailed(true)}
          />
        ) : (
          <span className="absolute inset-0 grid place-items-center text-5xl font-black tracking-[0.2em] text-white/45">IDI</span>
        )}
      </figure>
    </div>
  )
}

function LoadingState() {
  return (
    <main className="container min-h-[70vh] animate-pulse pb-24 pt-36" aria-label="Đang tải bài viết">
      <div className="mb-5 h-5 w-40 rounded bg-light-mist" />
      <div className="h-14 max-w-4xl rounded-xl bg-light-mist" />
      <div className="mt-4 h-14 max-w-3xl rounded-xl bg-light-mist" />
      <div className="mt-10 aspect-[16/7] rounded-3xl bg-light-mist" />
    </main>
  )
}

function ErrorState({ notFound, onRetry }) {
  const { t } = useLanguage()
  return (
    <main className="container flex min-h-[70vh] items-center justify-center pb-24 pt-36 text-center">
      <div className="max-w-lg" role="alert">
        <span className="text-sm font-bold uppercase tracking-[0.18em] text-seafoam">
          {notFound ? '404 · Bài viết không tồn tại' : 'Không thể tải nội dung'}
        </span>
        <h1 className="mt-4 text-h2 font-black text-ocean-deep">
          {notFound ? 'Bài viết bạn đang tìm không còn ở đây' : 'Đã có lỗi xảy ra khi tải bài viết'}
        </h1>
        <p className="mt-4">
          {notFound ? 'Đường dẫn có thể đã thay đổi hoặc bài viết đã được gỡ khỏi hệ thống.' : 'Vui lòng thử lại sau ít phút hoặc quay về trang tin tức.'}
        </p>
        <div className="mt-8 flex flex-wrap justify-center gap-3">
          {!notFound && <button type="button" onClick={onRetry} className="btn btn-primary">{t('actions.retryLoad')}</button>}
          <Link to="/news" className="btn btn-secondary">{t('actions.allNews')}</Link>
        </div>
      </div>
    </main>
  )
}

export default function NewsDetailPage() {
  const { slug } = useParams()
  const { language, t } = useLanguage()
  const progress = useScrollProgress()
  const [article, setArticle] = useState(null)
  const [pageConfig, setPageConfig] = useState(DEFAULT_NEWS_PAGE_CONFIG)
  const [related, setRelated] = useState([])
  const [status, setStatus] = useState('loading')
  const [copyStatus, setCopyStatus] = useState('idle')
  const [requestKey, setRequestKey] = useState(0)

  useEffect(() => {
    const controller = new AbortController()
    window.scrollTo({ top: 0, behavior: 'auto' })
    setStatus('loading')
    setArticle(null)
    setRelated([])
    setCopyStatus('idle')

    newsService.getBySlug(slug, { locale: language, signal: controller.signal })
      .then(async ({ article: data, pageConfig: config }) => {
        setArticle(data)
        setPageConfig(config)
        setStatus('success')

        if (!config.showRelatedArticles || config.relatedLimit <= 0) return
        try {
          const items = await newsService.getRelated(data, config.relatedLimit, {
            locale: language,
            signal: controller.signal,
          })
          setRelated(items)
        } catch (error) {
          if (error?.code !== 'ERR_CANCELED') setRelated([])
        }
      })
      .catch((error) => {
        if (error?.code === 'ERR_CANCELED') return
        setStatus(getNewsErrorStatus(error) === 404 ? 'not-found' : 'error')
      })

    return () => controller.abort()
  }, [language, requestKey, slug])

  const headings = useMemo(() => (
    (article?.content ?? [])
      .filter(block => block.type === 'heading')
      .map((block, index) => ({ id: block.id ?? `noi-dung-${index}`, text: block.text }))
  ), [article])

  const shareUrl = typeof window === 'undefined' ? '' : window.location.href

  const copyLink = async () => {
    try {
      await navigator.clipboard.writeText(shareUrl)
      setCopyStatus('copied')
      window.setTimeout(() => setCopyStatus('idle'), 1800)
    } catch {
      setCopyStatus('failed')
    }
  }

  if (status === 'loading') return <LoadingState />
  if (status === 'not-found') return <ErrorState notFound />
  if (status === 'error') return <ErrorState onRetry={() => setRequestKey(key => key + 1)} />
  if (!article) return null

  const date = formatDate(article.publishedAt, language)
  const hasHeroMedia = Boolean(article.imageUrl || pageConfig.showPlaceholderImage)

  return (
    <>
      <PageHead
        title={article.seo.title || `${article.title} | IDI Seafood`}
        description={article.seo.description || article.excerpt}
        keywords={article.seo.keywords}
        canonical={`${SITE_URL}/news/${article.slug}`}
        image={article.imageUrl}
        type="article"
        ogTitle={article.seo.ogTitle}
        ogDescription={article.seo.ogDescription}
      />

      <div className="fixed left-0 top-0 z-[110] h-1 bg-coral-gold transition-[width] duration-150" style={{ width: `${progress}%` }} role="progressbar" aria-label="Tiến độ đọc bài" aria-valuemin="0" aria-valuemax="100" aria-valuenow={progress} />

      <article className="bg-white">
        <header className={`relative overflow-hidden bg-ocean-deep pt-28 text-white lg:pt-36 ${hasHeroMedia ? 'pb-32 lg:pb-48' : 'pb-20 lg:pb-28'}`}>
          <div className="absolute inset-0 opacity-20" style={{ background: 'radial-gradient(circle at 85% 10%, #1A936F 0%, transparent 35%), radial-gradient(circle at 10% 90%, #E8A045 0%, transparent 28%)' }} />
          <div className="container relative z-10">
            {pageConfig.showBreadcrumb && (
              <nav aria-label="Đường dẫn" className="mb-10 flex flex-wrap items-center gap-2 text-sm text-white/55">
                <Link to="/" className="hover:text-white">Trang chủ</Link><span aria-hidden="true">/</span>
                <Link to="/news" className="hover:text-white">Tin tức</Link><span aria-hidden="true">/</span>
                <span className="max-w-xs truncate text-white/80" aria-current="page">{article.title}</span>
              </nav>
            )}
            <div className="max-w-5xl">
              {article.category && <span className={`inline-flex rounded-full px-4 py-1.5 text-xs font-bold ${categoryStyle(article.category)}`}>{article.category.name}</span>}
              <h1 className="mt-6 max-w-5xl text-h1 font-black leading-[1.08] text-white text-balance">{article.title}</h1>
              {article.excerpt && <p className="mt-6 max-w-3xl text-lg leading-8 text-white/65 sm:text-xl">{article.excerpt}</p>}
              <div className="mt-8 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-white/60">
                {pageConfig.showAuthor && article.authorName && <span className="font-bold text-white">{article.authorName}</span>}
                {pageConfig.showPublishedDate && date && <time dateTime={article.publishedAt}>{date}</time>}
                {pageConfig.showReadingTime && article.readTime > 0 && <span>{article.readTime} phút đọc</span>}
                {pageConfig.showViewCount && article.viewCount > 0 && <span>{article.viewCount.toLocaleString(DATE_LOCALES[language])} lượt xem</span>}
              </div>
            </div>
          </div>
        </header>

        <HeroMedia article={article} showPlaceholder={pageConfig.showPlaceholderImage} />

        <div className={`container grid gap-12 py-16 lg:grid-cols-[14rem_minmax(0,46rem)] lg:justify-center lg:gap-16 ${hasHeroMedia ? 'lg:py-24' : 'lg:py-20'}`}>
          <aside className="lg:sticky lg:top-28 lg:self-start">
            {headings.length > 0 && (
              <div className="border-b border-light-mist pb-7">
                <p className="mb-4 text-xs font-black uppercase tracking-[0.14em] text-ocean-deep">Trong bài viết</p>
                <nav aria-label="Mục lục bài viết">
                  <ol className="space-y-3">
                    {headings.map((heading, index) => (
                      <li key={heading.id}>
                        <a href={`#${heading.id}`} className="flex gap-3 text-sm leading-5 text-storm-grey hover:text-seafoam">
                          <span className="font-bold text-seafoam">{String(index + 1).padStart(2, '0')}</span><span>{heading.text}</span>
                        </a>
                      </li>
                    ))}
                  </ol>
                </nav>
              </div>
            )}

            {(pageConfig.showSocialShare || pageConfig.allowPrint || (pageConfig.showArticleSource && article.sourceUrl)) && (
              <div className="space-y-2 pt-7">
                <p className="mb-3 text-xs font-black uppercase tracking-[0.14em] text-ocean-deep">Tiện ích bài viết</p>
                {pageConfig.showSocialShare && (
                  <button type="button" onClick={copyLink} className="w-full rounded-lg border border-light-mist px-4 py-2.5 text-left text-sm font-bold text-ocean-deep transition hover:border-seafoam hover:text-seafoam">{t(`newsDetail.${copyStatus === 'idle' ? 'copyLink' : copyStatus === 'failed' ? 'copyFailed' : 'copied'}`)}</button>
                )}
                {pageConfig.allowPrint && (
                  <button type="button" onClick={() => window.print()} className="w-full rounded-lg border border-light-mist px-4 py-2.5 text-left text-sm font-bold text-ocean-deep transition hover:border-seafoam hover:text-seafoam">{t('actions.printArticle')}</button>
                )}
                {pageConfig.showArticleSource && article.sourceUrl && (
                  <a href={article.sourceUrl} target="_blank" rel="noreferrer noopener" className="block w-full rounded-lg border border-light-mist px-4 py-2.5 text-sm font-bold text-ocean-deep transition hover:border-seafoam hover:text-seafoam">Xem nguồn bài viết ↗</a>
                )}
              </div>
            )}
          </aside>

          <div className="min-w-0 space-y-7">
            {article.content.length > 0 ? article.content.map((block, index) => (
              <ArticleBlock key={`${block.type}-${block.id ?? index}`} block={block} index={index} />
            )) : (
              <p className="rounded-xl bg-arctic-white p-6 text-lg">Nội dung bài viết đang được cập nhật.</p>
            )}

            {(pageConfig.showTags || (pageConfig.showAuthor && article.authorName)) && (
              <footer className="mt-12 border-t border-light-mist pt-8">
                {pageConfig.showTags && article.tags.length > 0 && (
                  <div className="flex flex-wrap gap-2">
                    {article.tags.map(tag => <span key={tag} className="rounded-full bg-arctic-white px-3 py-1.5 text-xs font-bold text-storm-grey">#{tag}</span>)}
                  </div>
                )}
                {pageConfig.showAuthor && article.authorName && (
                  <div className="mt-8 rounded-2xl border border-light-mist bg-arctic-white p-6">
                    <span className="text-xs font-bold uppercase tracking-[0.12em] text-seafoam">Tác giả</span>
                    <h3 className="mt-2 text-lg font-black text-ocean-deep">{article.authorName}</h3>
                    {article.authorRole && <p className="mt-1 text-sm">{article.authorRole}</p>}
                  </div>
                )}
              </footer>
            )}
          </div>
        </div>
      </article>

      {pageConfig.showRelatedArticles && related.length > 0 && (
        <section className="border-t border-light-mist bg-arctic-white py-20 lg:py-24">
          <div className="container">
            <div className="mb-9 flex flex-wrap items-end justify-between gap-4">
              <div><span className="section-eyebrow">Tiếp tục khám phá</span><h2 className="text-h2 font-black text-ocean-deep">Bài viết liên quan</h2></div>
              <Link to="/news" className="font-bold text-seafoam hover:text-ocean-deep">{t('common.viewAll')} →</Link>
            </div>
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {related.map(item => <RelatedCard key={item.id} article={item} pageConfig={pageConfig} language={language} />)}
            </div>
          </div>
        </section>
      )}
    </>
  )
}
