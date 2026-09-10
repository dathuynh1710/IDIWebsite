import { useEffect, useState } from "react";
import { Link } from "react-router";
import PageHead from "@components/common/PageHead";
import { useLanguage } from "@hooks/useLanguage";
import { recipesService } from "@services/recipes.service";
import { SITE_URL } from "@utils/constants";

const COPY = {
  vi: {
    eyebrow: "Góc bếp IDI",
    featured: "Công thức nổi bật",
    all: "Thư viện công thức",
    view: "Xem công thức",
    empty: "Chưa có công thức để hiển thị.",
    error: "Không thể tải danh sách món ăn.",
    retry: "Thử lại",
    previous: "Trang trước",
    next: "Trang sau",
    loading: "Đang tải công thức",
    pagination: "Phân trang công thức",
  },
  en: {
    eyebrow: "IDI kitchen",
    featured: "Featured recipe",
    all: "Recipe library",
    view: "View recipe",
    empty: "No recipes are available.",
    error: "Unable to load recipes.",
    retry: "Try again",
    previous: "Previous page",
    next: "Next page",
    loading: "Loading recipes",
    pagination: "Recipe pagination",
  },
  "zh-CN": {
    eyebrow: "IDI 厨房",
    featured: "精选食谱",
    all: "食谱合集",
    view: "查看食谱",
    empty: "暂无食谱。",
    error: "无法加载食谱。",
    retry: "重试",
    previous: "上一页",
    next: "下一页",
    loading: "正在加载食谱",
    pagination: "食谱分页",
  },
};

const FALLBACK_CONFIG = {
  title: "Công thức bạn có thể thử",
  description: "Khám phá những công thức món ăn để làm mới thực đơn của bạn.",
  seo: {},
};

function ArrowIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" className="h-4 w-4" aria-hidden="true">
      <path
        d="M4 10h11M11 6l4 4-4 4"
        stroke="currentColor"
        strokeWidth="1.5"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

function RecipeImage({ recipe, eager = false }) {
  return recipe.image?.url ? (
    <img
      src={recipe.image.url}
      alt={recipe.image.alt || recipe.title}
      className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.025]"
      loading={eager ? "eager" : "lazy"}
    />
  ) : (
    <div className="grid h-full w-full place-items-center bg-ocean-deep text-2xl font-bold tracking-[0.18em] text-white/45">
      IDI
    </div>
  );
}

function RecipeCard({ recipe, labels }) {
  const href = `/recipes/${recipe.slug}`;

  return (
    <article className="group flex h-full flex-col border-t border-mist-mid pt-4">
      <Link
        to={href}
        className="block aspect-[3/2] overflow-hidden bg-light-mist focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-seafoam"
        aria-label={`${labels.view}: ${recipe.title}`}
      >
        <RecipeImage recipe={recipe} />
      </Link>
      <div className="flex flex-1 flex-col pt-5">
        <h3 className="text-xl font-bold leading-snug tracking-[-0.015em] text-ocean-deep line-clamp-2">
          <Link
            className="transition-colors hover:text-seafoam focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-seafoam"
            to={href}
          >
            {recipe.title}
          </Link>
        </h3>
        {recipe.summary && (
          <p className="mt-3 flex-1 text-sm leading-7 text-storm-grey line-clamp-3">{recipe.summary}</p>
        )}
        <Link
          to={href}
          className="mt-5 inline-flex w-fit items-center gap-2 border-b border-ocean-deep/25 pb-1 text-sm font-semibold text-ocean-deep transition-colors hover:border-seafoam hover:text-seafoam focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-seafoam"
        >
          {labels.view} <ArrowIcon />
        </Link>
      </div>
    </article>
  );
}

function RecipesLoading({ label }) {
  return (
    <div className="container animate-pulse py-16 lg:py-20" aria-busy="true" aria-label={label}>
      <div className="grid overflow-hidden border border-light-mist bg-white lg:grid-cols-[1.2fr_0.8fr]">
        <div className="min-h-72 bg-light-mist lg:min-h-[28rem]" />
        <div className="flex flex-col justify-center p-8 lg:p-12">
          <div className="h-3 w-28 bg-light-mist" />
          <div className="mt-6 h-9 w-4/5 bg-light-mist" />
          <div className="mt-5 h-20 w-full bg-light-mist" />
        </div>
      </div>
      <div className="mt-20 grid gap-x-7 gap-y-14 sm:grid-cols-2 lg:grid-cols-3">
        {Array.from({ length: 6 }, (_, index) => (
          <div key={index} className="aspect-[3/2] border-t border-light-mist bg-light-mist" />
        ))}
      </div>
    </div>
  );
}

