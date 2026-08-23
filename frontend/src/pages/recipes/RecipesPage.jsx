import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import PageHead from '@components/common/PageHead'
import RevealOnScroll from '@components/common/RevealOnScroll'
import { useLanguage } from '@hooks/useLanguage'
import { recipesService } from '@services/recipes.service'
import { SITE_URL } from '@utils/constants'

const COPY = {
  vi: { eyebrow: 'Góc bếp IDI', featured: 'Món ăn nổi bật', all: 'Khám phá công thức', view: 'Xem công thức', count: 'món ăn', empty: 'Chưa có công thức để hiển thị.', error: 'Không thể tải danh sách món ăn.', retry: 'Thử lại', previous: 'Trước', next: 'Sau' },
  en: { eyebrow: 'IDI kitchen', featured: 'Featured recipe', all: 'Explore recipes', view: 'View recipe', count: 'recipes', empty: 'No recipes are available.', error: 'Unable to load recipes.', retry: 'Try again', previous: 'Previous', next: 'Next' },
  zh: { eyebrow: 'IDI 厨房', featured: '精选食谱', all: '探索食谱', view: '查看食谱', count: '道食谱', empty: '暂无食谱。', error: '无法加载食谱。', retry: '重试', previous: '上一页', next: '下一页' },
}

const FALLBACK_CONFIG = {
  title: 'Công thức bạn có thể thử',
  description: 'Khám phá những công thức cá tra thơm ngon để làm mới thực đơn của bạn.',
  seo: {},
}

function RecipeImage({ recipe, eager = false }) {
  return recipe.image?.url ? (
    <img
      src={recipe.image.url}
      alt={recipe.image.alt || recipe.title}
      className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
      loading={eager ? 'eager' : 'lazy'}
    />
  ) : (
    <div className="grid h-full w-full place-items-center bg-gradient-to-br from-ocean-deep to-seafoam text-3xl font-black tracking-[0.2em] text-white/40">IDI</div>
  )
}

