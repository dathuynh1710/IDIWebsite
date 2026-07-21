import { Link } from 'react-router'
import { cn } from '@utils/cn'

export default function NavbarBrand({ scrolled }) {
  return (
    <Link to="/" className="flex items-center gap-2 flex-shrink-0">
      <img
        src="https://idiseafood.com/vnt_upload/weblink/logo.png"
        alt="IDI Seafood"
        className={cn(
          'h-10 w-auto object-contain transition-all duration-300',
          !scrolled && 'brightness-0 invert', // White logo on dark hero
        )}
        onError={(e) => {
          // Fallback to text if image fails
          e.target.style.display = 'none'
          e.target.nextElementSibling.style.display = 'block'
        }}
      />
      {/* Text fallback */}
      <span
        className={cn(
          'hidden text-xl font-black tracking-tight',
          scrolled ? 'text-ocean-deep' : 'text-white',
        )}
      >
        IDI<span className="text-coral-gold">.</span>
      </span>
    </Link>
  )
}
