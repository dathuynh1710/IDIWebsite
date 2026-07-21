/**
 * Modal — Accessible dialog modal with backdrop
 * TODO: Implement component
 */
export default function Modal({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
