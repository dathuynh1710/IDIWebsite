import { usePublicRouting } from '@context/PublicRoutingContext'
import { apiLocale, routeEntry } from '@utils/publicRoutes'
import { useState } from 'react'
import { useLocation, useNavigate } from 'react-router'
import { useAboutRouting } from '@context/AboutRoutingContext'
import { aboutLocale, LEGACY_ABOUT, localizedAboutPath } from '@utils/aboutRoutes'
import { LANGUAGES, LANGUAGE_LABELS } from '@utils/constants'
import { useLanguage } from '@hooks/useLanguage'
import { cn } from '@utils/cn'

export default function LanguageSwitcher({ className, buttonClassName, separatorClassName }) {
  const { language, setLanguage, t } = useLanguage()
  const routing = usePublicRouting()
  const { page } = useAboutRouting()
  const location = useLocation()
  const navigate = useNavigate()
  const [error, setError] = useState('')
  const changeLanguage = locale => {
    setError('')
    if (routing) {
      const entry = routeEntry(location.pathname, routing.entries)
      const path = entry?.paths[apiLocale(locale)]
      if (!path) {
        setError({ vi: 'Trang chưa có đường dẫn hoặc bản dịch cho ngôn ngữ này.', en: 'This page has no URL or translation in that language.', 'zh-CN': '此页面暂无该语言的链接或翻译。' }[language])
        return
      }
      navigate(path + location.search + location.hash)
      setLanguage(locale)
      return
    }
    if (page || aboutLocale(location.pathname) || LEGACY_ABOUT[location.pathname]) {
      const path = localizedAboutPath(page, locale)
      if (!path) {
        setError({ vi: 'Trang ch\u01b0a c\u00f3 \u0111\u01b0\u1eddng d\u1eabn ho\u1eb7c b\u1ea3n d\u1ecbch cho ng\u00f4n ng\u1eef n\u00e0y.', en: 'This page has no URL or translation in that language.', 'zh-CN': '\u6b64\u9875\u9762\u6682\u65e0\u8be5\u8bed\u8a00\u7684\u94fe\u63a5\u6216\u7ffb\u8bd1\u3002' }[language])
        return
      }
      navigate(path + location.search + location.hash)
    }
    setLanguage(locale)
  }

  return (
    <div className={cn('flex items-center gap-1', className)} role="group" aria-label={t('language.label')}>
      {error && <span role="status" className="text-sm">{error}</span>}
      {Object.values(LANGUAGES).map((locale, index) => (
        <span key={locale} className="contents">
          {index > 0 && <span aria-hidden="true" className={cn('pointer-events-none opacity-30', separatorClassName)}>|</span>}
          <button
            type="button"
            onClick={() => changeLanguage(locale)}
            aria-pressed={language === locale}
            lang={locale}
            className={cn(
              'cursor-pointer select-none rounded-lg px-3 py-2',
              'transition-[color,background-color,box-shadow,opacity,transform] duration-200 ease-out',
              'hover:-translate-y-0.5 hover:bg-seafoam/15 hover:opacity-100 hover:shadow-sm',
              'active:translate-y-px active:scale-95 active:bg-seafoam/25 active:shadow-inner',
              'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-seafoam/70 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent',
              'motion-reduce:transform-none motion-reduce:transition-none',
              language === locale
                ? 'bg-seafoam/15 font-bold opacity-100 shadow-[inset_0_0_0_1px_rgb(77_182_172_/_0.25)]'
                : 'opacity-60',
              buttonClassName,
            )}
          >
            {LANGUAGE_LABELS[locale]}
          </button>
        </span>
      ))}
    </div>
  )
}
