/**
 * MobileMenuDrawer — Navigation: MobileMenuDrawer
 * TODO: Implement component
 */
export default function MobileMenuDrawer({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
