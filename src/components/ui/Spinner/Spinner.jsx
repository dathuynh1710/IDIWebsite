/**
 * Spinner — Loading spinner for async states
 * TODO: Implement component
 */
export default function Spinner({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
