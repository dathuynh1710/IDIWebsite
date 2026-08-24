<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\InteractsWithSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentSeeder extends Seeder
{
    use InteractsWithSeedData;

    public function run(): void
    {
        $adminId = (int) DB::table('users')->where('email', 'admin@idiseafood.local')->value('id');
        $newsMediaId = (int) DB::table('media')->where('file_name', 'factory-news.jpg')->value('id');

        $this->seedPages($adminId, $newsMediaId);
    }

    private function seedPages(int $adminId, int $mediaId): void
    {
        $legacyAboutId = DB::table('pages')
            ->where('code', 'ABOUT')
            ->where('slug->vi', 've-chung-toi')
            ->value('id');
        if ($legacyAboutId && ! DB::table('pages')->where('code', 'ABOUT_MESSAGE')->exists()) {
            DB::table('pages')->where('id', $legacyAboutId)->update(['code' => 'ABOUT_MESSAGE']);
        }

        $messageMediaId = $this->upsertId('media', [
            'directory' => 'about',
            'file_name' => 'avatar.jpg',
        ], [
            'folder_id' => null,
            'disk' => 'public',
            'external_url' => 'https://idiseafood.com/vnt_upload/about/avatar.jpg',
            'original_name' => 'avatar.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'file_size' => null,
            'width' => null,
            'height' => null,
            'checksum' => null,
            'title' => $this->json(['vi' => 'Ông Lê Văn Chung', 'en' => 'Mr. Le Van Chung', 'zh' => 'Le Van Chung先生']),
            'alt_text' => $this->json(['vi' => 'Ông Lê Văn Chung - Cố vấn điều hành IDI', 'en' => 'Mr. Le Van Chung - Executive Advisor of I.D.I', 'zh' => 'Le Van Chung先生 - I.D.I执行顾问']),
            'caption' => null,
            'created_by' => $adminId,
            'deleted_at' => null,
        ]);

        $messageId = $this->upsertId('pages', ['code' => 'ABOUT_MESSAGE'], [
            'parent_id' => null,
            'featured_media_id' => $messageMediaId,
            'template' => 'about',
            'title' => $this->json(['vi' => 'Thông điệp của công ty', 'en' => 'A Message from I.D.I', 'zh' => 'I.D.I 致辞']),
            'slug' => $this->json(['vi' => 'thong-diep-cua-cong-ty', 'en' => 'a-message-from-i-d-i', 'zh' => 'i-d-i-zhi-ci']),
            'summary' => $this->json([
                'vi' => 'Tại I.D.I - Chúng tôi tâm niệm rằng việc phát triển bền vững chính là nền tảng để xây dựng công ty. Chúng tôi vẫn và sẽ tiếp tục tạo nên những giá trị đa dạng cho con người, đồng thời thích nghi với các thay đổi của xã hội và môi trường.',
                'en' => 'At I.D.I, we believe that sustainability is not a choice, but a way of life. We have, and will continue to create diverse value while adapting to social and environmental changes.',
                'zh' => '在I.D.I，我们相信可持续发展不是一种选择，而是一种生活方式。我们已经并将继续创造多样化的价值，同时适应社会和环境变化。',
            ]),
            'content' => $this->json(['vi' => <<<'HTML'
<h2>Ông. Lê Văn Chung</h2>
<p><em>Cố vấn điều hành</em></p>
<p>Trong Thế Giới phát triển nhanh chóng như vậy, sứ mệnh của I.D.I vẫn không thay đổi và sẽ tiếp tục đi sát với những mục tiêu đề ra ban đầu: chúng tôi muốn góp phần nuôi dưỡng một xã hội lành mạnh bằng cách cung cấp các thực phẩm ngon, an toàn và tốt cho sức khỏe. Triết lý này là tiền đề cho những nỗ lực không mệt mỏi của chúng tôi trong hơn 10 năm qua, để trở thành người bạn đồng hành không thể thiếu trong xã hội.</p>
<p>Chúng tôi là những người may mắn được hưởng nguồn lợi lớn từ sông Mekong và đồng bằng Sông Cửu Long. Điều này đồng nghĩa với việc IDI cũng có trách nhiệm trong việc bảo vệ môi trường tự nhiên này. Bởi vì nguồn tài nguyên nuôi trồng thủy sản trên thế giới không phải là vô hạn, nên đây là thách thức để chúng tôi tìm ra cách sống hài hòa với nhiên thiên. IDI phải tạo ra những bước tiến dài hướng đến sự phát triển bền vững của môi trường, bảo vệ nguồn nước sạch và hệ sinh thái tự nhiên để thế hệ tương lai được kế thừa món quà vô giá đó. Lời cam kết này là kim chỉ nam điều hướng công ty vận hành qua từng ngày và ảnh hướng lớn đến mọi quyết định của I.D.I. Cùng lúc đó, chúng tôi cố gắng thúc đẩy quá trình phát triển nội tại bằng cách ban hành nhiều chính sách hỗ trợ nhân viên, tạo sự bình đẳng, cơ hội phát triển trong công việc và triển khai các chương trình phát triển năng lực lãnh đạo trong công ty.</p>
<p>Chúng tôi hiểu rằng I.D.I không nằm ngoài guồng quay của các thay đổi Kinh tế - Xã hội, dẫn đến việc dự đoán các xu hướng phát triển trong tương lai mang tính thử thách hơn. Tuy nhiên, lối sống tích cực và tư duy đa chiều sẽ mở ra các cơ hội mới nhờ quá trình tích lũy và tận dụng kinh nghiệm thực tiễn của chúng tôi. Tất cả những điểm mạnh trên sẽ là yếu tố bảo chứng cho một I.D.I còn phát triển mạnh mẽ và đi xa hơn nữa trên con đường đã chọn.</p>
HTML, 'en' => <<<'HTML'
<h2>Mr. Le Van Chung</h2>
<p><em>Executive Advisor</em></p>
<p>In this fast-evolving world, I.D.I’s mission has remained constant, and will continue to be so: We want to enrich people’s lives and nurture their bodies by providing them with safe, healthy and delicious food. This philosophy has laid the foundation for our tireless efforts in the past 10 years to become a valued and indispensable part of society.</p>
<p>As beneficiaries of the Mekong Delta Region’s bounty, we believe that we also have corporate duties to protect the natural environment. The world’s aquaculture resources are not infinite, it is our challenge to live as one with nature. We must take great strides towards sustainability and environmental development; it is our job to keep the rivers and countless ecosystems clean and healthy for future generations. This commitment is what guides our day-to-day actions and our decision-making process. At the same time, we strive to fuel our organic growth still further through initiatives around health management, diversity, and leadership development.</p>
<p>I.D.I is not an exception to social and economic change, making predictions increasingly difficult. However, greater diversity in lifestyles and ways of thinking also allows us to unlock opportunities by leveraging on our experience. We must embrace innovation, while staying true to our values, to ensure that I.D.I will still exist a century from now—and beyond.</p>
HTML, 'zh' => <<<'HTML'
<h2>Mr. Le Van Chung</h2>
<p><em>执行顾问</em></p>
<p>在这个快速发展的世界中，I.D.I的使命一直保持不变，并且将继续如此：我们希望通过为人们提供安全、健康和美味的食物来丰富人们的生活并养育他们的身体。这一理念为我们过去十年来为成为社会的重要和不可或缺的一部分所做的不懈努力奠定了基础。</p>
<p>作为湄公河三角洲地区赏金的受益者，我们认为我们还有保护自然环境的公司职责。世界的水产养殖资源不是无限的，与大自然合一生活是我们面临的挑战。我们必须在可持续性和环境发展方面取得重大进展；为子孙后代保持河流和无数生态系统的清洁和健康是我们的工作。这种承诺指导着我们的日常行动和决策过程。同时，我们致力于通过围绕健康管理、多样性和领导力发展的举措进一步推动我们的有机增长。</p>
<p>I.D.I并非社会和经济变革的例外，这使得预测变得越来越困难。但是，生活方式和思维方式的更多多样性也使我们能够利用我们的经验来释放机会。我们必须坚持创新，同时恪守我们的价值观，以确保I.D.I从现在到未来还有一个世纪。</p>
HTML]),
            'seo_title' => $this->json(['vi' => 'Thông điệp của công ty', 'en' => 'A Message from I.D.I', 'zh' => 'I.D.I 致辞']),
            'meta_description' => $this->json([
                'vi' => 'Thông điệp của Cố vấn điều hành Lê Văn Chung về phát triển bền vững, con người, xã hội và môi trường tại I.D.I.',
                'en' => 'Executive Advisor Le Van Chung shares I.D.I’s commitment to sustainable growth, people, society and the environment.',
                'zh' => 'I.D.I执行顾问Le Van Chung先生分享公司对可持续发展、社会、人才和环境的承诺。',
            ]),
            'meta_keywords' => $this->json([
                'vi' => 'I.D.I, IDI Seafood, thông điệp công ty, phát triển bền vững',
                'en' => 'I.D.I, IDI Seafood, company message, sustainable development',
                'zh' => 'I.D.I, IDI Seafood, 公司致辞, 可持续发展',
            ]),
            'og_title' => null,
            'og_description' => null,
            'translation_status' => $this->json(['vi' => 'published', 'en' => 'published', 'zh' => 'published']),
            'locale_published_at' => $this->json(['vi' => now()->toIso8601String(), 'en' => now()->toIso8601String(), 'zh' => now()->toIso8601String()]),
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);

        $this->upsertId('pages', ['code' => 'ABOUT_HISTORY'], [
            'parent_id' => null,
            'featured_media_id' => null,
            'template' => 'about-history',
            'title' => $this->json(['vi' => 'Lịch sử hình thành và đổi mới', 'en' => 'A History of Innovation', 'zh' => '发展与创新历程']),
            'slug' => $this->json(['vi' => 'lich-su-doi-moi', 'en' => 'a-history-of-innovation', 'zh' => 'fa-zhan-yu-chuang-xin-li-cheng']),
            'summary' => $this->json([
                'vi' => 'Từ khi I.D.I bắt đầu cuộc hành trình của mình vào năm 2008, công ty đã liên tục trau dồi và chuyển đổi trên nhiều phương diện.',
                'en' => 'Since I.D.I embarked on its journey in 2008, the company has learned and transformed in many ways.',
                'zh' => '自2008年I.D.I开启发展征程以来，公司在众多领域不断学习、成长与转型。',
            ]),
            'content' => $this->json(['vi' => <<<'HTML'
<p>Với khởi đầu khiêm tốn của mình, khi những người tiên phong nhận ra cơ hội tuyệt vời của loài cá tra sống ở Việt Nam, I.D.I đã phát triển để trở thành công ty hàng đầu trong lĩnh vực này.</p>
<p>I.D.I gặt hái được thành công của mình nhờ vào việc phát triển và tuân theo một chiến lược toàn diện, gắn kết các mục tiêu và nhiệm vụ với các tôn chỉ hoạt động vì Hành tinh, Con người và Sản phẩm.</p>
<p>Công ty sỡ hữu một tầm nhìn được xác định và đặt ra rõ ràng trong việc phát triển bền vững, thương hiệu lớn mạnh trên thế giới và kinh nghiệm lãnh đạo dày dặn. Chúng tôi cam kết xây dựng một tổ chức tuyệt vời, đồng thời lưu lại các giá trị lâu dài cho thế giới nói chung và ngành công nghiệp thực phẩm nói riêng.</p>
<h2>Các cột mốc phát triển</h2>
<h3>2008</h3>
<p>Một số người tiên phong đã nhận ra tiềm năng của loài cá tra và nhà máy chế biến đầu tiên được xây dựng để giới thiệu các sản phẩm chất lượng cao trên toàn thế giới.</p>
<h3>2010</h3>
<p>Trở thành 1 trong 10 doanh nghiệp sản xuất thủy sản của Việt Nam được Bộ Nông nghiệp và Phát triển nông thôn công nhận chất lượng tốt nhất trong ngành.</p>
<h3>2011</h3>
<p>Nhảy lên top 5 doanh nghiệp xuất khẩu cá tra lớn nhất Việt Nam.</p>
<p>Được niêm yết trên sàn giao dịch chứng khoán Việt Nam.</p>
<h3>2016</h3>
<p>Được xếp hạng trong 500 công ty phát triển nhanh nhất Việt Nam.</p>
<p>Top 50 công ty kinh doanh hiệu quả nhất Việt Nam do Vietnam Business Review bình chọn.</p>
<h3>2017</h3>
<p>Xây dựng thêm các cơ sở để nâng công suất chế biến lên 1000 tấn mỗi ngày nhằm đáp ứng tốt hơn nhu cầu đang tăng nhanh trên toàn cầu.</p>
<h3>2019</h3>
<p>Đầu tư vào chương trình nghiên cứu phát triển cơ sở sản xuất giống và chất lượng đàn giống, cũng như đổi mới quản lý ao nuôi.</p>
<h2>Một lịch sử đầy trách nhiệm</h2>
<p>Là một trong những nhà sản xuất cá tra lớn nhất trên thế giới, chúng tôi hiểu việc kinh doanh luôn đi kèm với trách nhiệm to lớn. Giá trị của chúng tôi được quyết định bởi các mối quan hệ bền chặt mà chúng tôi đã xây dựng với khách hàng bằng sự tin cậy và chất lượng qua thời gian.</p>
HTML, 'en' => <<<'HTML'
<p>From its humble beginnings, when a few pioneers recognized the opportunity presented by Vietnam's wonderful pangasius species, I.D.I has grown to become a leader in its field.</p>
<p>I.D.I earns its share of success by developing and following an integrated strategy where our goals and missions are aligned with our guiding principles: Planet, People and Product.</p>
<p>With a clearly defined vision of sustainability, a strong global presence and extensive leadership experience, we are committed to building not only an excellent organization, but also enduring value for the world and the food industry.</p>
<h2>Development milestones</h2>
<h3>2008</h3>
<p>A few pioneers recognized the potential of the pangasius species, and the first processing plant was built to introduce high-quality products worldwide.</p>
<h3>2010</h3>
<p>Became one of 10 seafood producers in Vietnam recognized by the Ministry of Agriculture and Rural Development for having the best quality in the industry.</p>
<h3>2011</h3>
<p>Jumped into the top five pangasius exporters in Vietnam.</p>
<p>Became listed on the Vietnamese Stock Exchange.</p>
<h3>2016</h3>
<p>Ranked among the 500 fastest-growing companies in Vietnam.</p>
<p>Named among Vietnam's 50 most efficient companies by Vietnam Business Review.</p>
<h3>2017</h3>
<p>Constructed additional facilities to raise processing capacity to 1,000 tons per day and better meet fast-rising global demand.</p>
<h3>2019</h3>
<p>Invested in research programs for hatchery development and broodstock quality, as well as innovation in pond management.</p>
<h2>A history of responsibility</h2>
<p>As one of the largest producers of pangasius in the world, we understand that good business comes with great responsibility. Our value depends on the strong relationships we have built with trust, care and quality over time.</p>
HTML, 'zh' => <<<'HTML'
<p>I.D.I从朴素的起点出发。当一批先行者发现越南优质巴沙鱼品种所蕴藏的机遇后，公司逐步成长为该领域的领先企业。</p>
<p>I.D.I通过制定并践行一体化战略取得成功，使企业目标和使命始终与“地球、人类、产品”三大指导原则保持一致。</p>
<p>凭借清晰的可持续发展愿景、强大的全球影响力和丰富的领导经验，我们不仅致力于建设卓越的组织，也致力于为世界和食品行业创造长久价值。</p>
<h2>发展里程碑</h2>
<h3>2008</h3>
<p>先行者发现了巴沙鱼品种的巨大潜力，首座加工厂随之建成，将高品质产品推向全球市场。</p>
<h3>2010</h3>
<p>成为越南农业与农村发展部认可的十家行业优质水产品生产企业之一。</p>
<h3>2011</h3>
<p>跃居越南巴沙鱼出口企业前五名。</p>
<p>在越南证券交易所挂牌上市。</p>
<h3>2016</h3>
<p>入选越南发展速度最快的500家企业。</p>
<p>获《Vietnam Business Review》评选为越南经营效率最高的50家企业之一。</p>
<h3>2017</h3>
<p>扩建设施，将日加工能力提升至1,000吨，以更好地满足全球快速增长的市场需求。</p>
<h3>2019</h3>
<p>投资开展孵化设施、亲本种群质量及池塘管理创新等研究项目。</p>
<h2>责任相伴的发展历程</h2>
<p>作为全球最大的巴沙鱼生产商之一，我们深知卓越经营始终伴随着重大责任。我们的价值源于长期以来以信任、关怀与品质建立的稳固客户关系。</p>
HTML]),
            'seo_title' => $this->json(['vi' => 'Lịch sử hình thành và đổi mới', 'en' => 'A History of Innovation', 'zh' => 'I.D.I发展与创新历程']),
            'meta_description' => $this->json([
                'vi' => 'Hành trình hình thành và đổi mới của I.D.I từ năm 2008 qua các cột mốc phát triển quan trọng.',
                'en' => "Explore I.D.I's journey of growth and innovation since 2008 through its defining development milestones.",
                'zh' => '通过重要发展里程碑，了解I.D.I自2008年以来的成长与创新历程。',
            ]),
            'meta_keywords' => $this->json([
                'vi' => 'I.D.I, IDI Seafood, lịch sử hình thành, lịch sử đổi mới',
                'en' => 'I.D.I, IDI Seafood, company history, innovation, milestones',
                'zh' => 'I.D.I, IDI Seafood, 企业历史, 创新历程, 发展里程碑',
            ]),
            'og_title' => null,
            'og_description' => null,
            'translation_status' => $this->json(['vi' => 'published', 'en' => 'published', 'zh' => 'published']),
            'locale_published_at' => $this->json(['vi' => now()->toIso8601String(), 'en' => now()->toIso8601String(), 'zh' => now()->toIso8601String()]),
            'sort_order' => 1,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);

        $this->upsertId('pages', ['code' => 'ABOUT_VALUES'], [
            'parent_id' => null,
            'featured_media_id' => null,
            'template' => 'about-values',
            'title' => $this->json(['vi' => 'Giá trị cốt lõi', 'en' => "I.D.I's Values", 'zh' => '核心价值观']),
            'slug' => $this->json(['vi' => 'gia-tri-cot-loi', 'en' => 'i-d-i-s-values', 'zh' => 'he-xin-jia-zhi-guan']),
            'summary' => $this->json([
                'vi' => 'Niềm đam mê, sự đổi mới, sự sẻ chia và tinh thần trách nhiệm là những giá trị định hình văn hóa doanh nghiệp I.D.I.',
                'en' => "Passion, innovation, sharing and responsibility are the values that shape I.D.I's corporate culture.",
                'zh' => '热情、创新、分享与责任，是塑造I.D.I企业文化的核心价值观。',
            ]),
            'content' => $this->json(['vi' => <<<'HTML'
<h2>Giá trị cốt lõi</h2>
<h3>Niềm đam mê</h3>
<p>Niềm đam mê và sự cống hiến là yếu tố chủ chốt tạo nên thành công. Chúng gắn liền với mọi khía cạnh trong suốt quá trình hoạt động và là trái tim của văn hóa doanh nghiệp tại IDI.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt1.jpg" alt="Passion"></p>
<h3>Sự đổi mới</h3>
<p>Tại I.D.I sự đổi mới là tiêu chuẩn cho mọi thứ chúng tôi thực hiện và tất cả những gì chúng tôi sản xuất. Yếu tố này sẽ dẫn dắt chúng tôi đến đỉnh cao phát triển trong ngành công nghiệp thủy sản.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/07_2024/congnhan.png" alt="Innovation"></p>
<h3>Sự sẻ chia</h3>
<p>I.D.I khuyến khích xây dựng một môi trường cởi mở và minh bạch. Trong công ty I.D.I cơ hội được chia sẻ giữa tất cả nhân viên, cho phép họ có những đóng góp quan trọng trong phạm vi kinh doanh của chúng tôi.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt3.jpg" alt="Share"></p>
<h3>Tinh thần trách nhiệm</h3>
<p>Thừa hưởng nguồn lợi thiên nhiên đi kèm với trách nhiệm xã hội lớn lao. Sự tôn trọng và đóng góp liên tục của I.D.I cho xã hội và môi trường vẫn là yếu tố cần thiết để duy trì bản sắc và thành công của chúng tôi.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt4.jpg" alt="Responsibility"></p>
<h2>Thế mạnh</h2>
<h3>Chúng tôi đam mê</h3>
<p>Chúng tôi yêu mến loài cá tra. Chúng tôi muốn hiểu tất cả mọi thứ về nó, làm thế nào để khách hàng thưởng thức được hương vị ngon nhất và làm thế nào chúng tôi có thể sản xuất nó một cách thuần túy và có trách nhiệm nhất.</p>
<h3>Chúng tôi làm việc trung thực</h3>
<p>Chúng tôi tin tưởng vào việc hợp tác trung thực, minh bạch với các đối tác và các bên liên quan trong chuỗi giá trị. Đó là lý do chúng tôi tìm kiếm các đối tác lâu dài, bền vững cho các nguồn cung ứng, cũng như các khách hàng có cùng niềm tin như chúng tôi.</p>
<h3>Chúng tôi đáng tin cậy</h3>
<p>I.D.I cung cấp hàng loạt các mặt hàng cá tra tươi tự nhiên. Từ những sự lựa chọn đa dạng đó, chúng tôi tư vấn sản phẩm phù hợp nhất với nhu cầu của khách hàng. Chính vì thế, IDI đã trở thành một đối tác đáng tin cậy cho nhiều khách hàng - bán lẻ, bán buôn, và dịch vụ ăn uống.</p>
<h2>Nhiệm vụ</h2>
<h3>Nuôi trồng bền vững</h3>
<p>Chúng tôi muốn tăng sinh kế bền vững từ sản xuất nuôi trồng thủy sản mà không tạo ra các tác động xấu đến kinh tế xã hội hoặc môi trường.</p>
<h3>Chuỗi giá trị và dinh dưỡng cao</h3>
<p>Chúng tôi cố gắng khám phá hết tiềm năng tuyệt vời của loài cá tra, tăng cường khả năng tiếp cận và tiêu thụ các sản phẩm từ cá tra có giá trị dinh dưỡng cao, đồng thời nuôi trồng bền vững, đặc biệt là cung cấp cá tra cho các khu vực đang phát triển.</p>
<h3>Trách nhiệm xã hội</h3>
<p>Chúng tôi cũng nghĩ rằng việc công ty I.D.I đóng góp vào các chương trình xã hội mang ý nghĩa rất quan trọng, hướng đến việc phát triển các cơ sở nuôi trồng thủy sản quy mô nhỏ và nâng cao giáo dục trong cộng đồng nhằm xóa đói – giảm nghèo và đảm bảo an ninh lương thực trong các vùng địa lý trọng yếu.</p>
HTML, 'en' => <<<'HTML'
<h2>I.D.I's Values</h2>
<h3>Passion</h3>
<p>Passion and dedication are the key ingredients to our success; they are embedded in every aspect of our operations and at the heart of our culture.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt1.jpg" alt="Passion"></p>
<h3>Innovation</h3>
<p>At I.D.I, innovation is the norm—for everything we do and all that we produce. It is what guides us to the top of an ever-evolving seafood industry.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/07_2024/congnhan.png" alt="Innovation"></p>
<h3>Sharing</h3>
<p>I.D.I encourages an open and transparent environment. Within I.D.I, opportunities are shared among all our employees, enabling them to make important contributions across the spectrum of our business.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt3.jpg" alt="Sharing"></p>
<h3>Responsibility</h3>
<p>Enjoying nature's bounty comes with great responsibility. Our continued respect and contribution to society and the environment remain essential to our identity and our success.</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt4.jpg" alt="Responsibility"></p>
<h2>Strengths</h2>
<h3>We are passionate</h3>
<p>We simply love pangasius. We want to understand everything about it, how it tastes at its best, and how we can produce it as purely and responsibly as possible.</p>
<h3>We act with honesty and integrity</h3>
<p>We believe in honest and transparent cooperation with partners and stakeholders in the value chain. This is why we seek long-term, sustainable partners in our supply and customer base who share the same values.</p>
<h3>We are reliable</h3>
<p>I.D.I offers an extensive range of natural fresh fish products. From this wide selection, we recommend the products that best fit our customers' needs. We have become a dependable partner to many customers across retail, wholesale and food service.</p>
<h2>Mission</h2>
<h3>Sustainable aquaculture</h3>
<p>We want to enable sustainable increases in livelihoods from aquaculture production without creating adverse socio-economic or environmental impacts.</p>
<h3>Value chain and nutrition</h3>
<p>We strive to explore the full potential of pangasius and increase access to and consumption of nutritious, sustainably raised fish, especially in developing regions.</p>
<h3>Social responsibility</h3>
<p>We believe it is important for I.D.I to contribute to social initiatives, support the development of small-scale aquaculture and enhance education in communities to reduce poverty and strengthen food security in priority regions.</p>
HTML, 'zh' => <<<'HTML'
<h2>核心价值观</h2>
<h3>热情</h3>
<p>热情与奉献是我们取得成功的关键，它们融入经营活动的每一个环节，也是企业文化的核心。</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt1.jpg" alt="热情"></p>
<h3>创新</h3>
<p>在I.D.I，创新是我们开展一切工作和生产所有产品的准则，引领我们在不断发展的水产行业中持续迈向更高水平。</p>
<p><img src="https://idiseafood.com/vnt_upload/about/07_2024/congnhan.png" alt="创新"></p>
<h3>分享</h3>
<p>I.D.I倡导开放、透明的工作环境，与所有员工共享发展机会，使每个人都能在公司的各个业务领域作出重要贡献。</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt3.jpg" alt="分享"></p>
<h3>责任</h3>
<p>享受大自然的馈赠也意味着承担重大责任。我们始终尊重社会与环境并持续作出贡献，这是I.D.I保持自身特色并取得成功的重要基础。</p>
<p><img src="https://idiseafood.com/vnt_upload/about/gt4.jpg" alt="责任"></p>
<h2>我们的优势</h2>
<h3>我们充满热情</h3>
<p>我们热爱巴沙鱼，希望深入了解它的一切：如何呈现最佳风味，以及如何以最纯净、最负责任的方式进行生产。</p>
<h3>我们秉持诚实与诚信</h3>
<p>我们重视与价值链中的合作伙伴及利益相关方开展诚实、透明的合作。因此，我们在供应链和客户群体中寻求拥有共同价值观的长期、可持续合作伙伴。</p>
<h3>我们值得信赖</h3>
<p>I.D.I提供丰富多样的天然鲜鱼产品，并根据客户需求推荐最合适的选择。如今，我们已成为零售、批发及餐饮服务等众多客户值得信赖的合作伙伴。</p>
<h2>使命</h2>
<h3>可持续水产养殖</h3>
<p>我们希望通过水产养殖生产可持续地改善生计，同时避免对社会经济或环境造成不利影响。</p>
<h3>价值链与营养</h3>
<p>我们致力于充分发掘巴沙鱼的潜力，提高营养丰富、可持续养殖鱼类的可获得性与消费量，尤其关注发展中地区。</p>
<h3>社会责任</h3>
<p>我们认为I.D.I应积极参与社会项目，推动小规模水产养殖发展并加强社区教育，从而减少贫困，保障重点地区的粮食安全。</p>
HTML]),
            'seo_title' => $this->json(['vi' => 'Giá trị cốt lõi', 'en' => "I.D.I's Values", 'zh' => 'I.D.I核心价值观']),
            'meta_description' => $this->json([
                'vi' => 'Các giá trị cốt lõi, thế mạnh và nhiệm vụ định hình văn hóa doanh nghiệp I.D.I.',
                'en' => "Discover the core values, strengths and mission that shape I.D.I's corporate culture and sustainable development.",
                'zh' => '了解塑造I.D.I企业文化与可持续发展的核心价值观、企业优势和使命。',
            ]),
            'meta_keywords' => $this->json([
                'vi' => 'I.D.I, IDI Seafood, giá trị cốt lõi, thế mạnh, nhiệm vụ',
                'en' => 'I.D.I, IDI Seafood, core values, strengths, mission',
                'zh' => 'I.D.I, IDI Seafood, 核心价值观, 企业优势, 使命',
            ]),
            'og_title' => null,
            'og_description' => null,
            'translation_status' => $this->json(['vi' => 'published', 'en' => 'published', 'zh' => 'published']),
            'locale_published_at' => $this->json(['vi' => now()->toIso8601String(), 'en' => now()->toIso8601String(), 'zh' => now()->toIso8601String()]),
            'sort_order' => 2,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);

        $sustainabilityId = $this->upsertId('pages', ['code' => 'SUSTAINABILITY'], [
            'parent_id' => null,
            'featured_media_id' => $mediaId,
            'template' => 'default',
            'title' => $this->translations('Phát triển bền vững', 'Sustainability', '可持续发展'),
            'slug' => $this->translations('phat-trien-ben-vung', 'sustainability', 'kechixu-fazhan'),
            'summary' => $this->translations(
                'Cam kết với môi trường, cộng đồng và chất lượng.',
                'Committed to the environment, communities, and quality.',
                '致力于环境、社区与品质。'
            ),
            'content' => null,
            'seo_title' => null,
            'meta_description' => null,
            'og_title' => null,
            'og_description' => null,
            'translation_status' => $this->publishedStatus(),
            'locale_published_at' => $this->publishedDates(),
            'sort_order' => 1,
            'is_active' => true,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'deleted_at' => null,
        ]);

        DB::table('page_sections')->where('page_id', $messageId)->delete();

        foreach ([
            [$sustainabilityId, 'environment', 'Môi trường', 'Environment', '环境', 0],
            [$sustainabilityId, 'community', 'Cộng đồng', 'Community', '社区', 1],
        ] as [$pageId, $type, $vi, $en, $zh, $sortOrder]) {
            $this->upsertId('page_sections', [
                'page_id' => $pageId,
                'section_type' => $type,
                'sort_order' => $sortOrder,
            ], [
                'title' => $this->translations($vi, $en, $zh),
                'content' => $this->translations(
                    "Nội dung mẫu cho phần {$vi}.",
                    "Sample content for {$en}.",
                    "{$zh}部分的示例内容。"
                ),
                'payload' => null,
                'is_active' => true,
            ]);
        }
    }

    private function publishedStatus(): string
    {
        return $this->json(['vi' => 'published', 'en' => 'published', 'zh' => 'published']);
    }

    private function publishedDates(): string
    {
        $date = now()->subDays(14)->toIso8601String();

        return $this->json(['vi' => $date, 'en' => $date, 'zh' => $date]);
    }
}
