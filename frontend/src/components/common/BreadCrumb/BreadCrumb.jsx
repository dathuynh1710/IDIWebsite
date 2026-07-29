/**
 * BreadCrumb — Common: BreadCrumb
 * TODO: Implement component
 */
export default function BreadCrumb({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
