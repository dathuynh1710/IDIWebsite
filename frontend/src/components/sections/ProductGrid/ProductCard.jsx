/**
 * ProductCard — Section: ProductCard
 * TODO: Implement component
 */
export default function ProductCard({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
