import AnimatedCounter from '@components/common/AnimatedCounter'
import RevealOnScroll from '@components/common/RevealOnScroll'

const STATS = [
  {
    id: 'countries',
    target: 50,
    suffix: '+',
    label: 'Quốc gia xuất khẩu',
    description: 'Hiện diện tại Châu Âu, Châu Mỹ, Châu Á và Trung Đông',
    color: 'text-coral-gold',
  },
  {
    id: 'capacity',
    target: 100000,
    suffix: ' MT',
    label: 'Công suất mỗi năm',
    description: 'Tấn sản phẩm cá tra được chế biến mỗi năm',
    color: 'text-coral-gold',
    compact: true,
  },
  {
    id: 'founded',
    target: 1997,
    suffix: '',
    label: 'Năm thành lập',
    description: 'Gần ba thập kỷ kinh nghiệm trong ngành thủy sản',
    color: 'text-coral-gold',
  },
  {
    id: 'staff',
    target: 5000,
    suffix: '+',
    label: 'Nhân sự',
    description: 'Đội ngũ lành nghề xuyên suốt chuỗi giá trị',
    color: 'text-coral-gold',
  },
]

export default function StatisticsSection() {
  return (
    <section
      className="py-24 relative overflow-hidden"
      style={{ background: 'linear-gradient(135deg, #0B2545 0%, #163D6B 100%)' }}
    >
      {/* Subtle background texture */}
      <div
        className="absolute inset-0 opacity-5"
        style={{
          backgroundImage: 'url("data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
        }}
      />

      <div className="container relative z-10">
        {/* Header */}
        <RevealOnScroll>
          <div className="text-center mb-16">
            <span className="section-eyebrow text-seafoam-light">Những con số nổi bật</span>
            <h2 className="text-h2 font-bold text-white mt-3">
              Quy mô tạo nên sự tin cậy
            </h2>
            <p className="text-white/60 mt-4 max-w-xl mx-auto">
              Năng lực chế biến và mạng lưới toàn cầu giúp đối tác an tâm
              về nguồn cung ổn định với sản lượng lớn.
            </p>
          </div>
        </RevealOnScroll>

        {/* Stats grid */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-px bg-white/10 rounded-2xl overflow-hidden">
          {STATS.map((stat, i) => (
            <RevealOnScroll key={stat.id} delay={i * 100}>
              <div className="bg-ocean-deep/80 p-8 lg:p-10 text-center hover:bg-ocean-mid/80 transition-colors duration-300 group">
                <div className="text-4xl lg:text-5xl font-black text-coral-gold mb-3 group-hover:scale-105 transition-transform duration-300">
                  <AnimatedCounter
                    target={stat.target}
                    suffix={stat.suffix}
                    compact={stat.compact}
                    duration={2200}
                  />
                </div>
                <div className="text-white font-bold text-base mb-2">
                  {stat.label}
                </div>
                <div className="text-white/50 text-sm leading-relaxed">
                  {stat.description}
                </div>
              </div>
            </RevealOnScroll>
          ))}
        </div>

        {/* Bottom tagline */}
        <RevealOnScroll delay={400}>
          <p className="text-center text-white/40 text-sm mt-10 tracking-wide">
            Niêm yết trên HOSE · Mã chứng khoán: IDI · Thành lập năm 1997
          </p>
        </RevealOnScroll>
      </div>
    </section>
  )
}
