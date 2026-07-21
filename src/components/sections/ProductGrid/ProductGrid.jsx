/**
 * ProductGrid — Section: ProductGrid
 * TODO: Implement component
 */
export default function ProductGrid({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
