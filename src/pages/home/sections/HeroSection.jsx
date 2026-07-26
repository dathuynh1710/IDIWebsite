import { Link } from 'react-router'

const STATS = [
  { value: '50+',     label: 'Quốc gia xuất khẩu' },
  { value: '100K MT', label: 'Công suất mỗi năm' },
  { value: '1997',    label: 'Năm thành lập' },
  { value: 'ASC',     label: 'Chứng nhận quốc tế' },
]

export default function HeroSection() {
  return (
    <section className="relative min-h-screen flex flex-col overflow-hidden">

      {/* ── Video Background ──────────────────────────────────── */}
      <div className="absolute inset-0">
        <video
          className="w-full h-full object-cover"
          autoPlay
          muted
          loop
          playsInline
          poster="https://idiseafood.com/vnt_upload/weblink/MAP_vn_1.jpg"
        >
          <source
            src="https://idiseafood.com/vnt_upload/weblink/IDI_FOOD_30s_vn.mp4"
            type="video/mp4"
          />
        </video>
        {/* Cinematic dark gradient */}
        <div className="absolute inset-0 bg-gradient-to-t from-[#0B2545] via-[#0B2545]/55 to-[#0B2545]/10" />
        {/* Subtle horizontal vignette */}
        <div className="absolute inset-0 bg-gradient-to-r from-[#0B2545]/60 via-transparent to-transparent" />
      </div>

      {/* ── Content ───────────────────────────────────────────── */}
      <div className="relative z-10 flex-1 flex items-center">
        <div className="container py-24 sm:py-32">
          <div className="max-w-3xl">

            {/* Eyebrow badge */}
            <div
              className="inline-flex max-w-full items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 text-white/90 text-xs font-semibold tracking-widest uppercase px-3 sm:px-4 py-2 rounded-full mb-6 sm:mb-8"
              style={{ animation: 'fadeInUp 0.6s ease forwards', animationDelay: '0ms' }}
            >
              <span className="w-2 h-2 rounded-full bg-coral-gold animate-pulse" />
              <span className="hidden xs:inline">Tiên phong Trái phiếu Xanh ngành thủy sản khu vực</span>
              <span className="xs:hidden">Tiên phong Trái phiếu Xanh</span>
            </div>

            {/* Main headline */}
            <h1
              className="text-display text-white mb-5 sm:mb-6 text-balance"
              style={{ animation: 'fadeInUp 0.7s ease forwards', animationDelay: '100ms', opacity: 0 }}
            >
              Nhà xuất khẩu{' '}
              <span className="text-coral-gold">cá tra</span>{' '}
              hàng đầu Việt Nam
            </h1>

            {/* Sub-headline */}
            <p
              className="text-lg sm:text-xl text-white/75 mb-8 sm:mb-10 max-w-xl leading-relaxed"
              style={{ animation: 'fadeInUp 0.7s ease forwards', animationDelay: '200ms', opacity: 0 }}
            >
              Cá tra chất lượng cao đạt chuẩn ASC từ Đồng bằng sông Cửu Long,
              vận hành theo chuỗi khép kín, truy xuất nguồn gốc minh bạch và
              hiện diện tại hơn 50 quốc gia từ năm 1997.
            </p>

            {/* CTAs */}
            <div
              className="flex flex-col xs:flex-row flex-wrap gap-3 sm:gap-4"
              style={{ animation: 'fadeInUp 0.7s ease forwards', animationDelay: '300ms', opacity: 0 }}
            >
              <Link to="/products" className="btn btn-gold text-base px-7 py-3.5 w-full xs:w-auto justify-center">
                Khám phá sản phẩm
                <svg className="w-4 h-4" viewBox="0 0 16 16" fill="none">
                  <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </Link>
              <Link to="/contact" className="btn btn-ghost text-base px-7 py-3.5 w-full xs:w-auto justify-center">
                Yêu cầu báo giá
              </Link>
            </div>

          </div>
        </div>
      </div>

      {/* ── Stats Bar ─────────────────────────────────────────── */}
      <div className="relative z-10 bg-white/8 backdrop-blur-md border-t border-white/10">
        <div className="container">
          <div className="grid grid-cols-2 md:grid-cols-4">
            {STATS.map((stat, i) => (
              <div
                key={stat.label}
                className="py-5 px-4 text-center border-r border-white/10 last:border-0 md:border-r first:border-l-0"
                style={{ animation: `fadeInUp 0.5s ease forwards`, animationDelay: `${400 + i * 80}ms`, opacity: 0 }}
              >
                <div className="text-2xl lg:text-3xl font-black text-coral-gold tracking-tight">
                  {stat.value}
                </div>
                <div className="text-xs text-white/60 mt-1 tracking-wide">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* ── Scroll indicator ──────────────────────────────────── */}
      <div className="absolute bottom-28 right-8 hidden lg:flex flex-col items-center gap-2 opacity-40">
        <div className="w-px h-12 bg-white" style={{ animation: 'shimmer 2s ease-in-out infinite' }} />
        <span className="text-white text-[10px] tracking-[0.2em] rotate-90 origin-center mt-2">CUỘN</span>
      </div>

    </section>
  )
}
