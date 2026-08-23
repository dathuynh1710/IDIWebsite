import { LANGUAGES, LANGUAGE_LABELS } from '@utils/constants'
import { useLanguage } from '@hooks/useLanguage'
import { cn } from '@utils/cn'

export default function LanguageSwitcher({ className, buttonClassName, separatorClassName }) {
  const { language, setLanguage, t } = useLanguage()

  return (
    <div className={cn('flex items-center gap-1', className)} role="group" aria-label={t('language.label')}>
      {Object.values(LANGUAGES).map((locale, index) => (
        <span key={locale} className="contents">
          {index > 0 && <span aria-hidden="true" className={cn('opacity-30', separatorClassName)}>|</span>}
          <button
            type="button"
            onClick={() => setLanguage(locale)}
            aria-pressed={language === locale}
            lang={locale}
            className={cn(
              'rounded px-2 py-1 transition-opacity hover:opacity-100',
              language === locale ? 'font-bold opacity-100' : 'opacity-60',
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
