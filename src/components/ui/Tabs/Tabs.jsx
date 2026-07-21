/**
 * Tabs — Tab navigation for product details and content sections
 * TODO: Implement component
 */
export default function Tabs({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
