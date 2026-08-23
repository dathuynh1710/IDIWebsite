import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router'
import PageHead from '@components/common/PageHead'
import { useLanguage } from '@hooks/useLanguage'
import { recipesService } from '@services/recipes.service'
import { SITE_URL } from '@utils/constants'

const COPY = {
  vi: { back: 'Tất cả món ăn', error: 'Không thể tải công thức này.' },
  en: { back: 'All recipes', error: 'Unable to load this recipe.' },
  zh: { back: '全部食谱', error: '无法加载此食谱。' },
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

  if (status === 'loading') return <div className="container min-h-[65vh] animate-pulse pb-24 pt-36"><div className="h-12 w-2/3 rounded bg-light-mist" /><div className="mt-8 aspect-[16/7] rounded-3xl bg-light-mist" /></div>
  if (status === 'error' || !recipe) return <div className="container min-h-[65vh] pb-24 pt-36 text-center"><p className="mb-6 text-storm-grey">{labels.error}</p><Link to="/recipes" className="btn btn-primary">{labels.back}</Link></div>

  return (
    <>
      <PageHead title={recipe.seo?.title || recipe.title} description={recipe.seo?.description || recipe.summary} canonical={`${SITE_URL}/recipes/${recipe.slug}`} />
      <article className="bg-arctic-white pb-24 pt-28 lg:pb-32 lg:pt-36">
        <div className="container max-w-6xl">
          <Link to="/recipes" className="mb-8 inline-flex items-center gap-2 text-sm font-bold text-seafoam">← {labels.back}</Link>
          <div className="grid items-center gap-10 lg:grid-cols-[1.08fr_0.92fr]">
            <div className="aspect-[4/3] overflow-hidden rounded-3xl bg-light-mist shadow-xl">
              {recipe.videoUrl ? <video controls poster={recipe.image?.url} className="h-full w-full object-cover"><source src={recipe.videoUrl} /></video> : recipe.image?.url ? <img src={recipe.image.url} alt={recipe.image.alt || recipe.title} className="h-full w-full object-cover" /> : null}
            </div>
            <div>
              <span className="section-eyebrow">IDI Seafood · Recipe</span>
              <h1 className="mb-6 mt-3 text-h1 font-black leading-tight text-ocean-deep text-balance">{recipe.title}</h1>
              <p className="text-body-lg leading-relaxed text-storm-grey">{recipe.summary}</p>
            </div>
          </div>

          {(recipe.contentLeftHtml || recipe.contentRightHtml) && (
            <div className="mt-14 grid gap-8 lg:grid-cols-2">
              <section className="cms-about-rich min-w-0 rounded-3xl border border-light-mist bg-white p-7 lg:p-9" dangerouslySetInnerHTML={{ __html: recipe.contentLeftHtml }} />
              <section className="cms-about-rich min-w-0 rounded-3xl border border-light-mist bg-white p-7 lg:p-9" dangerouslySetInnerHTML={{ __html: recipe.contentRightHtml }} />
            </div>
          )}
        </div>
      </article>
    </>
  )
}
