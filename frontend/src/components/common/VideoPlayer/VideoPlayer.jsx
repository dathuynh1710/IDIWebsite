/**
 * VideoPlayer — Common: VideoPlayer
 * TODO: Implement component
 */
export default function VideoPlayer({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
