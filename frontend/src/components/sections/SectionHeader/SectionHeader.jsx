/**
 * SectionHeader — Section: SectionHeader
 * TODO: Implement component
 */
export default function SectionHeader({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
