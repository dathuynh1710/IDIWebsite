import InvestorDocumentsPage from '@components/investors/InvestorDocumentsPage'
import { useLanguage } from '@hooks/useLanguage'

export default function GreenBondPage() {
  const { t } = useLanguage()

  return (
    <InvestorDocumentsPage
      category="trai-phieu"
      title={t('investorPages.greenBond.title')}
      description={t('investorPages.greenBond.description')}
      libraryTitle={t('investorPages.greenBond.libraryTitle')}
    />
  )
}
