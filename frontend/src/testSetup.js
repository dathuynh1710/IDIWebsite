import { JSDOM } from 'jsdom'
// Keep Node's Request and AbortSignal in the same realm for React Router.
const dom = new JSDOM('<!doctype html><html><body></body></html>', { url: 'http://localhost/' })
for (const key of ['window', 'document', 'navigator', 'localStorage', 'HTMLElement', 'Element', 'Node', 'MutationObserver', 'getComputedStyle']) {
  Object.defineProperty(globalThis, key, { configurable: true, value: dom.window[key] })
}
