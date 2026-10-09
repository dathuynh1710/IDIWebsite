import LocalizedHtml from '@components/common/LocalizedHtml'
import { useLocation, useNavigate, useParams } from 'react-router'
import { useAboutRouting } from '@context/AboutRoutingContext'
import { aboutLocale, aboutApiLocale, localizedAboutPath } from '@utils/aboutRoutes'
import { useEffect, useState } from 'react'
import PageHead from '@components/common/PageHead'
import { useLanguage } from '@hooks/useLanguage'
import { aboutService } from '@services/about.service'
import NotFoundPage from '@pages/errors/NotFoundPage'
import CoreValuesContent from './CoreValuesContent'
import HistoryContent from './HistoryContent'

function LoadingState({ label }) {
  return (
    <main className="cms-about-page" aria-busy="true" aria-label={label}>
      <div className="cms-about-loading container">
        <span />
        <span />
        <span />
      </div>
    </main>
  )
}

function ErrorState({ onRetry, t }) {
  return (
    <main className="cms-about-page cms-about-error">
      <section className="container" role="alert">
        <h1>{t('error.genericTitle')}</h1>
        <p>{t('error.genericMessage')}</p>
        <button type="button" className="btn btn-primary" onClick={onRetry}>{t('common.retry')}</button>
      </section>
    </main>
  )
}

export default function CmsAboutPage({ identifier }) {
  const { language, t } = useLanguage()
  const { slug } = useParams()
  const { pathname, search, hash } = useLocation()
  const navigate = useNavigate()
  const { setCurrent } = useAboutRouting()
  const locale = aboutLocale(pathname) || aboutApiLocale(language)
  const lookup = slug || identifier
  const [page, setPage] = useState(null)
  const [status, setStatus] = useState('loading')
  const [requestKey, setRequestKey] = useState(0)

  useEffect(() => {
    let isMounted = true
    setStatus('loading')
    setCurrent(null)

    aboutService.getPage(lookup, { locale, bySlug: Boolean(slug) })
      .then((data) => {
        if (!isMounted) return
        setCurrent({ pathname, page: data })
        const canonical = localizedAboutPath(data, locale)
        if (!slug && canonical) navigate(canonical + search + hash, { replace: true })
        setPage(data)
        setStatus('success')
      })
      .catch((error) => {
        if (isMounted) setStatus(error?.response?.status === 404 ? 'not-found' : 'error')
      })

    return () => {
      isMounted = false
    }
  }, [lookup, locale, slug, pathname, search, hash, navigate, setCurrent, requestKey])

  if (status === 'loading') return <LoadingState label={t('common.loading')} />
  if (status === 'not-found') return <NotFoundPage />
  if (status === 'error' || !page) {
    return <ErrorState t={t} onRetry={() => setRequestKey(key => key + 1)} />
  }

  const templateClass = String(page.template || 'about').replace(/[^a-z0-9-]/gi, '')
  const compactHero = page.code === 'ABOUT_HISTORY' || page.code === 'ABOUT_VALUES'

  return (
    <>
      <PageHead
        title={`${page.seo?.title || page.title} | IDI Seafood`}
        description={page.seo?.description}
        keywords={page.seo?.keywords}
      />

      <main className={`cms-about-page cms-about-page--${templateClass}`} data-page-code={page.code}>
        <header className={`cms-about-hero${compactHero ? ' cms-about-hero--compact' : ''}`}>
          <div className="container">
            <h1>{page.title}</h1>
            {!compactHero && !page.image && page.summary && <p>{page.summary}</p>}
          </div>
        </header>

        <article className="cms-about-article container">
          {page.image && (
            <section className="cms-about-lead">
              <figure>
                <img src={page.image.url} alt={page.image.alt || page.title} />
              </figure>
              {page.summary && <blockquote>“{page.summary}”</blockquote>}
            </section>
          )}

          {page.content && page.code === 'ABOUT_HISTORY' ? (
            <HistoryContent html={page.content} summary={page.summary} />
          ) : page.content && page.code === 'ABOUT_VALUES' ? (
            <CoreValuesContent html={page.content} />
          ) : page.content ? (
            <LocalizedHtml
              className="cms-about-rich"
              html={page.content} />
          ) : (
            <p className="cms-about-empty">{t('common.noContent')}</p>
          )}
        </article>
      </main>
    </>
  )
}
