/**
 * FormField — Common: FormField
 * TODO: Implement component
 */
export default function FormField({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
