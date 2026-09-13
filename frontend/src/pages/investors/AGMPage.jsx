import InvestorDocumentsPage from '@components/investors/InvestorDocumentsPage'
import { useLanguage } from '@hooks/useLanguage'

export default function AGMPage() {
  const { t } = useLanguage()

  return (
    <InvestorDocumentsPage
      category="dai-hoi-co-dong"
      title={t('investorPages.agm.title')}
      description={t('investorPages.agm.description')}
      libraryTitle={t('investorPages.agm.libraryTitle')}
    />
  )
}
