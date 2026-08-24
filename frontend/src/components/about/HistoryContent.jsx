import { useMemo } from 'react'

function parseHistory(html) {
  if (!html || typeof DOMParser === 'undefined') return null

  const document = new DOMParser().parseFromString(html, 'text/html')
  const result = { introduction: [], sections: [] }
  let section = null
  let item = null

  Array.from(document.body.children).forEach((element) => {
    const tag = element.tagName.toLowerCase()

    if (tag === 'h2') {
      section = { title: element.textContent.trim(), description: [], items: [] }
      result.sections.push(section)
      item = null
      return
    }

    if (tag === 'h3') {
      if (!section) return
      item = { year: element.textContent.trim(), description: '' }
      section.items.push(item)
      return
    }

    const text = element.textContent.trim()
    if (!text) return

    if (item) {
      item.description = [item.description, text].filter(Boolean).join(' ')
    } else if (section) {
      section.description.push(text)
    } else {
      result.introduction.push(text)
    }
  })

  return result
}

function Timeline({ section }) {
  return (
    <section className="history-timeline">
      <header className="history-section-heading">
        <h2>{section.title}</h2>
      </header>
      <div className="history-timeline__track">
        {section.items.map((item, index) => (
          <article className="history-milestone" key={`${item.year}-${index}`}>
            <div className="history-milestone__dot" aria-hidden="true" />
            <div className="history-milestone__card">
              <span>{item.year}</span>
              <p>{item.description}</p>
            </div>
          </article>
        ))}
      </div>
    </section>
  )
}

function Responsibility({ section }) {
  return (
    <section className="history-responsibility">
      <div className="history-responsibility__copy">
        <span className="history-responsibility__eyebrow" aria-hidden="true">I.D.I</span>
        <h2>{section.title}</h2>
        {section.description.map((paragraph, index) => <p key={index}>{paragraph}</p>)}
      </div>
      <div className="history-responsibility__visual" aria-hidden="true">
        <div className="history-globe">
          <span />
          <span />
          <span />
        </div>
      </div>
    </section>
  )
}

export default function HistoryContent({ html }) {
  const history = useMemo(() => parseHistory(html), [html])

  if (!history || (!history.introduction.length && !history.sections.length)) {
    return <div className="cms-about-rich" dangerouslySetInnerHTML={{ __html: html }} />
  }

  const timeline = history.sections.find(section => section.items.length > 0)
  const responsibility = history.sections.find(section => section !== timeline && section.description.length > 0)

  return (
    <div className="history-layout">
      {history.introduction.length > 0 && (
        <section className="history-introduction">
          <div className="history-introduction__mark" aria-hidden="true">
            <strong>2008</strong>
            <span>∞</span>
          </div>
          <div className="history-introduction__copy">
            {history.introduction.map((paragraph, index) => <p key={index}>{paragraph}</p>)}
          </div>
        </section>
      )}
      {timeline && <Timeline section={timeline} />}
      {responsibility && <Responsibility section={responsibility} />}
    </div>
  )
}
