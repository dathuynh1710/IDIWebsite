/**
 * Badge — Certification badge and category tag display
 * TODO: Implement component
 */
export default function Badge({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
