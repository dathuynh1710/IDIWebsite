import { useEffect, useState } from 'react'
import { useDebounce } from '@hooks/useDebounce'
import { useLanguage } from '@hooks/useLanguage'
import { investorsService } from '@services/investors.service'

const DATE_LOCALES = { vi: 'vi-VN', en: 'en-US', 'zh-CN': 'en-US' }
const LONG_DATE_OPTIONS = { year: 'numeric', month: 'long', day: 'numeric' }
const EMPTY_RESULT = { items: [], categories: [], years: [], total: 0, page: 1, limit: 20, lastPage: 1, pageConfig: null }
const COPY = {
  vi: { library: 'Tài liệu công bố', unavailableDate: 'Chưa cập nhật', loading: 'Đang tải tài liệu', search: 'Tìm tài liệu', searchPlaceholder: 'Tìm tài liệu, báo cáo...', year: 'Năm', allYears: 'Tất cả năm', category: 'Danh mục', allCategories: 'Tất cả danh mục', loadError: 'Không thể tải tài liệu', connection: 'Vui lòng kiểm tra kết nối và thử lại.', retry: 'Thử tải lại', quarter: 'Quý', view: 'Xem', viewLabel: 'Xem tài liệu', download: 'Tải tài liệu', noFile: 'Chưa có tệp', fallbackFile: 'Đang dùng tệp tiếng Việt', empty: 'Không tìm thấy tài liệu phù hợp.', noDocuments: 'Chưa có tài liệu được công bố.', clear: 'Xóa bộ lọc', results: '{{count}} tài liệu', dateColumn: 'Ngày', documentColumn: 'Nội dung tài liệu', metaColumn: 'Kỳ / Loại', actionColumn: 'Thao tác', pagination: 'Phân trang tài liệu', previous: 'Trước', next: 'Sau', page: 'Trang' },
  en: { library: 'Published documents', unavailableDate: 'Not updated', loading: 'Loading documents', search: 'Search documents', searchPlaceholder: 'Search documents, reports...', year: 'Year', allYears: 'All years', category: 'Category', allCategories: 'All categories', loadError: 'Unable to load documents', connection: 'Please check your connection and try again.', retry: 'Try again', quarter: 'Quarter', view: 'View', viewLabel: 'View document', download: 'Download document', noFile: 'No file', fallbackFile: 'Vietnamese file', empty: 'No matching documents found.', noDocuments: 'No documents have been published yet.', clear: 'Clear filters', results: '{{count}} documents', dateColumn: 'Date', documentColumn: 'Document', metaColumn: 'Period / Type', actionColumn: 'Actions', pagination: 'Document pagination', previous: 'Previous', next: 'Next', page: 'Page' },
  'zh-CN': { library: '披露文件', unavailableDate: '尚未更新', loading: '正在加载文件', search: '搜索文件', searchPlaceholder: '搜索文件、报告...', year: '年份', allYears: '所有年份', category: '类别', allCategories: '所有类别', loadError: '无法加载文件', connection: '请检查网络连接并重试。', retry: '重试', quarter: '季度', view: '查看', viewLabel: '查看文件', download: '下载文件', noFile: '暂无文件', fallbackFile: '使用越南语文件', empty: '未找到匹配的文件。', noDocuments: '尚未发布文件。', clear: '清除筛选', results: '{{count}} 份文件', dateColumn: '日期', documentColumn: '文件内容', metaColumn: '期间 / 类型', actionColumn: '操作', pagination: '文件分页', previous: '上一页', next: '下一页', page: '第' },
}

function SearchIcon() {
  return <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" className="h-5 w-5" stroke="currentColor" strokeWidth="1.8"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4 4" strokeLinecap="round" /></svg>
}

function DownloadIcon() {
  return <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" className="h-4 w-4" stroke="currentColor" strokeWidth="1.8"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 20h14" strokeLinecap="round" strokeLinejoin="round" /></svg>
}

