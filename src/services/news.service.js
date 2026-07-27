/**
 * News data gateway.
 *
 * - Without VITE_API_BASE_URL, the website uses the local seed data.
 * - When the backend is available, set VITE_API_BASE_URL and keep the same
 *   normalized article contract used by the UI.
 */
import { NEWS_DATA } from '@data/news'
import api from './api'
import { API_BASE_URL } from '@utils/constants'

const CATEGORY_CONTENT = {
  'Giải thưởng': {
    heading: 'Dấu ấn được cộng đồng quốc tế ghi nhận',
    detail:
      'Sự ghi nhận này phản ánh định hướng đầu tư dài hạn của IDI vào quản trị minh bạch, hiệu quả vận hành và các sáng kiến tạo tác động tích cực cho chuỗi giá trị thủy sản.',
    points: [
      'Khẳng định uy tín của doanh nghiệp trên thị trường quốc tế.',
      'Tạo thêm động lực cho các chương trình phát triển bền vững.',
      'Gia tăng niềm tin của đối tác, nhà đầu tư và khách hàng.',
    ],
  },
  'Tin doanh nghiệp': {
    heading: 'Bước tiến trong chiến lược phát triển của IDI',
    detail:
      'Hoạt động này nằm trong lộ trình mở rộng quy mô, đa dạng hóa thị trường và nâng cao khả năng phục vụ khách hàng bằng một chuỗi cung ứng chủ động, ổn định.',
    points: [
      'Mở rộng năng lực tiếp cận các thị trường trọng điểm.',
      'Tăng tính linh hoạt trong sản xuất và phân phối.',
      'Củng cố nền tảng tăng trưởng bền vững trong dài hạn.',
    ],
  },
  ESG: {
    heading: 'Tăng trưởng gắn với trách nhiệm',
    detail:
      'IDI theo đuổi cách tiếp cận cân bằng giữa hiệu quả kinh doanh, bảo vệ môi trường và sinh kế của cộng đồng trong toàn bộ chuỗi giá trị.',
    points: [
      'Sử dụng nguồn lực hiệu quả và giảm tác động môi trường.',
      'Tăng khả năng truy xuất, đo lường và công bố minh bạch.',
      'Lan tỏa lợi ích đến người lao động và vùng nuôi liên kết.',
    ],
  },
  'Thị trường': {
    heading: 'Những chuyển động đáng chú ý của thị trường',
    detail:
      'Nhu cầu thực phẩm an toàn, có thể truy xuất và có mức giá hợp lý tiếp tục mở ra cơ hội cho cá tra Việt Nam tại nhiều khu vực tiêu thụ.',
    points: [
      'Người mua ưu tiên nguồn cung ổn định và minh bạch.',
      'Sản phẩm tiện lợi, đa quy cách ngày càng được quan tâm.',
      'Tiêu chuẩn bền vững trở thành lợi thế cạnh tranh rõ nét.',
    ],
  },
  'Công nghệ': {
    heading: 'Công nghệ tạo nên chất lượng ổn định',
    detail:
      'Từ kiểm soát nguyên liệu đến chế biến, cấp đông và bảo quản, công nghệ giúp duy trì các chỉ tiêu chất lượng nhất quán trong suốt hành trình của sản phẩm.',
    points: [
      'Kiểm soát chặt chẽ các thông số quan trọng của quy trình.',
      'Duy trì độ tươi, cấu trúc và giá trị dinh dưỡng.',
      'Tăng hiệu quả vận hành và giảm hao hụt nguyên liệu.',
    ],
  },
  'Sản phẩm': {
    heading: 'Giải pháp sản phẩm linh hoạt cho từng thị trường',
    detail:
      'Danh mục sản phẩm được phát triển theo nhu cầu thực tế của nhà nhập khẩu, hệ thống bán lẻ, dịch vụ ăn uống và người tiêu dùng cuối.',
    points: [
      'Đa dạng quy cách cắt, kích thước và phương thức đóng gói.',
      'Phù hợp với nhiều kênh phân phối và thói quen tiêu dùng.',
      'Duy trì chất lượng đồng đều trên quy mô thương mại.',
    ],
  },
}

