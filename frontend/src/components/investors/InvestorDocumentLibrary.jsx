import { useEffect, useState } from 'react'
import { useDebounce } from '@hooks/useDebounce'
import { useLanguage } from '@hooks/useLanguage'
import { investorsService } from '@services/investors.service'

const DATE_LOCALES = { vi: 'vi-VN', en: 'en-US', 'zh-CN': 'zh-CN' }
const EMPTY_RESULT = { items: [], categories: [], years: [], total: 0, page: 1, limit: 20, lastPage: 1, pageConfig: null }
const COPY = {
  vi: { library: 'Thư viện tài liệu', description: 'Tra cứu các công bố và tài liệu dành cho cổ đông.', unavailableDate: 'Chưa cập nhật', loading: 'Đang tải tài liệu', search: 'Tìm tài liệu', searchPlaceholder: 'Tìm tên hoặc số hiệu...', year: 'Chọn năm', allYears: 'Tất cả năm', category: 'Chọn danh mục', allCategories: 'Tất cả danh mục', loadError: 'Không thể tải thư viện tài liệu', connection: 'Vui lòng kiểm tra kết nối và thử lại.', retry: 'Thử tải lại', quarter: 'Quý', fileAction: 'Xem / tải ↓', fileActionLabel: 'Xem hoặc tải tài liệu', noFile: 'Chưa có tệp', empty: 'Không tìm thấy tài liệu phù hợp.', clear: 'Xóa bộ lọc', showing: 'Hiển thị {{shown}} trong tổng số {{total}} tài liệu', pagination: 'Phân trang tài liệu', previous: 'Trước', next: 'Sau', page: 'Trang' },
  en: { library: 'Document library', description: 'Search disclosures and documents for shareholders.', unavailableDate: 'Not updated', loading: 'Loading documents', search: 'Search documents', searchPlaceholder: 'Search by name or reference...', year: 'Select year', allYears: 'All years', category: 'Select category', allCategories: 'All categories', loadError: 'Unable to load document library', connection: 'Please check your connection and try again.', retry: 'Try again', quarter: 'Quarter', fileAction: 'View / download ↓', fileActionLabel: 'View or download document', noFile: 'No file', empty: 'No matching documents found.', clear: 'Clear filters', showing: 'Showing {{shown}} of {{total}} documents', pagination: 'Document pagination', previous: 'Previous', next: 'Next', page: 'Page' },
  'zh-CN': { library: '文件库', description: '查询面向股东的公告和文件。', unavailableDate: '尚未更新', loading: '正在加载文件', search: '搜索文件', searchPlaceholder: '按名称或编号搜索...', year: '选择年份', allYears: '所有年份', category: '选择类别', allCategories: '所有类别', loadError: '无法加载文件库', connection: '请检查网络连接并重试。', retry: '重试', quarter: '季度', fileAction: '查看 / 下载 ↓', fileActionLabel: '查看或下载文件', noFile: '暂无文件', empty: '未找到匹配的文件。', clear: '清除筛选', showing: '显示 {{shown}} / 共 {{total}} 份文件', pagination: '文件分页', previous: '上一页', next: '下一页', page: '第' },
}

function formatDate(value, language, unavailableDate) {
  if (!value) return unavailableDate
  const date = new Date(`${value}T00:00:00`)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString(DATE_LOCALES[language] ?? 'vi-VN')
}

