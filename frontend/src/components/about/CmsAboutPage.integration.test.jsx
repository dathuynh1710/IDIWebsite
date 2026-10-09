import { afterEach, beforeEach, expect, test, vi } from 'vitest'
import { cleanup, fireEvent, render, screen, waitFor } from '@testing-library/react'
import { createMemoryRouter, RouterProvider, Outlet, Link } from 'react-router'
import { LanguageProvider } from '@context/LanguageContext'
import AboutRoutingProvider, { useAboutRouting } from '@context/AboutRoutingContext'
import LanguageSwitcher from '@components/navigation/LanguageSwitcher/LanguageSwitcher'
import CmsAboutPage from './CmsAboutPage'
import { aboutService } from '@services/about.service'
import { ABOUT_PATTERNS, LEGACY_ABOUT } from '@utils/aboutRoutes'

vi.mock('@services/about.service', () => ({ aboutService: { getPage: vi.fn(), getPages: vi.fn() } }))
vi.mock('@components/common/PageHead', () => ({ default: () => null }))
const pages = [
  { id: 7, code: 'ABOUT_CUSTOM', template: 'about-leadership', localizedPaths: { vi: '/vi/gioi-thieu/nhom', en: '/en/about/team', zh: '/zh/guanyu/tuandui' } },
  { id: 8, code: 'ABOUT_HISTORY', template: 'about-history', localizedPaths: { vi: '/vi/gioi-thieu/lich-su', en: '/en/about/history', zh: '/zh/guanyu/lishi' } },
  { id: 10, code: 'ABOUT_MESSAGE', template: 'about', localizedPaths: { vi: '/vi/gioi-thieu/message', en: '/en/about/message' } },
  { id: 9, code: 'ABOUT_VALUES', template: 'about-values', localizedPaths: { vi: '/vi/gioi-thieu/gia-tri', en: '/en/about/values' } },
]
function Shell() {
  const { resolveLink } = useAboutRouting()
  return <><LanguageSwitcher /><Link to={resolveLink('/about/story')}>Story</Link><Outlet /></>
}
function mount(path) {
  const router = createMemoryRouter([{
    element: <LanguageProvider><AboutRoutingProvider><Shell /></AboutRoutingProvider></LanguageProvider>,
    children: [
      ...Object.values(ABOUT_PATTERNS).map(prefix => ({ path: `${prefix}/:slug`, element: <CmsAboutPage /> })),
      ...Object.entries(LEGACY_ABOUT).map(([path, [identifier]]) => ({ path, element: <CmsAboutPage identifier={identifier} /> })),
    ],
  }], { initialEntries: [path] })
  render(<RouterProvider router={router} />)
  return router
}
beforeEach(() => {
  localStorage.clear()
  vi.clearAllMocks()
  aboutService.getPages.mockResolvedValue({ items: pages })
  aboutService.getPage.mockImplementation(async (identifier, { locale, bySlug }) => {
    const page = pages.find(page => bySlug ? page.localizedPaths[locale]?.split('/').at(-1) === identifier : page.code === identifier)
    if (!page) throw { response: { status: 404 } }
    return { ...page, locale, title: `${page.code} ${locale}`, content: `<p>Content ${page.id} ${locale}</p>` }
  })
})
afterEach(cleanup)
test.each(['vi', 'en', 'zh'])('direct %s URL overrides stored language and survives remount', async locale => {
  localStorage.setItem('idi_lang', locale === 'vi' ? 'en' : 'vi')
  const path = pages[0].localizedPaths[locale]
  mount(path)
  await screen.findByText(`Content 7 ${locale}`)
  expect(localStorage.getItem('idi_lang')).toBe(locale === 'zh' ? 'zh-CN' : locale)
  cleanup()
  mount(path)
  await screen.findByText(`Content 7 ${locale}`)
})
test('language switch keeps the same arbitrary CMS page; menu and back keep locale', async () => {
  const router = mount(pages[0].localizedPaths.vi)
  await screen.findByText('Content 7 vi')
  fireEvent.click(document.querySelector('button[lang="en"]'))
  await screen.findByText('Content 7 en')
  expect(router.state.location.pathname).toBe(pages[0].localizedPaths.en)
  fireEvent.click(document.querySelector('button[lang="zh-CN"]'))
  await screen.findByText('Content 7 zh')
  expect(router.state.location.pathname).toBe(pages[0].localizedPaths.zh)
  await waitFor(() => expect(screen.getByText('Story').getAttribute('href')).toBe(pages[1].localizedPaths.zh))
  fireEvent.click(screen.getByText('Story'))
  await screen.findByRole('heading', { name: 'ABOUT_HISTORY zh' })
  await router.navigate(-1)
  await screen.findByText('Content 7 zh')
})
test.each([['/about/story', 1], ['/about/values', 3], ['/about', 2]])('legacy %s retains the correct record and redirects', async (path, index) => {
  const router = mount(path)
  await screen.findByRole('heading', { name: `${pages[index].code} vi` })
  await waitFor(() => expect(router.state.location.pathname).toBe(pages[index].localizedPaths.vi))
})
test('missing target locale keeps URL and language and reports status', async () => {
  const router = mount(pages[3].localizedPaths.en)
  await screen.findByRole('heading', { name: 'ABOUT_VALUES en' })
  fireEvent.click(document.querySelector('button[lang="zh-CN"]'))
  expect(screen.getByRole('status').textContent).toContain('no URL or translation')
  expect(router.state.location.pathname).toBe(pages[3].localizedPaths.en)
  expect(localStorage.getItem('idi_lang')).toBe('en')
})
