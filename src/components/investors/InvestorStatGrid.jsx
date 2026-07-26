export default function InvestorStatGrid({ items }) {
  return (
    <section className="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      {items.map((item) => (
        <div key={item.label} className="rounded-xl border border-light-mist bg-white p-5">
          <span className="text-[10px] font-extrabold uppercase tracking-[0.12em] text-seafoam">{item.label}</span>
          <strong className="mt-2 block text-2xl font-extrabold tracking-[-0.03em] text-ocean-deep">{item.value}</strong>
          {item.note && <p className="mt-1 text-xs leading-5">{item.note}</p>}
        </div>
      ))}
    </section>
  )
}
