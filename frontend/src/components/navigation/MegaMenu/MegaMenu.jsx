/**
 * MegaMenu — Navigation: MegaMenu
 * TODO: Implement component
 */
export default function MegaMenu({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
