import InvestorDocumentsPage from '@components/investors/InvestorDocumentsPage'
import { useLanguage } from '@hooks/useLanguage'

export default function AnnouncementsPage() {
  const { t } = useLanguage()

  return (
    <InvestorDocumentsPage
      category="thong-bao"
      title={t('investorPages.announcements.title')}
      description={t('investorPages.announcements.description')}
      libraryTitle={t('investorPages.announcements.libraryTitle')}
    />
  )
}
