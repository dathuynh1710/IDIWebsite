import { lazy, Suspense } from 'react'
import { createBrowserRouter } from 'react-router'

// Layouts
import RootLayout from '@layouts/RootLayout'
import InvestorLayout from '@layouts/InvestorLayout'

// Error pages (always eager — needed for error boundaries)
import ErrorPage from '@pages/errors/ErrorPage'
import NotFoundPage from '@pages/errors/NotFoundPage'

// Page loader (shown while lazy chunks download)
import PageLoader from '@components/common/PageLoader'

// ─────────────────────────────────────────────────────────────
// EAGER LOADED — Critical path pages (no lazy split)
// Reason: Homepage and Products are the most-visited entry points.
// Splitting them would add unnecessary round-trip on first load.
// ─────────────────────────────────────────────────────────────
import HomePage from '@pages/home/HomePage'
import ProductsPage from '@pages/products/ProductsPage'

// ─────────────────────────────────────────────────────────────
// LAZY LOADED — Non-critical pages (split into separate chunks)
// Each lazy() call = separate JS bundle, downloaded on demand.
// ─────────────────────────────────────────────────────────────
const AboutPage             = lazy(() => import('@pages/about/AboutPage'))
const StoryPage             = lazy(() => import('@pages/about/StoryPage'))
const ValuesPage            = lazy(() => import('@pages/about/ValuesPage'))

const QualityPage           = lazy(() => import('@pages/quality/QualityPage'))

const SustainabilityPage    = lazy(() => import('@pages/sustainability/SustainabilityPage'))

const InvestorsPage         = lazy(() => import('@pages/investors/InvestorsPage'))
const AnnouncementsPage     = lazy(() => import('@pages/investors/AnnouncementsPage'))
const FinancialsPage        = lazy(() => import('@pages/investors/FinancialsPage'))
const AnnualReportsPage     = lazy(() => import('@pages/investors/AnnualReportsPage'))
const AGMPage               = lazy(() => import('@pages/investors/AGMPage'))
const GreenBondPage         = lazy(() => import('@pages/investors/GreenBondPage'))

const NewsPage              = lazy(() => import('@pages/news/NewsPage'))
const NewsDetailPage        = lazy(() => import('@pages/news/NewsDetailPage'))
const RecipesPage           = lazy(() => import('@pages/recipes/RecipesPage'))
const RecipeDetailPage      = lazy(() => import('@pages/recipes/RecipeDetailPage'))

const CareersPage           = lazy(() => import('@pages/careers/CareersPage'))

const ContactPage           = lazy(() => import('@pages/contact/ContactPage'))

// ─────────────────────────────────────────────────────────────
// Helper: Wraps a lazy component in a Suspense boundary.
// The RootLayout also has a Suspense wrapper — this is intentional:
// Page-level Suspense gives finer-grained fallback control.
// ─────────────────────────────────────────────────────────────
const withSuspense = (Component) => (
  <Suspense fallback={<PageLoader />}>
    <Component />
  </Suspense>
)

// ─────────────────────────────────────────────────────────────
// ROUTE TREE
// Structure mirrors the sitemap from react_architecture.md.
// Each route group is annotated with its purpose.
// ─────────────────────────────────────────────────────────────
export const router = createBrowserRouter([
  {
    // ── Root: All public-facing pages share RootLayout (Navbar + Footer)
    path: '/',
    element: <RootLayout />,
    errorElement: <ErrorPage />,
    children: [

      // ── Homepage
      { index: true, element: <HomePage /> },

      // ── Products
      { path: 'products', element: <ProductsPage /> },

      // ── About (parent page + sub-pages)
      { path: 'about', element: withSuspense(AboutPage) },
      { path: 'about/story', element: withSuspense(StoryPage) },
      { path: 'about/values', element: withSuspense(ValuesPage) },

      // ── Quality
      { path: 'quality', element: withSuspense(QualityPage) },

      // ── Sustainability
      { path: 'sustainability', element: withSuspense(SustainabilityPage) },

      // ── Investors: Uses InvestorLayout (adds sidebar nav)
      {
        path: 'investors',
        element: withSuspense(InvestorLayout),
        children: [
          { index: true, element: withSuspense(InvestorsPage) },
          { path: 'announcements', element: withSuspense(AnnouncementsPage) },
          { path: 'financials', element: withSuspense(FinancialsPage) },
          { path: 'annual-reports', element: withSuspense(AnnualReportsPage) },
          { path: 'agm', element: withSuspense(AGMPage) },
          { path: 'green-bond', element: withSuspense(GreenBondPage) },
        ],
      },

      // ── News
      { path: 'news', element: withSuspense(NewsPage) },
      { path: 'news/:slug', element: withSuspense(NewsDetailPage) },

      // ── Recipes
      { path: 'recipes', element: withSuspense(RecipesPage) },
      { path: 'recipes/:slug', element: withSuspense(RecipeDetailPage) },

      // ── Careers
      { path: 'careers', element: withSuspense(CareersPage) },

      // ── Contact
      { path: 'contact', element: withSuspense(ContactPage) },

      // ── 404 catch-all (must be last)
      { path: '*', element: <NotFoundPage /> },
    ],
  },
], {
  basename: import.meta.env.BASE_URL,
})
