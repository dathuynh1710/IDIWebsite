import InvestorDocumentsPage from '@components/investors/InvestorDocumentsPage'
import { useLanguage } from '@hooks/useLanguage'

export default function AnnualReportsPage() {
  const { t } = useLanguage()

  return (
    <InvestorDocumentsPage
      category="bao-cao-thuong-nien"
      title={t('investorPages.annualReports.title')}
      description={t('investorPages.annualReports.description')}
      libraryTitle={t('investorPages.annualReports.libraryTitle')}
    />
  )
}
