import { useEffect, useState } from 'react'
import { useDebounce } from '@hooks/useDebounce'
import { useLanguage } from '@hooks/useLanguage'
import { investorsService } from '@services/investors.service'

const DATE_LOCALES = { vi: 'vi-VN', en: 'en-US', zh: 'zh-CN' }
const EMPTY_RESULT = { items: [], categories: [], years: [], total: 0, page: 1, limit: 20, lastPage: 1, pageConfig: null }

function formatDate(value, language) {
  if (!value) return 'Chưa cập nhật'
  const date = new Date(`${value}T00:00:00`)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString(DATE_LOCALES[language] ?? 'vi-VN')
}

function formatSize(bytes) {
  if (!bytes) return ''
  if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function LoadingRows() {
  return (
    <div className="divide-y divide-light-mist" aria-label="Đang tải tài liệu">
      {Array.from({ length: 5 }, (_, index) => (
        <div key={index} className="grid animate-pulse gap-4 p-5 sm:grid-cols-[6.5rem_minmax(0,1fr)_8rem] sm:items-center sm:px-6">
          <div className="h-4 rounded bg-light-mist" />
          <div className="space-y-2"><div className="h-4 w-24 rounded bg-light-mist" /><div className="h-5 rounded bg-light-mist" /></div>
          <div className="h-10 rounded-lg bg-light-mist" />
        </div>
      ))}
    </div>
  )
}

export default function InvestorDocumentLibrary({
  category: lockedCategory = '',
  title = 'Thư viện tài liệu',
  description = 'Tra cứu các công bố và tài liệu dành cho cổ đông.',
  onPageConfigChange,
}) {
  const { language } = useLanguage()
  const [query, setQuery] = useState('')
  const debouncedQuery = useDebounce(query.trim(), 350)
  const [year, setYear] = useState('')
  const [category, setCategory] = useState(lockedCategory)
  const [page, setPage] = useState(1)
  const [requestKey, setRequestKey] = useState(0)
  const [status, setStatus] = useState('loading')
  const [result, setResult] = useState(EMPTY_RESULT)

  useEffect(() => {
    setCategory(lockedCategory)
    setYear('')
    setQuery('')
    setPage(1)
  }, [language, lockedCategory])

  useEffect(() => {
    const controller = new AbortController()
    setStatus(previous => previous === 'success' ? 'refreshing' : 'loading')

    investorsService.getDocuments({
      locale: language,
      page,
      category: lockedCategory || category,
      year,
      search: debouncedQuery,
    }, { signal: controller.signal })
      .then((data) => {
        setResult(data)
        setStatus('success')
        onPageConfigChange?.(data.pageConfig)
      })
      .catch((error) => {
        if (error?.code !== 'ERR_CANCELED') setStatus('error')
      })

    return () => controller.abort()
  }, [category, debouncedQuery, language, lockedCategory, onPageConfigChange, page, requestKey, year])

  const resetFilters = () => {
    setQuery('')
    setYear('')
    if (!lockedCategory) setCategory('')
    setPage(1)
  }

  const hasFilters = Boolean(query || year || (!lockedCategory && category))

  return (
    <section className="overflow-hidden rounded-2xl border border-light-mist bg-white shadow-[0_20px_55px_-45px_rgba(11,37,69,0.65)]">
      <div className="border-b border-light-mist p-5 sm:p-6">
        <div className="flex flex-col justify-between gap-5 xl:flex-row xl:items-end">
          <div className="max-w-xl">
            <h2 className="text-xl font-extrabold tracking-[-0.02em] text-ocean-deep">{title}</h2>
            <p className="mt-1 text-sm leading-6">{description}</p>
          </div>
          <div className={`grid gap-2 ${lockedCategory ? 'sm:grid-cols-[minmax(14rem,1fr)_9rem]' : 'sm:grid-cols-[minmax(14rem,1fr)_9rem_12rem]'}`}>
            <label className="sr-only" htmlFor="investor-search">Tìm tài liệu</label>
            <input
              id="investor-search"
              type="search"
              value={query}
              onChange={(event) => { setQuery(event.target.value); setPage(1) }}
              placeholder="Tìm tên hoặc số hiệu..."
              className="min-h-11 rounded-lg border border-light-mist bg-arctic-white px-3 text-sm text-slate outline-none transition focus:border-seafoam focus:bg-white"
            />
            <label className="sr-only" htmlFor="investor-year">Chọn năm</label>
            <select
              id="investor-year"
              value={year}
              onChange={(event) => { setYear(event.target.value); setPage(1) }}
              className="min-h-11 rounded-lg border border-light-mist bg-arctic-white px-3 text-sm font-semibold text-slate outline-none transition focus:border-seafoam"
            >
              <option value="">Tất cả năm</option>
              {result.years.map(item => <option key={item} value={item}>{item}</option>)}
            </select>
            {!lockedCategory && (
              <>
                <label className="sr-only" htmlFor="investor-category">Chọn danh mục</label>
                <select
                  id="investor-category"
                  value={category}
                  onChange={(event) => { setCategory(event.target.value); setYear(''); setPage(1) }}
                  className="min-h-11 rounded-lg border border-light-mist bg-arctic-white px-3 text-sm font-semibold text-slate outline-none transition focus:border-seafoam"
                >
                  <option value="">Tất cả danh mục</option>
                  {result.categories.map(item => <option key={item.id} value={item.slug}>{item.name} ({item.count})</option>)}
                </select>
              </>
            )}
          </div>
        </div>
      </div>

      {status === 'loading' ? <LoadingRows /> : status === 'error' ? (
        <div className="px-6 py-14 text-center" role="alert">
          <h3 className="font-bold text-ocean-deep">Không thể tải thư viện tài liệu</h3>
          <p className="mt-2 text-sm text-storm-grey">Vui lòng kiểm tra kết nối và thử lại.</p>
          <button type="button" onClick={() => setRequestKey(key => key + 1)} className="mt-4 text-sm font-bold text-seafoam hover:text-ocean-deep">Thử tải lại</button>
        </div>
      ) : result.items.length > 0 ? (
        <div className={`divide-y divide-light-mist transition-opacity ${status === 'refreshing' ? 'opacity-55' : ''}`} aria-busy={status === 'refreshing'}>
          {result.items.map(document => (
            <article key={document.id} className="group grid gap-4 p-5 transition-colors hover:bg-arctic-white sm:grid-cols-[6.5rem_minmax(0,1fr)_auto] sm:items-center sm:px-6">
              <time className="text-sm font-bold text-ocean-deep" dateTime={document.publishedOn ?? undefined}>{formatDate(document.publishedOn, language)}</time>
              <div className="min-w-0">
                <div className="mb-1.5 flex flex-wrap items-center gap-2">
                  {document.category?.name && <span className="rounded-full bg-seafoam-pale px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.08em] text-seafoam">{document.category.name}</span>}
                  {document.quarter && <span className="text-xs font-semibold text-storm-grey">Quý {document.quarter}</span>}
                  {document.documentNumber && <span className="text-xs font-semibold text-storm-grey">{document.documentNumber}</span>}
                  {document.file?.extension && <span className="text-xs font-semibold text-storm-grey">{document.file.extension}{document.file.size ? ` · ${formatSize(document.file.size)}` : ''}</span>}
                </div>
                <h3 className="text-[15px] font-bold leading-6 tracking-[-0.01em] text-slate transition-colors group-hover:text-ocean-deep">{document.title}</h3>
                {document.summary && <p className="mt-1 line-clamp-2 text-xs leading-5 text-storm-grey">{document.summary}</p>}
              </div>
              {document.file ? (
                <a href={document.file.url} target="_blank" rel="noreferrer" aria-label={`Xem hoặc tải tài liệu: ${document.title}`} className="inline-flex min-h-10 items-center justify-center rounded-lg border border-mist-mid px-4 text-xs font-extrabold text-ocean-deep transition hover:border-ocean-deep hover:bg-ocean-deep hover:text-white">Xem / tải ↓</a>
              ) : <span className="text-xs font-semibold text-storm-grey">Chưa có tệp</span>}
            </article>
          ))}
        </div>
      ) : (
        <div className="px-6 py-14 text-center">
          <p className="font-semibold text-slate">Không tìm thấy tài liệu phù hợp.</p>
          {hasFilters && <button type="button" onClick={resetFilters} className="mt-3 text-sm font-bold text-seafoam hover:text-ocean-deep">Xóa bộ lọc</button>}
        </div>
      )}

      <div className="flex flex-col gap-3 border-t border-light-mist bg-arctic-white px-5 py-4 text-xs text-storm-grey sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <span>Hiển thị {result.items.length} trong tổng số {result.total} tài liệu</span>
        {result.lastPage > 1 && (
          <nav className="flex items-center gap-2" aria-label="Phân trang tài liệu">
            <button type="button" onClick={() => setPage(value => Math.max(1, value - 1))} disabled={page === 1 || status === 'refreshing'} className="rounded-md border border-light-mist bg-white px-3 py-2 font-bold text-ocean-deep disabled:opacity-40">Trước</button>
            <span>Trang {result.page}/{result.lastPage}</span>
            <button type="button" onClick={() => setPage(value => Math.min(result.lastPage, value + 1))} disabled={page === result.lastPage || status === 'refreshing'} className="rounded-md border border-light-mist bg-white px-3 py-2 font-bold text-ocean-deep disabled:opacity-40">Sau</button>
          </nav>
        )}
      </div>
    </section>
  )
}
