import PageHead from '@components/common/PageHead'

const APPROACHES = [
  {
    number: '01',
    title: 'Chúng tôi lắng nghe',
    description:
      'Chúng tôi quý trọng mọi cơ hội để lắng nghe khách hàng và người tiêu dùng, thấu hiểu các mối quan tâm của họ, xử lý các vấn đề quan trọng và sau đó là thiết lập các ý tưởng mới. Hiểu rõ được mối quan tâm của khách hàng sẽ giúp chúng tôi phát triển chiến lược và đáp ứng tốt hơn nhu cầu của xã hội.',
  },
  {
    number: '02',
    title: 'Chúng tôi yêu cầu',
    description:
      'I.D.I liên tục tham gia vào các buổi thảo luận với các nhà cung cấp, thông qua giáo dục để học hỏi những phương pháp mới duy trì và nâng cao các kỹ thuật khai thác bền vững. Tiêu chuẩn của chúng tôi được giám sát bởi đội ngũ nhân viên tận tụy, những người thường xuyên đến thăm các nhà cung ứng để đánh giá quá trình hoạt động của họ và đảm bảo rằng họ tuân thủ các quy tắc mà I.D.I đề ra.',
  },
  {
    number: '03',
    title: 'Chúng tôi đổi mới',
    description:
      'Chúng tôi tin rằng việc đầu tư vào nghiên cứu và phát triển kiến thức mới sẽ là chìa khóa làm tăng sản lượng nuôi trồng thủy sản một cách bền vững. Tại I.D.I, chúng tôi tận dụng chuỗi giá trị tích hợp toàn diện của mình được hỗ trợ bởi công nghệ mới tiên tiến để cải tiến mọi thứ không ngừng nghỉ.',
    extra:
      'Mục tiêu của I.D.I là toàn quyền kiểm soát trong tất cả các khâu từ sản xuất con giống, thức ăn, chăn nuôi đến thu hoạch và chế biến. Điều này mang lại cho chúng tôi những cơ hội đặc biệt, điều mà khi hoạt động riêng lẻ khó mà đạt được. Chúng tôi thu thập dữ liệu, kinh nghiệm thực tiễn và hoạt động sản xuất từ khắp nơi trên Thế Giới. Điều này cho phép chúng tôi tái thiết lập với mức độ chính xác cao hơn các đối thủ cạnh tranh và giúp chúng tôi dẫn đầu trong ngành thủy sản.',
  },
]

const SOURCE_IMAGES = {
  aquaculture: 'https://idiseafood.com/vnt_upload/File/10_2020/about1.png',
  commitment: 'https://idiseafood.com/vnt_upload/File/10_2020/banner.png',
  greenFinance:
    'https://idiseafood.com/vnt_upload/File/12_2024/z6157041673068_a3e1b616e3505bb73eaa3d41c9de8d09.jpg',
}

