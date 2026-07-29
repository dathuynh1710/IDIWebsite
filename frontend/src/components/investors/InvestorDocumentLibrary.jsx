import { useMemo, useState } from 'react'

const normalize = (value) => value
  .normalize('NFD')
  .replace(/[\u0300-\u036f]/g, '')
  .toLowerCase()

export default function InvestorDocumentLibrary({
  documents,
  title = 'Thư viện tài liệu',
  description = 'Tra cứu các công bố và tài liệu dành cho cổ đông.',
}) {
  const [query, setQuery] = useState('')
  const [year, setYear] = useState('Tất cả')
  const [type, setType] = useState('Tất cả')

  const years = useMemo(
    () => ['Tất cả', ...new Set(documents.map((document) => document.year))],
    [documents],
  )
  const types = useMemo(
    () => ['Tất cả', ...new Set(documents.map((document) => document.type))],
    [documents],
  )

  const visibleDocuments = useMemo(() => {
    const normalizedQuery = normalize(query.trim())
    return documents.filter((document) => {
      const matchesQuery = !normalizedQuery || normalize(document.title).includes(normalizedQuery)
      const matchesYear = year === 'Tất cả' || document.year === year
      const matchesType = type === 'Tất cả' || document.type === type
      return matchesQuery && matchesYear && matchesType
    })
  }, [documents, query, type, year])

  const resetFilters = () => {
    setQuery('')
    setYear('Tất cả')
    setType('Tất cả')
  }

  return (
    <section className="rounded-2xl border border-light-mist bg-white shadow-[0_20px_55px_-45px_rgba(11,37,69,0.65)]">
      <div className="border-b border-light-mist p-5 sm:p-6">
        <div className="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
          <div>
            <h2 className="text-xl font-extrabold tracking-[-0.02em] text-ocean-deep">{title}</h2>
            <p className="mt-1 text-sm">{description}</p>
          </div>
          <div className="grid gap-2 sm:grid-cols-[minmax(12rem,1fr)_8rem_10rem]">
            <label className="sr-only" htmlFor="investor-search">Tìm tài liệu</label>
            <input
              id="investor-search"
              type="search"
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              placeholder="Tìm theo tên tài liệu..."
              className="min-h-11 rounded-lg border border-light-mist bg-arctic-white px-3 text-sm text-slate outline-none transition focus:border-seafoam focus:bg-white"
            />
            <label className="sr-only" htmlFor="investor-year">Chọn năm</label>
            <select
              id="investor-year"
              value={year}
              onChange={(event) => setYear(event.target.value)}
              className="min-h-11 rounded-lg border border-light-mist bg-arctic-white px-3 text-sm font-semibold text-slate outline-none transition focus:border-seafoam"
            >
              {years.map((item) => <option key={item}>{item}</option>)}
            </select>
            <label className="sr-only" htmlFor="investor-type">Chọn loại tài liệu</label>
            <select
              id="investor-type"
              value={type}
              onChange={(event) => setType(event.target.value)}
              className="min-h-11 rounded-lg border border-light-mist bg-arctic-white px-3 text-sm font-semibold text-slate outline-none transition focus:border-seafoam"
            >
              {types.map((item) => <option key={item}>{item}</option>)}
            </select>
          </div>
        </div>
      </div>

      <div className="divide-y divide-light-mist">
        {visibleDocuments.map((document) => (
          <article
            key={document.id}
            className="group grid gap-4 p-5 transition-colors hover:bg-arctic-white sm:grid-cols-[6.5rem_minmax(0,1fr)_auto] sm:items-center sm:px-6"
          >
            <time className="text-sm font-bold text-ocean-deep" dateTime={document.date.split('/').reverse().join('-')}>
              {document.date}
            </time>
            <div className="min-w-0">
              <div className="mb-1.5 flex flex-wrap items-center gap-2">
                <span className="rounded-full bg-seafoam-pale px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.08em] text-seafoam">
                  {document.type}
                </span>
                {document.period && <span className="text-xs font-semibold text-storm-grey">{document.period}</span>}
                {document.pages && <span className="text-xs font-semibold text-storm-grey">{document.pages}</span>}
              </div>
              <h3 className="text-[15px] font-bold leading-6 tracking-[-0.01em] text-slate transition-colors group-hover:text-ocean-deep">
                {document.title}
              </h3>
            </div>
            <a
              href={document.href}
              target="_blank"
              rel="noreferrer"
              aria-label={`Mở tài liệu: ${document.title}`}
              className="inline-flex min-h-10 items-center justify-center rounded-lg border border-mist-mid px-4 text-xs font-extrabold text-ocean-deep transition hover:border-ocean-deep hover:bg-ocean-deep hover:text-white"
            >
              Xem tài liệu ↗
            </a>
          </article>
        ))}

        {visibleDocuments.length === 0 && (
          <div className="px-6 py-14 text-center">
            <p className="font-semibold text-slate">Không tìm thấy tài liệu phù hợp.</p>
            <button type="button" onClick={resetFilters} className="mt-3 text-sm font-bold text-seafoam hover:text-ocean-deep">
              Xóa bộ lọc
            </button>
          </div>
        )}
      </div>

      <div className="flex items-center justify-between gap-4 border-t border-light-mist bg-arctic-white px-5 py-4 text-xs text-storm-grey sm:px-6">
        <span>Hiển thị {visibleDocuments.length}/{documents.length} tài liệu</span>
        <span>Nguồn công bố: IDI Seafood</span>
      </div>
    </section>
  )
}
