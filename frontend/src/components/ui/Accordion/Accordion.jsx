/**
 * Accordion — Collapsible content panels for FAQ and details
 * TODO: Implement component
 */
export default function Accordion({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
