import { Fragment, useEffect, useState } from 'react'
import { Link, useParams } from 'react-router'
import NewsImage from '@components/common/NewsImage'
import PageHead from '@components/common/PageHead'
import { useLanguage } from '@hooks/useLanguage'
import { useScrollProgress } from '@hooks/useScrollProgress'
import {
  DEFAULT_NEWS_PAGE_CONFIG,
  getNewsErrorStatus,
  newsService,
} from '@services/news.service'
import { SITE_URL } from '@utils/constants'

const DATE_LOCALES = { vi: 'vi-VN', en: 'en-US', 'zh-CN': 'zh-CN' }

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
              ? 'w-full scroll-mt-28 pt-5 text-2xl font-black leading-tight text-ocean-deep sm:text-3xl'
              : 'w-full scroll-mt-28 pt-3 text-xl font-extrabold leading-tight text-ocean-deep sm:text-2xl'
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
        <p className={block.lead ? 'text-justify text-xl font-medium leading-9 text-slate' : 'text-justify text-base leading-8 text-slate sm:text-lg sm:leading-9'}>
          <InlineContent nodes={block.children} fallback={block.text} />
        </p>
      )
  }
}

function RelatedCard({ article, pageConfig, language }) {
  const date = formatDate(article.publishedAt, language)

  return (
    <article className="group border-b border-light-mist pb-6 last:border-b-0 last:pb-0">
      <Link to={`/news/${article.slug}`} className="relative block aspect-[4/3] overflow-hidden bg-light-mist" aria-label={`Đọc bài: ${article.title}`}>
        <NewsImage
          article={article}
          className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.025]"
        />
      </Link>
      <h3 className="mt-4 text-base font-bold leading-snug text-ocean-deep transition-colors group-hover:text-seafoam">
        <Link to={`/news/${article.slug}`}>{article.title}</Link>
      </h3>
      {pageConfig.showPublishedDate && date && (
        <time dateTime={article.publishedAt} className="mt-2 block text-xs text-storm-grey">{date}</time>
      )}
    </article>
  )
}

function HeroMedia({ article }) {
  return (
    <figure className="relative aspect-[16/10] overflow-hidden bg-light-mist">
      <NewsImage
        article={article}
        className="absolute inset-0 h-full w-full object-cover"
        loading="eager"
        fetchPriority="high"
      />
    </figure>
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
  const { language } = useLanguage()
  const progress = useScrollProgress()
  const [article, setArticle] = useState(null)
  const [pageConfig, setPageConfig] = useState(DEFAULT_NEWS_PAGE_CONFIG)
  const [related, setRelated] = useState([])
  const [status, setStatus] = useState('loading')
  const [requestKey, setRequestKey] = useState(0)

  useEffect(() => {
    const controller = new AbortController()
    window.scrollTo({ top: 0, behavior: 'auto' })
    setStatus('loading')
    setArticle(null)
    setRelated([])

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

  if (status === 'loading') return <LoadingState />
  if (status === 'not-found') return <ErrorState notFound />
  if (status === 'error') return <ErrorState onRetry={() => setRequestKey(key => key + 1)} />
  if (!article) return null

  const date = formatDate(article.publishedAt, language)
  const showRelated = pageConfig.showRelatedArticles && related.length > 0

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

      <article className="bg-white pb-20 pt-28 lg:pb-28 lg:pt-36">
        <div className="container">
          <header className="border-b border-mist-mid pb-9 lg:pb-12">
            <div className="w-full">
              <div className="flex flex-wrap items-center gap-3 text-sm">
                {article.category && <span className="font-bold uppercase tracking-[0.1em] text-seafoam">{article.category.name}</span>}
                {article.category && pageConfig.showPublishedDate && date && <span className="text-mist-mid" aria-hidden="true">•</span>}
                {pageConfig.showPublishedDate && date && <time dateTime={article.publishedAt} className="text-storm-grey">{date}</time>}
              </div>
              <h1 className="mt-5 max-w-[80rem] text-[clamp(2.25rem,4.2vw,4rem)] font-bold leading-[1.2] tracking-[-0.035em] text-ocean-deep text-balance">{article.title}</h1>
            </div>
          </header>

          <div className={`grid gap-14 pt-10 lg:items-start lg:gap-16 lg:pt-14 ${showRelated ? 'lg:grid-cols-[minmax(0,1fr)_18rem]' : 'mx-auto max-w-4xl'}`}>
            <div className="min-w-0">
              <HeroMedia article={article} />

              <div className="mt-10 w-full space-y-7 lg:mt-12">
                {article.content.length > 0 ? article.content.map((block, index) => (
                  <ArticleBlock key={`${block.type}-${block.id ?? index}`} block={block} index={index} />
                )) : (
                  <p className="border border-dashed border-mist-mid bg-arctic-white p-6 text-lg">Nội dung bài viết đang được cập nhật.</p>
                )}

              </div>
            </div>

            {showRelated && (
              <aside className="border-t-2 border-ocean-deep pt-5 lg:sticky lg:top-28" aria-labelledby="related-news-title">
                <div className="mb-7 flex items-center justify-between gap-4">
                  <h2 id="related-news-title" className="text-xl font-bold text-ocean-deep">Tin liên quan</h2>
                  <Link to="/news" className="shrink-0 text-sm font-semibold text-seafoam hover:text-ocean-deep">Xem tất cả</Link>
                </div>
                <div className="space-y-6">
                  {related.map(item => <RelatedCard key={item.id} article={item} pageConfig={pageConfig} language={language} />)}
                </div>
              </aside>
            )}
          </div>
        </div>
      </article>
    </>
  )
}
