import { Link } from 'react-router'
export default function NavbarBrand({ scrolled }) {
  return (
    <Link
      to="/"
      aria-label="Trang chủ IDI Seafood"
      className="flex items-center flex-shrink-0"
    >
      {scrolled ? (
        <>
          <img
            src="https://idiseafood.com/vnt_upload/weblink/logo.png"
            alt="IDI Seafood"
            className="h-10 w-auto object-contain"
            onError={(event) => {
              event.currentTarget.style.display = 'none'
              event.currentTarget.nextElementSibling.style.display = 'inline-flex'
            }}
          />
          <span className="hidden items-baseline text-xl font-black tracking-tight text-ocean-deep">
            IDI<span className="text-coral-gold">.</span>
          </span>
        </>
      ) : (
        <span className="inline-flex items-baseline text-white leading-none drop-shadow-sm">
          <span className="text-2xl font-black tracking-[-0.04em]">
            IDI<span className="text-coral-gold">.</span>
          </span>
          <span className="ml-2 text-[10px] font-bold uppercase tracking-[0.18em] text-white/80">
            Seafood
          </span>
        </span>
      )}
    </Link>
  )
}
