import { useEffect, useState } from 'react'
import PageHead from '@components/common/PageHead'
import { useLanguage } from '@hooks/useLanguage'
import { aboutService } from '@services/about.service'
import NotFoundPage from '@pages/errors/NotFoundPage'

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
  const [page, setPage] = useState(null)
  const [status, setStatus] = useState('loading')
  const [requestKey, setRequestKey] = useState(0)

  useEffect(() => {
    let isMounted = true
    setStatus('loading')

    aboutService.getPage(identifier, { locale: language })
      .then((data) => {
        if (!isMounted) return
        setPage(data)
        setStatus('success')
      })
      .catch((error) => {
        if (isMounted) setStatus(error?.response?.status === 404 ? 'not-found' : 'error')
      })

    return () => {
      isMounted = false
    }
  }, [identifier, language, requestKey])

  if (status === 'loading') return <LoadingState label={t('common.loading')} />
  if (status === 'not-found') return <NotFoundPage />
  if (status === 'error' || !page) {
    return <ErrorState t={t} onRetry={() => setRequestKey(key => key + 1)} />
  }

  const templateClass = String(page.template || 'about').replace(/[^a-z0-9-]/gi, '')

  return (
    <>
      <PageHead
        title={`${page.seo?.title || page.title} | IDI Seafood`}
        description={page.seo?.description}
        keywords={page.seo?.keywords}
      />

      <main className={`cms-about-page cms-about-page--${templateClass}`}>
        <header className="cms-about-hero">
          <div className="container">
            <span className="cms-about-eyebrow">{t('nav.about')}</span>
            <h1>{page.title}</h1>
            {!page.image && page.summary && <p>{page.summary}</p>}
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

          {page.content ? (
            <div
              className="cms-about-rich"
              dangerouslySetInnerHTML={{ __html: page.content }}
            />
          ) : (
            <p className="cms-about-empty">{t('common.noContent')}</p>
          )}
        </article>
      </main>
    </>
  )
}
