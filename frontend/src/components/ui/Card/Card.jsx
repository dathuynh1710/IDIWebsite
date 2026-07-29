/**
 * Card — Base card container with hover shadow
 * TODO: Implement component
 */
export default function Card({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