export default function SustainabilityPage() {
  return (
    <>
      <PageHead
        title="Phát triển bền vững | IDI Seafood"
        description="IDI phát triển nuôi trồng thủy sản bền vững, đổi mới chuỗi giá trị và thúc đẩy tài chính xanh vì một tương lai khỏe mạnh."
      />

      <main className="overflow-hidden bg-white">
        <section className="relative flex min-h-[620px] items-end overflow-hidden bg-ocean-deep pt-[72px]">
          <img
            src={SOURCE_IMAGES.aquaculture}
            alt="Vùng nuôi trồng thủy sản bền vững của IDI"
            className="absolute inset-0 h-full w-full object-cover"
          />
          <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(5,29,54,0.92)_0%,rgba(5,29,54,0.68)_52%,rgba(5,29,54,0.2)_100%)]" />
          <div className="absolute -right-24 top-20 h-80 w-80 rounded-full border border-white/15" />
          <div className="absolute -right-4 top-40 h-44 w-44 rounded-full border border-white/15" />

          <div className="container relative z-10 pb-16 pt-28 sm:pb-20 lg:pb-24">
            <div className="max-w-3xl">
              <p className="mb-5 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.24em] text-coral-light">
                <span className="h-px w-10 bg-coral-light" />
                Nuôi dưỡng thiên nhiên
              </p>
              <h1 className="max-w-3xl text-balance text-[clamp(2.75rem,7vw,5.75rem)] font-extrabold leading-[0.98] tracking-[-0.045em] text-white">
                Phát triển
                <span className="block text-seafoam-light">bền vững</span>
              </h1>
              <p className="mt-7 max-w-xl text-lg leading-8 text-white/78 sm:text-xl">
                Vì một tương lai khỏe mạnh, nơi tăng trưởng kinh tế song hành cùng thiên nhiên và cộng đồng.
              </p>
            </div>
          </div>
        </section>

        <section className="section-padding">
          <div className="container grid items-start gap-12 lg:grid-cols-[0.82fr_1.18fr] lg:gap-20">
            <div className="lg:sticky lg:top-28">
              <span className="section-eyebrow">Tầm nhìn dài hạn</span>
              <h2 className="max-w-lg text-balance text-ocean-deep">
                Nuôi trồng thủy sản bền vững
              </h2>
              <div className="mt-6 h-1 w-20 rounded-full bg-coral-gold" />
            </div>

            <div className="space-y-6 text-lg leading-9">
              <p className="text-slate">
                Nuôi trồng thủy sản bền vững là quan tâm đến sự phát triển của các quần thể đang khai thác cũng như bảo tồn hệ sinh thái và một hành tinh lành mạnh. Tại I.D.I, chúng tôi có niềm tin vững chắc rằng, bằng cách nuôi trồng tại vùng đồng bằng sông Cửu Long, chúng tôi có thể sản xuất ra những sản phẩm tốt cho sức khỏe, có giá trị dinh dưỡng và giá thành hợp lý cho toàn xã hội.
              </p>
              <p className="text-slate">
                Cho nên, việc chung sống hòa hợp với môi trường là điều thiết yếu để hiện thực hóa niềm tin đó và tiếp tục chia sẻ những điều tốt đẹp mà thiên nhiên ban tặng cho các thế hệ tương lai.
              </p>
              <div className="mt-8 grid grid-cols-2 gap-3 border-t border-light-mist pt-7 sm:grid-cols-3">
                {['Hệ sinh thái', 'Cộng đồng', 'Thế hệ tương lai'].map((label) => (
                  <div key={label} className="flex items-center gap-2 text-sm font-bold text-ocean-deep">
                    <span className="grid h-6 w-6 place-items-center rounded-full bg-seafoam-pale text-seafoam">✓</span>
                    {label}
                  </div>
                ))}
              </div>
            </div>
          </div>
        </section>

        <section className="bg-arctic-white section-padding">
          <div className="container">
            <div className="mb-12 max-w-2xl">
              <span className="section-eyebrow">Phương pháp của IDI</span>
              <h2 className="text-balance text-ocean-deep">Chúng tôi thực hiện điều đó như thế nào?</h2>
            </div>

            <div className="grid gap-5 lg:grid-cols-3">
              {APPROACHES.map((approach) => (
                <article
                  key={approach.number}
                  className="group relative overflow-hidden border border-light-mist bg-white p-7 shadow-[0_20px_60px_-45px_rgba(11,37,69,0.55)] transition duration-300 hover:-translate-y-1 hover:border-seafoam/40 sm:p-8"
                >
                  <div className="mb-10 flex items-start justify-between">
                    <span className="text-xs font-extrabold uppercase tracking-[0.18em] text-seafoam">
                      Hành động {approach.number}
                    </span>
                    <span className="text-5xl font-extrabold leading-none text-light-mist transition-colors group-hover:text-seafoam-pale">
                      {approach.number}
                    </span>
                  </div>
                  <h3 className="mb-5 text-2xl text-ocean-deep">{approach.title}</h3>
                  <p className="text-sm leading-7 text-storm-grey">{approach.description}</p>
                  {approach.extra && (
                    <p className="mt-4 border-t border-light-mist pt-4 text-sm leading-7 text-storm-grey">
                      {approach.extra}
                    </p>
                  )}
                  <div className="absolute inset-x-0 bottom-0 h-1 origin-left scale-x-0 bg-seafoam transition-transform duration-300 group-hover:scale-x-100" />
                </article>
              ))}
            </div>
          </div>
        </section>

        <section className="relative isolate min-h-[440px] overflow-hidden">
          <img
            src={SOURCE_IMAGES.commitment}
            alt="Cam kết phát triển nền nông nghiệp bền vững của IDI"
            className="absolute inset-0 h-full w-full object-cover"
          />
          <div className="absolute inset-0 bg-ocean-deep/72" />
          <div className="container relative z-10 flex min-h-[440px] items-center py-20">
            <div className="max-w-4xl border-l-4 border-coral-gold pl-6 sm:pl-9">
              <span className="mb-4 block text-xs font-bold uppercase tracking-[0.25em] text-coral-light">
                Cam kết của chúng tôi
              </span>
              <h2 className="text-balance text-[clamp(2rem,5vw,4.25rem)] font-extrabold uppercase leading-[1.08] tracking-[-0.035em] text-white">
                Chúng tôi cam kết phát triển một nền nông nghiệp bền vững
              </h2>
            </div>
          </div>
        </section>

        <section className="section-padding-lg bg-seafoam-pale/45">
          <div className="container grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div className="relative">
              <div className="absolute -bottom-5 -left-5 h-full w-full border border-seafoam/35" />
              <img
                src={SOURCE_IMAGES.greenFinance}
                alt="Hoạt động tài chính xanh của IDI"
                className="relative aspect-[4/3] w-full object-cover shadow-[0_30px_70px_-35px_rgba(11,37,69,0.55)]"
              />
              <div className="absolute -right-4 -top-4 bg-ocean-deep px-5 py-4 text-white shadow-lg sm:-right-6 sm:-top-6">
                <span className="block text-xs font-bold uppercase tracking-[0.16em] text-coral-light">Tiên phong</span>
                <strong className="mt-1 block text-lg">Trái phiếu xanh</strong>
              </div>
            </div>

            <div>
              <span className="section-eyebrow">Tăng trưởng có trách nhiệm</span>
              <h2 className="text-ocean-deep">Tài chính xanh</h2>
              <div className="mt-7 space-y-5">
                <p className="leading-8 text-slate">
                  Dưới sự hỗ trợ của GuarantCo, chúng tôi đã phát hành trái phiếu xanh đầu tiên trong ngành nuôi trồng thủy sản tại Châu Á. Theo khuôn khổ trái phiếu xanh của chúng tôi, chúng tôi cam kết phân bổ vốn thu được từ trái phiếu xanh vào các dự án xanh đủ điều kiện, với mục tiêu thúc đẩy việc nuôi trồng và sản xuất cá tra bền vững và thân thiện với môi trường.
                </p>
                <p className="leading-8 text-slate">
                  Các phương pháp chế biến cá bền vững của chúng tôi phù hợp với các tiêu chuẩn bền vững quốc tế được thiết lập bởi Hội đồng Quản lý Nuôi trồng Thủy sản (ASC) và Thực hành Nuôi trồng Thủy sản Tốt nhất (BAP).
                </p>
                <p className="leading-8 text-slate">
                  Những tiêu chuẩn này rất quan trọng đối với các công ty Việt Nam muốn tiếp cận các thị trường có yêu cầu về bền vững khắt khe và đáp ứng nhu cầu ngày càng tăng đối với các sản phẩm thân thiện với môi trường và có nguồn gốc có trách nhiệm.
                </p>
              </div>

              <div className="mt-8 flex flex-wrap gap-3">
                {['ASC', 'BAP', 'Green Bond'].map((standard) => (
                  <span
                    key={standard}
                    className="rounded-full border border-seafoam/25 bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.1em] text-seafoam shadow-sm"
                  >
                    {standard}
                  </span>
                ))}
              </div>
            </div>
          </div>
        </section>
      </main>
    </>
  )
}
