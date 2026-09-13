import { useMemo } from 'react'
import { publicAsset } from '@utils/publicAsset'

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
      <header className="history-responsibility__heading">
        <h2>{section.title}</h2>
      </header>
      <div className="history-responsibility__body">
        <div className="history-responsibility__copy">
          {section.description.map((paragraph, index) => <p key={index}>{paragraph}</p>)}
        </div>
        <figure className="history-responsibility__visual">
          <img
            src={publicAsset('assets/images/about/history-map.jpg')}
            alt={section.title}
            loading="lazy"
          />
        </figure>
      </div>
    </section>
  )
}

export default function HistoryContent({ html, summary }) {
  const history = useMemo(() => parseHistory(html), [html])

  if (!history || (!history.introduction.length && !history.sections.length)) {
    return <div className="cms-about-rich" dangerouslySetInnerHTML={{ __html: html }} />
  }

  const timeline = history.sections.find(section => section.items.length > 0)
  const responsibility = history.sections.find(section => section !== timeline && section.description.length > 0)
  const introduction = Array.from(new Set([summary, ...history.introduction].filter(Boolean)))

  return (
    <div className="history-layout">
      {introduction.length > 0 && (
        <section className="history-introduction">
          <div className="history-introduction__copy">
            {introduction.map((paragraph, index) => <p key={index}>{paragraph}</p>)}
          </div>
        </section>
      )}
      {timeline && <Timeline section={timeline} />}
      {responsibility && <Responsibility section={responsibility} />}
    </div>
  )
}