function formatDate(value, language, unavailableDate) {
  if (!value) return unavailableDate
  const date = new Date(`${value}T00:00:00`)
  if (Number.isNaN(date.getTime())) return value
  const options = language === 'vi' ? undefined : LONG_DATE_OPTIONS
  return date.toLocaleDateString(DATE_LOCALES[language] ?? 'vi-VN', options)
}

function formatSize(bytes) {
  if (!bytes) return ''
  if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function DocumentToolbar({ labels, query, setQuery, year, setYear, category, setCategory, result, status, lockedCategory, hasFilters, resetFilters, t }) {
  const controlClass = 'min-h-12 w-full rounded-lg border border-mist-mid bg-white px-3.5 text-sm font-semibold text-slate outline-none transition focus:border-seafoam focus:ring-2 focus:ring-seafoam/10'

  return (
    <div className="border-b border-light-mist bg-[#f4f7f9] p-3 sm:p-4">
      <div className={`grid gap-2.5 ${lockedCategory ? 'md:grid-cols-[minmax(16rem,1fr)_10rem]' : 'md:grid-cols-[minmax(18rem,1fr)_9.5rem_13rem]'}`}>
        <label className="relative block">
          <span className="sr-only">{labels.search}</span>
          <span className="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-storm-grey"><SearchIcon /></span>
          <input id="investor-search" type="search" value={query} onChange={event => setQuery(event.target.value)} placeholder={labels.searchPlaceholder} className={`${controlClass} pl-11 font-normal placeholder:text-storm-grey`} />
        </label>
        <label>
          <span className="sr-only">{labels.year}</span>
          <select id="investor-year" value={year} onChange={event => setYear(event.target.value)} className={controlClass}>
            <option value="">{labels.allYears}</option>
            {result.years.map(item => <option key={item} value={item}>{item}</option>)}
          </select>
        </label>
        {!lockedCategory && (
          <label>
            <span className="sr-only">{labels.category}</span>
            <select id="investor-category" value={category} onChange={event => setCategory(event.target.value)} className={controlClass}>
              <option value="">{labels.allCategories}</option>
              {result.categories.map(item => <option key={item.id} value={item.slug}>{item.name} ({item.count})</option>)}
            </select>
          </label>
        )}
      </div>
      <div className="mt-3 flex min-h-6 items-center justify-between gap-4 px-0.5 text-xs text-storm-grey">
        <span aria-live="polite">{status === 'loading' ? labels.loading : t('', labels.results, { count: result.total })}</span>
        {hasFilters && <button type="button" onClick={resetFilters} className="font-bold text-[#14785b] underline-offset-4 hover:text-ocean-deep hover:underline">{labels.clear}</button>}
      </div>
    </div>
  )
}

function LoadingRows({ label }) {
  return (
    <div className="divide-y divide-light-mist" aria-label={label} aria-busy="true">
      {Array.from({ length: 5 }, (_, index) => (
        <div key={index} className="grid animate-pulse gap-3 px-4 py-5 md:grid-cols-[7.25rem_minmax(0,1fr)_9rem_6.5rem] md:items-center md:px-5">
          <div className="h-4 w-20 rounded bg-light-mist" />
          <div className="space-y-2"><div className="h-4 w-24 rounded bg-light-mist" /><div className="h-5 max-w-md rounded bg-light-mist" /></div>
          <div className="h-4 w-24 rounded bg-light-mist" />
          <div className="h-9 rounded-lg bg-light-mist" />
        </div>
      ))}
    </div>
  )
}

function DocumentMeta({ document, language, labels }) {
  const items = []
  if (document.quarter) items.push(`${labels.quarter} ${document.quarter}`)
  if (document.file?.extension) items.push(document.file.extension)
  if (document.file?.size) items.push(formatSize(document.file.size))
  const requestedFileLocale = language === 'zh-CN' ? 'zh' : language
  if (document.file?.locale && document.file.locale !== requestedFileLocale) items.push(labels.fallbackFile)

  return (
    <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-storm-grey md:block md:space-y-1">
      {items.length > 0 ? items.map((item, index) => (
        <span key={item} className="inline-flex items-center md:flex">
          {index > 0 && <span aria-hidden="true" className="mr-2 text-mist-mid md:hidden">·</span>}
          {item}
        </span>
      )) : <span>—</span>}
      {document.documentNumber && <span className="block w-full truncate font-normal md:pt-0.5" title={document.documentNumber}>{document.documentNumber}</span>}
    </div>
  )
}

function DocumentRow({ document, language, labels }) {
  const formattedDate = formatDate(document.publishedOn, language, labels.unavailableDate)
  const titleClass = 'text-[15px] font-semibold leading-[1.5] tracking-[-0.01em] text-ocean-deep transition-colors sm:text-base'

  return (
    <article className="group m-3 grid gap-3 rounded-lg border border-light-mist px-4 py-4 transition-colors hover:bg-[#f8fbfa] md:m-0 md:grid-cols-[7.25rem_minmax(0,1fr)_9rem_6.5rem] md:items-center md:gap-4 md:rounded-none md:border-0 md:px-5 md:py-4">
      <time className="hidden text-[13px] font-semibold tabular-nums text-slate md:block" dateTime={document.publishedOn ?? undefined}>{formattedDate}</time>
      <div className="min-w-0">
        <div className="mb-1.5 flex flex-wrap items-center gap-2">
          <time className="text-xs font-semibold tabular-nums text-slate md:hidden" dateTime={document.publishedOn ?? undefined}>{formattedDate}</time>
          {document.category?.name && <span className="rounded bg-seafoam-pale px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.07em] text-[#14785b]">{document.category.name}</span>}
        </div>
        <h3 className={titleClass}>
          {document.file ? <a href={document.file.url} target="_blank" rel="noreferrer" className="hover:text-seafoam">{document.title}</a> : document.title}
        </h3>
        <div className="mt-2 md:hidden"><DocumentMeta document={document} language={language} labels={labels} /></div>
      </div>
      <div className="hidden md:block"><DocumentMeta document={document} language={language} labels={labels} /></div>
      <div className="flex items-center gap-1.5 md:justify-end">
        {document.file ? (
          <>
            <a href={document.file.url} target="_blank" rel="noreferrer" aria-label={`${labels.viewLabel}: ${document.title}`} className="inline-flex min-h-9 items-center rounded-md px-2.5 text-xs font-bold text-[#14785b] transition-colors hover:bg-seafoam-pale hover:text-ocean-deep">{labels.view}</a>
            <a href={document.file.url} download aria-label={`${labels.download}: ${document.title}`} title={labels.download} className="inline-flex h-9 w-9 items-center justify-center rounded-md border border-light-mist bg-white text-ocean-deep transition-colors hover:border-seafoam hover:bg-seafoam-pale hover:text-[#14785b]"><DownloadIcon /></a>
          </>
        ) : <span className="text-xs font-semibold text-storm-grey">{labels.noFile}</span>}
      </div>
    </article>
  )
}

function DocumentList({ result, status, language, labels, hasFilters, resetFilters, retry }) {
  if (status === 'loading') return <LoadingRows label={labels.loading} />
  if (status === 'error') {
    return <div className="px-6 py-14 text-center" role="alert"><h3 className="text-base font-bold text-ocean-deep">{labels.loadError}</h3><p className="mt-2 text-sm text-storm-grey">{labels.connection}</p><button type="button" onClick={retry} className="mt-4 text-sm font-bold text-[#14785b] hover:text-ocean-deep">{labels.retry}</button></div>
  }
  if (result.items.length === 0) {
    return <div className="px-6 py-14 text-center"><p className="text-sm font-semibold text-slate">{hasFilters ? labels.empty : labels.noDocuments}</p>{hasFilters && <button type="button" onClick={resetFilters} className="mt-3 text-sm font-bold text-[#14785b] hover:text-ocean-deep">{labels.clear}</button>}</div>
  }
  return <div className={`transition-opacity md:divide-y md:divide-light-mist ${status === 'refreshing' ? 'opacity-50' : ''}`} aria-busy={status === 'refreshing'}>{result.items.map(document => <DocumentRow key={document.id} document={document} language={language} labels={labels} />)}</div>
}

export default function InvestorDocumentLibrary({ category: lockedCategory = '', title, onPageConfigChange }) {
  const { language, t } = useLanguage()
  const labels = COPY[language] ?? COPY.vi
  const resolvedTitle = title ?? labels.library
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
    investorsService.getDocuments({ locale: language, page, category: lockedCategory || category, year, search: debouncedQuery }, { signal: controller.signal })
      .then((data) => { setResult(data); setStatus('success'); onPageConfigChange?.(data.pageConfig) })
      .catch((error) => { if (error?.code !== 'ERR_CANCELED') setStatus('error') })
    return () => controller.abort()
  }, [category, debouncedQuery, language, lockedCategory, onPageConfigChange, page, requestKey, year])

  const updateQuery = (value) => { setQuery(value); setPage(1) }
  const updateYear = (value) => { setYear(value); setPage(1) }
  const updateCategory = (value) => { setCategory(value); setYear(''); setPage(1) }
  const resetFilters = () => { setQuery(''); setYear(''); if (!lockedCategory) setCategory(''); setPage(1) }
  const hasFilters = Boolean(query || year || (!lockedCategory && category))

  return (
    <section aria-labelledby="investor-library-title">
      <div className="mb-3 flex items-baseline justify-between gap-4">
        <h2 id="investor-library-title" className="text-lg font-extrabold tracking-[-0.02em] text-ocean-deep sm:text-xl">{resolvedTitle}</h2>
      </div>
      <div className="overflow-hidden rounded-xl border border-light-mist bg-white">
        <DocumentToolbar labels={labels} query={query} setQuery={updateQuery} year={year} setYear={updateYear} category={category} setCategory={updateCategory} result={result} status={status} lockedCategory={lockedCategory} hasFilters={hasFilters} resetFilters={resetFilters} t={t} />
        <div className="hidden grid-cols-[7.25rem_minmax(0,1fr)_9rem_6.5rem] gap-4 border-b border-light-mist bg-white px-5 py-2.5 text-[10px] font-bold uppercase tracking-[0.1em] text-storm-grey md:grid">
          <span>{labels.dateColumn}</span><span>{labels.documentColumn}</span><span>{labels.metaColumn}</span><span className="text-right">{labels.actionColumn}</span>
        </div>
        <DocumentList result={result} status={status} language={language} labels={labels} hasFilters={hasFilters} resetFilters={resetFilters} retry={() => setRequestKey(key => key + 1)} />
        {status !== 'error' && result.lastPage > 1 && (
          <nav className="flex items-center justify-between gap-3 border-t border-light-mist bg-[#f8fafb] px-4 py-3 text-xs text-storm-grey sm:justify-end" aria-label={labels.pagination}>
            <button type="button" onClick={() => setPage(value => Math.max(1, value - 1))} disabled={page === 1 || status === 'refreshing'} className="rounded-md border border-light-mist bg-white px-3 py-2 font-bold text-ocean-deep transition-colors hover:border-mist-mid disabled:opacity-40">{labels.previous}</button>
            <span>{labels.page} {result.page}/{result.lastPage}</span>
            <button type="button" onClick={() => setPage(value => Math.min(result.lastPage, value + 1))} disabled={page === result.lastPage || status === 'refreshing'} className="rounded-md border border-light-mist bg-white px-3 py-2 font-bold text-ocean-deep transition-colors hover:border-mist-mid disabled:opacity-40">{labels.next}</button>
          </nav>
        )}
      </div>
    </section>
  )
}