export default function RecipesPage() {
  const { language } = useLanguage()
  const labels = COPY[language] ?? COPY.vi
  const [page, setPage] = useState(1)
  const [requestKey, setRequestKey] = useState(0)
  const [status, setStatus] = useState('loading')
  const [result, setResult] = useState({ items: [], pageConfig: FALLBACK_CONFIG, page: 1, lastPage: 1, total: 0 })

  useEffect(() => { setPage(1) }, [language])

  useEffect(() => {
    const controller = new AbortController()
    setStatus('loading')
    recipesService.getAll({ locale: language, page }, { signal: controller.signal })
      .then(data => { setResult(data); setStatus('success') })
      .catch(error => { if (error?.code !== 'ERR_CANCELED') setStatus('error') })
    return () => controller.abort()
  }, [language, page, requestKey])

  const config = result.pageConfig ?? FALLBACK_CONFIG
  const featured = result.items.find(recipe => recipe.isFeatured)
  const recipes = result.items.filter(recipe => recipe.id !== featured?.id)

  return (
    <>
      <PageHead
        title={config.seo?.title || `${config.title} | IDI Seafood`}
        description={config.seo?.description || config.description}
        canonical={`${SITE_URL}/recipes`}
      />

      <header className="relative overflow-hidden bg-ocean-deep pb-24 pt-32 text-white lg:pb-32 lg:pt-40">
        <div className="absolute inset-0 opacity-30" style={{ background: 'radial-gradient(circle at 82% 12%, #1A936F, transparent 36%), radial-gradient(circle at 12% 95%, #E8A045, transparent 28%)' }} />
        <div className="container relative z-10 max-w-4xl">
          <span className="mb-5 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-seafoam-light">
            <span className="h-2 w-2 rounded-full bg-coral-gold" />{labels.eyebrow}
          </span>
          <h1 className="mb-6 text-h1 font-black uppercase text-white text-balance">{config.title}</h1>
          {config.description && <p className="max-w-3xl text-body-lg leading-relaxed text-white/70">{config.description}</p>}
        </div>
      </header>

      <main className="bg-arctic-white pb-24 lg:pb-32">
        {status === 'loading' ? (
          <div className="container grid animate-pulse gap-6 pt-20 sm:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: 6 }, (_, index) => <div key={index} className="h-96 rounded-2xl bg-light-mist" />)}
          </div>
        ) : status === 'error' ? (
          <div className="container pt-20 text-center" role="alert">
            <p className="mb-5 text-storm-grey">{labels.error}</p>
            <button className="btn btn-primary" onClick={() => setRequestKey(key => key + 1)}>{labels.retry}</button>
          </div>
        ) : (
          <>
            {featured && (
              <section className="relative z-10 -mt-14 pb-20 lg:-mt-20 lg:pb-28">
                <div className="container">
                  <RevealOnScroll>
                    <article className="grid overflow-hidden rounded-3xl border border-light-mist bg-white shadow-2xl lg:grid-cols-[1.15fr_0.85fr]">
                      <Link to={`/recipes/${featured.slug}`} className="group block min-h-72 overflow-hidden lg:min-h-[30rem]">
                        <RecipeImage recipe={featured} eager />
                      </Link>
                      <div className="flex flex-col justify-center p-8 sm:p-10 lg:p-12">
                        <span className="mb-4 text-xs font-bold uppercase tracking-[0.16em] text-seafoam">{labels.featured}</span>
                        <h2 className="mb-5 text-3xl font-black leading-tight text-ocean-deep text-balance">{featured.title}</h2>
                        <p className="mb-8 leading-relaxed text-storm-grey">{featured.summary}</p>
                        <Link to={`/recipes/${featured.slug}`} className="btn btn-primary self-start">{labels.view} <span aria-hidden="true">→</span></Link>
                      </div>
                    </article>
                  </RevealOnScroll>
                </div>
              </section>
            )}

            <section className={featured ? '' : 'pt-20'}>
              <div className="container">
                <div className="mb-10 flex items-end justify-between gap-5">
                  <div><span className="section-eyebrow">IDI Seafood</span><h2 className="mt-2 text-h2 font-bold text-ocean-deep">{labels.all}</h2></div>
                  <span className="text-sm text-storm-grey">{result.total} {labels.count}</span>
                </div>
                {recipes.length ? (
                  <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {recipes.map((recipe, index) => (
                      <RevealOnScroll key={recipe.id} delay={(index % 3) * 80}>
                        <article className="group flex h-full flex-col overflow-hidden rounded-2xl border border-light-mist bg-white transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                          <Link to={`/recipes/${recipe.slug}`} className="block aspect-[4/3] overflow-hidden bg-light-mist"><RecipeImage recipe={recipe} /></Link>
                          <div className="flex flex-1 flex-col p-6">
                            <h3 className="mb-3 text-xl font-black leading-snug text-ocean-deep line-clamp-2"><Link to={`/recipes/${recipe.slug}`}>{recipe.title}</Link></h3>
                            <p className="mb-6 flex-1 text-sm leading-relaxed text-storm-grey line-clamp-3">{recipe.summary}</p>
                            <Link to={`/recipes/${recipe.slug}`} className="inline-flex items-center gap-2 text-sm font-bold text-seafoam">{labels.view} <span aria-hidden="true">→</span></Link>
                          </div>
                        </article>
                      </RevealOnScroll>
                    ))}
                  </div>
                ) : <p className="rounded-2xl border border-dashed border-mist-mid bg-white p-12 text-center text-storm-grey">{labels.empty}</p>}

                {result.lastPage > 1 && (
                  <nav className="mt-12 flex justify-center gap-3" aria-label="Pagination">
                    <button className="btn btn-secondary" disabled={page <= 1} onClick={() => setPage(value => value - 1)}>{labels.previous}</button>
                    <span className="grid place-items-center px-3 text-sm font-bold text-ocean-deep">{page} / {result.lastPage}</span>
                    <button className="btn btn-secondary" disabled={page >= result.lastPage} onClick={() => setPage(value => value + 1)}>{labels.next}</button>
                  </nav>
                )}
              </div>
            </section>
          </>
        )}
      </main>
    </>
  )
}
