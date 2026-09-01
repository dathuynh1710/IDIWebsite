import RevealOnScroll from '@components/common/RevealOnScroll'
import { useLanguage } from '@hooks/useLanguage'
import { getHomeTranslations } from '@/i18n/home'

const REASONS = [
  {
    id: 'vertical',
    icon: (
      <svg viewBox="0 0 32 32" fill="none" className="w-8 h-8">
        <path d="M16 4v24M4 16h24" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        <circle cx="16" cy="4" r="2" fill="currentColor" />
        <circle cx="16" cy="28" r="2" fill="currentColor" />
        <circle cx="4" cy="16" r="2" fill="currentColor" />
        <circle cx="28" cy="16" r="2" fill="currentColor" />
        <circle cx="16" cy="16" r="4" fill="currentColor" />
      </svg>
    ),
  },
  {
    id: 'quality',
    icon: (
      <svg viewBox="0 0 32 32" fill="none" className="w-8 h-8">
        <path d="M16 3l3.09 6.26L26 10.27l-5 4.87 1.18 6.88L16 19l-6.18 3.02L11 15.14 6 10.27l6.91-1.01L16 3z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
  },
  {
    id: 'sustainability',
    icon: (
      <svg viewBox="0 0 32 32" fill="none" className="w-8 h-8">
        <path d="M12 22s-8-4-8-11a8 8 0 0116 0c0 7-8 11-8 11z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M12 22v4M9 26h6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        <path d="M8 13c0-2.21 1.79-4 4-4" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
      </svg>
    ),
  },
  {
    id: 'reliability',
    icon: (
      <svg viewBox="0 0 32 32" fill="none" className="w-8 h-8">
        <path d="M4 16L16 4l12 12M6 14v12h6v-6h8v6h6V14" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
  },
]

export default function WhyChooseUsSection() {
  const { language } = useLanguage()
  const copy = getHomeTranslations(language).reasons

  return (
    <section className="py-24 lg:py-36 bg-arctic-white">
      <div className="container">

        {/* Header */}
        <div className="max-w-2xl mx-auto text-center mb-16">
          <RevealOnScroll>
            <span className="section-eyebrow">{copy.eyebrow}</span>
          </RevealOnScroll>
          <RevealOnScroll delay={80}>
            <h2 className="text-h2 font-bold text-ocean-deep mt-3 mb-4">
              {copy.title}
            </h2>
          </RevealOnScroll>
          <RevealOnScroll delay={160}>
            <p className="text-storm-grey text-body-lg">
              {copy.description}
            </p>
          </RevealOnScroll>
        </div>

        {/* Reasons grid */}
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {REASONS.map((reason, i) => (
            <RevealOnScroll key={reason.id} delay={i * 100}>
              <div className="group relative bg-white rounded-2xl p-7 border border-light-mist hover:border-ocean-light hover:shadow-xl transition-all duration-300 flex flex-col h-full">

                {/* Icon */}
                <div className="w-14 h-14 rounded-xl bg-seafoam-pale text-seafoam flex items-center justify-center mb-6 group-hover:bg-seafoam group-hover:text-white transition-colors duration-300">
                  {reason.icon}
                </div>

                {/* Content */}
                <h3 className="font-bold text-ocean-deep text-lg mb-3">{copy.items[i][0]}</h3>
                <p className="text-storm-grey text-sm leading-relaxed mb-5 flex-1">{copy.items[i][1]}</p>

                {/* Highlight pill */}
                <div className="inline-flex items-center gap-1.5 text-xs font-semibold text-seafoam">
                  <span className="w-1.5 h-1.5 rounded-full bg-seafoam" />
                  {copy.items[i][2]}
                </div>

                {/* Hover accent line */}
                <div className="absolute bottom-0 left-6 right-6 h-0.5 bg-seafoam scale-x-0 group-hover:scale-x-100 transition-transform duration-300 rounded-full" />
              </div>
            </RevealOnScroll>
          ))}
        </div>

      </div>
    </section>
  )
}