function formatSize(bytes) {
  if (!bytes) return ''
  if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function LoadingRows({ label }) {
  return (
    <div className="divide-y divide-light-mist" aria-label={label}>
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
  title,
  description,
  onPageConfigChange,
}) {
  const { language, t } = useLanguage()
  const labels = COPY[language] ?? COPY.vi
  const resolvedTitle = title ?? labels.library
  const resolvedDescription = description ?? labels.description
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
            <h2 className="text-xl font-extrabold tracking-[-0.02em] text-ocean-deep">{resolvedTitle}</h2>
            <p className="mt-1 text-sm leading-6">{resolvedDescription}</p>
          </div>
          <div className={`grid gap-2 ${lockedCategory ? 'sm:grid-cols-[minmax(14rem,1fr)_9rem]' : 'sm:grid-cols-[minmax(14rem,1fr)_9rem_12rem]'}`}>
            <label className="sr-only" htmlFor="investor-search">{labels.search}</label>
            <input
              id="investor-search"
              type="search"
              value={query}
              onChange={(event) => { setQuery(event.target.value); setPage(1) }}
              placeholder={labels.searchPlaceholder}
              className="min-h-11 rounded-lg border border-light-mist bg-arctic-white px-3 text-sm text-slate outline-none transition focus:border-seafoam focus:bg-white"
            />
            <label className="sr-only" htmlFor="investor-year">{labels.year}</label>
            <select
              id="investor-year"
              value={year}
              onChange={(event) => { setYear(event.target.value); setPage(1) }}
              className="min-h-11 rounded-lg border border-light-mist bg-arctic-white px-3 text-sm font-semibold text-slate outline-none transition focus:border-seafoam"
            >
              <option value="">{labels.allYears}</option>
              {result.years.map(item => <option key={item} value={item}>{item}</option>)}
            </select>
            {!lockedCategory && (
              <>
                <label className="sr-only" htmlFor="investor-category">{labels.category}</label>
                <select
                  id="investor-category"
                  value={category}
                  onChange={(event) => { setCategory(event.target.value); setYear(''); setPage(1) }}
                  className="min-h-11 rounded-lg border border-light-mist bg-arctic-white px-3 text-sm font-semibold text-slate outline-none transition focus:border-seafoam"
                >
                  <option value="">{labels.allCategories}</option>
                  {result.categories.map(item => <option key={item.id} value={item.slug}>{item.name} ({item.count})</option>)}
                </select>
              </>
            )}
          </div>
        </div>
      </div>

      {status === 'loading' ? <LoadingRows label={labels.loading} /> : status === 'error' ? (
        <div className="px-6 py-14 text-center" role="alert">
          <h3 className="font-bold text-ocean-deep">{labels.loadError}</h3>
          <p className="mt-2 text-sm text-storm-grey">{labels.connection}</p>
          <button type="button" onClick={() => setRequestKey(key => key + 1)} className="mt-4 text-sm font-bold text-seafoam hover:text-ocean-deep">{labels.retry}</button>
        </div>
      ) : result.items.length > 0 ? (
        <div className={`divide-y divide-light-mist transition-opacity ${status === 'refreshing' ? 'opacity-55' : ''}`} aria-busy={status === 'refreshing'}>
          {result.items.map(document => (
            <article key={document.id} className="group grid gap-4 p-5 transition-colors hover:bg-arctic-white sm:grid-cols-[6.5rem_minmax(0,1fr)_auto] sm:items-center sm:px-6">
              <time className="text-sm font-bold text-ocean-deep" dateTime={document.publishedOn ?? undefined}>{formatDate(document.publishedOn, language, labels.unavailableDate)}</time>
              <div className="min-w-0">
                <div className="mb-1.5 flex flex-wrap items-center gap-2">
                  {document.category?.name && <span className="rounded-full bg-seafoam-pale px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.08em] text-seafoam">{document.category.name}</span>}
                  {document.quarter && <span className="text-xs font-semibold text-storm-grey">{labels.quarter} {document.quarter}</span>}
                  {document.documentNumber && <span className="text-xs font-semibold text-storm-grey">{document.documentNumber}</span>}
                  {document.file?.extension && <span className="text-xs font-semibold text-storm-grey">{document.file.extension}{document.file.size ? ` · ${formatSize(document.file.size)}` : ''}</span>}
                </div>
                <h3 className="text-[15px] font-bold leading-6 tracking-[-0.01em] text-slate transition-colors group-hover:text-ocean-deep">{document.title}</h3>
                {document.summary && <p className="mt-1 line-clamp-2 text-xs leading-5 text-storm-grey">{document.summary}</p>}
              </div>
              {document.file ? (
                <a href={document.file.url} target="_blank" rel="noreferrer" aria-label={`${labels.fileActionLabel}: ${document.title}`} className="inline-flex min-h-10 items-center justify-center rounded-lg border border-mist-mid px-4 text-xs font-extrabold text-ocean-deep transition hover:border-ocean-deep hover:bg-ocean-deep hover:text-white">{labels.fileAction}</a>
              ) : <span className="text-xs font-semibold text-storm-grey">{labels.noFile}</span>}
            </article>
          ))}
        </div>
      ) : (
        <div className="px-6 py-14 text-center">
          <p className="font-semibold text-slate">{labels.empty}</p>
          {hasFilters && <button type="button" onClick={resetFilters} className="mt-3 text-sm font-bold text-seafoam hover:text-ocean-deep">{labels.clear}</button>}
        </div>
      )}

      <div className="flex flex-col gap-3 border-t border-light-mist bg-arctic-white px-5 py-4 text-xs text-storm-grey sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <span>{t('', labels.showing, { shown: result.items.length, total: result.total })}</span>
        {result.lastPage > 1 && (
          <nav className="flex items-center gap-2" aria-label={labels.pagination}>
            <button type="button" onClick={() => setPage(value => Math.max(1, value - 1))} disabled={page === 1 || status === 'refreshing'} className="rounded-md border border-light-mist bg-white px-3 py-2 font-bold text-ocean-deep disabled:opacity-40">{labels.previous}</button>
            <span>{labels.page} {result.page}/{result.lastPage}</span>
            <button type="button" onClick={() => setPage(value => Math.min(result.lastPage, value + 1))} disabled={page === result.lastPage || status === 'refreshing'} className="rounded-md border border-light-mist bg-white px-3 py-2 font-bold text-ocean-deep disabled:opacity-40">{labels.next}</button>
          </nav>
        )}
      </div>
    </section>
  )
}
