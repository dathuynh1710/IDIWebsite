import { beforeEach, afterEach, expect, test, vi } from 'vitest'
import { cleanup, render, screen, fireEvent, waitFor } from '@testing-library/react'
import { createMemoryRouter, RouterProvider, Outlet } from 'react-router'
import { LanguageProvider } from './LanguageContext'
import AboutRoutingProvider from './AboutRoutingContext'
import PublicRoutingProvider, { usePublicRouting } from './PublicRoutingContext'
import LanguageSwitcher from '@components/navigation/LanguageSwitcher/LanguageSwitcher'
import { Link } from '@components/navigation/LocalizedLink'
import PageHead from '@components/common/PageHead'
import { useLanguage } from '@hooks/useLanguage'
import { staticRoutes, publicLink } from '@utils/publicRoutes'
import api from '@services/api'
vi.mock('@services/api', () => ({ default: { get: vi.fn() } }))
vi.mock('@services/about.service', () => ({ aboutService: { getPages: vi.fn().mockResolvedValue({ items: [] }) } }))
const entries = [
  ...Object.entries(staticRoutes).map(([legacy, paths]) => ({ legacy, paths, name: 'static' })),
  ...['news', 'recipes', 'products', 'careers'].map(group => ({ key: group, group, name: group + '.show', slugs: { vi: 'viet', en: 'english' }, paths: { vi: staticRoutes['/' + group].vi + '/viet', en: staticRoutes['/' + group].en + '/english' } })),
  { name: 'product-categories.show', group: 'products', slugs: { vi: 'ca', en: 'fish' }, paths: { vi: '/vi/san-pham/ca', en: '/en/products/fish' } },
]
function Page() {
  const { language } = useLanguage()
  const { entry } = usePublicRouting()
  return <><PageHead title="Page" /><p>{language}:{entry?.name}</p><Link to="/contact?ref=menu#form">Contact menu</Link><Link to="/products?category=fish">Category</Link></>
}
function mount(path) {
  const router = createMemoryRouter([{ element: <LanguageProvider><PublicRoutingProvider><AboutRoutingProvider><LanguageSwitcher /><Outlet /></AboutRoutingProvider></PublicRoutingProvider></LanguageProvider>, children: [{ path: '*', element: <Page /> }] }], { initialEntries: [path] })
  render(<RouterProvider router={router} />)
  return router
}
beforeEach(() => {
  localStorage.clear()
  api.get.mockResolvedValue({ data: { entries, redirects: { '/en/news/old': '/en/news/english' } } })
})
afterEach(() => { cleanup(); document.head.innerHTML = '' })
test.each(['vi', 'en', 'zh'])('every static %s URL selects language on direct entry and reload', async locale => {
  for (const paths of Object.values(staticRoutes)) {
    localStorage.setItem('idi_lang', 'vi')
    mount(paths[locale])
    await screen.findByText(`${locale === 'zh' ? 'zh-CN' : locale}:static`)
    await waitFor(() => expect(document.querySelector('link[rel=canonical]').href).toContain(paths[locale]))
    expect(document.querySelectorAll('link[rel=alternate]').length).toBe(3)
    cleanup()
  }
})
test.each(['news', 'recipes', 'products', 'careers'])('%s switches translated slug, preserves query/hash and refuses missing translation', async group => {
  const entry = entries.find(item => item.key === group)
  const router = mount(entry.paths.vi + '?ref=test#section')
  await screen.findByText('vi:' + group + '.show')
  fireEvent.click(document.querySelector('button[lang=en]'))
  await screen.findByText('en:' + group + '.show')
  expect(router.state.location.pathname).toBe(entry.paths.en)
  expect(router.state.location.search + router.state.location.hash).toBe('?ref=test#section')
  fireEvent.click(document.querySelector('button[lang="zh-CN"]'))
  await screen.findByRole('status')
  expect(router.state.location.pathname).toBe(entry.paths.en)
  expect(screen.getByText('Contact menu').getAttribute('href')).toBe('/en/contact?ref=menu#form')
  expect(screen.getByText('Category').getAttribute('href')).toBe('/en/products/fish')
})
test('old slug redirects and direct remount retains detail identity', async () => {
  const router = mount('/en/news/old?ref=old#body')
  await screen.findByText('en:news.show')
  await waitFor(() => expect(router.state.location.pathname).toBe('/en/news/english'))
  expect(router.state.location.search + router.state.location.hash).toBe('?ref=old#body')
  cleanup()
  mount('/en/news/english')
  await screen.findByText('en:news.show')
})
test('external, API and download links are preserved; unavailable localized links are disabled', () => {
  for (const path of ['https://example.com', '/api/health', '/investor-documents/4/download']) expect(publicLink(path, 'en', entries)).toBe(path)
  expect(publicLink('/en/news/english', 'zh', entries)).toBeNull()
})
