import InvestorDocumentsPage from '@components/investors/InvestorDocumentsPage'
import { useLanguage } from '@hooks/useLanguage'

export default function FinancialsPage() {
  const { t } = useLanguage()

  return (
    <InvestorDocumentsPage
      category="bao-cao-tai-chinh"
      title={t('investorPages.financials.title')}
      description={t('investorPages.financials.description')}
      libraryTitle={t('investorPages.financials.libraryTitle')}
    />
  )
}
