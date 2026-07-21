/**
 * LazyImage — Common: LazyImage
 * TODO: Implement component
 */
export default function LazyImage({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
