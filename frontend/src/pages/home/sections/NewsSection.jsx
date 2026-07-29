import { Link } from 'react-router'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { NEWS_DATA } from '@data/news'

const CATEGORY_COLORS = {
  gold:  { bg: 'bg-coral-pale',   text: 'text-[#B37518]' },
  blue:  { bg: 'bg-[#EBF4FF]',    text: 'text-ocean-deep' },
  green: { bg: 'bg-seafoam-pale', text: 'text-seafoam' },
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('vi-VN', {
    day:   'numeric',
    month: 'long',
    year:  'numeric',
  })
}

export default function NewsSection() {
  const articles = NEWS_DATA.slice(0, 3)

  return (
    <section className="py-20 lg:py-28 bg-arctic-white">
      <div className="container">

        {/* Header row */}
        <div className="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-12">
          <div>
            <RevealOnScroll>
              <span className="section-eyebrow">Tin mới nhất</span>
            </RevealOnScroll>
            <RevealOnScroll delay={80}>
              <h2 className="text-h2 font-bold text-ocean-deep mt-3">
                Tin tức &amp; Sự kiện
              </h2>
            </RevealOnScroll>
          </div>
          <RevealOnScroll direction="right">
            <Link to="/news" className="btn btn-secondary whitespace-nowrap">
              Xem tất cả →
            </Link>
          </RevealOnScroll>
        </div>

        {/* Articles grid */}
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {articles.map((article, i) => {
            const catColor = CATEGORY_COLORS[article.categoryColor] ?? CATEGORY_COLORS.blue
            return (
              <RevealOnScroll key={article.id} delay={i * 100}>
                <Link
                  to={`/news/${article.slug}`}
                  className="group flex flex-col bg-white rounded-2xl overflow-hidden border border-light-mist hover:border-transparent hover:shadow-xl transition-all duration-300 hover:-translate-y-1 h-full"
                >
                  {/* Thumbnail */}
                  <div className="aspect-[16/9] overflow-hidden bg-light-mist flex-shrink-0">
                    <img
                      src={article.image}
                      alt={article.title}
                      className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                      loading="lazy"
                      onError={(e) => {
                        e.target.parentElement.style.background = 'linear-gradient(135deg, #0B2545, #163D6B)'
                        e.target.style.display = 'none'
                      }}
                    />
                  </div>

                  {/* Content */}
                  <div className="flex flex-col flex-1 p-6">
                    {/* Category + date */}
                    <div className="flex items-center justify-between gap-3 mb-4">
                      <span className={`badge text-[11px] ${catColor.bg} ${catColor.text}`}>
                        {article.category}
                      </span>
                      <time
                        dateTime={article.date}
                        className="text-xs text-storm-grey"
                      >
                        {formatDate(article.date)}
                      </time>
                    </div>

                    {/* Title */}
                    <h3 className="font-bold text-ink text-base leading-snug mb-3 group-hover:text-ocean-deep transition-colors duration-200 line-clamp-2">
                      {article.title}
                    </h3>

                    {/* Excerpt */}
                    <p className="text-storm-grey text-sm leading-relaxed flex-1 line-clamp-3">
                      {article.excerpt}
                    </p>

                    {/* Read more */}
                    <div className="flex items-center gap-1.5 text-seafoam text-xs font-semibold mt-5 opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0 transition-all duration-300">
                      Đọc thêm
                      <svg className="w-3.5 h-3.5" viewBox="0 0 14 14" fill="none">
                        <path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                    </div>
                  </div>
                </Link>
              </RevealOnScroll>
            )
          })}
        </div>

      </div>
    </section>
  )
}
