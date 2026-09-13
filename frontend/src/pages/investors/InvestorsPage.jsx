import InvestorDocumentsPage from '@components/investors/InvestorDocumentsPage'
import { useLanguage } from '@hooks/useLanguage'

export default function InvestorsPage() {
  const { t } = useLanguage()

  return (
    <InvestorDocumentsPage
      title="Quan hệ cổ đông"
      description="Mang thành tâm biến thành lợi nhuận. I.D.I gặt hái được thành công của mình nhờ vào việc phát triển và tuân theo một chiến lược toàn diện, gắn kết các mục tiêu và nhiệm vụ với các tôn chỉ hoạt động vì Hành tinh, Con người và Sản phẩm."
      libraryTitle={t('investorPages.overview.latestDisclosures')}
    />
  )
}
