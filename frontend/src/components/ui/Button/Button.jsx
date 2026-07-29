/**
 * Button — Primary UI button with variant support (primary/secondary/ghost/gold)
 * TODO: Implement component
 */
export default function Button({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
