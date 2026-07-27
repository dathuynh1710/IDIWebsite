import PageHead from '@components/common/PageHead'
import { COMPANY_MESSAGE } from '@data/about'

export default function AboutPage() {
  const message = COMPANY_MESSAGE

  return (
    <>
      <PageHead
        title="Thông điệp của công ty | IDI Seafood"
        description="Chia sẻ từ Ban điều hành về định hướng phát triển và trách nhiệm của IDI đối với con người, xã hội và môi trường."
      />

      <main className="bg-white pb-14 pt-32 sm:pb-18 sm:pt-36">
        <article className="container max-w-4xl">
          <h1 className="sr-only">Thông điệp của công ty</h1>
          <header className="flex flex-col gap-5 border-b border-light-mist pb-7 sm:flex-row sm:items-center sm:gap-7">
            <img
              src={`${import.meta.env.BASE_URL}images/about/le-van-chung.jpg`}
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
