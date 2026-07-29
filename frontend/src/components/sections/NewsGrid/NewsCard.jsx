/**
 * NewsCard — Section: NewsCard
 * TODO: Implement component
 */
export default function NewsCard({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
