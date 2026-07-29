/**
 * DownloadButton — Common: DownloadButton
 * TODO: Implement component
 */
export default function DownloadButton({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
