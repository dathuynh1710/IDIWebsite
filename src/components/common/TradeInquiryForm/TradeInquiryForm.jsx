/**
 * TradeInquiryForm — Common: TradeInquiryForm
 * TODO: Implement component
 */
export default function TradeInquiryForm({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
