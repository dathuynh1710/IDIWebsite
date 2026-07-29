import { Suspense } from 'react'
import { Outlet, ScrollRestoration } from 'react-router'
import Navbar from '@components/navigation/Navbar'
import Footer from '@components/navigation/Footer'
import PageLoader from '@components/common/PageLoader'

/**
 * RootLayout — Wraps all public-facing pages.
 *
 * Responsibilities:
 * - Renders Navbar (sticky, with scroll effect)
 * - Renders Footer
 * - Wraps <Outlet /> in Suspense for lazy-loaded page chunks
 * - Handles ScrollRestoration (React Router v7 built-in)
 *
 * NOT responsible for:
 * - Page-specific layouts (that's InvestorLayout, etc.)
 * - SEO meta (that's PageHead per page)
 * - Data fetching (that's loaders in router.jsx)
 */
export default function RootLayout() {
  return (
    <div className="flex flex-col min-h-screen bg-arctic-white">
      {/* Sticky navigation */}
      <Navbar />

      {/* Main content area — grows to fill remaining height */}
      <main className="flex-1">
        <Suspense fallback={<PageLoader />}>
          <Outlet />
        </Suspense>
      </main>

      {/* Footer */}
      <Footer />

      {/*
        ScrollRestoration — Scrolls to top on route change.
        Must be inside RouterProvider context.
        React Router v7 built-in, no library needed.
      */}
      <ScrollRestoration />
    </div>
  )
}
