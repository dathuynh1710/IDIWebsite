/**
 * HeroStats — Section: HeroStats
 * TODO: Implement component
 */
export default function HeroStats({ className, children, ...props }) {
  return (
    <div className={className} {...props}>
      {children}
    </div>
  )
}
