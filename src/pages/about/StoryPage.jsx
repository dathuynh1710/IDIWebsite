import AboutPageHeader from '@components/about/AboutPageHeader'
import { DEVELOPMENT_TIMELINE } from '@data/about'

export default function StoryPage() {
  return (
    <>
      <AboutPageHeader
        eyebrow="Về IDI"
        title="Lịch sử hình thành và đổi mới"
        description="Hành trình phát triển của IDI qua những cột mốc quan trọng."
      />

      <main className="bg-white py-14 sm:py-18">
        <article className="container max-w-4xl">
          <div className="space-y-5 text-base leading-8 text-slate sm:text-lg sm:leading-9">
            <p>
              Từ khi IDI bắt đầu hành trình của mình, công ty đã liên tục trau dồi và chuyển đổi trên
              nhiều phương diện. Với khởi đầu khiêm tốn, khi những người tiên phong nhận ra tiềm năng
              của loài cá tra tại Việt Nam, IDI đã từng bước phát triển để trở thành một trong những
              doanh nghiệp hàng đầu trong lĩnh vực này.
            </p>
            <p>
              IDI gặt hái thành công nhờ xây dựng và theo đuổi một chiến lược toàn diện, gắn kết các
              mục tiêu kinh doanh với trách nhiệm đối với Hành tinh, Con người và Sản phẩm.
            </p>
            <p>
              Công ty duy trì định hướng rõ ràng về phát triển bền vững, xây dựng thương hiệu trên thị
              trường quốc tế và không ngừng củng cố năng lực quản trị. Chúng tôi cam kết tạo dựng một
              tổ chức vững mạnh, đồng thời lưu lại những giá trị lâu dài cho ngành công nghiệp thực phẩm.
            </p>
          </div>

          <hr className="my-10 border-light-mist" />

          <section aria-labelledby="development-history">
            <h2 id="development-history" className="text-2xl font-bold text-ocean-deep sm:text-3xl">
              Các cột mốc phát triển
            </h2>
            <div className="mt-7 divide-y divide-light-mist border-y border-light-mist">
              {DEVELOPMENT_TIMELINE.map((item) => (
                <div key={item.year} className="grid gap-2 py-6 sm:grid-cols-[8rem_1fr] sm:gap-8">
                  <strong className="text-xl text-seafoam">{item.year}</strong>
                  <div>
                    <h3 className="text-lg font-bold text-ocean-deep">{item.title}</h3>
                    <p className="mt-2 text-base leading-8 text-slate">{item.body}</p>
                  </div>
                </div>
              ))}
            </div>
          </section>

          <section className="mt-10" aria-labelledby="responsible-history">
            <h2 id="responsible-history" className="text-2xl font-bold text-ocean-deep sm:text-3xl">
              Một lịch sử đầy trách nhiệm
            </h2>
            <p className="mt-5 text-base leading-8 text-slate sm:text-lg sm:leading-9">
              Là một trong những nhà sản xuất cá tra lớn, chúng tôi hiểu hoạt động kinh doanh luôn đi
              kèm trách nhiệm to lớn. Giá trị của IDI được quyết định bởi những mối quan hệ bền chặt
              đã xây dựng với khách hàng bằng sự tin cậy và chất lượng qua thời gian.
            </p>
          </section>
        </article>
      </main>
    </>
  )
}
