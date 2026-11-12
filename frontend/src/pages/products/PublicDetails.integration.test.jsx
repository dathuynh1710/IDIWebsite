import { beforeEach, afterEach, expect, test, vi } from 'vitest'
import { cleanup, fireEvent, render, screen, waitFor } from '@testing-library/react'
import { createMemoryRouter, RouterProvider, Outlet, ScrollRestoration } from 'react-router'
import PageTransition from '@components/common/PageTransition'
import { LanguageProvider } from '@context/LanguageContext'
import PublicRoutingProvider from '@context/PublicRoutingContext'
import AboutRoutingProvider from '@context/AboutRoutingContext'
import ProductsPage from './ProductsPage'
import CareersPage from '@pages/careers/CareersPage'
import { productsService } from '@services/products.service'
import { careersService } from '@services/careers.service'
import api from '@services/api'
import { staticRoutes } from '@utils/publicRoutes'

vi.mock('@services/api', () => ({ default: { get: vi.fn() } }))
vi.mock('@services/about.service', () => ({ aboutService: { getPages: vi.fn().mockResolvedValue({ items: [] }) } }))
vi.mock('@services/products.service', () => ({ productsService: { getCatalog: vi.fn(), getBySlug: vi.fn() } }))
vi.mock('@services/careers.service', () => ({ careersService: { getOpenings: vi.fn(), getById: vi.fn() } }))
const entries = [
  ...Object.entries(staticRoutes).map(([legacy, paths]) => ({ legacy, paths, name: 'static' })),
  ...['products','careers'].map(group => ({ name: group + '.show', group, slugs: { vi: 'viet', en: 'english', zh: 'zhongwen' }, paths: { vi: staticRoutes['/' + group].vi + '/viet', en: staticRoutes['/' + group].en + '/english', zh: staticRoutes['/' + group].zh + '/zhongwen' } })),
]
beforeEach(() => {
  localStorage.clear()
  vi.clearAllMocks()
  window.requestAnimationFrame = callback => setTimeout(callback, 0)
  window.cancelAnimationFrame = clearTimeout
  api.get.mockResolvedValue({ data: { entries, redirects: {} } })
  productsService.getCatalog.mockResolvedValue({ categories: [], total: 0 })
  productsService.getBySlug.mockImplementation(async slug => ({ id: 1, slug, name: 'Product ' + slug, sizes: [], presentation: [] }))
  careersService.getOpenings.mockResolvedValue({ items: [], total: 0 })
  careersService.getById.mockImplementation(async slug => ({ id: 1, slug, title: 'Position ' + slug, description: '<p>Job description</p>' }))
})
afterEach(cleanup)
test('opening and closing a product preserves catalog scroll and does not animate the page again', async () => {
  const product = { id: 1, slug: 'viet', name: 'Product viet', sizes: [], presentation: [] }
  productsService.getCatalog.mockResolvedValue({ categories: [{ id: 1, slug: 'fish', name: 'Fish', products: [product] }], total: 1 })
  const scrollTo = vi.spyOn(window, 'scrollTo').mockImplementation(() => {})
  const animate = vi.fn(() => ({ cancel: vi.fn() }))
  const originalAnimate = HTMLElement.prototype.animate
  HTMLElement.prototype.animate = animate
  const router = createMemoryRouter([{
    element: <LanguageProvider><PublicRoutingProvider><PageTransition /><ScrollRestoration /></PublicRoutingProvider></LanguageProvider>,
    children: [
      { path: '/vi/san-pham', element: <ProductsPage /> },
      { path: '/vi/san-pham/:slug', element: <ProductsPage /> },
    ],
  }], { initialEntries: ['/vi/san-pham'] })
  try {
    render(<RouterProvider router={router} />)
    const trigger = await screen.findByRole('button', { name: 'Product viet' })
    trigger.focus()
    scrollTo.mockClear()
    animate.mockClear()
    fireEvent.click(trigger)
    await screen.findByRole('dialog')
    await waitFor(() => expect(productsService.getBySlug).toHaveBeenCalled())
    expect(router.state.location.pathname).toBe('/vi/san-pham/viet')
    expect(scrollTo).not.toHaveBeenCalled()
    expect(animate).not.toHaveBeenCalled()
    expect(document.body.style.overflow).toBe('hidden')
    fireEvent.keyDown(window, { key: 'Escape' })
    await waitFor(() => expect(screen.queryByRole('dialog')).toBeNull())
    expect(router.state.location.pathname).toBe('/vi/san-pham')
    expect(scrollTo).not.toHaveBeenCalled()
    expect(animate).not.toHaveBeenCalled()
    expect(document.body.style.overflow).toBe('')
    expect(document.activeElement).toBe(trigger)
  } finally {
    cleanup()
    scrollTo.mockRestore()
    if (originalAnimate) HTMLElement.prototype.animate = originalAnimate
    else delete HTMLElement.prototype.animate
  }
})

test.each(['vi','en','zh'])('product and position details load directly in %s and survive reload', async locale => {
  for (const [group, Component, title] of [['products', ProductsPage, 'Product'], ['careers', CareersPage, 'Position']]) {
    const entry = entries.find(item => item.group === group)
    for (let reload = 0; reload < 2; reload++) {
      const router = createMemoryRouter([{ element: <LanguageProvider><PublicRoutingProvider><AboutRoutingProvider><Outlet /></AboutRoutingProvider></PublicRoutingProvider></LanguageProvider>, children: [{ path: staticRoutes['/' + group][locale] + '/:slug', element: <Component /> }] }], { initialEntries: [entry.paths[locale]] })
      render(<RouterProvider router={router} />)
      await screen.findByRole('dialog')
      await screen.findByText(title + ' ' + entry.slugs[locale])
      const language = locale === 'zh' ? 'zh-CN' : locale
      if (group === 'products') expect(productsService.getBySlug).toHaveBeenCalledWith(entry.slugs[locale], { locale: language })
      else expect(careersService.getById).toHaveBeenCalledWith(entry.slugs[locale], language)
      await waitFor(() => expect(document.querySelector('link[rel=canonical]').href).toContain(entry.paths[locale]))
      cleanup()
    }
  }
})
