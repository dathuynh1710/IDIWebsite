import { Link, useRouteError } from 'react-router'
import { useLanguage } from '@hooks/useLanguage'

export default function ErrorPage() {
  const error = useRouteError()
  const { t } = useLanguage()
  const isNotFound = error?.status === 404

  return (
    <main className="container flex min-h-screen flex-col items-center justify-center py-24 text-center">
      <span className="text-7xl font-black text-seafoam/25">{isNotFound ? '404' : '!'}</span>
      <h1 className="mt-4 text-h2 font-bold text-ocean-deep">
        {t(isNotFound ? 'error.notFoundTitle' : 'error.genericTitle')}
      </h1>
      <p className="mt-4 max-w-xl text-storm-grey">
        {t(isNotFound ? 'error.notFoundMessage' : 'error.genericMessage')}
      </p>
      <Link to="/" className="btn btn-primary mt-8">{t('common.backHome')}</Link>
    </main>
  )
}
