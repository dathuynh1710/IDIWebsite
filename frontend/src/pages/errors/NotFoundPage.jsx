import { Link } from 'react-router'
import { useLanguage } from '@hooks/useLanguage'

export default function NotFoundPage() {
  const { t } = useLanguage()
  return (
    <main className="container flex min-h-[65vh] flex-col items-center justify-center py-32 text-center">
      <span className="text-7xl font-black text-seafoam/25">404</span>
      <h1 className="mt-4 text-h2 font-bold text-ocean-deep">{t('error.notFoundTitle')}</h1>
      <p className="mt-4 max-w-xl text-storm-grey">{t('error.notFoundMessage')}</p>
      <Link to="/" className="btn btn-primary mt-8">{t('common.backHome')}</Link>
    </main>
  )
}