function createFallbackContent(article) {
  const category = CATEGORY_CONTENT[article.category] ?? CATEGORY_CONTENT['Tin doanh nghiệp']

  return [
    {
      type: 'paragraph',
      text: article.excerpt,
      lead: true,
    },
    {
      type: 'heading',
      id: 'tong-quan',
      text: category.heading,
    },
    {
      type: 'paragraph',
      text: category.detail,
    },
    {
      type: 'quote',
      text: 'Chúng tôi xem chất lượng, tính minh bạch và trách nhiệm là nền tảng cho mọi quyết định phát triển.',
      attribution: 'IDI Seafood',
    },
    {
      type: 'heading',
      id: 'gia-tri-noi-bat',
      text: 'Những giá trị nổi bật',
    },
    {
      type: 'list',
      items: category.points,
    },
    {
      type: 'paragraph',
      text: `Với chủ đề “${article.title}”, IDI tiếp tục cho thấy cam kết phát triển chuỗi giá trị cá tra Việt Nam theo hướng hiện đại, hiệu quả và đáp ứng tốt hơn kỳ vọng của thị trường.`,
    },
    {
      type: 'callout',
      title: 'Thông tin dành cho đối tác',
      text: 'Đội ngũ IDI sẵn sàng cung cấp thêm hồ sơ sản phẩm, tiêu chuẩn chất lượng và thông tin thương mại theo từng thị trường.',
      link: '/contact',
      linkLabel: 'Liên hệ IDI',
    },
  ]
}

function normalizeContent(content, article) {
  if (Array.isArray(content) && content.length) return content

  if (typeof content === 'string' && content.trim()) {
    return content
      .split(/\n{2,}/)
      .map(text => text.trim())
      .filter(Boolean)
      .map(text => ({ type: 'paragraph', text }))
  }

  return createFallbackContent(article)
}

function normalizeArticle(payload) {
  const raw = payload?.data ?? payload
  if (!raw) return null

  const article = {
    ...raw,
    id: raw.id ?? raw._id ?? raw.slug,
    slug: raw.slug,
    title: raw.title,
    excerpt: raw.excerpt ?? raw.summary ?? raw.description ?? '',
    category:
      raw.category?.name ?? raw.categoryName ?? raw.category ?? 'Tin doanh nghiệp',
    categoryColor: raw.category?.color ?? raw.categoryColor ?? 'blue',
    date: raw.date ?? raw.publishedAt ?? raw.createdAt,
    updatedAt: raw.updatedAt,
    readTime: raw.readTime ?? raw.readingTime ?? 5,
    image:
      raw.image?.url ?? raw.featuredImage?.url ?? raw.featuredImage ?? raw.thumbnail ?? raw.image,
    imageAlt: raw.image?.alt ?? raw.featuredImage?.alt ?? raw.imageAlt ?? raw.title,
    author: raw.author?.name ?? raw.authorName ?? raw.author ?? 'Ban Truyền thông IDI',
    authorRole: raw.author?.role ?? raw.authorRole ?? 'IDI Seafood',
    tags: Array.isArray(raw.tags)
      ? raw.tags.map(tag => (typeof tag === 'string' ? tag : tag.name)).filter(Boolean)
      : [],
  }

  article.content = normalizeContent(
    raw.contentBlocks ?? raw.content ?? raw.body ?? raw.sections,
    article,
  )

  return article
}

async function getAllFromApi(params = {}) {
  const query = new URLSearchParams()
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') query.set(key, value)
  })

  const response = await api.get(`/news${query.size ? `?${query}` : ''}`)
  const data = response?.data ?? response
  const items = data?.items ?? data?.results ?? (Array.isArray(data) ? data : [])

  return {
    items: items.map(normalizeArticle),
    total: data?.total ?? items.length,
    page: data?.page ?? params.page ?? 1,
    limit: data?.limit ?? params.limit ?? 12,
  }
}

export const newsService = {
  getAll: async ({ page = 1, limit = 12, category } = {}) => {
    if (API_BASE_URL) return getAllFromApi({ page, limit, category })

    let results = NEWS_DATA.map(normalizeArticle).sort(
      (a, b) => new Date(b.date) - new Date(a.date),
    )
    if (category) results = results.filter(article => article.category === category)

    return {
      items: results.slice((page - 1) * limit, page * limit),
      total: results.length,
      page,
      limit,
    }
  },

  getBySlug: async (slug) => {
    if (API_BASE_URL) {
      const response = await api.get(`/news/${encodeURIComponent(slug)}`)
      const article = normalizeArticle(response)
      if (!article) {
        const error = new Error('Không tìm thấy bài viết')
        error.status = 404
        throw error
      }
      return article
    }

    const item = NEWS_DATA.find(article => article.slug === slug)
    if (!item) {
      const error = new Error('Không tìm thấy bài viết')
      error.status = 404
      throw error
    }
    return normalizeArticle(item)
  },

  getRelated: async (article, limit = 3) => {
    const response = await newsService.getAll({
      limit: 100,
      category: article.category,
    })
    const sameCategory = response.items.filter(item => item.slug !== article.slug)

    if (sameCategory.length >= limit || API_BASE_URL) return sameCategory.slice(0, limit)

    const all = await newsService.getAll({ limit: 100 })
    const fallback = all.items.filter(
      item =>
        item.slug !== article.slug &&
        !sameCategory.some(related => related.slug === item.slug),
    )
    return [...sameCategory, ...fallback].slice(0, limit)
  },

  getFeatured: async (limit = 3) => {
    const response = await newsService.getAll({ page: 1, limit })
    return response.items
  },
}
