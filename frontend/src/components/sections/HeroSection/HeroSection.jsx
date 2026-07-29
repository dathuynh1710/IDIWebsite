/**
 * HeroSection — Section: HeroSection
 * TODO: Implement component
 */
export default function HeroSection({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
