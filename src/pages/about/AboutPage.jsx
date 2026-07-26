import AboutPageHeader from '@components/about/AboutPageHeader'
import { COMPANY_MESSAGE } from '@data/about'

export default function AboutPage() {
  const message = COMPANY_MESSAGE

  return (
    <>
      <AboutPageHeader
        eyebrow="Về IDI"
        title="Thông điệp của công ty"
        description="Chia sẻ từ Ban điều hành về định hướng phát triển và trách nhiệm của IDI đối với con người, xã hội và môi trường."
      />

      <main className="bg-white py-14 sm:py-18">
        <article className="container max-w-4xl">
          <header className="flex flex-col gap-5 border-b border-light-mist pb-7 sm:flex-row sm:items-center sm:gap-7">
            <img
              src="/images/about/le-van-chung.jpg"
              alt={`Ông ${message.author}`}
              width="370"
              height="370"
              className="h-32 w-32 shrink-0 object-cover sm:h-40 sm:w-40"
            />
            <div>
              <h2 className="text-2xl font-bold text-ocean-deep sm:text-3xl">Ông {message.author}</h2>
              <p className="mt-1 text-sm font-semibold text-seafoam">{message.role}</p>
            </div>
          </header>

          <blockquote className="my-8 border-l-4 border-seafoam pl-5 text-xl font-semibold leading-9 text-ocean-deep sm:pl-7 sm:text-2xl">
            “{message.quote}”
          </blockquote>

          <div className="space-y-6 text-base leading-8 text-slate sm:text-lg sm:leading-9">
            {message.paragraphs.map((paragraph) => (
              <p key={paragraph}>{paragraph}</p>
            ))}
          </div>
        </article>
      </main>
    </>
  )
}
