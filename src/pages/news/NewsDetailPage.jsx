import { useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router'
import PageHead from '@components/common/PageHead'
import { useScrollProgress } from '@hooks/useScrollProgress'
import { newsService } from '@services/news.service'

const CATEGORY_STYLES = {
  'Giải thưởng': 'bg-coral-pale text-[#9A6517]',
  'Tin doanh nghiệp': 'bg-[#EBF4FF] text-ocean-deep',
  ESG: 'bg-seafoam-pale text-seafoam',
  'Thị trường': 'bg-[#F1EEFF] text-[#6246A8]',
  'Công nghệ': 'bg-[#E9F7FB] text-[#176B87]',
  'Sản phẩm': 'bg-[#FFF1EA] text-[#A5542D]',
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })
}

function ArticleBlock({ block, index }) {
  if (!block) return null

  switch (block.type) {
    case 'heading':
      return (
        <h2
          id={block.id ?? `noi-dung-${index}`}
          className="scroll-mt-28 pt-5 text-2xl font-black leading-tight text-ocean-deep sm:text-3xl"
        >
          {block.text}
        </h2>
      )

    case 'quote':
      return (
        <blockquote className="my-9 rounded-r-2xl border-l-4 border-coral-gold bg-coral-pale px-6 py-7 sm:px-8">
          <p className="text-xl font-semibold italic leading-relaxed text-ocean-deep">
            “{block.text}”
          </p>
          {block.attribution && (
            <footer className="mt-4 text-sm font-bold text-[#9A6517]">
              — {block.attribution}
            </footer>
          )}
        </blockquote>
      )

    case 'list':
      return (
        <ul className="space-y-4">
          {(block.items ?? []).map(item => (
            <li key={item} className="flex gap-3 text-base leading-7 text-slate sm:text-lg">
              <span
                className="mt-2.5 h-2 w-2 shrink-0 rounded-full bg-seafoam"
                aria-hidden="true"
              />
              <span>{item}</span>
            </li>
          ))}
        </ul>
      )

    case 'image':
      return (
        <figure className="my-9 overflow-hidden rounded-2xl bg-light-mist">
          <img
            src={block.url ?? block.src}
            alt={block.alt ?? ''}
            className="w-full object-cover"
            loading="lazy"
          />
          {block.caption && (
            <figcaption className="px-5 py-3 text-center text-sm text-storm-grey">
              {block.caption}
            </figcaption>
          )}
        </figure>
      )

    case 'callout':
      return (
        <aside className="my-9 rounded-2xl bg-ocean-deep p-7 text-white sm:p-8">
          <h3 className="text-xl font-bold text-white">{block.title}</h3>
          <p className="mt-3 leading-relaxed text-white/70">{block.text}</p>
          {block.link && (
            <Link
              to={block.link}
              className="mt-5 inline-flex items-center gap-2 font-bold text-coral-light hover:text-white"
            >
              {block.linkLabel ?? 'Xem thêm'} <span aria-hidden="true">→</span>
            </Link>
          )}
        </aside>
      )

    default:
      return (
        <p
          className={
            block.lead
              ? 'text-xl font-medium leading-9 text-slate'
              : 'text-base leading-8 text-slate sm:text-lg sm:leading-9'
          }
        >
          {block.text}
        </p>
      )
  }
}

function RelatedCard({ article }) {
  return (
    <article className="group overflow-hidden rounded-2xl border border-light-mist bg-white">
      <Link
        to={`/news/${article.slug}`}
        className="block aspect-[16/10] overflow-hidden bg-light-mist"
        aria-label={`Đọc bài: ${article.title}`}
      >
        <img
          src={article.image}
          alt=""
          className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
          loading="lazy"
        />
      </Link>
      <div className="p-5">
        <div className="mb-3 flex items-center gap-2 text-xs text-storm-grey">
          <span className="font-bold text-seafoam">{article.category}</span>
          <span aria-hidden="true">·</span>
          <time dateTime={article.date}>{formatDate(article.date)}</time>
        </div>
        <h3 className="text-lg font-bold leading-snug text-ocean-deep group-hover:text-seafoam">
          <Link to={`/news/${article.slug}`}>{article.title}</Link>
        </h3>
      </div>
    </article>
  )
}

function LoadingState() {
  return (
    <div className="container min-h-[70vh] animate-pulse pb-24 pt-36">
      <div className="mb-5 h-5 w-40 rounded bg-light-mist" />
      <div className="h-14 max-w-4xl rounded-xl bg-light-mist" />
      <div className="mt-4 h-14 max-w-3xl rounded-xl bg-light-mist" />
      <div className="mt-10 aspect-[16/7] rounded-3xl bg-light-mist" />
    </div>
  )
}

