import { useMemo } from 'react'

function parseSections(html) {
  if (!html || typeof DOMParser === 'undefined') return []

  const document = new DOMParser().parseFromString(html, 'text/html')
  const sections = []
  let section = null
  let item = null

  Array.from(document.body.children).forEach((element) => {
    const tag = element.tagName.toLowerCase()

    if (tag === 'h2') {
      section = { title: element.textContent.trim(), items: [] }
      sections.push(section)
      item = null
      return
    }

    if (tag === 'h3') {
      if (!section) {
        section = { title: '', items: [] }
        sections.push(section)
      }
      item = { title: element.textContent.trim(), description: '', image: null }
      section.items.push(item)
      return
    }

    if (!item) return

    const image = element.querySelector('img')
    if (image) {
      item.image = {
        src: image.getAttribute('src') || '',
        alt: image.getAttribute('alt') || item.title,
      }
      return
    }

    const text = element.textContent.trim()
    if (text) item.description = [item.description, text].filter(Boolean).join(' ')
  })

  return sections.filter(sectionItem => sectionItem.title || sectionItem.items.length)
}

function ValueCard({ item, index }) {
  return (
    <article className={`core-value-card core-value-card--${(index % 4) + 1}`}>
      {item.image && (
        <figure className="core-value-card__media">
          <img src={item.image.src} alt={item.image.alt} loading="lazy" />
          <span aria-hidden="true">{String(index + 1).padStart(2, '0')}</span>
        </figure>
      )}
      <div className="core-value-card__body">
        <h3>{item.title}</h3>
        {item.description && <p>{item.description}</p>}
      </div>
    </article>
  )
}

function PillarSection({ section, index }) {
  return (
    <section className={`core-values-pillar core-values-pillar--${index + 1}`}>
      <header className="core-values-pillar__header">
        <h2>{section.title}</h2>
      </header>
      <div className="core-values-pillar__items">
        {section.items.map((item, itemIndex) => (
          <article className="core-values-pillar__item" key={`${item.title}-${itemIndex}`}>
            <span className="core-values-pillar__marker" aria-hidden="true">
              {String(itemIndex + 1).padStart(2, '0')}
            </span>
            <div>
              <h3>{item.title}</h3>
              {item.description && <p>{item.description}</p>}
            </div>
          </article>
        ))}
      </div>
    </section>
  )
}

export default function CoreValuesContent({ html }) {
  const sections = useMemo(() => parseSections(html), [html])

  if (!sections.length) {
    return <div className="cms-about-rich" dangerouslySetInnerHTML={{ __html: html }} />
  }

  const [values, ...pillars] = sections

  return (
    <div className="core-values-layout">
      <section className="core-values-showcase">
        <header className="core-values-section-heading">
          <h2>{values.title}</h2>
        </header>
        <div className="core-values-grid">
          {values.items.map((item, index) => (
            <ValueCard item={item} index={index} key={`${item.title}-${index}`} />
          ))}
        </div>
      </section>

      {pillars.length > 0 && (
        <div className="core-values-pillars">
          {pillars.map((section, index) => (
            <PillarSection section={section} index={index} key={`${section.title}-${index}`} />
          ))}
        </div>
      )}
    </div>
  )
}
