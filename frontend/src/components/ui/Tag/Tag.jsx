/**
 * Tag — Small label tag for categories and filters
 * TODO: Implement component
 */
export default function Tag({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
