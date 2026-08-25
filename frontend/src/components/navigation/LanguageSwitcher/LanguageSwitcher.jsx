import { LANGUAGES, LANGUAGE_LABELS } from '@utils/constants'
import { useLanguage } from '@hooks/useLanguage'
import { cn } from '@utils/cn'

export default function LanguageSwitcher({ className, buttonClassName, separatorClassName }) {
  const { language, setLanguage, t } = useLanguage()

  return (
    <div className={cn('flex items-center gap-1', className)} role="group" aria-label={t('language.label')}>
      {Object.values(LANGUAGES).map((locale, index) => (
        <span key={locale} className="contents">
          {index > 0 && <span aria-hidden="true" className={cn('pointer-events-none opacity-30', separatorClassName)}>|</span>}
          <button
            type="button"
            onClick={() => setLanguage(locale)}
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
