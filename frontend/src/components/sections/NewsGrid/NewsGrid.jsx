/**
 * NewsGrid — Section: NewsGrid
 * TODO: Implement component
 */
export default function NewsGrid({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
