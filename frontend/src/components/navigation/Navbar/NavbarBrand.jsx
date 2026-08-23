import { Link } from 'react-router'
import { useLanguage } from '@hooks/useLanguage'

export default function NavbarBrand() {
  const { t } = useLanguage()
  return (
    <Link
      to="/"
      aria-label={`${t('nav.home')} IDI Seafood`}
      className="flex flex-shrink-0 items-center"
    >
      <img
        src="/images/brand/idi-logo.png"
        alt="IDI Seafood"
        className="h-14 w-auto rounded-md object-contain px-1.5 py-1 "
      />
    </Link>
  )
}
