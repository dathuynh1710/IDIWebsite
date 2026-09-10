import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router'
import PageHead from '@components/common/PageHead'
import { useLanguage } from '@hooks/useLanguage'
import { recipesService } from '@services/recipes.service'
import { SITE_URL } from '@utils/constants'

const COPY = {
  vi: {
    back: 'Tất cả món ăn', eyebrow: 'Gợi ý vào bếp', ingredients: 'Chuẩn bị nguyên liệu',
    directions: 'Các bước thực hiện', error: 'Không thể tải công thức này.', loading: 'Đang tải công thức',
    noMedia: 'Hình ảnh đang được cập nhật',
  },
  en: {
    back: 'All recipes', eyebrow: 'Kitchen inspiration', ingredients: 'Prepare the ingredients',
    directions: 'Cooking directions', error: 'Unable to load this recipe.', loading: 'Loading recipe',
    noMedia: 'Image coming soon',
  },
  'zh-CN': {
    back: '全部食谱', eyebrow: '烹饪灵感', ingredients: '准备食材', directions: '烹饪步骤',
    error: '无法加载此食谱。', loading: '正在加载食谱', noMedia: '图片即将更新',
  },
}

function BackIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" className="h-4 w-4" aria-hidden="true">
      <path d="M16 10H5m4-4-4 4 4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  )
}

function RecipeMedia({ recipe, noMediaLabel }) {
  if (recipe.videoUrl) {
    return (
      <video controls poster={recipe.image?.url} className="h-full w-full object-cover">
        <source src={recipe.videoUrl} />
      </video>
    )
  }

  if (recipe.image?.url) {
    return <img src={recipe.image.url} alt={recipe.image.alt || recipe.title} className="h-full w-full object-cover" />
  }

  return (
    <div className="grid h-full w-full place-items-center bg-ocean-deep px-6 text-center text-sm font-semibold tracking-wide text-white/55">
      {noMediaLabel}
    </div>
  )
}

function RecipeDetailLoading({ label }) {
  return (
    <div className="min-h-[70vh] animate-pulse bg-arctic-white pb-24 pt-28" aria-busy="true" aria-label={label}>
      <div className="container">
        <div className="h-5 w-36 bg-light-mist" />
        <div className="mt-9 grid lg:grid-cols-[minmax(0,1.15fr)_minmax(22rem,0.85fr)] lg:items-center">
          <div className="aspect-[4/3] bg-light-mist" />
          <div className="bg-ocean-deep p-9 lg:-ml-14 lg:p-12">
            <div className="h-3 w-36 bg-white/20" />
            <div className="mt-8 h-12 w-4/5 bg-white/20" />
            <div className="mt-4 h-12 w-3/5 bg-white/20" />
            <div className="mt-8 h-20 w-full bg-white/15" />
          </div>
        </div>
      </div>
    </div>
  )
}

export default function RecipeDetailPage() {
  const { slug } = useParams()
  const { language } = useLanguage()
  const labels = COPY[language] ?? COPY.vi
  const [status, setStatus] = useState('loading')
  const [recipe, setRecipe] = useState(null)

  useEffect(() => {
    const controller = new AbortController()
    setStatus('loading')
    recipesService.getBySlug(slug, language, { signal: controller.signal })
      .then(result => { setRecipe(result.data); setStatus('success') })
      .catch(error => { if (error?.code !== 'ERR_CANCELED') setStatus('error') })
    return () => controller.abort()
  }, [language, slug])

  if (status === 'loading') return <RecipeDetailLoading label={labels.loading} />

  if (status === 'error' || !recipe) {
    return (
      <div className="container grid min-h-[65vh] place-items-center pb-24 pt-36 text-center">
        <div>
          <p className="mb-6 text-storm-grey">{labels.error}</p>
          <Link to="/recipes" className="btn btn-primary"><BackIcon /> {labels.back}</Link>
        </div>
      </div>
    )
  }

  return (
    <>
      <PageHead
        title={recipe.seo?.title || recipe.title}
        description={recipe.seo?.description || recipe.summary}
        canonical={`${SITE_URL}/recipes/${recipe.slug}`}
      />

      <article className="recipe-detail-page bg-arctic-white pb-20 pt-28 lg:pb-28 lg:pt-32">
        <div className="container">
          <Link
            to="/recipes"
            className="inline-flex items-center gap-2 border-b border-transparent pb-1 text-sm font-semibold text-ocean-deep transition-colors hover:border-seafoam hover:text-seafoam focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-seafoam"
          >
            <BackIcon /> {labels.back}
          </Link>

          <header className="mt-8 grid lg:grid-cols-[minmax(0,1.15fr)_minmax(22rem,0.85fr)] lg:items-center">
            <figure className="relative z-0 aspect-[4/3] overflow-hidden bg-light-mist lg:aspect-[5/4]">
              <RecipeMedia recipe={recipe} noMediaLabel={labels.noMedia} />
              <span className="absolute bottom-0 left-0 h-1 w-24 bg-coral-gold" aria-hidden="true" />
            </figure>

            <div className="relative z-10 bg-ocean-deep p-7 text-white sm:p-10 lg:-ml-14 lg:p-12 xl:p-14">
              <span className="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.16em] text-seafoam-light">
                <span className="h-px w-8 bg-coral-gold" />
                {labels.eyebrow}
              </span>
              <h1 className="mt-7 text-[clamp(2.25rem,4.6vw,4.25rem)] font-bold leading-[1.04] tracking-[-0.045em] text-white text-balance">
                {recipe.title}
              </h1>
              {recipe.summary && (
                <p className="mt-7 border-l border-white/25 pl-5 text-base leading-8 text-white/72 sm:text-lg">
                  {recipe.summary}
                </p>
              )}
            </div>
          </header>

          {(recipe.contentLeftHtml || recipe.contentRightHtml) && (
            <div className={`recipe-detail-content mt-14 lg:mt-20 ${recipe.contentLeftHtml && recipe.contentRightHtml ? 'recipe-detail-content--split' : ''}`}>
              {recipe.contentLeftHtml && (
                <aside className="recipe-detail-panel recipe-detail-panel--ingredients" aria-label={labels.ingredients}>
                  <p className="recipe-detail-panel__index" aria-hidden="true">01</p>
                  <p className="recipe-detail-panel__label">{labels.ingredients}</p>
                  <div className="recipe-detail-rich recipe-detail-rich--ingredients" dangerouslySetInnerHTML={{ __html: recipe.contentLeftHtml }} />
                </aside>
              )}

              {recipe.contentRightHtml && (
                <section className="recipe-detail-panel recipe-detail-panel--directions" aria-label={labels.directions}>
                  <div className="recipe-detail-panel__heading">
                    <div>
                      <p className="recipe-detail-panel__index" aria-hidden="true">02</p>
                      <p className="recipe-detail-panel__label">{labels.directions}</p>
                    </div>
                  </div>
                  <div className="recipe-detail-rich recipe-detail-rich--directions" dangerouslySetInnerHTML={{ __html: recipe.contentRightHtml }} />
                </section>
              )}
            </div>
          )}
        </div>
      </article>
    </>
  )
}
