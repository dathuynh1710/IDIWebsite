/**
 * MobileMenu — Navigation: MobileMenu
 * TODO: Implement component
 */
export default function MobileMenu({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