function ErrorState({ notFound, onRetry }) {
  return (
    <main className="container flex min-h-[70vh] items-center justify-center pb-24 pt-36 text-center">
      <div className="max-w-lg">
        <span className="text-sm font-bold uppercase tracking-[0.18em] text-seafoam">
          {notFound ? '404 · Bài viết không tồn tại' : 'Không thể tải nội dung'}
        </span>
        <h1 className="mt-4 text-h2 font-black text-ocean-deep">
          {notFound
            ? 'Bài viết bạn đang tìm không còn ở đây'
            : 'Đã có lỗi xảy ra khi tải bài viết'}
        </h1>
        <p className="mt-4">
          {notFound
            ? 'Đường dẫn có thể đã thay đổi hoặc bài viết đã được gỡ khỏi hệ thống.'
            : 'Vui lòng thử lại sau ít phút hoặc quay về trang tin tức.'}
        </p>
        <div className="mt-8 flex flex-wrap justify-center gap-3">
          {!notFound && (
            <button type="button" onClick={onRetry} className="btn btn-primary">
              Thử tải lại
            </button>
          )}
          <Link to="/news" className="btn btn-secondary">
            Xem tất cả tin tức
          </Link>
        </div>
      </div>
    </main>
  )
}

export default function NewsDetailPage() {
  const { slug } = useParams()
  const progress = useScrollProgress()
  const [article, setArticle] = useState(null)
  const [related, setRelated] = useState([])
  const [status, setStatus] = useState('loading')
  const [copyLabel, setCopyLabel] = useState('Sao chép liên kết')
  const [requestKey, setRequestKey] = useState(0)

  useEffect(() => {
    let active = true
    window.scrollTo({ top: 0, behavior: 'auto' })
    setStatus('loading')
    setArticle(null)
    setRelated([])

    newsService
      .getBySlug(slug)
      .then(async data => {
        if (!active) return
        setArticle(data)
        setStatus('success')
        try {
          const items = await newsService.getRelated(data, 3)
          if (active) setRelated(items)
        } catch {
          // Related articles are optional; the main article remains available.
        }
      })
      .catch(error => {
        if (!active) return
        setStatus(error?.status === 404 ? 'not-found' : 'error')
      })

    return () => {
      active = false
    }
  }, [slug, requestKey])

  const headings = useMemo(
    () =>
      (article?.content ?? [])
        .map((block, index) =>
          block.type === 'heading'
            ? {
                id: block.id ?? `noi-dung-${index}`,
                text: block.text,
              }
            : null,
        )
        .filter(Boolean),
    [article],
  )

  const shareUrl = typeof window === 'undefined' ? '' : window.location.href

  const copyLink = async () => {
    try {
      await navigator.clipboard.writeText(shareUrl)
      setCopyLabel('Đã sao chép')
      window.setTimeout(() => setCopyLabel('Sao chép liên kết'), 1800)
    } catch {
      setCopyLabel('Không thể sao chép')
    }
  }

  if (status === 'loading') return <LoadingState />
  if (status === 'not-found') return <ErrorState notFound />
  if (status === 'error') {
    return <ErrorState onRetry={() => setRequestKey(key => key + 1)} />
  }
  if (!article) return null

  const categoryStyle =
    CATEGORY_STYLES[article.category] ?? CATEGORY_STYLES['Tin doanh nghiệp']

  return (
    <>
      <PageHead
        title={`${article.title} | IDI Seafood`}
        description={article.excerpt}
      />

      <div
        className="fixed left-0 top-0 z-[110] h-1 bg-coral-gold transition-[width] duration-150"
        style={{ width: `${progress}%` }}
        role="progressbar"
        aria-label="Tiến độ đọc bài"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow={progress}
      />

      <article className="bg-white">
        <header className="relative overflow-hidden bg-ocean-deep pb-32 pt-28 text-white lg:pb-48 lg:pt-36">
          <div
            className="absolute inset-0 opacity-20"
            style={{
              background:
                'radial-gradient(circle at 85% 10%, #1A936F 0%, transparent 35%), radial-gradient(circle at 10% 90%, #E8A045 0%, transparent 28%)',
            }}
          />
          <div className="container relative z-10">
            <nav
              aria-label="Đường dẫn"
              className="mb-10 flex flex-wrap items-center gap-2 text-sm text-white/55"
            >
              <Link to="/" className="hover:text-white">Trang chủ</Link>
              <span aria-hidden="true">/</span>
              <Link to="/news" className="hover:text-white">Tin tức</Link>
              <span aria-hidden="true">/</span>
              <span className="max-w-xs truncate text-white/80" aria-current="page">
                {article.title}
              </span>
            </nav>

            <div className="max-w-5xl">
              <span className={`inline-flex rounded-full px-4 py-1.5 text-xs font-bold ${categoryStyle}`}>
                {article.category}
              </span>
              <h1 className="mt-6 max-w-5xl text-h1 font-black leading-[1.08] text-white text-balance">
                {article.title}
              </h1>
              <p className="mt-6 max-w-3xl text-lg leading-8 text-white/65 sm:text-xl">
                {article.excerpt}
              </p>
              <div className="mt-8 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-white/60">
                <span className="font-bold text-white">{article.author}</span>
                <time dateTime={article.date}>{formatDate(article.date)}</time>
                <span>{article.readTime} phút đọc</span>
              </div>
            </div>
          </div>
        </header>

        <div className="container relative z-10 -mt-20 lg:-mt-28">
          <figure className="overflow-hidden rounded-3xl bg-light-mist shadow-2xl">
            <img
              src={article.image}
              alt={article.imageAlt}
              className="aspect-[16/8] w-full object-cover"
              fetchPriority="high"
              onError={event => {
                event.currentTarget.style.display = 'none'
                event.currentTarget.parentElement.style.minHeight = '24rem'
                event.currentTarget.parentElement.style.background =
                  'linear-gradient(135deg, #0B2545, #1A936F)'
              }}
            />
          </figure>
        </div>

        <div className="container grid gap-12 py-16 lg:grid-cols-[14rem_minmax(0,46rem)] lg:justify-center lg:gap-16 lg:py-24">
          <aside className="lg:sticky lg:top-28 lg:self-start">
            {headings.length > 0 && (
              <div className="border-b border-light-mist pb-7">
                <p className="mb-4 text-xs font-black uppercase tracking-[0.14em] text-ocean-deep">
                  Trong bài viết
                </p>
                <nav aria-label="Mục lục bài viết">
                  <ol className="space-y-3">
                    {headings.map((heading, index) => (
                      <li key={heading.id}>
                        <a
                          href={`#${heading.id}`}
                          className="flex gap-3 text-sm leading-5 text-storm-grey hover:text-seafoam"
                        >
                          <span className="font-bold text-seafoam">0{index + 1}</span>
                          <span>{heading.text}</span>
                        </a>
                      </li>
                    ))}
                  </ol>
                </nav>
              </div>
            )}

            <div className="pt-7">
              <p className="mb-3 text-xs font-black uppercase tracking-[0.14em] text-ocean-deep">
                Chia sẻ bài viết
              </p>
              <button
                type="button"
                onClick={copyLink}
                className="w-full rounded-lg border border-light-mist px-4 py-2.5 text-left text-sm font-bold text-ocean-deep transition hover:border-seafoam hover:text-seafoam"
              >
                {copyLabel}
              </button>
            </div>
          </aside>

          <div className="min-w-0 space-y-7">
            {(article.content ?? []).map((block, index) => (
              <ArticleBlock key={`${block.type}-${index}`} block={block} index={index} />
            ))}

            <footer className="mt-12 border-t border-light-mist pt-8">
              <div className="flex flex-wrap gap-2">
                {(article.tags?.length ? article.tags : [article.category, 'IDI Seafood']).map(tag => (
                  <span
                    key={tag}
                    className="rounded-full bg-arctic-white px-3 py-1.5 text-xs font-bold text-storm-grey"
                  >
                    #{tag}
                  </span>
                ))}
              </div>
              <div className="mt-8 rounded-2xl border border-light-mist bg-arctic-white p-6">
                <span className="text-xs font-bold uppercase tracking-[0.12em] text-seafoam">
                  Tác giả
                </span>
                <h3 className="mt-2 text-lg font-black text-ocean-deep">{article.author}</h3>
                <p className="mt-1 text-sm">{article.authorRole}</p>
              </div>
            </footer>
          </div>
        </div>
      </article>

      {related.length > 0 && (
        <section className="border-t border-light-mist bg-arctic-white py-20 lg:py-24">
          <div className="container">
            <div className="mb-9 flex flex-wrap items-end justify-between gap-4">
              <div>
                <span className="section-eyebrow">Tiếp tục khám phá</span>
                <h2 className="text-h2 font-black text-ocean-deep">Bài viết liên quan</h2>
              </div>
              <Link to="/news" className="font-bold text-seafoam hover:text-ocean-deep">
                Xem tất cả <span aria-hidden="true">→</span>
              </Link>
            </div>
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {related.map(item => <RelatedCard key={item.id} article={item} />)}
            </div>
          </div>
        </section>
      )}
    </>
  )
}
