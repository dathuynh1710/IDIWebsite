/**
 * MegaMenuPanel — Navigation: MegaMenuPanel
 * TODO: Implement component
 */
export default function MegaMenuPanel({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
