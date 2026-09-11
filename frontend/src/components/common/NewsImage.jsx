import { useState } from 'react'

const COMPANY_LOGO = '/images/brand/idi-logo.png'

export default function NewsImage({
  article,
  className = 'h-full w-full object-cover',
  loading = 'lazy',
  fetchPriority,
}) {
  const [failedUrl, setFailedUrl] = useState('')
  const imageUrl = article?.imageUrl

  if (imageUrl && imageUrl !== failedUrl) {
    return (
      <img
        src={imageUrl}
        alt={article.imageAlt || article.title}
        className={className}
        loading={loading}
        fetchPriority={fetchPriority}
        onError={() => setFailedUrl(imageUrl)}
      />
    )
  }

  return (
    <div className="absolute inset-0 grid place-items-center bg-light-mist p-[12%]">
      <img
        src={COMPANY_LOGO}
        alt="I.D.I Seafood"
        className="max-h-full w-full max-w-sm object-contain"
        loading={loading}
      />
    </div>
  )
}
