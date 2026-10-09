import test from 'node:test'
import assert from 'node:assert/strict'
import { matchRoutes } from 'react-router'
import { ABOUT_PATTERNS, LEGACY_ABOUT, aboutLocale, aboutLink, localizedAboutPath } from './aboutRoutes.js'

const page = { id: 42, code: 'ABOUT_CUSTOM', localizedPaths: { vi: '/vi/gioi-thieu/custom-vi', en: '/en/about/custom-en', zh: '/zh/guanyu/custom-zh' } }
test('localized URLs resolve on direct entry and select the URL locale', () => {
  const routes = [
    ...Object.keys(LEGACY_ABOUT).map(path => ({ path, id: path })),
    ...Object.values(ABOUT_PATTERNS).map(prefix => ({ path: `${prefix}/:slug`, id: prefix })),
  ]
  for (const locale of ['vi', 'en', 'zh']) {
    const path = localizedAboutPath(page, locale)
    assert.equal(aboutLocale(path), locale)
    assert.equal(matchRoutes(routes, path)[0].params.slug, `custom-${locale}`)
  }
  for (const path of Object.keys(LEGACY_ABOUT)) {
    assert.equal(matchRoutes(routes, path)[0].route.id, path)
    assert.equal(aboutLocale(path), undefined)
  }
  assert.equal(matchRoutes(routes, '/vi/about/custom-vi'), null)
})
test('switching uses backend URLs of the same record and rejects missing or unsafe paths', () => {
  assert.equal(localizedAboutPath(page, 'zh'), '/zh/guanyu/custom-zh')
  assert.equal(localizedAboutPath({ localizedPaths: { en: '' } }, 'en'), null)
  assert.equal(localizedAboutPath({ localizedPaths: { en: '//external.test' } }, 'en'), null)
  assert.equal(localizedAboutPath(null, 'en'), null)
})
test('legacy links use stable code before template; unrelated links and missing translations remain safe', () => {
  const pages = [{ ...page, template: 'about' }, { ...page, code: 'ABOUT_MESSAGE', localizedPaths: { en: '/en/about/message' } }]
  assert.equal(aboutLink('/about', pages, 'en'), '/en/about/message')
  assert.equal(aboutLink('/about', pages, 'zh'), '/about')
  assert.equal(aboutLink('/about', [pages[0]], 'en'), '/en/about/custom-en')
  assert.equal(aboutLink('/news', pages, 'en'), '/news')
})