export default function RecipesPage() {
  const { language } = useLanguage();
  const labels = COPY[language] ?? COPY.vi;
  const [page, setPage] = useState(1);
  const [requestKey, setRequestKey] = useState(0);
  const [status, setStatus] = useState("loading");
  const [result, setResult] = useState({ items: [], pageConfig: FALLBACK_CONFIG, page: 1, lastPage: 1, total: 0 });

  useEffect(() => {
    setPage(1);
  }, [language]);

  useEffect(() => {
    const controller = new AbortController();
    setStatus("loading");
    recipesService
      .getAll({ locale: language, page }, { signal: controller.signal })
      .then((data) => {
        setResult(data);
        setStatus("success");
      })
      .catch((error) => {
        if (error?.code !== "ERR_CANCELED") setStatus("error");
      });
    return () => controller.abort();
  }, [language, page, requestKey]);

  const config = result.pageConfig ?? FALLBACK_CONFIG;
  const featured = result.items.find((recipe) => recipe.isFeatured);
  const recipes = result.items.filter((recipe) => recipe.id !== featured?.id);

  const changePage = (nextPage) => {
    setPage(nextPage);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  return (
    <>
      <PageHead
        title={config.seo?.title || `${config.title} | IDI Seafood`}
        description={config.seo?.description || config.description}
        canonical={`${SITE_URL}/recipes`}
      />

      <header className="border-b border-light-mist bg-white pb-12 pt-28 sm:pb-14 sm:pt-32 lg:pb-16 lg:pt-36">
        <div className="container">
          <div className="grid gap-7 lg:grid-cols-[minmax(0,1.45fr)_minmax(19rem,0.55fr)] lg:items-end lg:gap-16">
            <div>
              <span className="mb-5 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.16em] text-seafoam">
                <span className="h-px w-8 bg-seafoam" />
                {labels.eyebrow}
              </span>
              <h1 className="max-w-4xl text-[clamp(2.25rem,5vw,4.5rem)] font-bold leading-[1.06] tracking-[-0.045em] text-ocean-deep text-balance">
                {config.title}
              </h1>
            </div>
            {config.description && (
              <p className="border-l-2 border-coral-gold pl-5 text-base leading-7 text-slate lg:mb-1">
                {config.description}
              </p>
            )}
          </div>
        </div>
      </header>

      <main className="bg-arctic-white pb-20 lg:pb-28">
        {status === "loading" ? (
          <RecipesLoading label={labels.loading} />
        ) : status === "error" ? (
          <div className="container py-24 text-center" role="alert">
            <p className="mb-6 text-storm-grey">{labels.error}</p>
            <button type="button" className="btn btn-primary" onClick={() => setRequestKey((key) => key + 1)}>
              {labels.retry}
            </button>
          </div>
        ) : (
          <>
            {featured && (
              <section className="py-14 lg:py-20" aria-labelledby="featured-recipe-title">
                <div className="container">
                  <div className="mb-5 flex items-center justify-between border-b border-mist-mid pb-4">
                    <p className="text-xs font-semibold uppercase tracking-[0.15em] text-seafoam">{labels.featured}</p>
                  </div>
                  <article className="grid border border-light-mist bg-white lg:grid-cols-[1.2fr_0.8fr]">
                    <Link
                      to={`/recipes/${featured.slug}`}
                      className="group block min-h-72 overflow-hidden bg-light-mist focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-seafoam lg:min-h-[28rem]"
                      aria-label={`${labels.view}: ${featured.title}`}
                    >
                      <RecipeImage recipe={featured} eager />
                    </Link>
                    <div className="flex flex-col justify-center border-t border-light-mist p-7 sm:p-10 lg:border-l lg:border-t-0 lg:p-12">
                      <h2
                        id="featured-recipe-title"
                        className="text-[clamp(1.75rem,3vw,2.5rem)] font-bold leading-tight tracking-[-0.03em] text-ocean-deep text-balance"
                      >
                        {featured.title}
                      </h2>
                      {featured.summary && <p className="mt-5 leading-7 text-slate">{featured.summary}</p>}
                      <Link
                        to={`/recipes/${featured.slug}`}
                        className="mt-8 inline-flex w-fit items-center gap-3 bg-ocean-deep px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-ocean-mid focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ocean-deep"
                      >
                        {labels.view} <ArrowIcon />
                      </Link>
                    </div>
                  </article>
                </div>
              </section>
            )}

            {(recipes.length > 0 || !featured || result.lastPage > 1) && (
              <section className={featured ? "pt-4" : "pt-14 lg:pt-20"} aria-labelledby="recipe-library-title">
                <div className="container">
                  <div className="mb-9">
                    <h2
                      id="recipe-library-title"
                      className="text-[clamp(1.75rem,3vw,2.5rem)] font-bold leading-tight tracking-[-0.025em] text-ocean-deep"
                    >
                      {labels.all}
                    </h2>
                  </div>

                  {recipes.length ? (
                    <div className="grid gap-x-7 gap-y-14 sm:grid-cols-2 lg:grid-cols-3">
                      {recipes.map((recipe) => (
                        <RecipeCard key={recipe.id} recipe={recipe} labels={labels} />
                      ))}
                    </div>
                  ) : !featured ? (
                    <p className="border border-dashed border-mist-mid bg-white p-10 text-center text-storm-grey">
                      {labels.empty}
                    </p>
                  ) : null}

                  {result.lastPage > 1 && (
                    <nav
                      className="mt-16 flex items-center justify-between border-t border-mist-mid pt-6"
                      aria-label={labels.pagination}
                    >
                      <button
                        type="button"
                        className="inline-flex items-center gap-2 text-sm font-semibold text-ocean-deep transition-colors hover:text-seafoam disabled:cursor-not-allowed disabled:opacity-35"
                        disabled={page <= 1}
                        onClick={() => changePage(page - 1)}
                      >
                        <span aria-hidden="true">←</span> {labels.previous}
                      </button>
                      <span className="text-sm tabular-nums text-storm-grey">
                        {page} / {result.lastPage}
                      </span>
                      <button
                        type="button"
                        className="inline-flex items-center gap-2 text-sm font-semibold text-ocean-deep transition-colors hover:text-seafoam disabled:cursor-not-allowed disabled:opacity-35"
                        disabled={page >= result.lastPage}
                        onClick={() => changePage(page + 1)}
                      >
                        {labels.next} <span aria-hidden="true">→</span>
                      </button>
                    </nav>
                  )}
                </div>
              </section>
            )}
          </>
        )}
      </main>
    </>
  );
}
