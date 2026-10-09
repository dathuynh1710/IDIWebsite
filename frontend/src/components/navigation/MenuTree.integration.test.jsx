import { afterEach, beforeEach, expect, test, vi } from 'vitest'
import { cleanup, fireEvent, render, screen, waitFor } from '@testing-library/react'
import { MemoryRouter } from 'react-router'
import { LanguageProvider } from '@context/LanguageContext'
import { useLanguage } from '@hooks/useLanguage'
import MenuProvider, { useMenu } from '@context/MenuContext'
import { menuService } from '@services/menu.service'
import MenuTree from './MenuTree'

vi.mock('@services/menu.service', () => ({ menuService: { getMenu: vi.fn() } }))
vi.mock('@context/AboutRoutingContext', () => ({ useAboutRouting: () => ({ resolveLink: href => href }) }))
vi.mock('@hooks/useProductCategories', () => ({ useProductCategories: () => [] }))

const tree = [{ id: 1, label: 'Parent', href: '/about', children: [
  { id: 2, label: 'Child', href: '/child', children: [{ id: 3, label: 'Leaf', href: '/leaf' }] },
  { id: 4, label: 'External', href: 'https://example.com' },
  { id: 5, label: 'Unavailable', href: null },
] }]

function Shell({ mode, location = 'main' }) {
  const menu = useMenu(location)
  const { setLanguage } = useLanguage()
  return <><div role="status">{menu.status}</div><MenuTree items={menu.items} mode={mode} />
    <button onClick={() => setLanguage('zh-CN')}>Chinese</button>
    <button onClick={menu.retry}>Retry</button></>
}
function mount(mode = 'desktop', location) {
  return render(<MemoryRouter><LanguageProvider><MenuProvider><Shell mode={mode} location={location} /></MenuProvider></LanguageProvider></MemoryRouter>)
}
beforeEach(() => {
  localStorage.clear()
  vi.clearAllMocks()
  menuService.getMenu.mockResolvedValue(tree)
})
afterEach(cleanup)

test.each(['desktop', 'mobile', 'footer'])('%s renders arbitrary depth and toggles accessibly with Escape focus return', async mode => {
  mount(mode)
  await screen.findByRole('link', { name: 'Parent' })
  expect(screen.queryByRole('link', { name: 'Leaf' })).toBeNull()
  const rootToggle = screen.getByRole('button', { name: /Parent:/ })
  expect(rootToggle.getAttribute('aria-expanded')).toBe('false')
  fireEvent.click(rootToggle)
  const childToggle = screen.getByRole('button', { name: /Child:/ })
  fireEvent.click(childToggle)
  expect(screen.getByRole('link', { name: 'Leaf' }).getAttribute('href')).toBe('/leaf')
  expect(screen.getByRole('link', { name: 'External' }).getAttribute('href')).toBe('https://example.com')
  expect(screen.queryByRole('link', { name: 'Unavailable' })).toBeNull()
  fireEvent.keyDown(screen.getByRole('link', { name: 'Leaf' }), { key: 'Escape' })
  expect(screen.queryByRole('link', { name: 'Leaf' })).toBeNull()
  expect(document.activeElement).toBe(childToggle)
  expect(rootToggle.getAttribute('aria-expanded')).toBe('true')
})

test('loading and initial API error leave a usable home link; retry loads menu', async () => {
  let reject
  menuService.getMenu.mockImplementation(() => new Promise((_, fail) => { reject = fail }))
  mount('desktop', 'footer')
  expect(screen.getByRole('status').textContent).toBe('loading')
  expect(screen.getByRole('link').getAttribute('href')).toBe('/')
  reject(new Error('offline'))
  await waitFor(() => expect(screen.getByRole('status').textContent).toBe('error'))
  menuService.getMenu.mockResolvedValue(tree)
  fireEvent.click(screen.getByText('Retry'))
  await screen.findByRole('link', { name: 'Parent' })
})

test('empty CMS menus remain empty, including after a later 404', async () => {
  menuService.getMenu.mockResolvedValue([])
  mount()
  await waitFor(() => expect(screen.getByRole('status').textContent).toBe('ready'))
  expect(screen.queryAllByRole('link')).toHaveLength(0)
  menuService.getMenu.mockRejectedValue({ response: { status: 404 } })
  fireEvent.click(screen.getByText('Retry'))
  await waitFor(() => expect(screen.getByRole('status').textContent).toBe('error'))
  expect(screen.queryAllByRole('link')).toHaveLength(0)
})

test('an uninstalled API uses the compatible legacy menu', async () => {
  menuService.getMenu.mockRejectedValue({ response: { status: 404 } })
  mount()
  await waitFor(() => expect(screen.getByRole('status').textContent).toBe('legacy'))
  expect(screen.getByRole('link', { name: 'Về IDI' }).getAttribute('href')).toBe('/about')
})

test('locale changes request backend zh paths without translating titles', async () => {
  menuService.getMenu.mockImplementation(async (_, locale) => [{ id: 42, label: locale === 'zh' ? '中文页面' : 'Page', href: locale === 'zh' ? '/zh/guanyu/from-cms' : '/vi/gioi-thieu/from-cms' }])
  mount()
  await screen.findByRole('link', { name: 'Page' })
  fireEvent.click(screen.getByText('Chinese'))
  const link = await screen.findByRole('link', { name: '中文页面' })
  expect(link.getAttribute('href')).toBe('/zh/guanyu/from-cms')
  expect(menuService.getMenu).toHaveBeenCalledWith('main', 'zh')
  expect(localStorage.getItem('idi_lang')).toBe('zh-CN')
})

test('unsafe hrefs never become clickable links', () => {
  render(<MemoryRouter><LanguageProvider><MenuTree items={[{ id: 1, label: 'Bad', href: 'javascript:alert(1)' }]} /></LanguageProvider></MemoryRouter>)
  expect(screen.queryByRole('link')).toBeNull()
})
